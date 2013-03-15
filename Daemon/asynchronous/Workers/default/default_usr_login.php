<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

//* 注意类名与文件名保持一致，必须以小写开头
class default_usr_login extends BackGroundController {
    
    //注意大小写
    protected $_tasks = array('usr_last_login_time', 'usr_ping', 'usr_active', 'usr_init_data');
    
    public function run($job, &$log) {

        $workload = $job->workload();
        $client_account = $workload;  // as client_account
        
        if (empty($client_account)) {
            $log[] = "Work Failure: client_account is null";
            return false;
        }
        
        
        foreach($this->_tasks as $_taskName) {
            $task = $this->getTaskClass($_taskName);
            if (!empty($task)) {
                $result = $task->run($client_account);
            }
        }
        
        $log[] = "Success";
        
        return "default_usr_login Success";

    }

}


?>