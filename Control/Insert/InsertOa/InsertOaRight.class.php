<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertOaRight implements InsertInterface {
    protected $uid = 0;
    protected $statistics_list = array();
    protected $tag_list = array();
    
    /**
     * 对外接口函数
     * @param $params
     * @param $smarty
     */
    public function run($params, & $smarty) {
        
        $this->uid = $params['uid'];
        $this->exec();
        
        $smarty->assign('statistics_list', $this->statistics_list);
        $smarty->assign('task_tags', $this->task_tags);
        $smarty->assign('date_list', $this->statistics_list['date_list']);
        $smarty->assign('year', date('Y'));
        
        return $smarty->fetch("./Public/oa_right.html");
    }
    
    /**
     * 执行统计处理
     */
    protected function exec() {
        $this->StatisticsTaskByTags();
        
        $schedule_list = $this->StatisticsSchedule();
        $task_list = $this->StatisticsTask();
        $date_list = $this->mergeKeepKeyRecursive((array)$schedule_list['date'], (array)$task_list['date']);
        
        $this->statistics_list = array(
            'schedule_type' => & $schedule_list,
            'task_type' => & $task_list,
            'date_list' => & $date_list
        );
    }
    
    //统计个人日程安照类型
    protected function StatisticsSchedule() {
        $mScheduleType = ClsFactory::Create('Model.mScheduleType');
        $schedule_type = $mScheduleType->getScheduleTypeByUid($this->uid, true);
        
        array_unshift($schedule_type, array('type_name'=>"自定义分类",'type_id'=>0));
        
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $schedule_arr = $mSchedule->getScheduleByUid($this->uid);
        $schedule_list = & $schedule_arr;
        $statistics_schedule = array();  
        $new_statistics_schedule_type = $new_statistics_schedule_date =  array();
        $stat_arr = $date_arr = array();
        if(!empty($schedule_list)) {
            foreach($schedule_list as $key=>$val){
                $type_id = intval($val['type_id']);
                if($type_id > 3) {
                    isset($stat_arr[0]) ? $stat_arr[0]++ : $stat_arr[0] = 1;
                } elseif(in_array($type_id, array(1,2,3))) {
                    isset($stat_arr[$type_id]) ? $stat_arr[$type_id]++ : $stat_arr[$type_id] = 1;
                }
                $year = date('Y', $val['add_time']);
                isset($date_arr[$year]) ? $date_arr[$year]++ : $date_arr[$year] = 1;
            }
            unset($schedule_list, $schedule_arr);
        }
        $total_nums = 0;
        if(!empty($schedule_type) && !empty($stat_arr)) {
            foreach($schedule_type as $type_id=>$schedule) {
                if(!in_array($type_id, array(0,1,2,3))){
                    unset($schedule_type[$type_id]);
                    continue;
                }
                if($stat_arr[$type_id] == 0 || isset($stat_arr[$type_id])) {
                    $schedule['nums'] = $stat_arr[$type_id];
                } else {
                    $schedule['nums'] = 0;
                }
                $total_nums += $schedule['nums'];
                $schedule['type'] = 'scheudle';
                $schedule_type[$type_id] = $schedule;
            }
        }elseif(empty($date_arr)){
            $schedule_type = array_slice($schedule_type,0,4,true);
        }
        
        return array(
            'date' => $date_arr,
            'type' => $schedule_type,
            'total_nums' => $total_nums,
        );
    }
    
    
    //统计安排给自己的工作类型分类统计
    protected function StatisticsTask(){
        //获取系统工作类型        
        $mTaskType = ClsFactory::Create('Model.mTaskType');
        $Task_Type_list = $mTaskType->getTaskTypeSystemAll();
        
        $mTaskPush = ClsFactory::Create("Model.mTaskPush");
        $TaskPush_arr = $mTaskPush->getTaskPushByUid($this->uid, 'push_id desc');
        $TaskPush_list = & $TaskPush_arr[$this->uid];
        
        $stat_arr = $date_arr = array();
        if(!empty($TaskPush_list)) {
            foreach($TaskPush_list as $key=>$val){
                $type_id = intval($val['task_type']);
                isset($stat_arr[$type_id]) ? $stat_arr[$type_id]++ : $stat_arr[$type_id] = 1;
                $year = date('Y', $val['add_time']);
                isset($date_arr[$year]) ? $date_arr[$year]++ : $date_arr[$year] = 1;
            }
            unset($TaskPush_list, $TaskPush_arr);
        }
        if(!empty($Task_Type_list) & !empty($stat_arr)){
            foreach($Task_Type_list as $key=>$val){
                if(isset($stat_arr[$key])) {
                    $val['nums'] = $stat_arr[$key];
                } else {
                    $val['nums'] = 0;
                }
                $total_nums += $stat_arr[$key];
                $val['type'] = 'task';
                $Task_Type_list[$key] = $val;
            }
        }
        return array(
            'date'=>$date_arr,
            'type'=>$Task_Type_list,
            'total_nums'=>$total_nums,
        );
    }
    
    //按照标签分类统计工作
    protected function StatisticsTaskByTags(){
        $mTaskPush = ClsFactory::Create("Model.mTaskPush");
        $taskpush_arr = $mTaskPush->getTaskPushByUid($this->uid, 'push_id desc');
        $taskpush_list = & $taskpush_arr[$this->uid];
        $task_ids = array();
        if(!empty($taskpush_list)) {
            foreach($taskpush_list as $key=>$val){
                $task_id = intval($val['task_id']);
                if($task_id > 0) {
                   $task_ids[] = $task_id;
                }
                unset($taskpush_list[$key]);
            }
        }
        $mTask = ClsFactory::Create("Model.mTask");
        $tasklist = $mTask->getTaskById($task_ids);
        $statistics_tags = array();
        if(!empty($tasklist)){
            foreach($tasklist as $task_id=>$task){
               $tag_ids = $task['tag_ids'];
               if(empty($tag_ids)) {
                   continue;
               }
               $tag_arr = is_array($tag_ids) ? $tag_ids : explode(',', $tag_ids);
               foreach($tag_arr as $tag_id) {
                   isset($statistics_tags[$tag_id]) ? $statistics_tags[$tag_id]++ : $statistics_tags[$tag_id] = 1;
               }
               unset($tasklist[$task_id]);
            }
        }
        
        $new_tag_list = $sort_keys = array();
        if(!empty($statistics_tags)) {
            $total_tag_ids = array_keys($statistics_tags);
            $mTag = ClsFactory::Create('Model.mTaskTags');
            $tag_list = $mTag->getTaskTagById($total_tag_ids);
            foreach($statistics_tags as $tag_id=>$nums) {
                if(!isset($tag_list[$tag_id])) {
                    continue;
                }
                $tag = $tag_list[$tag_id];
                $tag['nums'] = $nums;
                $new_tag_list[$tag_id] = $tag;
                $sort_key[$tag_id] = $nums;
            }
            unset($tag_list, $statistics_tags);
        }
        array_multisort($sort_key, SORT_DESC, SORT_NUMERIC, $new_tag_list);
        $this->tag_list = array_slice($new_tag_list, 0, 6);
    }
    
    /**
     * 未查看工作统计
     */
    protected function StatisticsNoViewed(){
        $mTaskPush = ClsFactory::Create("Model.mTaskPush");
        $Taskpush = $mTaskPush->getTaskPushByUid($this->uid);
        $new_taskpush = $Taskpush[$this->uid];
        foreach($new_taskpush as $val){
            $reply[$val['task_id']] = $val['is_replied'];
            $viewed[$val['task_id']] = $val['is_viewed'];
            $taskpush_ids[]=$val['task_id'];
        }
        $mTask = ClsFactory::Create('Model.mTask');
        $new_Taskpush = $mTask->getTaskById($taskpush_ids);
        foreach($new_Taskpush as $key=>$val){
            if(!empty($val['need_reply'])){
                if(!empty($reply[$val['task_id']])){
                    unset($new_Taskpush[$key]);
                }
            }elseif(!empty($viewed[$val['task_id']])){
                unset($new_Taskpush[$key]);
            }
        }
        
        $this->no_viewed_nums = !empty($new_Taskpush) ? count($new_Taskpush) : 0;
    }
    
    /**
     * 处理的纬度为2纬的
     * @param unknown_type $arr
     * @param unknown_type $arr1
     */
    protected function mergeKeepKeyRecursive($arr, $arr1) {
        if(empty($arr) && empty($arr1)) {
            return false;
        } elseif(empty($arr)) {
            krsort($arr1);
            return $arr1;
        } elseif(empty($arr1)) {
            krsort($arr);
            return $arr;
        }
        foreach($arr1 as $year=>$list1) {
            if(isset($arr[$year])) {
                $list = $arr[$year];
                if(isset($list1)) {
                    $list += $list1;
                } else {
                    $list = $list1;
                }
                krsort($list);
                $arr[$year] = $list;
            } else {
                $arr[$year] = $list1;
            }
        }
        krsort($arr);
        return $arr;
    }
}