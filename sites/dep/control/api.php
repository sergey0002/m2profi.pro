<?php
require_once __DIR__.'/project.php';
require_once __DIR__.'/ssh.php';
function fcRun($action,$target,$dir,$env,$user,$project) {
    if($dir['type']==='prod')throw new RuntimeException('FTP-контроль разрешён только для TEST.');
    $repo=$env['REPO_URL'] ?? ''; $host=$env['SSH_HOST'] ?? '';
    if(!preg_match('~^https://github\.com/[a-zA-Z0-9_.-]+/[a-zA-Z0-9_.-]+$~D',$repo))throw new RuntimeException('Проверьте REPO_URL.');
    $other=[];
    foreach (glob(dirname(__DIR__).'/projects/*.env') as $f) {
        $e=loadEnv($f); if (($e['SSH_HOST'] ?? '')!==$host) continue;
        foreach(projectTargets($e) as $d) if(basename($f,'.env')!==$project || $d['name']!==$target)$other[]=$d['path'];
    }
    $q=['action'=>$action,'target'=>$target,'user'=>$user,'path'=>$dir['path'],'repo'=>$repo,'other'=>$other,
        'name'=>trim($env['GIT_COMMIT_NAME'] ?? '') ?: 'fromserver','email'=>trim($env['GIT_COMMIT_EMAIL'] ?? '') ?: 'from@server.ru',
        'write_branches'=>fcList($env['FTP_WRITE_BRANCHES'] ?? ''),'accept_missing'=>($_POST['accept_missing'] ?? '')==='1',
        'allow'=>fcList($env['FTP_NEW_FILES'] ?? ''),'branch'=>$_POST['branch'] ?? '','token'=>$_POST['token'] ?? '','base_oid'=>$_POST['base_oid'] ?? '', 'maintenance'=>($_POST['maintenance'] ?? '')==='1'];
    if (!is_string($q['branch']) || strlen($q['branch'])>220) throw new Exception('Недопустимая ветка.');
    if ($action==='status') {
        $q['targets']=[['name'=>$target,'path'=>$dir['path'],'guard'=>true]];
        $r=fcRemote('inspect.sh',$q,$env);
        $item=$r['targets'][0];
        if (isset($item['reason']) || in_array($item['state'],['busy','incomplete'],true)) throw new Exception($item['reason'] ?? 'Цель занята или выкладка не завершена.');
        return ['status'=>'ok','bound'=>$item['bound'] ?? false,'message'=>!empty($item['bound'])?'Привязано к Git':'Не привязано'];
    }
    return fcRemote('worker.sh',$q,$env);
}

// Both scripts and the SSH command are selected by the server, not the request.
function fcRemote($file,$q,$env) {
    if(!in_array($file,['inspect.sh','worker.sh'],true))throw new LogicException('Неизвестный серверный сценарий.');
    $script=@file_get_contents(__DIR__.'/'.$file); if (!$script) throw new Exception('На сервере панели отсутствует или не читается control/'.$file.'.');
    // Only a fixed command goes through the remote shell; all data travels on stdin.
    $readOnly=$file==='inspect.sh';
    if ($readOnly) {
        $script.="\nset -e -o pipefail\n";
        foreach ($q['targets'] as $idx=>$t) {
            $key=$t['guard']?substr(hash('sha256',$t['path']),0,24):'-';
            $script.="printf 'DEP_STEP target:%d\\n' $idx >&2\ninspect '".base64_encode($t['path'])."' '$key'\n";
        }
    } else {
        $script=fcWorkerInput($q)."\nwork(){\n".$script."\n}\nwork </dev/null\n";
    }
    [$rc,$raw]=projectSsh($env,$script,'protocol',$q['targets'] ?? []);
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
        if ($readOnly) throw new Exception('Не удалось прочитать состояние через SSH/Bash. Проверьте OpenSSH, Bash, Git и доступность каталога.');
        throw new Exception('Нет подтверждения от Bash-сценария. Проверьте SSH и сообщения терминала.');
    }
    return $r;
}

// POSIX literals for data in the stdin script, never interpolated into the SSH command.
function fcWorkerInput($q) {
    $text='';$args=['live'=>'path','url'=>'repo','action'=>'action','branch'=>'branch','target'=>'target','name'=>'name','email'=>'email','token'=>'token','base_oid'=>'base_oid'];
    foreach($args as $var=>$field)$text.=$var.'='.bashLiteral($q[$field] ?? '')."\n";
    foreach(['other','allow','write_branches'] as $var) $text.=$var.'=('.implode(' ',array_map('bashLiteral',$q[$var] ?? [])).")\n";
    foreach(['maintenance','accept_missing'] as $var)$text.=$var.'='.(!empty($q[$var])?'1':'0')."\n";
    return $text;
}

function directoryStatuses($env,$dirs) {
    if(!$dirs)return ['status'=>'ok','targets'=>[]];
    return fcRemote('inspect.sh',['targets'=>array_values($dirs),'repo'=>$env['REPO_URL'] ?? ''],$env);
}

// The remote host needs no JSON parser: decode the saved state on the panel.
function fcInspectResponse($q,$raw) {
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
