<?php
// Shared by the HTML view and the API. Values are exact DIRS target names.
function fcList($s) { return array_values(array_filter(array_map('trim',explode(',', $s)), 'strlen')); }
function fcConfig($env) {
    $enabled=fcList($env['FTP_CONTROL_TARGETS'] ?? ''); $dirs=[];
    if (!$enabled) return [];
    foreach (fcList($env['DIRS'] ?? '') as $entry) {
        if (!preg_match('~^([^:]+):(/[^|]+)\|(test|prod)$~D',$entry,$m) || isset($dirs[trim($m[1])])) throw new Exception('Ошибка конфигурации DIRS.');
        $dirs[trim($m[1])]=[$m[2],$m[3]];
    }
    if (array_diff($enabled,array_keys($dirs)) || count($enabled)!==count(array_unique($enabled))) throw new Exception('Ошибка конфигурации FTP_CONTROL_TARGETS.');
    return array_intersect_key($dirs,array_flip($enabled));
}
function fcRun($action,$target,$dir,$env,$user,$project) {
    if (!is_string($_POST['csrf'] ?? null) || empty($_SESSION['fc_csrf']) || !hash_equals($_SESSION['fc_csrf'],$_POST['csrf'])) throw new Exception('Обновите страницу: проверка CSRF не пройдена.');
    if ($dir[1]==='prod') throw new Exception('Первая версия защищённой публикации разрешена только для TEST.');
    session_write_close();
    $repo=$env['REPO_URL'] ?? ''; $host=$env['SSH_HOST'] ?? ''; $login=$env['SSH_USER'] ?? '';
    if (!preg_match('~^https://github\.com/[a-zA-Z0-9_.-]+/[a-zA-Z0-9_.-]+$~D',$repo) || !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/D',$host) || !preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*$/D',$login)) throw new Exception('Проверьте SSH_HOST, SSH_USER и REPO_URL.');
    $other=[];
    foreach (glob(dirname(__DIR__).'/projects/*.env') as $f) {
        $e=loadEnv($f); if (($e['SSH_HOST'] ?? '')!==$host) continue;
        foreach (fcList($e['DIRS'] ?? '') as $d) { if (!preg_match('~^([^:]+):(/[^|]+)\|~',$d,$m)) continue;
            if (basename($f,'.env')!==$project || trim($m[1])!==$target) $other[]=$m[2]; }
    }
    $q=['action'=>$action,'target'=>$target,'user'=>$user,'path'=>$dir[0],'repo'=>$repo,'other'=>$other,
        'name'=>trim($env['GIT_COMMIT_NAME'] ?? '') ?: 'fromserver','email'=>trim($env['GIT_COMMIT_EMAIL'] ?? '') ?: 'from@server.ru',
        'write_branches'=>fcList($env['FTP_WRITE_BRANCHES'] ?? ''),'accept_missing'=>($_POST['accept_missing'] ?? '')==='1',
        'allow'=>fcList($env['FTP_NEW_FILES'] ?? ''),'branch'=>$_POST['branch'] ?? '','token'=>$_POST['token'] ?? '','base_oid'=>$_POST['base_oid'] ?? '', 'maintenance'=>($_POST['maintenance'] ?? '')==='1'];
    if (!is_string($q['branch']) || strlen($q['branch'])>220) throw new Exception('Недопустимая ветка.');
    return fcRemote('worker.py',$q,$login,$host);
}

// Both scripts and the SSH command are selected by the server, not the request.
function fcRemote($file,$q,$login,$host) {
    if (!in_array($file,['worker.py','directory_status.py'],true) || !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/D',$host) || !preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*$/D',$login)) throw new Exception('Некорректный SSH-хост.');
    $script=file_get_contents(__DIR__.'/'.$file); if (!$script) throw new Exception('Не найден worker.');
    // Only a fixed command goes through the remote shell; all data travels on stdin.
    $input=tmpfile();$out=tmpfile();$err=tmpfile();fwrite($input,$script."\nraise SystemExit(main(json.loads(base64.b64decode('".base64_encode(json_encode($q, JSON_THROW_ON_ERROR))."'))))\n");rewind($input);
    $proc=proc_open(['ssh','-T','-o','BatchMode=yes','-o','StrictHostKeyChecking=yes','-o','ConnectTimeout=8','-o','ServerAliveInterval=15','-o','ServerAliveCountMax=2',$login.'@'.$host,'python3 -I -'],[$input,$out,$err],$pipes);
    if (!is_resource($proc)) throw new Exception('Не удалось запустить OpenSSH.');
    ignore_user_abort(true);set_time_limit(0);$end=microtime(true)+180;
    do { $s=proc_get_status($proc); if (!$s['running']) break; if (microtime(true)>$end) {proc_terminate($proc,9);proc_close($proc);throw new Exception('Таймаут. Результат неизвестен; проверьте состояние цели перед повтором.');} usleep(50000); } while (true);
    $rc=$s['exitcode'];proc_close($proc);rewind($out);$r=json_decode(stream_get_contents($out,1048576),true);
    if (!is_array($r) || !isset($r['status']) || ($rc!==0 && $r['status']!=='error')) throw new Exception('Нет подтверждения от хостинга. Проверьте SSH-ключ панели, known_hosts и Python 3.9+.');
    return $r;
}

function directoryStatuses($env,$fc,$user) {
    if (!is_string($_POST['csrf'] ?? null) || empty($_SESSION['fc_csrf']) || !hash_equals($_SESSION['fc_csrf'],$_POST['csrf'])) throw new Exception('Обновите страницу.');
    $prod=fcList($env['PROD_ALLOWED_USERS'] ?? ''); $targets=[];
    foreach (fcList($env['DIRS'] ?? '') as $entry) {
        if (!preg_match('~^([^:]+):(/[^|]+)\|(test|prod)$~D',$entry,$m)) continue;
        if ($m[3]==='prod' && !in_array($user,$prod,true)) continue;
        $name=trim($m[1]); $targets[]=['name'=>$name,'path'=>$m[2],'guard'=>isset($fc[$name])];
    }
    session_write_close();
    if (!$targets) return ['status'=>'ok','targets'=>[]];
    return fcRemote('directory_status.py',['targets'=>$targets,'repo'=>$env['REPO_URL'] ?? ''],$env['SSH_USER'] ?? '',$env['SSH_HOST'] ?? '');
}
