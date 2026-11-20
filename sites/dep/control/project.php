<?php
// Project configuration and target policy are the same for the UI and all API routes.
function loadEnv($file) {
    $lines=@file($file,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    if($lines===false)throw new RuntimeException('Конфигурация проекта недоступна.');
    $env=[];
    foreach($lines as $line){$line=trim($line);if($line==='' || $line[0]==='#')continue;
        $pair=explode('=',$line,2);if(count($pair)===2)$env[trim($pair[0])]=trim($pair[1]);}
    return $env;
}
function fcList($s){return array_values(array_filter(array_map('trim',explode(',',$s)),'strlen'));}
function projectAllowed($env,$user){$acl=fcList($env['ALLOWED_USERS'] ?? '');return !$acl || in_array($user,$acl,true);}
function projectTargets($env){
    $dirs=[];$enabled=fcList($env['FTP_CONTROL_TARGETS'] ?? '');
    foreach(fcList($env['DIRS'] ?? '') as $entry){
        if(!preg_match('~^([^:]+):\s*(/[^|]+)\|\s*(prod|test)$~D',$entry,$m))throw new RuntimeException('Ошибка конфигурации DIRS.');
        $name=trim($m[1]);if($name==='' || isset($dirs[$name]))throw new RuntimeException('Неоднозначное имя цели.');
        $dirs[$name]=['name'=>$name,'path'=>trim($m[2]),'type'=>$m[3],'guard'=>in_array($name,$enabled,true)];
    }
    if(array_diff($enabled,array_keys($dirs)) || count($enabled)!==count(array_unique($enabled)))throw new RuntimeException('Ошибка конфигурации FTP_CONTROL_TARGETS.');
    return $dirs;
}
function visibleTargets($env,$user){
    if(!projectAllowed($env,$user))return [];
    $prod=in_array($user,fcList($env['PROD_ALLOWED_USERS'] ?? ''),true);
    return array_filter(projectTargets($env),static function($d)use($prod){return $prod || $d['type']!=='prod';});
}
function panelCsrf(){
    if(!is_string($_POST['csrf'] ?? null) || empty($_SESSION['fc_csrf']) || !hash_equals($_SESSION['fc_csrf'],$_POST['csrf']))throw new RuntimeException('Обновите страницу: проверка CSRF не пройдена.');
}
