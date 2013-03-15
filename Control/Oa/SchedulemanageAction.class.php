<?php
class SchedulemanageAction extends OaController{
    protected $user = array();
    
    public function _initialize(){
        parent::_initialize();
        
        import("@.Common_wmw.WmwString");
       
		$this->assign('uid', $this->user['client_account']);
    }

    //个人日程展示
    public function serarchshowScheduleinfo(){
        $uid = $this->user['client_account'];
        $schedule_id = $this->objInput->postInt("schedule_id");
        $schedule_name = $this->objInput->postStr("schedule_name");
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $Schedule_info = $mSchedule->getScheduleById($schedule_id);
        if(!empty($Schedule_info)) {
            $Schedule_info[$schedule_id]['schedule_message'] = htmlspecialchars_decode($Schedule_info[$schedule_id]['schedule_message']);
            $Schedule_info[$schedule_id]['add_time'] = date("Y-m-d", $Schedule_info[$schedule_id]['add_time']);
            if(!empty($Schedule_info[$schedule_id]['expiration_time'])){
                $Schedule_info[$schedule_id]['expiration_time'] = date('Y-m-d', $Schedule_info[$schedule_id]['expiration_time']);
            }else{
                $Schedule_info[$schedule_id]['expiration_time'] = "";
            }
            $Schedule_info[$schedule_id]['now_time'] = date("m月d日 H时");
        }
        $this->assign("Schedule_info", $Schedule_info[$schedule_id]);
        $this->assign("schedule_id", $schedule_id);
        $this->assign("publish_name", $this->user["client_name"]);
        $this->assign("schedule_name", $schedule_name);
        $this->display("schedule_info");
    }
    
    //个人日程展示
    public function showScheduleinfo(){
        $uid = $this->user['client_account'];
        $schedule_id = $this->objInput->getInt("schedule_id");
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $Schedule_info = $mSchedule->getScheduleById($schedule_id);
        if(!empty($Schedule_info)) {
            $Schedule_info[$schedule_id]['schedule_message'] = htmlspecialchars_decode($Schedule_info[$schedule_id]['schedule_message']);
            $Schedule_info[$schedule_id]['add_time'] = date("Y-m-d", $Schedule_info[$schedule_id]['add_time']);
            if(!empty($Schedule_info[$schedule_id]['expiration_time'])) {
                $Schedule_info[$schedule_id]['expiration_time'] = date('Y-m-d', $Schedule_info[$schedule_id]['expiration_time']);
            }else {
                $Schedule_info[$schedule_id]['expiration_time'] = "";
            }
            $Schedule_info[$schedule_id]['now_time'] = date("m月d日 H时");
        }
        $this->assign("Schedule_info", $Schedule_info[$schedule_id]);
        $this->assign("schedule_id", $schedule_id);
        $this->assign("publish_name", $this->user["client_name"]);
        $this->display("show_schedule_info");
    }
    
    //删除个人日程
    public function delSchedule(){
        $schedule_id = $this->objInput->postInt("schedule_id");
        $err_msg = array();
        if(!empty($schedule_id)) {
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $schedule_list = $mSchedule->getScheduleById($schedule_id);
            $schedule = & $schedule_list[$schedule_id];
            
            if(!empty($schedule)) {
                if($schedule['client_account'] == $this->user['client_account']) {
                    $resault = $mSchedule->delSchedule($schedule_id);
                    if(empty($resault)) {
                       $err_msg[] = "删除失败!"; 
                    }
                } else {
                    $err_msg[] = "您无权删除该日程信息!";
                }
            } else {
                $err_msg[] = "该日程已经被删除!";
            }
        } else {
            $err_msg[] = "系统繁忙，稍后重试!";
        }
        
        if(empty($err_msg)) {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '删除成功!'
                ),
                'data' => array(),
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => array_shift($err_msg),
                ),
                'data' => array(),
            );
        }
        
        echo json_encode($json_data);
    }

    //提取个人日程草稿
    function getScheduleDraftInfo(){
        $page = $this->objInput->postInt("page");
        $type_id = $this->objInput->postInt("type_id");
        $page = max(1,$page);
        $length = 11;
        $offset = ($page -1) *($length -1);
        $uid = $this->user['client_account'];
        $is_end_page = false;
        if(empty($type_id)){
            $code = -1;
            $message = "获取个人日程草稿信息失败或这当前分类下没有草稿！";
        }
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $Schedule_list = $mSchedule->getScheduleByUidAndIs_draft($uid, $type_id, $offset, $length);
        $code = 1;
        if(empty($Schedule_list)){
            $code = -1;
            $message = "获取个人日程草稿信息失败或这当前分类下没有草稿！";
        }
        if($code >0) {
            if(count($Schedule_list)<$length){
                $is_end_page = true;
            }else{
                array_pop($Schedule_list);
            }
            $new_Schedule_list = array();
            $upd_time = array();
            foreach($Schedule_list as $key=>$val){
                $upd_time[] = $val['upd_time'];
                $val['schedule_shor_name'] = cutstr($val,20,true);
                $val['upd_time'] = date("m月d日 H时",$val['upd_time']);
                $new_Schedule_list[$key] = $val;
            }
        }
        $json_list = array(
            'error'=>array(
    			'code'=>$code,
    			'message'=>$message,
            ),
            'data'=>array(
            	'draftinfo'=>$new_Schedule_list,
                'is_end_page'=>$is_end_page,
            ),
        );
        echo json_encode($json_list);
    }

    //修改个人日程的类型
    function changescheduleType(){
        $schedule_id = $this->objInput->postInt("schedule_id");
        $schedule_type = $this->objInput->postInt("schedule_type");
        $dataarr = array(
            'type_id'=>$schedule_type,
        );
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $resault = $mSchedule->modifySchedule($dataarr, $schedule_id);
        if($resault){
            $code = 1;
            $message="修改成功！";
        }else{
            $code = -1;
            $message = "修改失败！";
        }
        $jsonarr = array(
            'error'=>array(
                'code'=>$code,
                'message'=>$message,
            ),
            'data'=>"",
        );
        echo json_encode($jsonarr);
    }

    //个人日程信息修改页面
    function show_modifySchedule_info(){
        $schedule_id = $this->objInput->getInt("schedule_id");
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $schedule_info = $mSchedule->getScheduleById($schedule_id);
        $mSchedule_Type = ClsFactory::Create("Model.mScheduleType");
        $ScheduleTypeList = $mSchedule_Type->getScheduleTypeByUid($this->user['client_account'], true);
        $new_schedule_info = $schedule_info[$schedule_id];
        $new_schedule_info['schedule_title'] = html_entity_decode($new_schedule_info['schedule_title']);
        if(!empty($new_schedule_info['expiration_time'])){
            $date = date("Y-m-d",$new_schedule_info['expiration_time']);
            $hours = 24-$new_schedule_info['deadline_hours'];
            $new_schedule_info['sms_remaind'] = $date." ".$hours.":00";
            $new_schedule_info['expiration_time'] = $date;
        } else {    
            $new_schedule_info['expiration_time'] = "";
        }
        
        $date = date("Y-m-d");
        $this->assign('schedule_info', $new_schedule_info);
        $this->assign('ScheduleTypeList', $ScheduleTypeList);
        $this->assign('date', $date);
        $this->display('modifyschedule_info');
    }
    
    //个人日程信息修改
    function modifySchedule_info(){
        $draft_id = $this->objInput->postInt('draft_id');
        $schedule_id = $this->objInput->postInt('schedule_id');
        $schedule_type = $this->objInput->postInt("schedule_type");
        $schedule_title = $this->objInput->postStr("schedule_title");
        $schedule_message = $this->objInput->postStr("schedule_message");
        $expiration_time = $this->objInput->postStr("expiration_time");
        $deadline_hours = $this->objInput->postInt("deadline_hours");
        $is_time = $this->objInput->postInt('is_time');
        $is_hours = $this->objInput->postInt('is_hours');
        $push_time = $this->objInput->postStr("push_time");
        $code = 1;
        if(empty($schedule_id) || empty($schedule_type)|| empty($schedule_title) || empty($schedule_message)){
            $code = -1;
            $message="修改个人日程失败！";
        }
        
        if(WmwString::mbstrlen($schedule_title) > 20){
            $message="标题长度不能大于20个字符！";
        }
        
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $schedule_list = $mSchedule->getScheduleById($schedule_id);
        $schedule = & $schedule_list[$schedule_id];
        if($schedule["client_account"] != $this->user["client_account"]){
            $code = -1;    
            $message="无权限操作！";
        }
        if($code>0){
            $uid = $this->user['client_account'];
            if(!empty($is_time)){
                if(!empty($is_hours)){
                    $dataarr = array(
                        'schedule_title'=>$schedule_title,
                        'schedule_message'=>$schedule_message,
            	        'schedule_start_time'=>time(),
            	        'upd_time'=>time(),
            			'type_id'=>$schedule_type,
                    	'expiration_time'=>!empty($expiration_time) ? strtotime($expiration_time) : $expiration_time,
                        'deadline_hours'=>!empty($is_hours) ? $deadline_hours : 0,
                    );
                }else{
                    $dataarr = array(
                        'schedule_title'=>$schedule_title,
                        'schedule_message'=>$schedule_message,
            	        'schedule_start_time'=>time(),
            	        'upd_time'=>time(),
            			'type_id'=>$schedule_type,
                    	'expiration_time'=>!empty($expiration_time) ? strtotime($expiration_time) : $expiration_time,
                        'deadline_hours'=>0,
                    );
                }
            }else{
                $dataarr = array(
                        'schedule_title'=>$schedule_title,
                        'schedule_message'=>$schedule_message,
            	        'schedule_start_time'=>time(),
            	        'upd_time'=>time(),
            			'type_id'=>$schedule_type,
                    	'expiration_time'=>"",
                        'deadline_hours'=>0,
                    );
            }
            $resalut = $mSchedule->modifySchedule($dataarr, $schedule_id);
            if($resalut){
                $code = 1;
                $message="修改个人日程成功！";
            }else{
                $code = -1;
                $message="修改个人日程失败！";
            }
            if($resalut && $this->user['school_info'] && $deadline_hours) {
                 $school_id = key($this->user['school_info']);
                 $operation_strategy = $this->user['school_info'][$school_id];
                 $phone_num = $this->is_bingphone($uid);
            }
            if($resalut && $phone_num && $deadline_hours) {
                $send_message_content = $this->user['client_name']."-日程安排：标题。到期时间：".$expiration_time." 请登录集中办公平台查看全文";
                $message_dataarr = array(
                    'accept_phone'=>$phone_num,
                    'sms_message'=>$send_message_content,
                    'push_time'=>time($push_time),
                    'business_type'=>$operation_strategy,
                    'add_time'=>time(),
                );
                $mPretreatSms = ClsFactory::Create("Model.mPretreatSms");
                $resault = $mPretreatSms->addPretreatSms($message_dataarr);
                if(!$resalut) {
                    $code = -1;
                    $message="修改个人日程失败！";
                }
            }
            
            if(!empty($draft_id)){
                $mSchedule->delSchedule($draft_id);
            }
        }
        $json_arr = array(
            'error'=>array(
                'code'=>$code,
                'message'=>$message,
            ),
            'data'=>"",
        );
        echo json_encode($json_arr);
    }

    //个人日程保存为草稿
    function saveScheduleToDraft(){
        $draft_id = $this->objInput->postInt("draft_id");
        $schedule_type = $this->objInput->postInt("schedule_type");
        $schedule_title = $this->objInput->postStr("schedule_title");
        $schedule_message = $this->objInput->postStr("schedule_message");
        $code = 1;
        if(empty($schedule_type) || empty($schedule_title) || empty($schedule_message)){
            $code = -1;
            $message="添加个人日程失败！";
        }
        if($code>0){
            $uid = $this->user['client_account'];
            $dataarr = array(
                'client_account'=>$uid,
                'schedule_title'=>$schedule_title,
                'schedule_message'=>$schedule_message,
    	        'schedule_start_time'=>time(),
                'is_draft'=>SCHEDULE_IS_DRAFT_TRUE,
    	        'upd_time'=>time(),
    			'type_id'=>$schedule_type,
    			'add_time'=>time(),
            );
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $resalut = $mSchedule->addSchedule($dataarr);
            if($resalut){
                $code = 1;
                $message="个人日程保存为草稿成功！";
                if(!empty($draft_id)) {
                    $mSchedule->delSchedule($draft_id);
                }
            }else{
                $code = -1;
                $message="个人日程保存为草稿失败！";
            }
        }
        $json_arr = array(
            'error'=>array(
                'code'=>$code,
                'message'=>$message,
            ),
            'data'=>"",
        );
        echo json_encode($json_arr);
    }

    //添加个人日程个人日程
    function addSchedule_info(){
        $draft_id = $this->objInput->postInt("schedule_id");
        $schedule_type = $this->objInput->postInt("schedule_type");
        $schedule_title = $this->objInput->postStr("schedule_title");
        $schedule_message = $this->objInput->postStr("schedule_message");
        $expiration_time = $this->objInput->postStr("expiration_time");
        $deadline_hours = $this->objInput->postInt("deadline_hours");
        $push_time = $this->objInput->postStr("push_time");
        $code = 1;
        if(empty($schedule_type) || empty($schedule_title) || empty($schedule_message)){
            $code = -1;
            $message="添加个人日程失败！";
        }
        
        if(WmwString::mbstrlen($schedule_title) > 20){
            $message="标题长度不能大于20个字符！";
        }
        
        if($code>0){
            $uid = $this->user['client_account'];
            if(empty($expiration_time) || empty($deadline_hours)){
                $dataarr = array(
                    'client_account'=>$uid,
                    'schedule_title'=>$schedule_title,
                    'schedule_message'=>$schedule_message,
        	        'schedule_start_time'=>time(),
        	        'upd_time'=>time(),
        			'type_id'=>$schedule_type,
        			'add_time'=>time(),
                );
            }elseif(empty($deadline_hours)){
                $dataarr = array(
                    'client_account'=>$uid,
                    'schedule_title'=>$schedule_title,
                    'schedule_message'=>$schedule_message,
        	        'schedule_start_time'=>time(),
        	        'upd_time'=>time(),
        			'type_id'=>$schedule_type,
        			'add_time'=>time(),
        			'expiration_time'=>strtotime($expiration_time),
                );
            }else{
                $dataarr = array(
                    'client_account'=>$uid,
                    'schedule_title'=>$schedule_title,
                    'schedule_message'=>$schedule_message,
        	        'schedule_start_time'=>time(),
        	        'upd_time'=>time(),
        			'type_id'=>$schedule_type,
        			'add_time'=>time(),
        			'expiration_time'=>strtotime($expiration_time),
                    'deadline_hours'=>$deadline_hours,
                );
            }
            $mSchedule = ClsFactory::Create("Model.mSchedule");
            $resalut = $mSchedule->addSchedule($dataarr);
            if($resalut){
                $code = 1;
                $message="添加个人日程成功！";
            }else{
                $code = -1;
                $message="添加个人日程失败！";
            }
            if($resalut && $this->user['school_info'] && $deadline_hours) {
                 $school_id = key($this->user['school_info']);
                 $operation_strategy = $this->user['school_info'][$school_id];
                 $phone_num = $this->is_bingphone($uid);
            }
            if($resalut && $phone_num && $deadline_hours) {
                $resault = 0;
                $send_message_content = $this->user['client_name']."-日程安排：标题。到期时间：".$expiration_time." 请登录集中办公平台查看全文";
                $message_dataarr = array(
                    'accept_phone'=>$phone_num,
                    'sms_message'=>$send_message_content,
                    'push_time'=>strtotime($push_time),
                    'business_type'=>$operation_strategy,
                    'add_time'=>time(),
                );
                $mPretreatSms = ClsFactory::Create("Model.mPretreatSms");
                $resault = $mPretreatSms->addPretreatSms($message_dataarr);
                if(!$resalut) {
                    $code = -1;
                    $message="添加个人日程失败！";
                }
            }
            if(!empty($draft_id)){
                $mSchedule->delSchedule($draft_id);
            }
        }
        $json_arr = array(
            'error'=>array(
                'code'=>$code,
                'message'=>$message,
            ),
            'data'=>"",
        );
        echo json_encode($json_arr);
    }

    //修改个人日程分类
    function showSchedule_type(){
        $uid = $this->user['client_account'];
        $mScheduleType = ClsFactory::Create('Model.mScheduleType');
        $ScheduleType_info = $mScheduleType->getScheduleTypeByUid($uid, true);
        if(!empty($ScheduleType_info)){
            $code = 1;
        }else{
            $code = -1;
        }

        $json_data = array(
            'error' => array(
                'code' => $code,
                'message' => '',
            ),
            'data' => $ScheduleType_info,
        );

        echo json_encode($json_data);
    }

    //日程添加页面
    function showaddSchedule(){
        $mSchedule_Type = ClsFactory::Create("Model.mScheduleType");
        $ScheduleTypeList = $mSchedule_Type->getScheduleTypeByUid($this->user['client_account'], true);
        $date = date("Y-m-d");
        $this->assign('ScheduleTypeList', $ScheduleTypeList);
        $this->assign('date', $date);
        $this->display("scheduleinfo");
    }
    
    //账号转换手机码
    function is_bingphone($uid){
        if(empty($uid)){
            return false;
        }
        $mBusinessphone = ClsFactory::Create("Model.mBusinessphone");
        $phone_info = $mBusinessphone->changeuidtophonenum($uid);
        return !empty($phone_info) ? $phone_info[$uid]: false;
    }

    //通过时间个名称得到日程列表
    public function searchScheduleinfo(){
        $page = $this->objInput->postInt("page");
        $uid = $this->user['client_account'];
        $schedule_name = $this->objInput->postStr("schedule_name");
        $page = max(1,$page);
        $length = 11;
        $offset = ($page - 1) *($length - 1);
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $mSchedule_list = $mSchedule->getScheduleByName($schedule_name, $uid , $offset, $length);
        $is_end_page = false;
        if(!empty($mSchedule_list)) {
            if(count($mSchedule_list) < $length){
                $is_end_page = true;
            }else{
                array_pop($mSchedule_list);
            }
            foreach($mSchedule_list as $schedule_id=>$schedule_info){
                $mSchedule_list[$schedule_id]['upd_time'] = date("Y年m月d日", $schedule_info['upd_time']);
                $mSchedule_list[$schedule_id]['schedule_title'] = cutstr($schedule_info['schedule_title'], 50, true);
            }
        }
        $this->assign("page", $page);
        $this->assign("is_end_page", $is_end_page);
        $this->assign("mSchedule_list", $mSchedule_list);
        $this->assign("schedule_name", $schedule_name);
        $this->display("schedule_search");
    }
    
    //通过时间个名称得到日程列表
    public function jsonsearchScheduleinfo(){
        $page = $this->objInput->postInt("page");
        $uid = $this->user['client_account'];
        $schedule_name = $this->objInput->postStr("schedule_name");
        $page = max(1,$page);
        $length = 11;
        $offset = ($page - 1) *($length - 1);
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $mSchedule_list = $mSchedule->getScheduleByName($schedule_name, $uid, $offset, $length);
        $new_mSchedule_list = array();
        $is_end_page = false;
        $code = -1;
        $message = "系统繁忙！";
        if(!empty($mSchedule_list)) {
            $code = 1;
            $message="成功！";
            if(count($mSchedule_list) < $length){
                $is_end_page = true;
            }else{
                array_pop($mSchedule_list);
            }
            foreach($mSchedule_list as $schedule_id=>$schedule_info){
                $mSchedule_list[$schedule_id]['upd_time'] = date("Y年m月d日", $schedule_info['upd_time']);
                $mSchedule_list[$schedule_id]['schedule_title'] = cutstr($schedule_info['schedule_title'], 50, true);
            }
        }
        $json_arr = array(
            'error'=>array(
                'code'=>$code,
                'message'=>$message,
            ),
            'data'=>array(
            	'mSchedule'=>$mSchedule_list,
                'is_end_page'=>$is_end_page,
                'schedule_name'=>$schedule_name,
            ),
        );
        echo json_encode($json_arr);
    }
    
  
    //日程分类管理列表--查询
    public function ScheduleTypeList(){
        $mScheduleType = ClsFactory::Create("Model.mScheduleType");
        $stype_list = $mScheduleType->getScheduleTypeByUid($this->user['client_account'], false);//只查询用户自定义日程类型
        
        if(!empty($stype_list)) {
            foreach($stype_list as $type_id=>$type) {
                $stype_list[$type_id] = array(
                    'type_id' => $type_id,
                    'type_name' => $type['type_name'],
                );
            }
        }
        
        if(empty($stype_list)) {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '没有分类信息!'
                ),
                'data' => array(),
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '分类信息获取成功!',
                ),
                'data' => $stype_list,
            );
        }
        
        echo json_encode($json_data);
    }
    
    //日程分类管理列表--添加    
    public function addScheduleType(){
        $type_name = $this->objInput->postStr('type_name');
        $current_uid = $this->user['client_account'];
        
        $err_msg = array();
        if(empty($type_name)) {
            $err_msg[] = "类型名称不能为空!";
        } else {
            $mScheduleType = ClsFactory::Create('Model.mScheduleType');
            $exist_list = $mScheduleType->getScheduleTypeByUid($current_uid, true);
            if(!empty($exist_list)) {
                foreach($exist_list as $typeinfo) {
                    if($typeinfo['type_name'] == $type_name) {
                        $err_msg[] = "该分类已存在!";
                        break;
                    }
                }
            }
        }
        
        if(empty($err_msg)) {
            $add_data = array(
            	'type_name' => $type_name,
                'client_account' => $current_uid,
                'add_time' => time(),
            );
            $type_id = $mScheduleType->addScheduleType($add_data, true);
            if(empty($type_id)) {
                $err_msg[] = "系统繁忙,添加失败!";
            }
        }
        
        if(empty($err_msg)) {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '添加成功!',
                ),
                'data' => array(
                    'type_id' => $type_id,
                    'type_name' => $type_name,
                ),
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => array_shift($err_msg),
                ),
                'data' => array(),
            );
        }
        
        echo json_encode($json_data);
    }
    
    
 //个人日程列表
    function Schedeule_list_info(){
        $uid = $this->user['client_account'];
        $length = 11;
        $page = $this->objInput->getInt("page");
        $page = max(1,$page);
        $offset = ($page -1)*($length-1);
        $mSchedule = ClsFactory::Create("Model.mSchedule");
        $Schedule_list = $mSchedule->getScheduleByUid($uid, $offset, $length);
        $is_end_page = false;
        if(count($Schedule_list) < $length){
            $is_end_page = true;
        }else{
            array_pop($Schedule_list);
        }
        $mScheduleType = ClsFactory::Create("Model.mScheduleType");
        $schedule_type_list = $mScheduleType->getScheduleTypeByUid($uid,true);
        $new_Schedule_list = array();
        $upd_time = array();
        if(!empty($Schedule_list)){
            foreach($Schedule_list as $key=>$val){
                $val['add_time'] = date('Y年m月d日');
                $val['short_schedule_message'] = cutstr(strip_tags(html_entity_decode($val['schedule_message'])), 500, true);
                $val['is_expiration_time'] = !empty($val['expiration_time']) ? 1 : 0;
                $val['schedule_message'] = strip_tags(html_entity_decode(html_entity_decode($val['schedule_message'])));
                $val['is_deadline_hours'] = !empty($val['deadline_hours']) ? 1 : 0;
                $val['type_name'] = $schedule_type_list[$val['type_id']]['type_name'];
                $upd_time[] = $val['upd_time']; 
                $val['schedule_title'] = cutstr($val['schedule_title'], 10, true);
                $new_Schedule_list[$key] = $val;
            }
        }
        $this->assign("shedule","shedule");
        $this->assign('schedule_list', $new_Schedule_list);
        $this->assign('is_end_page', $is_end_page);
        $this->assign('page', $page);
        $this->display('schedule_list');
    }
    
    
    
    
    //日程分类管理列表--修改    
    public function modifyScheduleType(){
        $type_id = $this->objInput->postInt('type_id');
        $new_type_name = $this->objInput->postStr('type_name');
        $current_uid = $this->user['client_account'];
        
        $err_msg = array();
        if(empty($type_id) || empty($new_type_name)) {
            $err_msg[] = "类型名称不能为空!";
        } else {
            $mScheduleType = ClsFactory::Create('Model.mScheduleType');
            $exist_list = $mScheduleType->getScheduleTypeByUid($current_uid, true);
            
            if(!isset($exist_list[$type_id])) {
                $err_msg[] = "你要修改的分类不存在!";
            } else {
                $typeinfo = $exist_list[$type_id];
                //未做修改
                if($typeinfo['type_name'] == $new_type_name) {
                    $err_msg[] = "未作修改!";
                } else {
                    //判断是否和其他分类重名
                    foreach($exist_list as $key=>$type) {
                        if($key != $type_id && $type['type_name'] == $new_type_name) {
                            $err_msg[] = "该分类已存在!";
                            break;
                        }
                    }
                }
            }
        }
        
        //重命名
        if(empty($err_msg)) {
            $upd_data = array(
                'type_name' => $new_type_name,
                'add_time'  => time(),
            );
            $effect_id = $mScheduleType->modifyScheduleType($upd_data, $type_id);
            if(empty($effect_id)) {
                $err_msg[] = "系统繁忙,修改失败!";
            }
        }
        
        if(empty($err_msg)) {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '修改成功!',
                ),
                'data' => array(
                    'type_id' => $type_id,
                    'type_name' => $new_type_name,
                )
            );
        } else {
            $json_data = array(
                'error' => array(
                	'code' => -1,
                    'message' => array_shift($err_msg),
                ),
                'data' => array(),
            );
        }
        
        echo json_encode($json_data);
    }
    
    //日程分类管理列表--删除    
    public function delScheduleType(){
        $type_id = $this->objInput->getInt('type_id');
        
        $err_msg = array();
        
        $mScheduleType = ClsFactory::Create('Model.mScheduleType');
        if(!empty($type_id)) {
            $stypelist = $mScheduleType->getScheduleTypeById($type_id);
            $typeinfo = & $stypelist[$type_id];
            if(empty($typeinfo)) {
                $err_msg[] = "您要删除的分类信息不存在!";
            } else if($typeinfo['client_account'] != $this->user['client_account']) {
                $err_msg[] = "您无权删除该分类!";
            }
        } else {
            $err_msg[] = "您要删除的分类信息不存在!";
        }
        
        //删除相应的分类信息
        if(empty($err_msg)) {
             $effect_id = $mScheduleType->delScheduleType($type_id);
             if(empty($effect_id)) {
                 $err_msg[] = "系统繁忙,删除失败!";
             }else{//将该分类下的日程类型修改为默认分类
                 $datas = array(
                 	'type_id' => 1//默认分类的id为1
                 );
                 $mSchedule = ClsFactory::Create("Model.mSchedule");
                 $mSchedule->modifyScheduleByUidAndTypeId($datas, $this->user['client_account'], $type_id);   
             }
        }
        
        if(empty($err_msg)) {
            $json_data = array(
                'error' =>array(
                    'code' => 1,
             		'message' => '删除成功!',    
                ),
                'data' =>array(
                    'type_id' => $type_id,
                ),
            );
        } else {
            $json_data = array(
                'error' =>array(
                    'code' => -1,
             		'message' => array_shift($err_msg),    
                ),
                'data' =>array(),
            );  
        }
        
        echo json_encode($json_data);
    }
}