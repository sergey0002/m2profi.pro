<?php
// Run via tests/run.sh only; all repos and credentials are test fixtures.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$t=getenv('T'); if(!$t || !is_file($t.'/panel/projects/demo.env'))exit(2);
function assertThat($v,$msg){if(!$v)throw new RuntimeException($msg);}
function procCmd($args,$cwd=null){$p=proc_open($args,[['file','/dev/null','r'],['pipe','w'],['pipe','w']],$pipes,$cwd);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$rc=proc_close($p);assertThat(!$rc,implode(' ',$args).": $err");return trim($out);}
function api($a,$extra=[],$ok=true,$auto=true){global $t,$trace;$post=array_merge(['project'=>'demo','action'=>$a,'target'=>'pilot','csrf'=>'fixture','branch'=>'test','maintenance'=>'1','terminal'=>'1'],$extra);
  $body=procCmd(['php','-r',"session_save_path('".$t."');session_id('fixture-confirm');session_start();\$_SESSION['fc_csrf']='fixture';\$_SESSION['user']='operator';\$_POST=json_decode(\$argv[1],true);require 'ajax.php';",json_encode($post)],$t.'/panel');
  foreach(['abcdefgh234','never-show-key','never-show-this'] as $secret)assertThat(strpos($body,$secret)===false,'secret escaped redaction');
  $lines=explode("\n",$body);$r=json_decode(array_pop($lines),true,512,JSON_THROW_ON_ERROR);$trace='';foreach($lines as $l){$f=json_decode($l,true,512,JSON_THROW_ON_ERROR);assertThat($f['event']==='trace','invalid frame');$trace.=$f['text']."\n";}
  if($auto && isset($r['confirmation']) && !isset($extra['confirmation']))return api($a,array_merge($extra,['confirmation'=>$r['confirmation']]),$ok,false);
  assertThat(($r['status']==='ok')===$ok,"$a unexpected outcome: ".json_encode($r,JSON_UNESCAPED_UNICODE)."\n$trace");return $r;}
function bindSite(){ $v=api('fc_preview');return api('fc_bind',['token'=>$v['token'],'accept_missing'=>$v['missing']?'1':'0']); }
function gitAt($dir,...$args){return procCmd(array_merge(['git','-C',$dir],$args));}
function pass($s){echo "OK: $s\n";}
$site=$t.'/site';$remote=$t.'/remote';$dev=$t.'/dev';$key=substr(hash('sha256',$site),0,24);$h=getenv('HOME').'/.dep-control/'.$key;
$r=api('directory_statuses');assertThat(array_column($r['targets'],'state','name')===['pilot'=>'none','legacy'=>'unavailable'],'wrong cards');assertThat(strpos($trace,'ordinary stderr before test')!==false,'ordinary stderr lost');pass('cards, PROD isolation, terminal redaction');
api('fc_status',['csrf'=>'invalid'],false);api('fc_status',['target'=>'legacy'],false);assertThat(!api('fc_status')['bound'],'empty target');assertThat(api('fc_branches')['branches']===['test'],'branches');pass('CSRF, target policy, remote refs');
$html=procCmd(['php','-r',"session_start();\$_SESSION['user']='operator';session_write_close();\$_SERVER['REQUEST_METHOD']='GET';require 'index.php';"],$t.'/panel');
assertThat(strpos($html,'data-guard="1" value="pilot"')!==false && strpos($html,'data-guard="0" value="legacy"')!==false && strpos($html,'value="live"')===false,'target visibility differs between UI and API');
pass('same project ACL, target names and flags in HTML and API');
// Password-only Linux panel: require the existing project password via fd 3, never argv/stdin.
$helper=$t.'/bin/sshpass';file_put_contents($helper, <<<'BASH'
#!/bin/bash
set -eu
[[ $1 == -d && $2 == 3 && " $* " != *abcdefgh234* && " $* " == *StrictHostKeyChecking=yes* ]] || exit 90
IFS= read -r secret <&3;exec 3<&-;[[ $secret == abcdefgh234 ]] || exit 5
shift 2; export FIXTURE_AUTH=ok;exec "$@"
BASH
);chmod($helper,0700);putenv('REQUIRE_PASSWORD=1');$cfg=$t.'/panel/projects/demo.env';$oldCfg=file_get_contents($cfg);file_put_contents($cfg,"PROD_ALLOWED_USERS=operator\n",FILE_APPEND);mkdir($t.'/prod');
$r=api('directory_statuses');assertThat(array_column($r['targets'],'state','name')['live']==='none','authorized PROD not read');assertThat(strpos($trace,'sshpass -d 3')!==false,'wrong transport');
// Exercise the actual ordinary deployment and the same branch endpoint with password-only SSH.
procCmd(['git','clone','-qb','test','https://github.com/example/fixture.git',$t.'/missing']);
file_put_contents($t.'/missing/index.txt',"local edit\n");$refsBefore=gitAt($remote,'show-ref');
assertThat(api('get_branches',['target'=>'legacy'])['branches']===api('get_branches')['branches'],'different catalogues');
api('deploy',['target'=>'legacy']);assertThat(strpos($trace,'sshpass -d 3')!==false && trim(file_get_contents($t.'/missing/index.txt'))==='first','ordinary deployment via shared SSH');
assertThat($refsBefore===gitAt($remote,'show-ref') && !is_dir(getenv('HOME').'/.dep-control/'.substr(hash('sha256',$t.'/missing'),0,24)),'ordinary deployment created snapshots');
api('deploy',['csrf'=>'bad','target'=>'legacy'],false);pass('same SSH, branch catalogue and CSRF for plain and guarded targets');
file_put_contents($cfg,$oldCfg);putenv('REQUIRE_PASSWORD');unlink($helper);pass('password-authenticated PROD card, no deploy or credential exposure');
putenv('FAIL_SSH=1');$r=api('fc_status',[],false);assertThat(strpos($trace,'Actual diagnostic')!==false && strpos($r['message'],'known_hosts')!==false,'lost SSH error');putenv('FAIL_SSH');pass('real SSH error in stream');
require $t.'/panel/control/branches.php';$seen=[];
$catalog=graphqlBranches('fixture','example','demo',static function($url,$token,$payload)use(&$seen){
    $after=$payload['variables']['after'];$seen[]=$after;
    return ['code'=>200,'error'=>'','data'=>['data'=>['repository'=>['refs'=>['pageInfo'=>['hasNextPage'=>$after===null,'endCursor'=>'next'],'nodes'=>[['name'=>$after===null?'one':'two'],['name'=>'ftp-snapshots/private']]]]]]];
});assertThat($seen===[null,'next'] && $catalog['branches']===['one','two'],'catalogue pagination');pass('one paginated GraphQL catalogue, hidden service branches');
bindSite();assertThat(count(scandir($site))===2,'bind wrote to site');api('fc_sync',[],false);api('deploy');assertThat(trim(file_get_contents($site.'/index.txt'))==='first','first deploy');assertThat(api('directory_statuses')['targets'][0]['branch']==='test','publication branch');pass('empty binding and first deployment');
file_put_contents($site.'/index.txt',"ftp\n");mkdir($site.'/src');file_put_contents($site.'/src/new name.txt',"new\n");file_put_contents($site.'/.env','private');file_put_contents($site.'/anything.txt','automatic');$logos=$site.'/admin/uploads/logo__190';file_put_contents($logos.'/45.jpg','edited');file_put_contents($logos.'/46.jpg','ignored');file_put_contents($logos.'/.gitignore',"!47.jpg\n");file_put_contents($logos.'/47.jpg','included');$r=api('fc_check');assertThat(strpos($r['message'],'index.txt')!==false && strpos($r['message'],'new name.txt')===false,'control must ignore unrelated new files');$preview=api('fc_sync',[],true,false);assertThat(strpos($preview['message'],'index.txt')!==false && strpos($preview['message'],'anything.txt')===false,'preview included an unmanaged path');api('fc_sync');$names=explode("\n",gitAt($remote,'ls-tree','--name-only','-r','test'));foreach(['.env','anything.txt','src/new name.txt','admin/uploads/logo__190/46.jpg','admin/uploads/logo__190/47.jpg'] as $unmanaged)assertThat(!in_array($unmanaged,$names,true),'unmanaged file committed: '.$unmanaged);assertThat(gitAt($remote,'show','test:admin/uploads/logo__190/45.jpg')==='edited','tracked ignored file lost');assertThat(strpos(gitAt($remote,'log','test','--format=%an <%ae>'),'fromserver <from@server.ru>')!==false,'identity');pass('tracked edits only; unmanaged paths excluded from preview and push');
api('deploy');$refs=gitAt($remote,'show-ref');api('deploy');assertThat($refs===gitAt($remote,'show-ref'),'deploy loop');pass('repeat without new commits');
gitAt($dev,'fetch',$remote,'test');gitAt($dev,'reset','--hard','FETCH_HEAD');gitAt($dev,'push',$remote,'test:other');file_put_contents($site.'/index.txt',"ftp2\n");api('fc_sync',['branch'=>'other']);assertThat(gitAt($remote,'show','other:index.txt')==='ftp2' && gitAt($remote,'show','test:index.txt')==='ftp','wrong destination branch');file_put_contents($dev.'/index.txt',"developer\n");gitAt($dev,'commit','-am','developer');gitAt($dev,'push',$remote,'test');gitAt($dev,'pull','--ff-only',$remote,'test');file_put_contents($dev.'/only-remote','remote');gitAt($dev,'add','only-remote');gitAt($dev,'commit','-m','remote only');gitAt($dev,'push',$remote,'test');$before=gitAt($remote,'show-ref');$check=api('fc_check',['branch'=>'other']);assertThat(isset($check['baseline'],$check['selected']) && strpos($check['message'],'only-remote')===false,'wrong comparison branch');$second=api('fc_check',['branch'=>'test']);assertThat($second['baseline']===$check['baseline'] && $second['selected']!==$check['selected'] && strpos($second['message'],"G\tonly-remote")!==false,'target-only change not classified');file_put_contents($site.'/only-remote','ftp collision');$collision=api('fc_check',['branch'=>'test']);assertThat(strpos($collision['message'],"C\tonly-remote")!==false,'missing untracked collision');$blockedDeploy=api('deploy',[],false,false);assertThat(strpos($blockedDeploy['message'],'Коллизия выкладки')!==false && file_get_contents($site.'/only-remote')==='ftp collision','deploy did not stop on collision');file_put_contents($site.'/only-remote','remote');$same=api('fc_check',['branch'=>'test']);assertThat(strpos($same['message'],"C\tonly-remote")===false,'identical new file reported as collision');assertThat($before===gitAt($remote,'show-ref'),'control pushed');$r=api('deploy',[],false);assertThat(strpos($r['message'],'конфликт')!==false && trim(file_get_contents($site.'/index.txt'))==='ftp2','conflict');preg_match('~ftp-snapshots/[a-f0-9]+/[a-f0-9]+~',$r['message'],$match);gitAt($dev,'fetch',$remote,'refs/heads/'.$match[0]);gitAt($dev,'merge','-s','ours','--no-edit','FETCH_HEAD');gitAt($dev,'push',$remote,'test');api('deploy');assertThat(file_get_contents($site.'/only-remote')==='remote' && trim(file_get_contents($site.'/index.txt'))==='developer','resolved merge');pass('deployment baseline comparison, selected branch sync, conflict and manual resolution');
assertThat(file_get_contents($site.'/src/new name.txt')==="new\n" && file_get_contents($site.'/anything.txt')==='automatic','deploy removed unmanaged data');unlink($site.'/only-remote');api('deploy');assertThat(strpos(gitAt($remote,'ls-tree','-r','test'),'only-remote')===false,'deletion not preserved');pass('tracked removal');
// Even an exclusive lock from an older worker cannot hide a branch or block a read.
$f=fopen($h.'/lock','a');flock($f,LOCK_EX);$stateBefore=file_get_contents($h.'/state.v2');$refsBefore=gitAt($h.'/repo','show-ref');
assertThat(api('fc_status')['bound']===true && api('directory_statuses')['targets'][0]['branch']==='test','writer blocked status');
api('fc_check');assertThat(in_array('test',api('get_branches')['branches'],true),'writer blocked catalogue');
assertThat(file_get_contents($h.'/state.v2')===$stateBefore && gitAt($h.'/repo','show-ref')===$refsBefore,'reader modified writer state');
$unlockedPreview=api('deploy',[],true,false);assertThat(isset($unlockedPreview['confirmation']),'writer blocked deployment preview');
api('deploy',['confirmation'=>$unlockedPreview['confirmation']],false);fclose($f);pass('reads and previews bypass writer locks; confirmed writes remain serialized');
file_put_contents($h.'/incomplete.json','{}');assertThat(api('fc_status')['state']==='incomplete','interruption misreported as busy');api('fc_check',[],false);unlink($h.'/incomplete.json');pass('concurrent and interrupted deployment');
rename($site,$site.'.first');rename($h,$h.'.first');mkdir($site);copy($site.'.first/.gitignore',$site.'/.gitignore');file_put_contents($site.'/.env','private');$v=api('fc_preview');assertThat($v['missing'],'missing deletion flag');api('fc_bind',['token'=>$v['token']],false);file_put_contents($site.'/index.txt',"different\n");api('fc_bind',['token'=>$v['token'],'accept_missing'=>'1'],false);bindSite();api('deploy');assertThat(file_get_contents($site.'/.env')==='private','private file overwritten');pass('non-Git site: preview token, explicit deletions');
rename($site,$site.'.second');rename($h,$h.'.second');procCmd(['git','clone','-qb','test','https://github.com/example/fixture.git',$site]);assertThat(api('fc_status')['bound'],'clone recognition');file_put_contents($site.'/index.txt',"clone\n");api('deploy');assertThat(gitAt($site,'rev-parse','HEAD')===gitAt($remote,'rev-parse','test'),'clone head');
// Only the selected commit's ignore files govern new paths, not either host's excludes.
file_put_contents($site.'/.git/info/exclude',"host-info.txt\n");file_put_contents($t.'/global-ignore',"host-global.txt\n");procCmd(['git','config','--global','core.excludesFile',$t.'/global-ignore']);
file_put_contents($site.'/host-info.txt','info');file_put_contents($site.'/host-global.txt','global');
$rules=file_get_contents($site.'/.gitignore');unlink($site.'/.gitignore');file_put_contents($site.'/.env','local-secret-fixture');
$r=api('fc_check');assertThat(strpos($r['message'],'.env')===false && strpos($r['message'],'host-info.txt')===false && strpos($r['message'],'host-global.txt')===false,'control scanned unrelated files');
file_put_contents($site.'/.gitignore',$rules);$odd=":name with newline\n";
$publisher=$t.'/publisher';procCmd(['git','clone','-qb','test',$remote,$publisher]);file_put_contents($publisher.'/'.$odd,'seed');file_put_contents($publisher.'/large.bin','seed');file_put_contents($publisher.'/confirm.txt','seed');file_put_contents($publisher.'/private.key','tracked fixture');gitAt($publisher,'add','-A');gitAt($publisher,'commit','-m','tracked fixtures');gitAt($publisher,'push','origin','test');api('deploy');
file_put_contents($site.'/'.$odd,'odd');$big=$site.'/large.bin';$bytes=33*1024*1024;$fh=fopen($big,'wb');ftruncate($fh,$bytes);fclose($fh);api('fc_sync');
assertThat(gitAt($remote,'cat-file','-s','test:large.bin')===(string)$bytes && gitAt($remote,'show','test:'.$odd)==='odd','unexpected file size or name cap');
gitAt($publisher,'pull','--ff-only','origin','test');rename($publisher.'/large.bin',$publisher.'/from-git.bin');gitAt($publisher,'add','-A');gitAt($publisher,'commit','-m','rename large file');gitAt($publisher,'push','origin','test');api('deploy');assertThat(filesize($site.'/from-git.bin')===$bytes && !file_exists($big),'large incoming blob not published');pass('unrelated files ignored; unusual names and >32 MiB tracked files');
$before=gitAt($remote,'show-ref');file_put_contents($site.'/confirm.txt','before');
$preview=api('fc_sync',[],true,false);assertThat(isset($preview['confirmation']) && strpos($preview['message'],'confirm.txt')!==false && gitAt($remote,'show-ref')===$before,'preview performed push or lost paths');
api('fc_sync',['confirmation'=>str_repeat('0',64).'.'.str_repeat('0',64)],false,false);
file_put_contents($site.'/confirm.txt','after');api('fc_sync',['confirmation'=>$preview['confirmation']],false,false);
assertThat(gitAt($remote,'show-ref')===$before,'stale approval performed push');
file_put_contents($site.'/private.key','fixture');$blocked=api('fc_sync',[],false,false);assertThat(strpos($blocked['message'],'private.key')!==false && gitAt($remote,'show-ref')===$before,'sensitive tracked path not blocked');file_put_contents($site.'/private.key','tracked fixture');
api('fc_sync');assertThat(gitAt($remote,'show','test:confirm.txt')==='after','approved data not pushed');pass('confirmation scope, stale preview, no premature push and sensitive paths');
gitAt($site,'remote','set-url','origin','wrong');api('fc_status',[],false);pass('existing clone, HEAD and origin');
putenv('LEGACY_FIXTURE=1');$r=api('deploy',['target'=>'legacy'],false);putenv('LEGACY_FIXTURE');
assertThat(strpos($trace,'regular legacy stdout')!==false && strpos($trace,'regular legacy stderr')!==false && strpos($trace,':long-line-end')!==false,'lost stdout/stderr or long line');
assertThat(strpos($trace,'known secret [скрыто]')!==false && strpos($r['message'],'7')!==false,'lost redaction/exit status');pass('legacy stdout + stderr, long and split lines, truthful exit');
$log=file_get_contents($t.'/panel/operations.log.php');assertThat(strpos($log,'abcdefgh234')===false && strpos($log,'"user":"operator"')!==false,'operation journal');pass('append-only journal');
