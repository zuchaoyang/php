<?php
class IndexAction extends OaController{
    public function _initialize(){
        parent::_initialize();
        
        import("@.Common_wmw.WmwString");
        
		$this->assign('uid', $this->user['client_account']);
    }
    public function index(){
        $sys_role_nums = count(Constancearr::oaRoleAccessModel());
        for($i = 0; $i < $sys_role_nums; $i++) {
            if(isset($this->user['access_name_arr'][$i])) {
                $show_access_arr[$i] = true;
            } else {
                $show_access_arr[$i] = false;
            }
        }
        $remind_task_infos = $this->expireinfoslist();
        $remind_schedule_infos = $this->getScheduleByRemindTime();
        $TaskInfos1 = $this->getTaskInfosByUidType(1);
        $TaskInfos2 = $this->getTaskInfosByUidType(2);
        $TaskInfos3 = $this->getTaskInfosByUidType(3);
        $TaskInfos4 = $this->getTaskInfosByUidType(4);
        
        $this->assign('TaskInfos1',$TaskInfos1);
        $this->assign('TaskInfos2',$TaskInfos2);
        $this->assign('TaskInfos3',$TaskInfos3);
        $this->assign('TaskInfos4',$TaskInfos4);
        $this->assign('remind_schedule_infos',$remind_schedule_infos);
        $this->assign('remind_task_infos',$remind_task_infos);
        $this->assign('show_access_arr', $show_access_arr);
        $this->assign('year',date('Y'));
        $this->display("index");
    }
    
    //所有的日程和工作列表
    function getScheduleOrTask(){
        $type = $this->objInput->getInt('type');
        $page = $this->objInput->getInt('page');
        $type = intval($type);
        $limit = 10;
        $page = max(1, $page);
        $uid = $this->user['client_account'];
        $offset = ($page -1) * $limit;
        $new_list = array();
        if(!empty($type) && $type == 1) {
            $mTaskPush = ClsFactory::Create("Model.mTaskPush");
            $mTaskType = ClsFactory::Create("Model.mTaskType");
            $TaskType = $mTaskType->getTaskTypeSystemAll();
            $TaskPush_list = $mTaskPush->getTaskPushByUid($uid,'push_id asc', $offset, $limit+1); 
            $new_TaskPush_list = $TaskPush_list[$uid];
            $taskpush_ids = array();
            foreach($new_TaskPush_list as $key=>$val){
                $taskpush_ids[] = $val['task_id'];
                $replay[$val['task_id']] = $val['is_replied'];
            }
            $mTask = ClsFactory::Create("Model.mTask");
            $Task_list = $mTask->getTaskById($taskpush_ids);
            if(!empty($Task_list)) {
                if (count($Task_list) < ($limit+1)) {
                    $is_end_page = true;
                } else {
                    array_pop($Task_list);
                }
                foreach($Task_list as $key=>$val){
                    if(!empty($Task_list[$val['task_id']])) {
                        $val['add_time'] = date("Y年m月d日", $Task_list[$val['task_id']]['add_time']);
                        $val['type_name'] = $TaskType[$val['task_type']];
                        $val['task_title'] = cutstr($Task_list[$val['task_id']]['task_title'],10,true);
                        $val['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])), 200, true);
                        if(!empty($val['need_reply'])) {
                            if(!empty($replay[$val['task_id']])){
                                $val['replied_status'] = 1;
                            } else {
                                $val['replied_status'] = 2;
                            }
                            
                        }
                        $new_list[$key] = $val;   
                    }else{
                        unset($new_TaskPush_list[$key]);
                    }
                    
                }
            }
        }else {
            $mScheduleType = ClsFactory::Create("Model.mScheduleType");
            $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid,true);
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $schedule_list = $mSchedule->getScheduleByUid($uid, $offset, $limit+1);
            if(!empty($schedule_list)) {
                if (count($schedule_list) < ($limit+1)) {
                    $is_end_page = true;
                } else {
                    array_pop($schedule_list);
                }
                
                foreach($schedule_list as $key=>$val){
                    $val['add_time'] = date("Y年m月d日", $val['add_time']);
                    $val['type_name'] = $schedule_type_list[$val['type_id']];
                    $val['schedule_title'] = cutstr($val['schedule_title'],10,true);
                    $val['schedule_message'] = cutstr(strip_tags(htmlspecialchars_decode($val['schedule_message'])), 200, true);
                    $new_list[$key] = $val;   
                }
            }
        }
        $this->assign('type',$type);
        $this->assign('is_end_page', $is_end_page);
        $this->assign('page', $page);
        $this->assign('new_list', $new_list);
        $this->display('scheduleandtask_list');
    }
    
    //所有的日程和工作列表
    function getScheduleOrTaskbyType(){
        $show_type = $this->objInput->getInt("show_type");
        $type = $this->objInput->getInt('type');
        $page = $this->objInput->getInt('page');
        $type = intval($type);
        $limit = 10;
        $page = max(1, $page);
        $uid = $this->user['client_account'];
        $offset = ($page -1) * $limit;
        $new_list = array();
        if($type == 1) {
            $mTaskPush = ClsFactory::Create("Model.mTaskPush");
            $mTaskType = ClsFactory::Create("Model.mTaskType");
            $TaskType = $mTaskType->getTaskTypeSystemAll();
            $TaskPush_list = $mTaskPush->getTaskPushByClientAccountAndTaskType($uid, $show_type, $offset, $limit+1); 
            // 不用再做降维处理 M 取出就是二维的。
            //$new_TaskPush_list = $TaskPush_list[$uid];
            
            $taskpush_ids = array();
            foreach($TaskPush_list as $key=>$val){
                $taskpush_ids[] = $val['task_id'];
                $replay[$val['task_id']] = $val['is_replied'];
            }
            $mTask = ClsFactory::Create("Model.mTask");
            $Task_list = $mTask->getTaskById($taskpush_ids);
            if(!empty($Task_list)) {
                if(count($Task_list) < ($limit+1)){
                    $is_end_page = true;
                }else{
                    array_pop($Task_list);
                }
                foreach($Task_list as $key=>&$val){
                    if(!empty($Task_list[$val['task_id']]) && $show_type == $val['task_type']) {
                        $val['add_time'] = date("Y年m月d日", $Task_list[$val['task_id']]['add_time']);
                        $val['type_name'] = $TaskType[$val['task_type']];
                        $val['task_title'] = cutstr($Task_list[$val['task_id']]['task_title'],10,true);
			            $val['task_content']= cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])), 200, true);
			            
                        if(!empty($val['need_reply'])) {
                            if(!empty($replay[$val['task_id']])){
                                $val['replied_status'] = 1;
                            } else {
                                $val['replied_status'] = 2;
                            }
                            
                        }
                        $new_list[$key] = $val;   
                    }else{
                        unset($TaskPush_list[$key]);
                    }
                    
                }
            }
        }else {
            $mScheduleType = ClsFactory::Create("Model.mScheduleType");
            $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid,true);
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $schedule_list = $mSchedule->getScheduleByUidandType($uid, $show_type, $offset, $limit+1);
            if(!empty($schedule_list)) {
                if(count($schedule_list) < $limit+1){
                    $is_end_page = true;
                }else{
                    array_pop($schedule_list);
                }
                foreach($schedule_list as $key=>$val){
                    $val['schedule_message'] = htmlspecialchars_decode($val['schedule_message']);
                    $val['add_time'] = date("Y年m月d日", $val['add_time']);
                    $val['type_name'] = $schedule_type_list[$val['type_id']];
                    $val['schedule_title'] = cutstr($val['schedule_title'],10,true);
                    $val['schedule_message']= cutstr(strip_tags(htmlspecialchars_decode($val['schedule_message'])), 200, true);
                    $new_list[$key] = $val;   
                }
            }
        }
        $this->assign('type',$type);
        $this->assign('show_type', $show_type);
        $this->assign('is_end_page', $is_end_page);
        $this->assign('page', $page);
        $this->assign('new_list', $new_list);
        $this->display('scheduleandtaskbytype_list');
    }
    
    //得到所有非系统类型的日程
    function getSchedule_listWithoutsys(){
        $show_type = $this->objInput->getInt("show_type");
        $type = $this->objInput->getInt('type');
        $page = $this->objInput->getInt('page');
        $length = 11;
        $page = max(1, $page);
        $uid = $this->user['client_account'];
        $offset = ($page -1) * ($length -1);
        $uid = $this->user['client_account'];
        $mScheduleType = ClsFactory::Create("Model.mScheduleType");
        $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid);
        $type_ids = array_keys($schedule_type_list);
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $schedule_list = $mSchedule->getScheduleByUidandType($uid, $type_ids, $offset,$length);
        if(!empty($schedule_list)) {
            if(count($schedule_list) < $length){
                $is_end_page = true;
            }else{
                array_pop($schedule_list);
            }
            foreach($schedule_list as $key=>$val){
                $val['schedule_message'] = htmlspecialchars_decode($val['schedule_message']);
                $val['add_time'] = date("Y年m月d日", $val['add_time']);
                $val['type_name'] = $schedule_type_list[$val['type_id']];
                $val['schedule_title'] = cutstr($val['schedule_title'], 10, true);
	            $val['schedule_message']= strip_tags(htmlspecialchars_decode(cutstr($val['schedule_message'],200,true)));
                $new_list[$key] = $val;   
            }
        }
        $this->assign('type',$type);
        $this->assign('show_type', $show_type);
        $this->assign('is_end_page', $is_end_page);
        $this->assign('page', $page);
        $this->assign('new_list', $new_list);
        $this->display('scheduleandtaskbytype_list');
    }
    
    //所有的日程和工作列表根据时间
    function getScheduleOrTaskByDate(){
        $date = $this->objInput->getInt('datestr');
        $month = $this->objInput->getInt('month');
        $type = $this->objInput->getInt('type');
        $page = $this->objInput->getInt('page');
        $type = intval($type);
        $year = date('Y');
        $start_time = mktime(0,0,0,1,1,$year);
        $end_time = mktime(0,0,0,1,1,$year+1);
        if(!empty($date)){
            $start_time = mktime(0,0,0,1,1,$date);
            $end_time = mktime(0,0,0,1,1,$date+1);
            if(!empty($month)){
                $start_time = mktime(0,0,0,$month,1,$date);
                $end_time = mktime(0,0,0,$month+1,1,$date);
            }
        }
        $limit = 11;
        $page = max(1, $page);
        $uid = $this->user['client_account'];
        $offset = ($page -1) * ($limit -1);
        $new_list = array();
        if(!empty($type) && $type == 1) {
            $mTaskPush = ClsFactory::Create("Model.mTaskPush");
            $mTaskType = ClsFactory::Create("Model.mTaskType");
            $TaskType = $mTaskType->getTaskTypeSystemAll();
            $TaskPush_list = $mTaskPush->getTaskPushByAddTime($uid, $start_time, $end_time, $offset, $limit);
            //更换了函数名称数据维度也由三维变成二维 
            //$taskpushinfos = $taskpushinfos[$uid];
            
            $taskpush_ids = array();
            foreach($TaskPush_list as $key=>$val){
                $taskpush_ids[] = $val['task_id'];
                $replay[$val['task_id']] = $val['is_replied'];
            }
            $mTask = ClsFactory::Create("Model.mTask");
            $Task_list = $mTask->getTaskById($taskpush_ids);
            if(!empty($Task_list)) {
                if(count($Task_list) < $limit){
                    $is_end_page = true;
                }else{
                    array_pop($Task_list);
                }
                foreach($Task_list as $key=>$val){
                    if(!empty($Task_list[$key])) {
                        $val['add_time'] = date("Y年m月d日", $Task_list[$val['task_id']]['add_time']);
                        $val['type_name'] = $TaskType[$val['task_type']];
                        $val['task_title'] = cutstr($val['task_title'],10,true);
    		            $val['task_content']= cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])),200,true);
                        if(!empty($val['need_reply'])) {
                            if(!empty($replay[$val['task_id']])){
                                $val['replied_status'] = 1;
                            } else {
                                $val['replied_status'] = 2;
                            }
                            
                        }
                        $new_list[$key] = $val;   
                    }else{
                        unset($TaskPush_list[$key]);
                    }
                    
                }
            }
        }else {
            $mScheduleType = ClsFactory::Create("Model.mScheduleType");
            $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid,true);
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $schedule_list = $mSchedule->getScheduleByDate($uid, $start_time, $end_time, $offset, $limit);
            if(!empty($schedule_list)) {
                if(count($schedule_list) < $limit){
                    $is_end_page = true;
                }else{
                    array_pop($schedule_list);
                }
                foreach($schedule_list as $key=>$val){
                    $val['schedule_message'] = htmlspecialchars_decode($val['schedule_message']);
                    $val['add_time'] = date("Y年m月d日", $val['add_time']);
                    $val['type_name'] = $schedule_type_list[$val['type_id']];
                    $val['schedule_title'] = cutstr($val['schedule_title'],10,true);
		            $val['schedule_message']= cutstr(strip_tags(htmlspecialchars_decode($val['schedule_message'])),200,true);
                    $new_list[$key] = $val;   
                }
            }
        }
        $this->assign('datestr' ,$year);
        $this->assign('month', $month);
        $this->assign('type',$type);
        $this->assign('is_end_page', $is_end_page);
        $this->assign('page', $page);
        $this->assign('new_list', $new_list);
        $this->display('scheduleandtaskbydate_list');
    }
    

    //快速记事
    public function QuickNotes(){
        $uid = $this->user['client_account'];
        $ksjs = $this->objInput->postStr("ksjs");
        $grjs_id = $this->getScheduleTypeidByName("个人记事");
        if(empty($ksjs) || empty($uid) || empty($grjs_id)){
                $code = -1;
                $message = "系统繁忙请稍候重试！";
        }
        
        if($code != -1) {
            $expration_time = mktime(0,0,0,date("m"),date("d")+1,date("Y"));
            $grjs_dataarr = array(
                'client_account'=>$uid,
                'schedule_title'=>date("Y-m-d H:i:s"),
                'schedule_message'=>$ksjs,
                'type_id'=>$grjs_id,
                'schedule_start_time'=>time(),
                'add_time'=>time(),
                'upd_time'=>time(),
            );
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $resault = $mSchedule->addSchedule($grjs_dataarr);
            if($resault){
                    $code=1;
                    $message = "快速记事添加成功！";
            }else{
                    $code = -1;
                    $message = "快速记事添加失败！";
            }
        }
        $resault_arr = array(
                        'error'=>array(
                            'code'=>$code,
                            'message'=>$message,
                        ),
                        'data'=>''
                       );
        echo json_encode($resault_arr);
    }
    
    //统计个人日程安照类型
    
    //标签分类信息
    function getTaskTag(){
        $school_id = key($this->user['school_info']);
        $mTaskTags = ClsFactory::Create("Model.mTaskTags");
        $TaskTags_list = $mTaskTags->getTaskTagBySchoolId($school_id);
        return !empty($TaskTags_list) ? $TaskTags_list[$school_id] : false;
    }
    
    //个人日程分类
    function getScheduleType($is_sys = true){
        $uid = $this->user['client_account'];
        $mScheduleType = ClsFactory::Create('Model.mScheduleType');
        $ScheduleType_info = $mScheduleType->getScheduleTypeByUid($uid, $is_sys);
        return !empty($ScheduleType_info) ? $ScheduleType_info : false;
    }
    
    //工作分类
    function getTaskType(){
        $mTaskType = ClsFactory::Create('Model.mTaskType');
        $TaskType_info = $mTaskType->getTaskTypeSystemAll();
        return !empty($TaskType_info) ? $TaskType_info : false;
    }

    //根据日程类型名称得到id
    public function getScheduleTypeidByName($ScheduleType_name){
        $uid = $this->user['client_account'];
        $mScheduleType = ClsFactory::Create("Model.mScheduleType");
        $ScheduleType_list = $mScheduleType->getScheduleTypeByUid($uid, true);
        $type_id = 0;
        foreach($ScheduleType_list as $val){
            if($val['type_name'] == trim($ScheduleType_name)){
                $type_id = $val['type_id'];
            }
        }

        return !empty($type_id) ? $type_id : false;
    }
    
	//到期提醒任务列表
	public function expireinfoslist(){
		$page = $this->objInput->getInt('page');
		$end = 8;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($end);
		$uid = $this->user['client_account'];
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		$TaskPushInfos = $mTaskPush->getTaskPushByUid($uid);
		$repliedarr = array();
		foreach($TaskPushInfos[$uid] as $key=>$val){
			$repliedarr[$val['task_id']]['is_replied'] = $val['is_replied'];
		}
		$taskarr = array();
		foreach($TaskPushInfos[$uid] as $key=>$val){
			$taskarr[$uid][] = $val['task_id'];
		}
		$mTask = ClsFactory::Create('Model.mTask');
		$TaskInfos = $mTask->getTaskByRemindTime($taskarr[$uid]);
		
		$new_TaskInfos = array_slice($TaskInfos,$offset,$end,true);
		if(count($new_TaskInfos) < $end){
			$flag = true;
		}else{
			array_pop($new_TaskInfos);
		}
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeSystemAll();
		$new_typelist = array();
		foreach($typelist as $val){
			$new_typelist[$val['type_id']] = $val['type_name'];
		}
		$new_task_infos = array();
		foreach($new_TaskInfos as $key=>$val){
			 $val['expiration_time'] = date('Y-m-d',$val['expiration_time']);
			 $val['task_title'] = cutstr($val['task_title'],18,true);
			$new_task_infos[$key] = $val;
		}
		if(empty($new_task_infos)){
			$new_task_infos = false;
		}
		return $new_task_infos;
	}
	
	//到期个人日程提醒列表
	public function getScheduleByRemindTime(){
		$page = $this->objInput->getInt('page');
		$end = 8;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($end-1);
		$uid = $this->user['client_account'];
		$mSchedule = ClsFactory::Create('Model.mSchedule');
		$ScheduleInfos = $mSchedule->getScheduleByRemindTime($uid,$offset,$end);
		$ScheduleInfos[$uid] = $ScheduleInfos; 
		if(count($ScheduleInfos[$uid])< $end){
			$is_end_page = true;
		}else{
			array_pop($ScheduleInfos[$uid]);
		}
		$mScheduleType = ClsFactory::Create("Model.mScheduleType");
        $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid,true);
        $new_ScheduleInfos = $ScheduleInfos[$uid];
        foreach($new_ScheduleInfos as $key=>$val){
			$val['expiration_time'] = date("Y-m-d",($val['expiration_time']+86400));
			$val['schedule_title'] = cutstr($val['schedule_title'],18,true);
			$ScheduleInfos[$uid][$key] = $val;
		}
        return $ScheduleInfos[$uid];
	}
	
	//查询所有系统类型的工作列表
	public function getTaskInfosByUidType($type){
		$uid = $this->user['client_account'];
		if(empty($type)){
			$type = 1;
		}
		
		$page = $this->objInput->getInt('page');
		$limit = 9;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($limit-1);
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		$TaskPushIds = $mTaskPush-> getTaskPushByClientAccountAndTaskType($uid, $type, $offset, $limit);
		$repliedarr = array();
		
		//foreach($TaskPushIds[$uid] as $key=>$val)  //调整MD 影响了数据维度 C做相应处理
		foreach($TaskPushIds as $key=>$val){
			$repliedarr[$val['task_id']]['is_replied'] = $val['is_replied'];
		}//追加是否回复
		
		$TaskIds = array();
		foreach ($TaskPushIds as $key1=>$val1){
			$TaskIds[$uid][] = $val1['task_id'];
		}//获取任务id
		
		$mTask = ClsFactory::Create('Model.mTask');
		$TaskInfos = $mTask->getTaskById($TaskIds[$uid]);
		foreach($TaskInfos as $key=>$val){
		    $val['task_title'] = cutstr($val['task_title'], 20, true);
		    $TaskInfos[$key] = $val;
		}
		if(count($TaskInfos) < $limit){
			$flag = true;
		}else{
			array_pop($TaskInfos);
		}
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeById($type);
		$new_task_infos = array();
		foreach($TaskInfos as $key=>&$val){
			$task_conten = WmwString::unhtmlspecialchars($val['task_content']);
            $task_conten = WmwString::delhtml($task_conten);
            $task_conten = addslashes ($task_conten);
            $val['task_content']= strip_tags(cutstr(WmwString::unhtmlspecialchars($task_conten), 20, true));
            
			if($val['need_reply'] == 1){
				if($repliedarr[$val['task_id']]['is_replied'] == 1){
					 $val['is_reply'] = "已回复";
				}else{
					$val['is_reply'] = "未回复";
				
				}
					
				$new_task_infos[$key] = $val;
				$new_task_infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
				$new_task_infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
				$new_task_infos[$key]['type_name'] = $typelist[$type]['type_name'];
				$new_task_infos[$key]['task_title'] = cutstr($val['task_title'],20,true);
				
			}else{
				$val['is_reply'] = "";
				$new_task_infos[$key] = $val;
				$new_task_infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
				$new_task_infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
				$new_task_infos[$key]['type_name'] = $typelist[$type]['type_name'];
				$new_task_infos[$key]['task_title'] = cutstr($val['task_title'],20,true);
			}
		}
//		dump($new_task_infos);
		if(empty($new_task_infos)){
			$new_task_infos = false;
		}
		return $new_task_infos;
	}
}