<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class message_response extends BackGroundController{
    public function run($job, &$log) {
        
        $workload = $job->workload();
        
        $mMsgResponse = ClsFactory::Create("Model.Message.mMsgResponse");
        $workload = unserialize($workload);
        $result = $mMsgRequire->addMsgResponse($workload['data']);
        
        file_put_contents('/tmp/gearman-test.log', date('Y-m-d H:i:s') .': 好友请求回复'. $result . "\n", FILE_APPEND );
    
        $log[] = "好友请求回复 $result ";
        
        return $result;
    }
}