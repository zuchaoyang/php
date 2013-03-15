<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');
class wcf_gearman_test extends BackGroundController {

    public function run($job, &$log) {

        $workload = $job->workload();
        
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        
        $result = $mSchoolInfo->getSchoolInfoByOperationStrategy($workload);        
        
        
        file_put_contents('/tmp/gearman-test.log', date('Y-m-d H:i:s') .':'. count($result) . "\n", FILE_APPEND );
    
        $log[] = "Success";
    
        return $result;

    }

}


?>