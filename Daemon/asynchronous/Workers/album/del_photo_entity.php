<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

//* 注意类名与文件名保持一致，必须以小写开头
class del_photo_entity extends BackGroundController {
    
    
    public function run($job, &$log) {

        $workload = $job->workload();
        $photo_path_list = unserialize($workload); 

        if(empty($photo_path_list)) {
            return false;
        }
        //1. 做路径验证的校验
        foreach($photo_path_list as $key=>$val) {
            if(!file_exists($val)){
                continue;
            }
            if(unlink($val)) {
                $log[] = $val." delete Success";
            }else{
                $log[] = $val." delete False";
            }
        }
        
        return "del_photo_entity Success";
    }

}


?>