<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');
class feed_class_dispatch extends BackGroundController {

    //注意大小写
    
    protected $_common_tasks = array( 
        'feed_class_all',
        'feed_user_my',
        'feed_user_all',
        'feed_user_children',
    );
    
    protected $_tasks = array(
        FEED_MOOD => array(
            FEED_ACTION_PUBLISH => array(),
            FEED_ACTION_COMMENT => array(),
        ),
        FEED_BLOG => array(
            FEED_ACTION_PUBLISH => array(),
            FEED_ACTION_COMMENT => array(),
        ),
        FEED_ALBUM => array(
            FEED_ACTION_PUBLISH => array('feed_class_album'),
            FEED_ACTION_COMMENT => array(),
        ),
    );
    
    /**
     * 
     * @param Serializable(array()) $job
     * array(
     * 	'id'=>1
     *  'feed_id' => 1
     *  'feed_type' => 日志/说说/相册
     *  'action' => 发表/评论
     * )
     * 
     * @param $log
     */
    public function run($job, &$log) {

        $workload = $job->workload();
        
        $workload = unserialize($workload);
        
        $class_code = $workload["class_code"];
        $uid = $workload["uid"];
        $feed_id = $workload["feed_id"];
        $feed_type = $workload["feed_type"];
        $action = $workload["action"];
        if(empty($action) || empty($feed_id) || empty($feed_type)){
            $log[] = "Work Failure: id or feed_id or feed_type or action is null";
            return false;  
        }
        
        $tasks = array_merge((array)$this->_common_tasks, (array)$this->_tasks[$feed_type][$action]);

        foreach($tasks as $taskName) {
            $task = $this->getTaskClass($taskName);
            if (!empty($task)) {
                $result = $task->run($uid, $class_code, $feed_id);
            }
        }
        
        $log[] = "Success";
    
        return $result;

    }

}


?>