<?php
function bird_rate_limit(string $key,int $limit=10,int $window=60):bool{
  if(session_status()!==PHP_SESSION_ACTIVE)session_start();
  $now=time();$bucket=$_SESSION['_bird_rl'][$key]??['start'=>$now,'count'=>0];
  if($now-$bucket['start']>=$window)$bucket=['start'=>$now,'count'=>0];
  $bucket['count']++;$_SESSION['_bird_rl'][$key]=$bucket;
  return $bucket['count']<=$limit;
}
