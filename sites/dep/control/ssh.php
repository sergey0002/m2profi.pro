<?php
// Only transport boundary: PHP never passes the script or user data to a local shell.
function bashLiteral($v){if(!is_scalar($v) || preg_match('/[\x00-\x1f\x7f]/',(string)$v))throw new Exception('Недопустимое значение параметра.');return "'".str_replace("'", "'\\''",(string)$v)."'";}
function projectSsh($env,$script,$mode='protocol',$targets=[]) {
    $host=$env['SSH_HOST'] ?? ''; $login=$env['SSH_USER'] ?? ''; $password=$env['SSH_PASS'] ?? ''; $command='bash -s';
    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/D',$host) || !preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*$/D',$login))throw new RuntimeException('Проверьте SSH_HOST и SSH_USER проекта.');
    if (!function_exists('proc_open'))throw new RuntimeException('На сервере панели отключён proc_open.');
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    $input=tmpfile();$out=tmpfile();$err=tmpfile();fwrite($input,$script);rewind($input);
    $dest=$login.'@'.$host; $fds=[$input,$mode==='terminal'?$err:$out,$err]; $auth='key';$helper=null;
    if ($password!=='' && PHP_OS_FAMILY!=='Windows') {
        foreach(explode(PATH_SEPARATOR,getenv('PATH') ?: '/usr/bin:/bin') as $dir){
            if(substr($dir,0,1)!=='/')continue;
            $candidate=rtrim($dir,'/').'/sshpass';
            if(@is_file($candidate) && @is_executable($candidate)) {$helper=$candidate;break;}
        }
    }
    $plink=dirname(__DIR__).'/plink.exe';
    if($password!=='' && ($helper!==null || (PHP_OS_FAMILY==='Windows' && is_file($plink)))){
        if(strpbrk($password,"\r\n\0")!==false) throw new Exception('SSH_PASS должен быть однострочным.');
        $pw=tmpfile();if(!$pw || fwrite($pw,$password."\n")!==strlen($password)+1) throw new Exception('Не удалось подготовить SSH-аутентификацию.');rewind($pw);
        $auth='configured';
    }
    if(PHP_OS_FAMILY==='Windows' && $auth==='configured'){
        $argv=[$plink,'-ssh','-batch','-pwfile',stream_get_meta_data($pw)['uri'],$dest,$command];
        opsStep('$ plink -ssh -batch -pwfile [скрыто] '.$dest.' '.$command);
    }else{
        $args=['ssh','-T','-o','BatchMode='.($auth==='configured'?'no':'yes'),'-o','StrictHostKeyChecking=yes','-o','NumberOfPasswordPrompts=1','-o','ConnectTimeout=8','-o','ServerAliveInterval=15','-o','ServerAliveCountMax=2',$dest,$command];
        $argv=$args;
        if($auth==='configured'){$fds[3]=$pw;$argv=array_merge([$helper,'-d','3'],$args);}
        opsStep('$ '.($auth==='configured'?'sshpass -d 3 ':'').implode(' ',$args));
    }
    $reader=fopen(stream_get_meta_data($err)['uri'],'rb');
    $proc=proc_open($argv,$fds,$pipes);
    if (!is_resource($proc)) throw new Exception('Не удалось запустить OpenSSH.');
    ignore_user_abort(true);set_time_limit(0);$end=microtime(true)+180;
    try {
        do {
            opsDrain($reader,$targets);
            $status=proc_get_status($proc);
            if(!$status['running'])break;
            if(microtime(true)>$end){proc_terminate($proc,9);throw new RuntimeException('Таймаут SSH. Результат неизвестен; проверьте цель до повтора.');}
            usleep(50000);
        } while(true);
        $rc=$status['exitcode'];
    } finally {
        proc_close($proc);opsDrain($reader,$targets,true);fclose($reader);
    }
    opsStep('Завершение SSH: код '.$rc);
    rewind($err);$diag=stream_get_contents($err,8192);
    if($mode==='terminal'){rewind($err);$raw=opsRedact(stream_get_contents($err));}
    else {rewind($out);$raw=stream_get_contents($out,1048577);if(strlen($raw)>1048576)throw new RuntimeException('Слишком большой ответ хостинга.');}
    if($rc!==0){
        if (preg_match('/host key verification failed|host identification has changed/i',$diag)) throw new Exception('Не подтверждён ключ SSH-хоста. Сверьте отпечаток и known_hosts пользователя панели.');
        if ($auth==='configured' && ($rc===5 || preg_match('/access denied|permission denied \(|authentication failed/i',$diag))) throw new Exception('SSH отклонил вход. Проверьте SSH_USER, SSH_PASS и разрешённые сервером способы входа.');
        if (preg_match('/permission denied \(|no supported authentication/i',$diag)) throw new Exception($password!==''?'SSH не принял ключ. Для входа по SSH_PASS на Linux-панели нужен sshpass в PATH.':'SSH не принял ключ; SSH_PASS проекта не задан.');
        if (preg_match('/could not resolve hostname|connection timed out|connection refused|no route to host/i',$diag)) throw new Exception('Не удалось соединиться с SSH-хостом (адрес, порт или сеть).');
    }
    return [$rc,$raw];
}
