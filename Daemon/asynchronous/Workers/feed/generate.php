<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class generate extends BackGroundController{

    public function run($job, &$log) {
        
        $workload = $job->workload();
        $workload = unserialize($workload);
        $id = $workload;
        
        
        /**
        * 日志类的动态生成.
        * 1.根据ID获取相应数据
        * 2.根据数据生成动态信息
        */
        
        $m = ClsFactory::Create("Model.Blog.mBlog");
        $blog_data = $m->getBlogById($id);
        
        print_r($blog_data);
        $log[] = "Success";
    
        return true;

    }

}


?>