<?php
class ExpireremindAction extends OaController{
	protected $user = array();
	public function _initialize(){
	    parent::_initialize();
	    
		$this->assign('uid', $this->user['client_account']);
    }

	//到期提醒任务列表
	public function expireinfoslist(){
		$page = $this->objInput->getInt('page');
		$limit = 9;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($limit);
		$uid = $this->user['client_account'];
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		$TaskPushInfos = $mTaskPush->getTaskPushByUid($uid,'push_id asc', $offset, $limit+1);
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
		if(count($TaskInfos) < $limit+1){
			$flag = true;
		}else{
			array_pop($TaskInfos);
		}
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeSystemAll();
		$new_typelist = array();
		foreach($typelist as $val){
			$new_typelist[$val['type_id']] = $val['type_name'];
		}
		$new_task_infos = array();
		foreach($TaskInfos as $key=>$val){
    		 $val['add_time'] = date('Y-m-d',$val['add_time']);
			 $val['upd_time'] = date('Y-m-d',$val['upd_time']);
			 $val['task_content'] =  htmlspecialchars_decode(cutstr($val['task_content'], 80, true));
			 $val['type_name'] = $new_typelist[$val['task_type']];
			 if($val['need_reply'] == 1){
					if($repliedarr[$val['task_id']]['is_replied'] == 1){
						 $val['is_reply'] = "已回复";
					}else{
						$val['is_reply'] = "未回复";
					}
			 }else{
					$val['is_reply'] = "";
			}
			$new_task_infos[$key] = $val;
		}
		if(empty($new_task_infos)){
			$new_task_infos = false;
		}
		$this->assign('page',$page);
		$this->assign('flag',$flag);
		$this->assign('TaskInfos',$new_task_infos);
		
		$this->display('expireinfos_list');
	}

	//到期个人日程提醒列表
	public function getScheduleByRemindTime(){
		$page = $this->objInput->getInt('page');

		$end = 9;

		$page = max(1, $page);
		$offset = ($page-1) * $end;
		$uid = $this->user['client_account'];
		$mSchedule = ClsFactory::Create('Model.mSchedule');
		$schedule_arr = $mSchedule->getScheduleByRemindTime($uid, $offset, $end + 1);

		if(count($schedule_arr) < $end + 1) {
			$flag = true;
		}
        $schedule_list = $schedule_arr;
		if(!empty($schedule_list)) {
    		//获取日程类型信息
    		$mScheduleType = ClsFactory::Create("Model.mScheduleType");
            $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid, true);
            if(!empty($schedule_type_list)) {
                foreach($schedule_type_list as $type_id=>$typeinfo) {
                    $schedule_type_list[$type_id] = $typeinfo['type_name'];
                }
            }

            foreach($schedule_list as $schedule_id=>$schedule) {
                $type_id = intval($schedule['type_id']);
            	$schedule['add_time'] = date('Y-m-d', $schedule['add_time']);
    			$schedule['upd_time'] = date('Y-m-d',$schedule['upd_time']);
    			if(!empty($schedule['expiration_time'])) {
    			    $schedule['expiration_time'] = date("Y-m-d", $schedule['expiration_time'] - 86400);
    			}
    			$schedule['schedule_message'] = cutstr(strip_tags(htmlspecialchars_decode($schedule['schedule_message'])), 80, true);
    			$schedule['type_name'] = isset($schedule_type_list[$type_id]) ? $schedule_type_list[$type_id] : "默认分类";
    			$schedule_list[$schedule_id] = $schedule;
            }
		}
		$this->assign('schedule_list', $schedule_list);
		$this->assign('page', $page);
		$this->assign('flag', $flag);
		
		$this->display('schedule_list');
	}

	//根据接收人账号和工作类型查询工作信息列表
	public function getTaskInfosByUidType(){
		$uid = $this->user['client_account'];
		$type = $this->objInput->getInt('type_id');
		if(empty($type)){
			$type = 1;
		}
		$page = $this->objInput->getInt('page');
		$limit = 10;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($limit-1);
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		// 规范了函数名称 数据由原来的三维换成了二维
		//$TaskPushIds = $mTaskPush->getTaskPushByUidType($uid, $type, $offset, $limit);
		$TaskPushIds = $mTaskPush->getTaskPushByClientAccountAndTaskType($uid, $type, $offset, $limit);

		$repliedarr = array();
		foreach($TaskPushIds as $key=>$val){
			$repliedarr[$val['task_id']]['is_replied'] = $val['is_replied'];
		}//追加是否回复

		$TaskIds = array();
		foreach ($TaskPushIds as $key1=>$val1){
			$TaskIds[$uid][] = $val1['task_id'];
		}//获取任务id

		$mTask = ClsFactory::Create('Model.mTask');
		$TaskInfos = $mTask->getTaskById($TaskIds[$uid]);
		if(count($TaskInfos) < $limit){
			$flag = true;
		}else{
			array_pop($TaskInfos);
		}
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeById($type);
		$new_task_infos = array();
		foreach($TaskInfos as $key=>$val){
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
				$new_task_infos[$key]['task_title'] = cutstr($val['task_title'], 10, true);
				$new_task_infos[$key]['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])) ,120,true);
				
			}else{
				$val['is_reply'] = "";
				$new_task_infos[$key] = $val;
				$new_task_infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
				$new_task_infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
				$new_task_infos[$key]['type_name'] = $typelist[$type]['type_name'];
				$new_task_infos[$key]['task_title'] = cutstr($val['task_title'], 10, true);
				$new_task_infos[$key]['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])) ,120,true);
			}
		}
		if(empty($new_task_infos)){
			$new_task_infos = false;
		}
		$this->assign('TaskInfos',$new_task_infos);
		$this->assign('page',$page);
		$this->assign('flag',$flag);
		
		$this->display('expireinfos_list');
	}

	//查询所有系统类型的工作列表
	public function getTaskInfosByType(){
		$type = $this->objInput->getInt('type');
		$page = $this->objInput->getInt('page');
		$school_id = key($this->user['school_info']);
		$type = $type && in_array($type, array(1, 2, 3, 4)) ? $type : 1;
		$page = max($page, 1);

		$school_id = key($this->user['school_info']);

		$limit = 10;
		$offset = ($page-1)*$limit;
		$mTask = ClsFactory::Create('Model.mTask');
		$TaskInfos = $mTask->getTaskByType($type, $school_id, $offset, $limit + 1);

		if(count($TaskInfos) < ($limit + 1)){
			$flag = true;
		}else{
			array_pop($TaskInfos);
		}
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeById($type);
		foreach($TaskInfos as $key=>&$val){
			if($typelist[$type]['type_id'] == $val['task_type']){
				$TaskInfos[$key]['type_name'] = $typelist[$type]['type_name'];
			}
			$val['task_title'] = cutstr($val['task_title'], 10, true);
            $val['task_content']= cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])),200,true);
			$TaskInfos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
			$TaskInfos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
		}
		$this->assign('type',$type);
		$this->assign('page',$page);
		$this->assign('flag',$flag);
		$this->assign('school_id',$school_id);
		$this->assign('TaskInfos',$TaskInfos);
		
		$this->display('work_type_list');
	}


	//布置给我的工作列表根据账号查询列表
	public function arrangeworkself(){
		$uid = $this->user['client_account'];
		$page = $this->objInput->getInt('page');
		$end = 10;
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)*($end);
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		$TaskPushInfos = $mTaskPush->getTaskPushByUid($uid);
		$taskIds = $replay = $viewed = array();
		foreach($TaskPushInfos[$uid] as $key=>&$val){
			$taskIds[$uid][] = $val['task_id'];
			$replay[$val['task_id']] = $val['is_replied'];
			$viewed[$val['task_id']] = $val['is_viewed'];
		}
		$mTask = ClsFactory::Create('Model.mTask');
		$TaskInfos = $mTask->getTaskById($taskIds[$uid]);
		$mTaskType = ClsFactory::Create('Model.mTaskType');
		$typelist = $mTaskType->getTaskTypeSystemAll();
		$new_typelist = array();
		foreach($typelist as $val){
			$new_typelist[$val['type_id']] = $val['type_name'];
		}
		foreach($TaskInfos as $key=>$val){
			if($TaskInfos[$key]['need_reply'] == 1){
				if($replay[$val['task_id']] == 1){
					unset($TaskInfos[$key]);
					continue;
				}else{
					$TaskInfos[$key]['is_reply'] = "未回复";
				}
			}elseif($viewed[$val['task_id']]){
				unset($TaskInfos[$key]);
				continue;
			}
					$TaskInfos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
					$TaskInfos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
					$TaskInfos[$key]['task_title'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_title'])), 10, true);
					$TaskInfos[$key]['task_content'] =  cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])), 300, true);
					$task_type = intval($val['task_type']);
					$TaskInfos[$key]['type_name'] = $new_typelist[$task_type];
		}
		$new_TaskInfos = array();
		$new_TaskInfos = array_slice($TaskInfos,$offset,$end,true);

		$totalpage = ceil(count($TaskInfos)/$end);

		if($page >= $totalpage){
			$flag = true;
		}
		if(empty($new_TaskInfos)){
			$TaskInfos = false;
		}

		$this->assign('page',$page);
		$this->assign('flag',$flag);
		$this->assign('TaskInfos',$new_TaskInfos);
		
		$this->display('arrangeworkself');
	}

	//根据Task_id查看任务详情
	public function arrangeworkdetail(){
		$flag = $this->objInput->getStr('flag');
		$task_id = $this->objInput->getInt('task_id');
		$mTask = ClsFactory::Create('Model.mTask');
		$Taskinfolist = $mTask->getTaskById($task_id);
		$TaskPush = ClsFactory::Create('Model.mTaskPush');
		$TaskPushInfos = $TaskPush->getTaskPushByTaskId($task_id);
		$push_id = key($TaskPushInfos[$task_id]);
		$new_Taskinfos = array();
		foreach($Taskinfolist as $val){
			$new_Taskinfos[$val['task_id']] = $val;
			$new_Taskinfos[$val['task_id']]['add_time']= date('Y-m-d',$val['add_time']);
			$new_Taskinfos[$val['task_id']]['expiration_time']= date('Y-m-d',$val['expiration_time']);
			$new_Taskinfos[$val['task_id']]['is_replied'] = $TaskPushInfos[$task_id][$push_id]['is_replied'];
			$new_Taskinfos[$val['task_id']]['is_viewed'] = $TaskPushInfos[$task_id][$push_id]['is_viewed'];
		}
		if($new_Taskinfos[$task_id]['is_viewed'] == 0){
			$mTaskPush = ClsFactory::Create('Model.mTaskPush');
			$datarr = array(
				'is_viewed' => 1
			);
			$resault = $mTaskPush->modifyTaskPush($datarr,$push_id);
		}

		$this->assign('TaskInfos',$new_Taskinfos[$task_id]);
		$this->assign('push_id',$push_id);
		$this->assign('flag',$flag);
		$this->assign("task_id", $task_id);
		
		$this->display('arrangeworkdetail');
		}
		//查询工作回复信息
		public function getReplyList(){
			$page = $this->objInput->getInt('page');
			$task_id = $this->objInput->getInt('task_id');
			$client_name = $this->user['client_name'];
			$limit = 10;
			if(empty($page) || $page < 1){
				$page = 1;
			}
			$offset = ($page-1)*($limit-1);
			$mTaskReply = ClsFactory::Create('Model.mTaskReply');
			$TaskReplyInfos = $mTaskReply->getTaskReplyByTaskId($task_id, $offset, $limit);
			if(count($TaskReplyInfos[$task_id]) < $limit){
				$fl = true;
			}else{
				array_pop($TaskReplyInfos[$task_id]);
			}
			foreach($TaskReplyInfos[$task_id] as $key=>$val){
				$TaskReplyInfos[$task_id][$key]['add_time'] = date('Y-m-d',$val['add_time']);
				$TaskReplyInfos[$task_id][$key]['client_name'] = $client_name;
			}
			if($TaskReplyInfos){
				$json_arr = array(
				'error'=>array(
					'code'=>1,
					'message'=>""
				),
				'data'=>$TaskReplyInfos[$task_id],
				);
			}else{
				$json_arr = array(
				'error'=>array(
					'code'=>-1,
					'message'=>"查询失败！"
				),
				'data'=>$TaskReplyInfos[$task_id],
				);
			}
			echo json_encode($json_arr);
		}
		//工作回复信息
		public function addTaskReply(){
			$uid = $this->user['client_account'];
			$task_id = $this->objInput->postInt('task_id');
			$reply_content = $this->objInput->postStr('reply_content');
			$flag = $this->objInput->postStr('flag');
			$push_id = $this->objInput->postInt('push_id');
			$datarr = array(
				'task_id' => $task_id,
				'add_account' => $uid,
				'reply_content' => $reply_content,
				'add_time' => time()
			);
			$pushdatarr = array(
				'push_id'=>$push_id,
				'is_replied'=> 1
			);
			$mTaskPush = ClsFactory::Create('Model.mTaskPush');
			$pushresault = $mTaskPush->modifyTaskPush($pushdatarr,$push_id);
			$mTaskReply = ClsFactory::Create('Model.mTaskReply');
			$resault = $mTaskReply->addTaskReply($datarr);
			if($resault && $pushresault){
				$json_arr = array(
						'error'=>array(
							'code'=>1,
							'message'=>'回复成功'
						)
				);
			}else{
				$json_arr = array(
						'error'=>array(
							'code'=>-1,
							'message'=>'回复失败'
						)
				);
			}
			echo json_encode($json_arr);
		}

		//通过日期查询日程
		public function ScheduleManage(){
			$fl = $this->objInput->getStr('fl');
			$page = $this->objInput->getInt('page');
			$q_date = $this->objInput->postStr('q_date');//起始日期
			$h_date = $this->objInput->postStr('h_date');//结束日期

			if(!empty($q_date)){
				$q_date = strtotime($q_date);
			}
			if(!empty($h_date)){
				$h_date = strtotime($h_date);
			}

			if(!empty($q_date) && !empty($h_date)){
				$upd_start_time  = mktime(0,0,0,date('m',$q_date." 0:0:0"),date('d',$q_date." 0:0:0"),date('Y',$q_date." 0:0:0"));
				$upd_end_time = mktime(0,0,0,date('m',$h_date." 0:0:0"),date('d',$h_date." 0:0:0")+1,date('Y',$h_date." 0:0:0"));
			}elseif(!empty($q_date) && empty($h_date)){
				$upd_start_time = mktime(0,0,0,date('m',$q_date." 0:0:0"),date('d',$q_date." 0:0:0"),date('Y',$q_date." 0:0:0"));
				$upd_end_time = mktime(0,0,0,date('m',$q_date." 0:0:0"),date('d',$q_date." 0:0:0")+1,date('Y',$q_date." 0:0:0"));
			}
			$uid = $this->user['client_account'];
			$limit = 10;
			if(empty($page) || $page < 1){
				$page = 1;
			}
			$offset = ($page-1) * $limit;
			if(!empty($q_date)){
				if($fl == 'schedule'){
					$mScheduleType = ClsFactory::Create("Model.mScheduleType");
					$schedule_type = $mScheduleType->getScheduleTypeByUid($uid, true);
					$mSchedule = ClsFactory::Create('Model.mSchedule');
					$Infos = $mSchedule->getScheduleByDate($uid, $upd_start_time, $upd_end_time, $offset, $limit+1);
					foreach($Infos as $key=>$val){
						$Infos[$key]['schedule_message'] =cutstr(strip_tags(htmlspecialchars_decode($val['schedule_message'])),200,true);
						$Infos[$key]['schedule_title'] =cutstr(strip_tags(htmlspecialchars_decode($val['schedule_title'])),10,true);
						$Infos[$key]['type_name'] = $schedule_type[$val['type_id']]['type_name'];
						$Infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
					}

				}else{
					$mTaskPush = ClsFactory::Create('Model.mTaskPush');
					
					//跟换了函数名称 数据也由原来的三维换成了二维
					$taskpushinfos = $mTaskPush->getTaskPushByAddTime($uid, $upd_start_time, $upd_end_time, $offset, $limit+1);
					//$taskpushinfos = $taskpushinfos[$uid];

					$taskids = array();
					foreach($taskpushinfos as $key=>$val){
						$taskids[]=$val['task_id'];
					}
					$mTask = ClsFactory::Create('Model.mTask');
					$TaskInfos = $mTask->getTaskByIdTime($taskids);
					$repliedarr = array();

					foreach($taskpushinfos as $key=>$val){
						$repliedarr[$val['task_id']]['is_replied'] = $val['is_replied'];
					}//追加是否回复

					$mTaskType = ClsFactory::Create('Model.mTaskType');
					$typelist = $mTaskType->getTaskTypeSystemAll();
					$new_typelist = array();
					foreach($typelist as $val){
						$new_typelist[$val['type_id']] = $val['type_name'];
					}
					foreach($TaskInfos as $key=>$val){
						if($val['need_reply'] == 1){
							if($repliedarr[$val['task_id']]['is_replied'] == 1){
								 $val['is_reply'] = "已回复";
							}else{
								$val['is_reply'] = "未回复";

							}

							$Infos[$key] = $val;
							$Infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
							$Infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
							$Infos[$key]['type_name'] = $typelist[$val['task_type']]['type_name'];
							$Infos[$key]['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])),200,true);
							$Infos[$key]['task_title'] = cutstr($val['task_title'],10,true);
							
						}else{
							$val['is_reply'] = "";
							$Infos[$key] = $val;
							$Infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
							$Infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
							$Infos[$key]['type_name'] = $typelist[$val['task_type']]['type_name'];
							$Infos[$key]['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($val['task_content'])),200,true);
							$Infos[$key]['task_title'] = cutstr($val['task_title'],10,true);
						}

					}

				}
				if(count($Infos) < $limit+1){
						$flag = true;
					}else{
						array_pop($Infos);
					}
			}

			$h_date = !empty($h_date) ? date("Y-m-d", $h_date) : "";
			$q_date = !empty($q_date) ? date("Y-m-d", $q_date) : "";

			$this->assign('q_date',$q_date);
			$this->assign('h_date',$h_date);
			$this->assign('fl',$fl);
			$this->assign('page',$page);
			$this->assign('flag',$flag);
			$this->assign('Infos',$Infos);
			
			$this->display('schedulemanage');
		}

		//通过日程管理查询日程详细内容
		public function ScheduleManageDetail(){
			$fl = $this->objInput->getStr('fl');
			$task_id = $this->objInput->getInt('task_id');
			$schedule_id = $this->objInput->getInt('schedule_id');
			if($fl == "schedule"){
				$mSchedule = ClsFactory::Create('Model.mSchedule');
				$Infos = $mSchedule->getScheduleById($schedule_id);
			}else{
				$mTask = ClsFactory::Create('Model.mTask');
				$Infos = $mTask->getTaskById($task_id);
			}
			foreach($Infos as $key=>$val){
				$Infos[$key]['add_time'] = date('Y-m-d',$val['add_time']);
				$Infos[$key]['upd_time'] = date('Y-m-d',$val['upd_time']);
			}
			$this->assign('Infos',$Infos);
			$this->assign('fl',$fl);
			$this->display('schedulemanagedetail');
		}

		/**
		 * 解析工作内容信息
		 * @param $task
		 */
	   protected function parseTask($task) {
	       if(empty($task)) {
	           return false;
	       }

	       $systypelist = $this->getSysTypeList();
	       $task_type_name = $systypelist[$task['task_type']];

	       $task['add_time'] = date('Y-m-d', $task['add_time']);
		   $task['upd_time'] = date('Y-m-d', $task['upd_time']);
		   $task['type_name'] = $task_type_name;
	 	   $task['task_content'] = cutstr(strip_tags(htmlspecialchars_decode($task['task_content'])), 80, true);

	 	   return $task;
	   }

	   /**
	    * 获取系统分类类型
	    */
	   protected function getSysTypeList() {
	       static $systypelist = null;
	       if(is_null($systypelist)) {
	           $mTaskType = ClsFactory::Create('Model.mTaskType');
		       $systypelist = $mTaskType->getTaskTypeSystemAll();
	       }

	       return !empty($systypelist) ? $systypelist : false;
	   }
}