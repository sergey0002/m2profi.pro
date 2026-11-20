<?php
// One catalogue for every target: GitHub metadata, then remote Git/cache on the same transport.
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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

    opsStep('GitHub GraphQL: запрос веток (HTTPS POST)');
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return ['code' => $httpCode, 'data' => json_decode($result, true), 'error' => $error];
}

function projectBranches($env,$dir){
    $token=$env['GITHUB_TOKEN'] ?? ''; $repo=$env['REPO_URL'] ?? '';
    if(!preg_match('~^https://github\.com/([a-zA-Z0-9_.-]+)/([a-zA-Z0-9_.-]+)$~D',$repo,$m))throw new RuntimeException('Неверный REPO_URL проекта.');
    if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
    if($token!=='' && function_exists('curl_init')){
        $r=graphqlBranches($token,$m[1],preg_replace('/\.git$/i','',$m[2]));
        if($r!==null)return $r;
    }
    $source=file_get_contents(dirname(__DIR__).'/get_branches.sh');if(!$source)throw new RuntimeException('Нет сценария получения веток.');
    $script='repo='.bashLiteral($repo)."\nfolder=".bashLiteral($dir['path'])."\ntoken=".bashLiteral($token)."\n".$source."\n";
    [$rc,$raw]=projectSsh($env,$script);
    if($rc!==0)throw new RuntimeException('Не получены ветки Git. См. терминал; код '.$rc.'.');
    $parts=explode("\0",$raw);$source=array_shift($parts);
    if(!in_array($source,['remote','cache'],true)||array_pop($parts)!==''||count($parts)%5!==0)throw new RuntimeException('Некорректный ответ каталога веток.');
    $rows=[];foreach(array_chunk($parts,5) as [$name,$oid,$subject,$author,$ts]){
        $rows[]=['name'=>$name,'hash'=>$subject!==''?$subject:$oid,'author'=>$author?:'unknown','timestamp'=>(int)$ts,'date'=>$ts?date('d.m.Y H:i:s',(int)$ts):'N/A'];
    }
    usort($rows,static function($a,$b){return ($b['timestamp']<=>$a['timestamp'])?:strcmp($a['name'],$b['name']);});
    return ['status'=>'ok','data'=>$rows,'branches'=>array_column($rows,'name'),'source'=>$source==='cache'?'ssh-git-cache':'ssh-git'];
}
function graphqlBranches($githubToken,$owner,$repo,$fetch='githubApiRequest'){
    $graphqlQuery = <<<'GQL'
query ($owner: String!, $name: String!, $after: String) {
  repository(owner: $owner, name: $name) {
    refs(refPrefix: "refs/heads/", first: 100, after: $after, orderBy: {field: TAG_COMMIT_DATE, direction: DESC}) {
      pageInfo { hasNextPage endCursor }
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

    $cursor=null; $seen=[]; $nodes=[];
    do {
        $response=$fetch('https://api.github.com/graphql',$githubToken,[
            'query'=>$graphqlQuery,'variables'=>['owner'=>$owner,'name'=>$repo,'after'=>$cursor],
        ]);
        if(in_array($response['code'],[401,403],true))throw new RuntimeException('GitHub отклонил доступ: HTTP '.$response['code']);
        $refs=$response['data']['data']['repository']['refs'] ?? null;
        if($response['error'] || $response['code']!==200 || !empty($response['data']['errors']) || !is_array($refs['nodes'] ?? null)){
            opsWrite('get_branches','fallback',['http'=>$response['code']]);opsStep('GraphQL недоступен. Чтение веток через SSH.');return null;
        }
        foreach($refs['nodes'] as $node)$nodes[]=$node;
        if(empty($refs['pageInfo']['hasNextPage']))break;
        $cursor=$refs['pageInfo']['endCursor'] ?? null;
        if(!is_string($cursor) || $cursor==='' || isset($seen[$cursor]))return null;
        $seen[$cursor]=true;
    }while(true);
    $branches = [];
    foreach ($nodes as $node) {
        if(strpos($node['name'] ?? '', 'ftp-snapshots/')===0)continue;
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

    return ['status'=>'ok','data'=>$branches,'branches'=>array_column($branches,'name'),'source'=>'graphql'];
}
