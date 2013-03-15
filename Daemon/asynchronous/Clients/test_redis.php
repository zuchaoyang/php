<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');

$redis = RedisIo::getInstance();

//测试zset 

//$key = 'zset-test-key';
//$redis->delete('z');
//$redis->zAdd($key, 1, 'one');
//$redis->zAdd($key, 2, 'two');
//$redis->expireAt($key, time() + 300);
//
//echo "=================test redis zset Rank===================================== \n";   
//print_r('key one index ===' .$redis->zRevRank($key, 'one') . "\n"); /* 1 */
//print_r('key two index ===' .$redis->zRevRank($key, 'two') . "\n"); /* 0 */
//print_r('key not exist key index ===' .$redis->zRevRank($key, 'not-exist') . "\n"); /* 0 */
//dump($redis->zRevRank($key, 'not-exist'));
//print_r($redis->zRevRange($key, 0, -1));

$key = "cls:146:student";

$redis->sRem($key, '12441111');