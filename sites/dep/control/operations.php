<?php
// One append-only JSON line per event. Never log POST bodies, raw output or cookies.
function opsRedact($text) {
    static $secrets=null;
    if($secrets===null){
        $secrets=[];
        foreach(array_merge([dirname(__DIR__).'/.env'],glob(dirname(__DIR__).'/projects/*.env') ?: []) as $file){
            foreach(@file($file,FILE_IGNORE_NEW_LINES) ?: [] as $line){
                $parts=explode('=',trim($line),2);if(count($parts)!==2)continue;
                if($parts[0]==='USERS') {foreach(explode(',',$parts[1]) as $pair){$v=explode(':',$pair,2);if(isset($v[1]))$secrets[]=trim($v[1]);}}
                elseif(preg_match('/(?:PASS(?:WORD)?|TOKEN|SECRET|API_KEY)$/i',$parts[0]))$secrets[]=trim($parts[1]);
            }
        }
        $secrets=array_values(array_filter(array_unique($secrets),'strlen'));
        foreach($secrets as $v){$secrets[]=rawurlencode($v);$secrets[]=urlencode($v);$secrets[]=base64_encode($v);}
        usort($secrets,static function($a,$b){return strlen($b)<=>strlen($a);});
    }
    $text=str_replace($secrets,'[скрыто]',(string)$text);
    $text=preg_replace('~-----BEGIN [^-]*PRIVATE KEY-----.*?(?:-----END [^-]*PRIVATE KEY-----|$)~s','[ключ скрыт]',$text);
    $text=preg_replace('~((?:https?|ssh)://)[^/\s@]+@~i','$1[скрыто]@',$text);
    $text=preg_replace('~(?:github_pat_|gh[pousr]_)[a-zA-Z0-9_]+~','[скрыто]',$text);
    $text=preg_replace('~((?:authorization|proxy-authorization|cookie|set-cookie)\s*:)\s*[^\r\n]+~i','$1 [скрыто]',$text);
    $text=preg_replace('~((?:[\w-]*(?:password|passwd|token|secret|api[_-]?key)|SSH_PASS)\s*[:=]\s*)[^\r\n]+~i','$1[скрыто]',$text);
    $text=preg_replace('~((?:sshpass\s+-p|-pw|--password)\s+)[^\r\n]+~i','$1[скрыто]',$text);
    return preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/','',$text);
}
function opsText($v,$limit=180) {
    return is_scalar($v)?substr(preg_replace('/[\r\n\t]+/',' ',opsRedact($v)),0,$limit):'-';
}
function opsSafeResult($r){foreach($r as $k=>$v){if(is_array($v))$r[$k]=opsSafeResult($v);elseif(is_string($v) && !in_array($k,['status','token','confirmation'],true))$r[$k]=opsRedact($v);}return $r;}
// Single journal in the panel itself. PHP stops before parsing any journal data.
function opsAppend($line) {
    $path=dirname(__DIR__).'/operations.log.php';
    $header="<?php http_response_code(404); exit; __halt_compiler();\n";
    if (@is_link($path)) return false;
    $mask=umask(0077);
    try { $f=@fopen($path,'c+b'); } finally {umask($mask);}
    if (!$f) return false;
    try {
        if (!flock($f,LOCK_EX)) return false;
        $stat=fstat($f);
        if (!$stat || ($stat['mode'] & 0170000)!==0100000) return false;
        if ($stat['size']===0) {
            if (fwrite($f,$header)!==strlen($header)) return false;
        } elseif (fgets($f,strlen($header)+1)!==$header) return false;
        return fseek($f,0,SEEK_END)===0 && fwrite($f,$line)===strlen($line) && fflush($f);
    } finally { fclose($f); }
}
function opsWrite($action,$status,$more=[]) {
    static $failed=false,$writing=false;
    if ($failed || $writing) return;
    $writing=true;
    try {
    $base=['time'=>gmdate('Y-m-d\TH:i:s\Z'),'id'=>$GLOBALS['ops_id'],'user'=>$_SESSION['user'] ?? 'guest','action'=>$action,'status'=>$status];
    foreach (['project','target','branch'] as $k) if (isset($_POST[$k])) $base[$k]=$_POST[$k];
    $row=[]; foreach (array_merge($base,$more) as $k=>$v) $row[$k]=opsText($v,$k==='reason'?500:180);
    $line=json_encode($row,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE)."\n";
    if (!opsAppend($line)) {
        $failed=true;error_log('DEP: cannot append operations.log.php in panel directory; check write permissions, header and free space.');
        if(!headers_sent()) header('X-Operations-Log: unavailable');
    }
    } finally { $writing=false; }
}
function opsReason($s) {
    // Older transports concatenate third-party replies/stderr. Keep classification, not that output.
    foreach (['Сетевая ошибка cURL','GitHub GraphQL:','Ошибка доступа к API','Не удалось получить ветки через SSH/git'] as $prefix)
        if (strpos($s,$prefix)===0) return $prefix;
    return explode("\n",(string)$s,2)[0];
}
function opsReply($r) {
    $a=$GLOBALS['ops_action'];$ok=($r['status'] ?? '')==='ok';$extra=[];
    if (!$ok) $extra['reason']=opsReason($r['message'] ?? 'Ответ без сообщения');
    if($a==='fc_check' && isset($r['changed']))$extra['comparison']=$r['changed']?'changed':'clean';
    foreach (['run','source','state'] as $field) if(isset($r[$field])) $extra[$field]=$r[$field];
    $state=$ok?(isset($r['confirmation'])?'confirmation_required':'ok'):'error';
    if ($a==='directory_statuses' && $ok) {
        foreach ($r['targets'] ?? [] as $v) {
            $st=$v['state'] ?? 'unknown';$det=['target'=>$v['name'] ?? '-', 'state'=>$st];
            if (isset($v['reason'])) $det['reason']=$v['reason'];
            if (isset($v['branch'])) $det['branch']=$v['branch'];
            opsFrame($det['target'].': '.($det['reason'] ?? ($det['branch'] ?? $st)));
            $bad=in_array($st,['unknown','unavailable','incomplete'],true);
            opsWrite('directory_status',$bad?'error':$st,$det);if($bad)$state='partial';
        }
        $extra['count']=count($r['targets'] ?? []);
    }
    $extra['ms']=round((microtime(true)-$GLOBALS['ops_time'])*1000);
    opsFrame(($ok ? ($state==='partial'?'Завершено с замечаниями':($state==='confirmation_required'?'Предпросмотр готов, push не выполнялся':'Готово')) : 'Ошибка: '.$extra['reason']).' · '.$extra['ms'].' мс');
    opsWrite($a,$state,$extra);$GLOBALS['ops_done']=true;
    return json_encode(opsSafeResult($r),JSON_INVALID_UTF8_SUBSTITUTE);
}
function opsInit($action,$api=false) {
    $GLOBALS['ops_id']=bin2hex(random_bytes(8));$GLOBALS['ops_action']=is_string($action)?$action:'invalid';$GLOBALS['ops_time']=microtime(true);$GLOBALS['ops_done']=false;
    if (!headers_sent()) header('X-Operation-Id: '.$GLOBALS['ops_id']);
    set_error_handler(function($no,$msg,$file,$line){if(error_reporting() & $no) opsWrite($GLOBALS['ops_action'],'php_warning',['reason'=>basename($file).':'.$line,'code'=>$no]);return false;},E_WARNING|E_USER_WARNING);
    register_shutdown_function(function() use($api) {
        $e=error_get_last();if ($e && in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR],true)) opsWrite($GLOBALS['ops_action'],'error',['reason'=>'PHP fatal: '.basename($e['file']).':'.$e['line'],'code'=>$e['type']]);
        elseif($api && !$GLOBALS['ops_done']) opsWrite($GLOBALS['ops_action'],'unknown',['reason'=>'Запрос завершился без подтверждённого ответа']);
    });
    $GLOBALS['ops_stream']=$api && !empty($_SESSION['user']) && ($_POST['terminal'] ?? '')==='1'
        && in_array($action,['directory_statuses','fc_status','fc_branches','fc_preview','fc_bind','fc_check','fc_sync','fc_deploy','get_branches','deploy'],true);
    if($GLOBALS['ops_stream']) {
        ignore_user_abort(true);header('Content-Type: application/x-ndjson; charset=UTF-8');header('Cache-Control: no-store');header('X-Accel-Buffering: no');
        @ini_set('zlib.output_compression','0');while(ob_get_level()>0 && @ob_end_flush()){}
        opsFrame('Начало операции');
    }
    if($api) opsWrite($GLOBALS['ops_action'],'start');
}

// Opt-in response: zero or more newline-terminated trace frames, then the usual JSON result.
// Only the originating HTTP request sees the stream; never tail another user's terminal.
function opsFrame($text) {
    if (empty($GLOBALS['ops_stream'])) return;
    echo json_encode(['event'=>'trace','time'=>gmdate('H:i:s'),'id'=>$GLOBALS['ops_id'],
        'text'=>opsRedact($text)],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)."\n";
    flush();
}
function opsStep($text) {
    opsWrite($GLOBALS['ops_action'],'step',['step'=>$text]);opsFrame($text);
}
// Retain incomplete lines between reads. Redact before emitting, even if a secret or
// a private key spans multiple writes to a growing file.
function opsDrain($reader,$targets=[],$final=false){
    static $tails=[], $insideKey=[];
    if(!$reader)return;$id=(int)$reader;$tail=$tails[$id] ?? '';$pem=$insideKey[$id] ?? false;
    fseek($reader,ftell($reader));
    $emit=static function($line)use(&$pem,$targets){
        if(strpos($line,'-----BEGIN ')!==false && strpos($line,'PRIVATE KEY-----')!==false){$pem=true;opsFrame('[закрытый ключ скрыт]');}
        if($pem){if(strpos($line,'-----END ')!==false && strpos($line,'PRIVATE KEY-----')!==false)$pem=false;return;}
        if(preg_match('/^DEP_STEP target:(\d+)$/D',$line,$m) && isset($targets[(int)$m[1]])){opsStep('Каталог: '.$targets[(int)$m[1]]['name']);return;}
        if($line==='DEP_STEP directory'){opsStep('Проверка доступа к каталогу');return;}
        opsFrame($line);
    };
    while(($chunk=fread($reader,65536))!==false && $chunk!==''){
        $lines=preg_split('/\r\n|\n|\r/',$tail.$chunk);$tail=array_pop($lines);foreach($lines as $line)$emit($line);
    }
    if($final){if($tail!=='')$emit($tail);unset($tails[$id],$insideKey[$id]);}
    else{$tails[$id]=$tail;$insideKey[$id]=$pem;}
}
