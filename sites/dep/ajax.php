<?php
session_start();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$project = $_POST['project'] ?? 'noffprod';

if (empty($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Несанкционированный доступ']);
    exit;
}
$user = $_SESSION['user'];

function loadEnv($file)
{
    $env = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0)
                continue;
            @list($k, $v) = explode('=', $line, 2);
            $k = trim((string) ($k ?? ''));
            if ($k !== '') {
                $env[$k] = trim((string) ($v ?? ''));
            }
        }
    }
    return $env;
}

$env = loadEnv(__DIR__ . "/projects/{$project}.env");

// Проверка прав доступа к проекту
$allowedUsersStr = $env['ALLOWED_USERS'] ?? '';

if (!empty($allowedUsersStr)) {
    $allowedArr = array_map('trim', explode(',', $allowedUsersStr));
    if (!in_array($user, $allowedArr)) {
        echo json_encode(['status' => 'error', 'message' => 'У вас нет доступа к проекту: ' . $project]);
        exit;
    }
}

if ($action === 'get_branches') {
    $githubToken = $env['GITHUB_TOKEN'] ?? '';
    $repoUrl = $env['REPO_URL'] ?? '';

    if (empty($githubToken) || empty($repoUrl)) {
        echo json_encode(['status' => 'error', 'message' => 'GITHUB_TOKEN или REPO_URL не задан']);
        exit;
    }

    if (!preg_match('#github\.com/([^/]+)/([^/]+)#i', $repoUrl, $matches)) {
        echo json_encode(['status' => 'error', 'message' => 'Неверный формат REPO_URL. Ожидается URL GitHub.']);
        exit;
    }

    $owner = $matches[1];
    $repo = preg_replace('/\.git$/i', '', $matches[2]);

    function githubApiRequest($url, $token, $payload = null)
    {
        $ch = curl_init();
        $headers = [
            "Authorization: Bearer {$token}",
            "Accept: application/vnd.github+json",
            "User-Agent: PHP Deployment Script",
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        // На части хостингов IPv6 до api.github.com висит до таймаута
        if (defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return ['code' => $httpCode, 'data' => json_decode($result, true), 'error' => $error];
    }

    function firstDeployPath($dirsStr)
    {
        foreach (explode(',', (string) $dirsStr) as $dirItem) {
            if (!trim($dirItem)) {
                continue;
            }
            @list($name, $rest) = explode(':', $dirItem, 2);
            @list($path, $type) = explode('|', (string) $rest, 2);
            $path = trim((string) $path);
            if ($path !== '') {
                return $path;
            }
        }
        return '';
    }

    function runSshCapture($env, $remoteBash)
    {
        $sshHost = $env['SSH_HOST'] ?? '';
        $sshUser = $env['SSH_USER'] ?? '';
        $sshPass = $env['SSH_PASS'] ?? '';
        if ($sshHost === '' || $sshUser === '') {
            return ['ok' => false, 'output' => '', 'error' => 'SSH_HOST/SSH_USER не заданы'];
        }

        $quoted = escapeshellarg($remoteBash);
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $plink = __DIR__ . '/plink.exe';
            if (file_exists($plink)) {
                $cmd = "\"$plink\" -ssh -batch -pw \"$sshPass\" $sshUser@$sshHost bash -lc $quoted 2>&1";
            } else {
                $cmd = "ssh -o StrictHostKeyChecking=no $sshUser@$sshHost bash -lc $quoted 2>&1";
            }
        } else {
            if (trim((string) shell_exec('which sshpass 2>/dev/null'))) {
                $cmd = 'sshpass -p ' . escapeshellarg($sshPass)
                    . ' ssh -o StrictHostKeyChecking=no '
                    . escapeshellarg($sshUser . '@' . $sshHost)
                    . ' bash -lc ' . $quoted . ' 2>&1';
            } else {
                $cmd = 'ssh -o StrictHostKeyChecking=no '
                    . escapeshellarg($sshUser . '@' . $sshHost)
                    . ' bash -lc ' . $quoted . ' 2>&1';
            }
        }

        $output = (string) shell_exec($cmd);
        return ['ok' => $output !== '', 'output' => $output, 'error' => ''];
    }

    function parseBranchLines($output)
    {
        $branches = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($output)) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '|') === false) {
                continue;
            }
            $parts = explode('|', $line);
            if (count($parts) < 2) {
                continue;
            }
            $name = trim($parts[0]);
            if ($name === '' || $name === 'HEAD') {
                continue;
            }
            $timestamp = isset($parts[4]) ? (int) $parts[4] : 0;
            $iso = $parts[2] ?? '';
            $date = 'N/A';
            if ($timestamp > 0) {
                $date = date('d.m.Y H:i:s', $timestamp);
            } elseif ($iso !== '') {
                $ts = strtotime($iso);
                if ($ts) {
                    $timestamp = $ts;
                    $date = date('d.m.Y H:i:s', $ts);
                }
            }
            $branches[] = [
                'name' => $name,
                'hash' => $parts[1] !== '' ? $parts[1] : $name,
                'date' => $date,
                'timestamp' => $timestamp,
                'author' => isset($parts[3]) && $parts[3] !== '' ? $parts[3] : 'unknown',
            ];
        }

        $uniq = [];
        foreach ($branches as $b) {
            if (!isset($uniq[$b['name']])) {
                $uniq[$b['name']] = $b;
            }
        }
        $branches = array_values($uniq);
        usort($branches, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        return $branches;
    }

    function getBranchesViaRemoteGit($env, $repoUrl, $token)
    {
        $path = firstDeployPath($env['DIRS'] ?? '');
        if ($path === '') {
            return ['status' => 'error', 'message' => 'Нет DIRS для fallback через SSH/git'];
        }

        // github.com (git) часто доступен, api.github.com — нет. Берём ветки с деплой-хоста.
        $authUrl = preg_replace('#https://#', 'https://' . $token . '@', $repoUrl, 1);
        $remoteBash = 'cd ' . escapeshellarg($path)
            . ' && (git for-each-ref --sort=-committerdate refs/heads/ refs/remotes/origin/'
            . ' --format="%(refname:short)|%(subject)|%(committerdate:iso-strict)|%(authorname)|%(committerdate:unix)"'
            . ' | sed "s|^origin/||" | grep -v "^HEAD$" | awk -F"|" "!seen[\$1]++"'
            . ' ; echo "__LSREMOTE__"'
            . ' ; git ls-remote --heads ' . escapeshellarg($authUrl)
            . ' | awk \'{print $2"|"$1"|||0"}\' | sed "s|refs/heads/||")';

        $res = runSshCapture($env, $remoteBash);
        if (!$res['ok']) {
            return [
                'status' => 'error',
                'message' => 'api.github.com недоступен, SSH/git fallback пустой'
                    . ($res['error'] ? (': ' . $res['error']) : ''),
            ];
        }

        $parts = explode("__LSREMOTE__", $res['output'], 2);
        $branches = parseBranchLines($parts[0] ?? '');
        if (count($branches) === 0 && isset($parts[1])) {
            $branches = parseBranchLines($parts[1]);
        }
        if (count($branches) === 0) {
            return [
                'status' => 'error',
                'message' => 'Не удалось получить ветки через SSH/git. Вывод: '
                    . substr(trim($res['output']), 0, 300),
            ];
        }

        return ['status' => 'ok', 'data' => $branches, 'source' => 'ssh-git'];
    }

    $graphqlQuery = <<<'GQL'
query ($owner: String!, $name: String!) {
  repository(owner: $owner, name: $name) {
    refs(refPrefix: "refs/heads/", first: 100, orderBy: {field: TAG_COMMIT_DATE, direction: DESC}) {
      nodes {
        name
        target {
          ... on Commit {
            messageHeadline
            committedDate
            author { name }
            oid
          }
        }
      }
    }
  }
}
GQL;

    $response = githubApiRequest('https://api.github.com/graphql', $githubToken, [
        'query' => $graphqlQuery,
        'variables' => ['owner' => $owner, 'name' => $repo],
    ]);

    // api.github.com с сервера часто timeout — fallback на git по SSH (как деплой)
    if (!empty($response['error'])) {
        $fallback = getBranchesViaRemoteGit($env, $repoUrl, $githubToken);
        if (($fallback['status'] ?? '') === 'ok') {
            echo json_encode($fallback);
            exit;
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Сетевая ошибка cURL: ' . $response['error']
                . ' | Fallback: ' . ($fallback['message'] ?? 'недоступен'),
        ]);
        exit;
    }

    if ($response['code'] === 401 || $response['code'] === 403) {
        $msg = $response['data']['message'] ?? 'Неизвестная ошибка';
        echo json_encode(['status' => 'error', 'message' => "Ошибка доступа к API ({$response['code']}): {$msg}"]);
        exit;
    }

    if ($response['code'] !== 200 || !empty($response['data']['errors']) || !is_array($response['data']['data']['repository']['refs']['nodes'] ?? null)) {
        $fallback = getBranchesViaRemoteGit($env, $repoUrl, $githubToken);
        if (($fallback['status'] ?? '') === 'ok') {
            echo json_encode($fallback);
            exit;
        }
        if (!empty($response['data']['errors'])) {
            $msg = $response['data']['errors'][0]['message'] ?? 'GraphQL error';
            echo json_encode(['status' => 'error', 'message' => "GitHub GraphQL: {$msg}"]);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Ошибка GraphQL GitHub. Код: ' . $response['code']]);
        exit;
    }

    $nodes = $response['data']['data']['repository']['refs']['nodes'];
    $branches = [];
    foreach ($nodes as $node) {
        $commit = $node['target'] ?? [];
        $timestamp = 0;
        $date = 'N/A';
        if (!empty($commit['committedDate'])) {
            $timestamp = strtotime($commit['committedDate']);
            $date = date('d.m.Y H:i:s', $timestamp);
        }

        $branches[] = [
            'name' => $node['name'] ?? '',
            'hash' => $commit['messageHeadline'] ?? ($commit['oid'] ?? ''),
            'date' => $date,
            'timestamp' => $timestamp,
            'author' => $commit['author']['name'] ?? 'unknown',
        ];
    }

    usort($branches, function ($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    echo json_encode(['status' => 'ok', 'data' => $branches, 'source' => 'graphql']);
    exit;
}

if ($action === 'deploy') {
    $branch = $_POST['branch'] ?? '';
    $targetName = $_POST['target'] ?? '';

    if (!$branch || !$targetName) {
        echo json_encode(['status' => 'error', 'message' => 'Не указаны ветка или цель']);
        exit;
    }

    $dirsStr = $env['DIRS'] ?? '';
    $targetFolder = '';
    $isProd = false;

    foreach (explode(',', $dirsStr) as $dirItem) {
        if (!trim($dirItem))
            continue;
        @list($name, $rest) = explode(':', $dirItem, 2);
        @list($path, $type) = explode('|', $rest, 2);
        if ($name === $targetName) {
            $targetFolder = $path;
            $isProd = (trim($type) === 'prod');
            break;
        }
    }

    if (!$targetFolder) {
        echo json_encode(['status' => 'error', 'message' => 'Целевая папка не найдена']);
        exit;
    }

    $prodAllowedUsersStr = $env['PROD_ALLOWED_USERS'] ?? '';
    if ($isProd) {
        $allowedUsers = array_map('trim', explode(',', $prodAllowedUsersStr));
        if (!in_array($user, $allowedUsers)) {
            echo json_encode(['status' => 'error', 'message' => 'У вас нет прав на деплой в PROD окружения']);
            exit;
        }
    }

    $logFile = __DIR__ . '/lastlog.log';
    file_put_contents($logFile, "Начало деплоя. Ветка: $branch -> $targetName\n");

    $sshHost = $env['SSH_HOST'] ?? '';
    $sshUser = $env['SSH_USER'] ?? '';
    $sshPass = $env['SSH_PASS'] ?? '';
    $remoteScript = __DIR__ . '/deploy_remote.sh';

    $remoteCommand = "bash -s " . escapeshellarg($targetFolder) . " " . escapeshellarg($branch) . " " . escapeshellarg($user) . " " . escapeshellarg($allowedUsersStr) . " " . ($isProd ? "1" : "0") . " " . escapeshellarg($prodAllowedUsersStr);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $plink = __DIR__ . '/plink.exe';
        if (file_exists($plink)) {
            $cmd = "\"$plink\" -ssh -batch -pw \"$sshPass\" $sshUser@$sshHost $remoteCommand < \"$remoteScript\" >> \"$logFile\" 2>&1";
        } else {
            $cmd = "ssh -o StrictHostKeyChecking=no $sshUser@$sshHost $remoteCommand < \"$remoteScript\" >> \"$logFile\" 2>&1";
        }
    } else {
        // Linux
        if (trim(shell_exec("which sshpass"))) {
            $cmd = "sshpass -p " . escapeshellarg($sshPass) . " ssh -o StrictHostKeyChecking=no $sshUser@$sshHost $remoteCommand < " . escapeshellarg($remoteScript) . " >> " . escapeshellarg($logFile) . " 2>&1";
        } else {
            $cmd = "ssh -o StrictHostKeyChecking=no $sshUser@$sshHost $remoteCommand < " . escapeshellarg($remoteScript) . " >> " . escapeshellarg($logFile) . " 2>&1";
        }
    }

    // Синхронное выполнение для простоты (можно реализовать асинхронное)
    shell_exec($cmd);

    echo json_encode(['status' => 'ok', 'message' => 'Деплой завершен']);
    exit;
}

if ($action === 'get_log') {
    $log = '';
    if (file_exists(__DIR__ . '/lastlog.log')) {
        $log = file_get_contents(__DIR__ . '/lastlog.log');
    }
    echo json_encode(['status' => 'ok', 'log' => $log]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Неизвестное действие']);
