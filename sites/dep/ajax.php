<?php
ini_set('display_errors','0'); // never render PHP stack arguments/credentials into an AJAX response
session_start();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$project = $_POST['project'] ?? 'noffprod';
require_once __DIR__.'/control/operations.php';
opsInit($action,true);

if (empty($_SESSION['user'])) {
    echo opsReply(['status' => 'error', 'message' => 'Несанкционированный доступ']);
    exit;
}
$user = $_SESSION['user'];

require_once __DIR__.'/control/api.php';
require_once __DIR__.'/control/branches.php';
try {
    if(!is_string($project) || !preg_match('/^[A-Za-z0-9_-]+$/D',$project))throw new RuntimeException('Некорректный проект.');
    $env=loadEnv(__DIR__.'/projects/'.$project.'.env');
    if(!projectAllowed($env,$user))throw new RuntimeException('Нет доступа к проекту.');
    $targets=visibleTargets($env,$user);$target=$_POST['target'] ?? '';
    if(!is_string($action)||!is_string($target))throw new RuntimeException('Некорректный запрос.');
    // Older read-only clients can still request the catalogue/log without a token.
    if(!in_array($action,['get_branches','get_log'],true))panelCsrf();
    if ($action==='client_error') {
        $op=$_POST['operation'] ?? ''; $kind=$_POST['kind'] ?? '';
        if (!in_array($op,['directory_statuses','fc_status','fc_branches','fc_check','fc_preview','fc_bind','fc_sync','fc_deploy','get_branches','deploy','get_log'],true) || !in_array($kind,['timeout','network','http','json','missing_target'],true)) throw new Exception('Неизвестная диагностика.');
        opsWrite($op,'client_error',['reason'=>$kind,'http'=>(int)($_POST['http'] ?? 0)]);echo opsReply(['status'=>'ok']);exit;
    }
    if($action==='directory_statuses'){echo opsReply(directoryStatuses($env,$targets));exit;}
    if($action==='get_branches' || $action==='fc_branches'){
        $dir=$target!==''?($targets[$target] ?? null):reset($targets);
        if(!$dir)throw new RuntimeException('Цель недоступна.');
        echo opsReply(projectBranches($env,$dir));exit;
    }
if ($action === 'get_log') {
    $log = '';
    if (file_exists(__DIR__ . '/lastlog.log')) {
        $log = file_get_contents(__DIR__ . '/lastlog.log');
    }
    echo opsReply(['status' => 'ok', 'log' => $log]);
    exit;
}

    if(!isset($targets[$target]))throw new RuntimeException('Цель не найдена или недоступна.');
    $dir=$targets[$target];
    if($action==='deploy' || ($action==='fc_deploy' && $dir['guard'])){
        $branch=$_POST['branch'] ?? '';
        if(!is_string($branch) || $branch==='' || strlen($branch)>220 || $branch[0]==='-' || preg_match('/[\x00-\x20\x7f]/',$branch)) throw new RuntimeException('Некорректная ветка.');
        $result=$dir['guard']?fcRun('deploy',$target,$dir,$env,$user,$project):standardDeploy($env,$dir,$user,$branch);
    } elseif($dir['guard'] && in_array($action,['fc_status','fc_preview','fc_bind','fc_check','fc_sync'],true)) {
        $result=fcRun(substr($action,3),$target,$dir,$env,$user,$project);
    } else throw new RuntimeException('Действие недоступно для этой цели.');
    echo opsReply($result);
} catch(Throwable $e){echo opsReply(['status'=>'error','message'=>$e->getMessage()]);}

// Ordinary targets still use the existing fetch/checkout/reset scenario, without sync or push.
function standardDeploy($env,$dir,$user,$branch){
    $args=[$dir['path'],$branch,$user,implode(',',fcList($env['ALLOWED_USERS'] ?? '')),$dir['type']==='prod'?'1':'0',implode(',',fcList($env['PROD_ALLOWED_USERS'] ?? ''))];
    $body=@file_get_contents(__DIR__.'/deploy_remote.sh');if(!$body)throw new RuntimeException('Нет deploy_remote.sh.');
    $script='set -- '.implode(' ',array_map('bashLiteral',$args))."\ngit check-ref-format --branch \"\$2\" >/dev/null || exit 2\n".$body."\n";
    [$rc,$log]=projectSsh($env,$script,'terminal');
    file_put_contents(__DIR__.'/lastlog.log',$log,LOCK_EX);
    if($rc!==0)throw new RuntimeException('SSH/Git завершился с ошибкой. Код: '.$rc);
    return ['status'=>'ok','message'=>'Деплой завершён'];
}
