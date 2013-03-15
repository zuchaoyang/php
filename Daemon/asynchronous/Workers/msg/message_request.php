<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class message_request extends BackGroundController{
    public function run($job, &$log) {
        $workload = $job->workload();
        
        $mMsgRequire = ClsFactory::Create("Model.Message.mMsgRequire");
        $workload = unserialize($workload);
        $result = $mMsgRequire->addMsgRequire($workload['data']);
        
        file_put_contents('/tmp/gearman-test.log', date('Y-m-d H:i:s') .': 好友请求'. $result . "\n", FILE_APPEND );
    
        $log[] = "好友请求 $result ";
        
        return $result;
    }
}