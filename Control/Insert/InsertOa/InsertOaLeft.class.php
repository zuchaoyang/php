<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertOaLeft implements InsertInterface {
    protected $no_viewed_nums = 0;
    protected $uid = 0;
    protected $statistics_list = array();
    
    public function run($params, & $smarty) {
        import("@.Common_wmw.Constancearr");
        $this->uid = $params['uid'];
        $date = date('Y年m月d日', time());
        $week_arr = array('日', '一', '二', '三', '四', '五', '六');
        $week_day = "周" . $week_arr[date('w')];
        $day = date('d');
        
        //获取用户的基本信息
        $mUser = ClsFactory::Create('Model.mUser');
        $user = $mUser->getOaCurrentUser();
        $dpt_list = & $user['dpt_list'];
        //权限的显示
        $show_access_arr = array();

        $sys_role_nums = count(Constancearr::oaRoleAccessModel());
        for($i = 0; $i < $sys_role_nums; $i++) {
            if(isset($user['access_name_arr'][$i])) {
                $show_access_arr[$i] = true;
            } else {
                $show_access_arr[$i] = false;
            }
        }
        $this->exec();
        
        $smarty->assign('no_viewed_nums', $this->no_viewed_nums);
        $smarty->assign('statistics_list', $this->statistics_list);
        $smarty->assign('week_day', $week_day);
        $smarty->assign('date', $date);
        $smarty->assign('day', $day);
        $smarty->assign('multi', count($dpt_list) > 1 ? true : false);
        $smarty->assign('dpt_list', $dpt_list);
        $smarty->assign('show_access_arr', $show_access_arr);
        
        return $smarty->fetch('./Public/oa_left.html');
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
    
     /**
     * 执行统计处理
     */
    protected function exec() {
        $this->StatisticsNoViewed();
        $schedule_list = $this->StatisticsSchedule();
        $task_list = $this->StatisticsTask();
        
        $this->statistics_list = array(
            'schedule_type' => & $schedule_list,
            'task_type' => & $task_list
        );
    }
}