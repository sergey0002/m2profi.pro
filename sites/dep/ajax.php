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
            if ($k) {
                $env[trim($k)] = trim($v);
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

    // Извлекаем owner и repo из URL (например, https://github.com/sergey0002/noff-site.git)
    if (!preg_match('#github\.com/([^/]+)/([^/]+)#i', $repoUrl, $matches)) {
        echo json_encode(['status' => 'error', 'message' => 'Неверный формат REPO_URL. Ожидается URL GitHub.']);
        exit;
    }

    $owner = $matches[1];
    $repo = preg_replace('/\.git$/i', '', $matches[2]);

    // Функция для выполнения cURL запроса к GitHub API
    function githubApiRequest($url, $token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Deployment Script');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: token {$token}",
            "Accept: application/vnd.github.v3+json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL (частая проблема в Laragon/Windows)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return ['code' => $httpCode, 'data' => json_decode($result, true), 'error' => $error];
    }

    // 1. Получаем список веток
    $branchesUrl = "https://api.github.com/repos/{$owner}/{$repo}/branches";
    $response = githubApiRequest($branchesUrl, $githubToken);

    if (!empty($response['error'])) {
        echo json_encode(['status' => 'error', 'message' => "Сетевая ошибка cURL: " . $response['error']]);
        exit;
    }

    if ($response['code'] === 401 || $response['code'] === 403 || $response['code'] === 404) {
        $msg = $response['data']['message'] ?? 'Неизвестная ошибка';
        echo json_encode(['status' => 'error', 'message' => "Ошибка доступа к API ({$response['code']}): {$msg}"]);
        exit;
    }

    if ($response['code'] !== 200 || !is_array($response['data'])) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при получении списка веток от GitHub. Код: ' . $response['code']]);
        exit;
    }

    $branchesList = $response['data'];
    $branches = [];

    // 2. Для каждой ветки получаем детальную информацию о коммите конвейерно (или по одному)
    // В идеале можно использовать GraphQL API GitHub для получения всего сразу, но REST проще.
    // Если веток много, это может занять пару секунд.
    foreach ($branchesList as $branchData) {
        $branchName = $branchData['name'];
        $commitSha = $branchData['commit']['sha'];

        // Получаем детали коммита
        $commitUrl = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$commitSha}";
        $commitResponse = githubApiRequest($commitUrl, $githubToken);

        $date = 'N/A';
        $timestamp = 0;
        $author = 'unknown';
        $subject = $commitSha;

        if ($commitResponse['code'] === 200 && isset($commitResponse['data']['commit'])) {
            $commitDetails = $commitResponse['data']['commit'];
            $author = $commitDetails['author']['name'] ?? $author;
            // Форматируем дату в дд.мм.гггг чч:мм:сс
            if (isset($commitDetails['author']['date'])) {
                $timestamp = strtotime($commitDetails['author']['date']);
                $date = date('d.m.Y H:i:s', $timestamp);
            }
            // Берем только первую строку из сообщения коммита
            if (isset($commitDetails['message'])) {
                $lines = explode("\n", str_replace("\r", "", $commitDetails['message']));
                $subject = $lines[0];
            }
        }

        $branches[] = [
            'name' => $branchName,
            'hash' => $subject,
            'date' => $date,
            'timestamp' => $timestamp,
            'author' => $author
        ];
    }

    // Сортируем ветки по дате (от реальных timestamp новых к старым)
    usort($branches, function ($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    echo json_encode(['status' => 'ok', 'data' => $branches]);
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
