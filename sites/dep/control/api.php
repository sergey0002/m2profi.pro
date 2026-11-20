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
    if ($action==='status' || $action==='branches') {
        $q['targets']=[['name'=>$target,'path'=>$dir[0],'guard'=>true]];
        $r=fcRemote('inspect.sh',$q,$login,$host);
        if ($action==='branches') return $r;
        $item=$r['targets'][0];
        if (isset($item['reason']) || in_array($item['state'],['busy','incomplete'],true)) throw new Exception($item['reason'] ?? 'Цель занята или выкладка не завершена.');
        return ['status'=>'ok','bound'=>$item['bound'] ?? false,'message'=>!empty($item['bound'])?'Привязано к Git':'Не привязано'];
    }
    return fcRemote('worker.sh',$q,$login,$host);
}

// Both scripts and the SSH command are selected by the server, not the request.
function fcRemote($file,$q,$login,$host) {
    if (!in_array($file,['worker.sh','inspect.sh'],true) || !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/D',$host) || !preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*$/D',$login)) throw new Exception('Некорректный SSH-хост.');
    if (!function_exists('proc_open')) throw new Exception('На сервере панели отключён proc_open.');
    $script=@file_get_contents(__DIR__.'/'.$file); if (!$script) throw new Exception('На сервере панели отсутствует или не читается control/'.$file.'.');
    // Only a fixed command goes through the remote shell; all data travels on stdin.
    $q['_trace']=!empty($GLOBALS['ops_stream']);
    $readOnly=$file==='inspect.sh'; $command='bash -s';
    if ($readOnly) {
        $script.="\nset -e -o pipefail\n";
        if (($q['action'] ?? '')==='branches') $script.="remote_branches '".base64_encode($q['repo'])."'\n";
        else foreach ($q['targets'] as $idx=>$t) {
            $key=$t['guard']?substr(hash('sha256',$t['path']),0,24):'-';
            $script.="printf 'DEP_STEP target:%d\\n' $idx >&2\ninspect '".base64_encode($t['path'])."' '$key'\n";
        }
    } else {
        $script=fcWorkerInput($q)."\nwork(){\n".$script."\n}\nwork </dev/null\n";
    }
    $input=tmpfile();$out=tmpfile();$err=tmpfile();fwrite($input,$script);rewind($input);
    opsStep('$ ssh -T -o BatchMode=yes -o StrictHostKeyChecking=yes '.$login.'@'.$host.' '.$command);
    $reader=fopen(stream_get_meta_data($err)['uri'],'rb');
    $proc=proc_open(['ssh','-T','-o','BatchMode=yes','-o','StrictHostKeyChecking=yes','-o','ConnectTimeout=8','-o','ServerAliveInterval=15','-o','ServerAliveCountMax=2',$login.'@'.$host,$command],[$input,$out,$err],$pipes);
    if (!is_resource($proc)) throw new Exception('Не удалось запустить OpenSSH.');
    ignore_user_abort(true);set_time_limit(0);$end=microtime(true)+180;
    do { opsDrain($reader,$q['targets'] ?? []); $s=proc_get_status($proc); if (!$s['running']) break; if (microtime(true)>$end) {proc_terminate($proc,9);proc_close($proc);throw new Exception('Таймаут. Результат неизвестен; проверьте состояние цели перед повтором.');} usleep(50000); } while (true);
    $rc=$s['exitcode'];proc_close($proc);opsDrain($reader,$q['targets'] ?? [],true);if($reader)fclose($reader);
    opsStep('Завершение SSH: код '.$rc);
rewind($out);$raw=stream_get_contents($out,1048577);
    if(strlen($raw)>1048576) throw new Exception('Слишком большой ответ хостинга.');
    if ($readOnly && $rc===0) return fcInspectResponse($q,$raw);
    $r=null;
    if (!$readOnly && $raw!=='') {
        $f=explode("\0",$raw);
        if(count($f)===5 && $f[4]==='' && in_array($f[0],['ok','error'],true)) {
            $r=['status'=>$f[0],'message'=>$f[1]];
            if($q['action']==='preview' && $f[0]==='ok') {$r['token']=$f[2];$r['missing']=$f[3]==='1';}
        }
    }
    if (!is_array($r) || !isset($r['status']) || ($rc!==0 && $r['status']!=='error')) {
        rewind($err); $diag=stream_get_contents($err,8192); // Never send raw SSH stderr to the browser.
        if (preg_match('/host key verification failed|host identification has changed/i',$diag)) throw new Exception('Не подтверждён ключ SSH-хоста. Сверьте отпечаток и known_hosts пользователя панели.');
        if (preg_match('/permission denied|no supported authentication/i',$diag)) throw new Exception('SSH не разрешил вход по ключу пользователя панели.');
        if (preg_match('/could not resolve hostname|connection timed out|connection refused|no route to host/i',$diag)) throw new Exception('Не удалось соединиться с SSH-хостом (адрес, порт или сеть).');
        if ($readOnly && $rc===3) throw new Exception('Git не смог получить список веток: проверьте REPO_URL и доступ на чтение.');
        if ($readOnly) throw new Exception('Не удалось прочитать состояние через SSH/Bash. Проверьте OpenSSH, Bash, Git и доступность каталога.');
        throw new Exception('Нет подтверждения от Bash-сценария. Проверьте SSH и сообщения терминала.');
    }
    return $r;
}

// POSIX literals for data in the stdin script, never interpolated into the SSH command.
function fcWorkerInput($q) {
    $literal=static function($v){if(!is_scalar($v) || preg_match('/[\x00-\x1f\x7f]/',(string)$v))throw new Exception('Недопустимое значение параметра.');return "'".str_replace("'", "'\\''",(string)$v)."'";};
    $text='';$args=['live'=>'path','url'=>'repo','action'=>'action','branch'=>'branch','target'=>'target','name'=>'name','email'=>'email','token'=>'token','base_oid'=>'base_oid'];
    foreach($args as $var=>$field)$text.=$var.'='.$literal($q[$field] ?? '')."\n";
    foreach(['other','allow','write_branches'] as $var) $text.=$var.'=('.implode(' ',array_map($literal,$q[$var] ?? [])).")\n";
    foreach(['maintenance','accept_missing'] as $var)$text.=$var.'='.(!empty($q[$var])?'1':'0')."\n";
    return $text;
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
    return fcRemote('inspect.sh',['targets'=>$targets,'repo'=>$env['REPO_URL'] ?? ''],$env['SSH_USER'] ?? '',$env['SSH_HOST'] ?? '');
}

// The remote host needs no JSON parser: decode the saved state on the panel.
function fcInspectResponse($q,$raw) {
    if (($q['action'] ?? '')==='branches') {
        $names=[];foreach(explode("\n",trim($raw)) as $line) {
            if($line==='')continue;
            if(!preg_match('~^[0-9a-f]{40,64}\trefs/heads/(.+)$~D',$line,$m)) throw new Exception('Некорректный ответ Git.');
            if(strpos($m[1],'ftp-snapshots/')!==0)$names[]=$m[1];
        }
        sort($names,SORT_STRING);return ['status'=>'ok','branches'=>$names];
    }
    $fields=explode("\0",$raw);if(array_pop($fields)!=='' || count($fields)!==5*count($q['targets']))throw new Exception('Неполный ответ проверки каталогов.');
    $rows=[];$errors=['path'=>'Проверьте путь DIRS и права на каталог.','denied'=>'Служебный файл недоступен.','nogit'=>'Не найдена команда git.','noflock'=>'Недоступна проверка блокировки цели.','giterror'=>'Git не смог прочитать рабочую копию.'];
    foreach($q['targets'] as $i=>$target){
        [$code,$head,$branch,$json,$origin]=array_slice($fields,5*$i,5);
        $row=['name'=>$target['name'],'state'=>'none','bound'=>false];
        if(in_array($code,['busy','incomplete'],true)) $row['state']=$code;
        elseif(!in_array($code,['ok','unborn'],true)) $row+=['reason'=>$errors[$code] ?? 'Ошибка чтения каталога.'];
        else {
            $s=$json===''?[]:json_decode($json,true);
            if(strpos($json,"v2:\n")===0){
                $parts=explode("\n",substr($json,4));
                $s=count($parts)===7?['repo'=>$parts[0],'base'=>$parts[1],'snap'=>$parts[2],'head'=>$parts[3],'empty'=>$parts[4]==='1','active'=>$parts[5]==='1','published_branch'=>$parts[6]]:null;
            }
            if(!is_array($s) || ($json!=='' && (($s['repo'] ?? null)!==$q['repo'] || ($s['head'] ?? null)!==$head))) $row['reason']='Состояние привязки не соответствует каталогу.';
            elseif($target['guard'] && $head!=='' && preg_replace('~\.git$~','',$origin)!==preg_replace('~\.git$~','',$q['repo'])) $row['reason']='Рабочая копия относится к другому origin.';
            else {
                $row['bound']=($head!=='' || !empty($s));
                if(!empty($s['published_branch'])) $row=array_merge($row,['state'=>'branch','branch'=>$s['published_branch'],'sha'=>$s['base'],'source'=>'publish']);
                elseif($head!=='' && $branch!=='') $row=array_merge($row,['state'=>'branch','branch'=>$branch,'sha'=>$head,'source'=>'checkout']);
                elseif($head!=='' || ($s && empty($s['empty']) && !array_key_exists('published_branch',$s))) $row['state']='unknown';
            }
        }
        if(isset($row['reason']))$row['state']='unavailable';$rows[]=$row;
    }
    return ['status'=>'ok','targets'=>$rows];
}
