<?php
class reverse_string {


    public function run($job, &$log) {

        $workload = $job->workload();

        $result = strrev($workload);
        $log[] = "Success";


        return $result;

    }

}

?>