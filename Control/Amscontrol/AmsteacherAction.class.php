<?php
class AmsteacherAction extends AmsController{
    //作者：郭学文
    protected $is_school = true;
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html;charset=utf-8");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Pathmanagement_ams");
	    $this->assign('username', $this->user['ams_name']);
	}
	
	//跳转验证检测登录账号是否属于此学校的
    private function checkUser($uid,$schoolid) {
        $isThis = $this->checkLoginerInSchool($uid, $schoolid);
        
        return !empty($isThis) ? true : false;
    }
    
	//老师列表信息
	function showTercherManage(){
	    $schoolid     =     $this->user['schoolinfo']['school_id'];
	    $flag         =     $this->objInput->getStr('stamp');
	    $excelflag    =     $this->objInput->getStr('outputexcel');
	    
	    if(!(self::checkUser($this->user['ams_account'], $schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }

	    $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略
		$this->assign('operationStrategy', $operationStrategy);

	    $subInfo = $this->showSubjectInfo($schoolid);
	    
	    $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	    $schoolTeacherInfo_arr = $mSchoolTeacher->getSchoolTeacherInfoBySchoolId($schoolid);
	    $schoolTeacherInfo = & $schoolTeacherInfo_arr[$schoolid];
	    
	    $user_subject_list = array();    //老师账号所对应的科目值
	    if(!empty($schoolTeacherInfo)) {
    	    foreach($schoolTeacherInfo as $key=>$schoolteacher) {
    	        $uid = $schoolteacher['client_account'];
    	        
    	        $subject_id = intval($schoolteacher['subject_id']);
    	        $subject_name = isset($subInfo[$subject_id]) ? $subInfo[$subject_id] : "暂不任课";
    	        
    	        //如果相应的信息已经存在则使用","对数据进行分割
    	        if(isset($user_subject_list[$uid])) {
    	            $user_subject_list[$uid]['subject_id'] .= "," . $subject_id;
    	            $user_subject_list[$uid]['subject_name'] .= "," . $subject_name;
    	        } else {
    	            $user_subject_list[$uid]['subject_id'] = $subject_id;
    	            $user_subject_list[$uid]['subject_name'] = $subject_name;
    	        }
    	        
    	        unset($schoolTeacherInfo[$key]);
    	    }
	    }
	    
		$mDepartment = ClsFactory::Create('Model.mDepartment');
		$department_arr = $mDepartment->getDepartmentBySchoolId($schoolid);
		$department_list = & $department_arr[$schoolid];
		
		$department_info = array();
		if(!empty($department_list)) {
    		foreach($department_list as $dpt_id=>$dpt_info){
                $department_info[$dpt_id] = $dpt_info['dpt_name'];
                
                unset($department_list[$dpt_id]);
    		}
		}
		
		$teacher_uids = array_keys($user_subject_list);
		
		$mUser = ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getUserBaseByUid($teacher_uids);
        
        $mDepartmentMembers = ClsFactory::Create('Model.mDepartmentMembers');
        $mDepartmentMembers_info = $mDepartmentMembers->getDepartmentMembersByUid($teacher_uids);
        
        $mRole = ClsFactory::Create('Model.mRole');
        $role_info = $mRole->getRoleBySchoolId($schoolid, true);
        
        $teacherinfo_list = $arr = array();
        if(!empty($userInfo)) {
            foreach($userInfo as $uid=>$user) {
                //处理用户的基本信息
                $teacherinfo_list[$uid] = array(
                    'client_account' => $uid,
                    'client_name' => $user['client_name'],
                    'teacher_subject' => $user_subject_list[$uid]['subject_id'],
                    'subjectName' => $user_subject_list[$uid]['subject_name'],
                    'is_use_office' => 0,
                );
                //处理用户的部门信息
                if(isset($mDepartmentMembers_info[$uid])) {
                    $dpt_member_info = reset($mDepartmentMembers_info[$uid]);
                    $dpt_id = $dpt_member_info['dpt_id'];
                    
                    $dpt_info = array(
                        'dpt_id' => $dpt_id,
                        'dpt_name' => $department_info[$dpt_id],
                        'duty_name' => $dpt_member_info['duty_name'],
                        'role_id' => $dpt_member_info['role_ids'],
                        'is_use_office' => 1,
                    );
                    $teacherinfo_list[$uid] = array_merge($teacherinfo_list[$uid], $dpt_info);
                    
                    unset($mDepartmentMembers_info[$uid]);
                }
                
                if($excelflag == 'excel') {
                    $arr[] = array(
                        0 => $user['client_name'],
                        1 => $user_subject_list[$uid]['subject_name'],
                        2 => $uid,
                    );
                }
                
                unset($userInfo[$uid]);
            }
        }
	    
	    $this->assign('schoolid', $schoolid);
	    $this->assign('subInfo', $subInfo);
	    $this->assign('tercherInfo', $teacherinfo_list);
	    $this->assign('role_info', $role_info);
	    
	    if($excelflag == 'excel') {
	        $title = $this->objInput->postArr('title');
	        $index = 1;
	        $new_teacher_list[$index++] =$title;
	        foreach($arr as $key=>$data) {
	            $new_teacher_list[$index++] = $data;
	        }

	        $excel_datas[0] = array(
	            'title' => '学校老师账号信息',
	            'cols' => 4,
	            'rows' => count($new_teacher_list),
	            'datas' => $new_teacher_list,
	        );
	        $excel_pre = "import_student_list_" . date('Ymd', time()) . "_"; 
	        $pFileName = Pathmanagement_ams::uploadExcel() . uniqid($excel_pre) . ".xls";
	        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        	$HandlePHPExcel->saveToExcelFile($excel_datas, $pFileName);
	        $HandlePHPExcel->export($pFileName,$excel_datas[0]['title']);
	        unset($excel_datas);
	        
	    } elseif($flag == 'stamp') {
	        $this->display('teacherlist');
	        return ;
	    } else {
	        $this->display('teacherinfo');
	    }
	}

	//添加教师
	function addTeacher(){
	    $tercherName = $this->objInput->getStr('tercherName');
	    $schoolId = $this->user['schoolinfo']['school_id'];
        $subjectId_Arr = $this->objInput->getArr('subjectId');
        $department_id = $this->objInput->getInt('department_id');
        $duty = $this->objInput->getStr('duty');
        $role = $this->objInput->getInt('role');
        $is_use_office = $this->objInput->getStr('is_use_office');
        
        //去掉重复数据
        $subjectId_Arr = array_unique($subjectId_Arr);
        
	    if(!(self::checkUser($this->user['ams_account'], $schoolId))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
        //页面传入的flag处理
        $is_use_office = empty($is_use_office) || $is_use_office === 'false' ? false : true; 
        
        import("@.Common_wmw.WmwString");

        if(empty($tercherName)) {
           $error_message = '姓名不能为空!';
        } elseif(empty($subjectId_Arr)) {
            $error_message = '请您选择科目信息!';
        } elseif(empty($schoolId)) {
            $error_message = '不存在该学校!';
        } elseif(empty($department_id) && $is_use_office){
            $error_message = '部门不能为空!';
        }elseif(empty($duty) && $is_use_office){
            $error_message = '部门职务不能为空!';
        }elseif(empty($role) && $is_use_office){
            $error_message = '角色不能为空!';
        }elseif(WmwString::mbstrlen($duty)>8){
             $error_message = '职务长度不能超过八个字!';
        }
        if(!empty($error_message)){
            echo json_encode(array('result'=>array('code'=>-1,'message'=>$error_message)));
            return false;
        }

		$mUser = ClsFactory::Create('Model.mUser');
		
		$newUid = $mUser->createNewUid();
		
		if ($newUid) {
	        $acronym = WmwString::getfirstchar($tercherName);
	        $acountData = array(
    	        array(
                    'client_account'=>$newUid,
                    'client_password'=>MD5_PASSWORD,
    	        	'client_name'=>$tercherName,
                    'add_time'=>time(),
    	        	'upd_time'=>time(),
                    'client_type'=>CLIENT_TYPE_TEACHER,
                    'status' => CLIENT_STOP_FLAG,
                )
            );
	        $userInfo = array(
    	        array(
                    'client_account'=>$newUid,
            		'client_firstchar'=>$acronym,
    	        	'add_time'=>time(),
    	        	'upd_time'=>time(),
                )
            );
	        $schoolTeacher_arr = array();
	        foreach($subjectId_Arr as $subject_id) {
	        	if(intval($subject_id) <= 0) {
	        		continue;
	        	}
	        	$arr = array(
		        	'client_account'=>$newUid,
	                'school_id'=>$schoolId,
	                'subject_id'=>$subject_id,
	                'add_time'=>time(),
	                'add_account'=>$this->user['ams_account'],
	        	);
	        	$schoolTeacher_arr[$subject_id] = $arr;
	        }
	        
	        if(empty($schoolTeacher_arr) && $is_use_office) {
                $arr = array(
		        	'client_account'=>$newUid,
	                'school_id'=>$schoolId,
	                'subject_id'=>-1,
	                'add_time'=>time(),
	                'add_account'=>$this->user['ams_account'],
                	'upd_time'=>time(),
	                'upd_account'=>$this->user['ams_account'],
	        	);
	        	$schoolTeacher_arr[-1] = $arr;
	        }

	        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	        $rs = $mSchoolTeacher->addSchoolTeacherBat($schoolTeacher_arr);
            if($is_use_office){
    	        $office_list = array(
                    'dpt_id'=>$department_id,
                    'client_account'=>$newUid,
                    'role_ids'=>$role,
                    'duty_name'=>$duty,
                    'add_time'=>time(),
                );
                $mDepartmentMember = ClsFactory::Create('Model.mDepartmentMembers');
    	        $resault = $mDepartmentMember->addDepartmentMembers($office_list);
            }
	        $result = $mUser->addUserClientAccountBat($acountData);
	        $res = $mUser->addUserClientInfoBat($userInfo);
	        if($res && $result && $rs && (($resault && $is_use_office) || (empty($resault) && empty($is_use_office)))){
	            echo json_encode(array('result'=>array('code'=>1,'message'=>'添加成功'),'data'=>array('uid'=>$newUid)));
	        }else{
	            echo json_encode(array('result'=>array('code'=>-1,'message'=>'系统繁忙')));
	        }
	    }else{
	        echo json_encode(array('result'=>array('code'=>-1,'message'=>'系统繁忙')));
	    }
	}

	//修改教师信息
	function modifyTercher(){
	    $add_uid = $this->user['ams_account'];
	    $uid = $this->objInput->getInt('uid');
	    $schoolId = $this->user['schoolinfo']['school_id'];
	    $tercherName = $this->objInput->getStr('tercherName');
	    $subjectId_Arr = $this->objInput->getArr('subjectId');
	    $upd_department_id = $this->objInput->getInt('upd_department_id');
        $upd_duty = $this->objInput->getStr('upd_duty');
        $upd_role = $this->objInput->getInt('upd_role');
        $upd_is_use_office = $this->objInput->getStr('upd_is_use_office');
	    $acronym = WmwString::getfirstchar($tercherName);
	    //去掉重复的id值
	    $subjectId_Arr = array_unique($subjectId_Arr);
        
	    if(!(self::checkUser($this->user['ams_account'], $schoolId))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    
		$mUser = ClsFactory::Create('Model.mUser');
		
		import("@.Common_wmw.WmsString");

	    if(empty($tercherName)) {
           $error_message = '姓名不能为空!';
        } elseif(empty($subjectId_Arr)) {
            $error_message = '请您选择科目信息!';
        } elseif(empty($schoolId)) {
            $error_message = '不存在该学校!';
        } elseif(empty($upd_department_id) & $upd_is_use_office == 'true'){
            $error_message = '部门不能为空!';
        }elseif(empty($upd_duty) & $upd_is_use_office == 'true'){
            $error_message = '部门职务不能为空!';
        }elseif(empty($upd_role) & $upd_is_use_office == 'true'){
            $error_message = '角色不能为空!';
        }elseif(empty($schoolId)){
            $error_message = '系统繁忙';
        }elseif(WmwString::mbstrlen($upd_duty)>8){
             $error_message = '职务长度不能超过八个字!';
        }
        
        if(!empty($error_message)){
            echo json_encode(array('result'=>array('code'=>-1,'message'=>$error_message)));
            return false;
        }

        $clientInfo = array(
			'client_firstchar'=>$acronym,
            'upd_time'=>time(),
        );
        $clientAccount = array(
        	'client_name'=>$tercherName,
        	'upd_time'=>time(),
        );
		$schoolTeacher_arr = array();
        foreach($subjectId_Arr as $subject_id) {
        	if(intval($subject_id) <= 0) {
        		continue;
        	}
        	$arr = array(
	        	'client_account'=>$uid,
                'school_id'=> intval($schoolId),
                'subject_id'=> $subject_id,
                'add_time'=> time(),
                'add_account'=>$add_uid,
        	);
        	$schoolTeacher_arr[$subject_id] = $arr;
        }
        if(empty($schoolTeacher_arr)) {
            $arr = array(
	        	'client_account'=>$uid,
                'school_id'=> intval($schoolId),
                'subject_id'=> 0,
                'add_time'=> time(),
                'add_account'=>$add_uid,
        	);
        	$schoolTeacher_arr[0] = $arr;
        }
        $mDepartmentMember = ClsFactory::Create('Model.mDepartmentMembers');
        $departmentmember_list = $mDepartmentMember->getDepartmentMembersByUid($uid);
        if(!empty($departmentmember_list[$uid])){
            $departmentmember_info = array_shift($departmentmember_list[$uid]);
            if($upd_is_use_office == 'true'){
    	        $office_list = array(
                    'dpt_id'=>$upd_department_id,
                    'client_account'=>$uid,
                    'role_ids'=>$upd_role,
                    'duty_name'=>$upd_duty,
                    'add_time'=>time(),
                );
        	    $resault = $mDepartmentMember->modifyDepartmentMembers($office_list, $departmentmember_info['dptmb_id']);
            }else{
                $resault = $mDepartmentMember->delDepartmentMembers($departmentmember_info['dptmb_id']);
            }
        }else{
            if($upd_is_use_office == 'true'){
    	        $office_list = array(
                    'dpt_id'=>$upd_department_id,
                    'client_account'=>$uid,
                    'role_ids'=>$upd_role,
                    'duty_name'=>$upd_duty,
                    'add_time'=>time(),
                );
        	    $resault = $mDepartmentMember->addDepartmentMembers($office_list);
            }
        }


		//搜索当前用户下对应的科目信息
		$mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $schoolTeacher_arrs = $mSchoolTeacher->getSchoolTeacherByTeacherUid($uid);
        $schoolTeacher_list = & $schoolTeacher_arrs[$uid];
        $exists_subjectids = array();
        if(!empty($schoolTeacher_list)) {
            foreach($schoolTeacher_list as $subject) {
                $exists_subjectids[$subject['subject_id']] = $subject['subject_id'];
            }
        }
        $delarr = array_diff($exists_subjectids, $subjectId_Arr);
        if(!empty($delarr)) {
        	$mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
	        $tips['find_in_class'] = $mClassTeacher->getClassTeacherByUid($uid);
	        $tips['find_user_info'] = $mUser->getUserByUid($uid);
	        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subinfo = $mSubjectInfo->getSubjectInfoByTeacherUidFromClassTeacher($uid);
	      	$i = 0;
      		foreach($delarr as $delval){
      			foreach($subinfo[$uid] as $subkey=>$subval){
      				if($delval == $subkey){
      					$tips['del_class'][$delval]['subject_name'] = $subval['subject_name'];
      					break;
      				}
      			}
      			foreach($tips['find_in_class'][$uid] as $classkey=>$classval){
	      			if($classval['subject_id'] == $delval){
	      				$tips['del_class'][$delval]['class_code'][$classval['class_code']] = $classval['class_code'];
	      			}
	      		}
	      	}
	      	$i++;
	      	foreach($tips['find_user_info'][$uid]['class_info'] as $clakey=>$claval){
	      		foreach($tips['del_class'] as $delkey1=>$delval1){
	      			if(in_array($clakey,$delval1['class_code'])){
	      				$tips['info'] .= $claval['grade_id_name'].'  '.$claval['class_name'].'  担任'.$delval1['subject_name'].'<br>';
	      			}
	      		}
	      	}
	      	if($tips['info']){
	      		echo json_encode(array('result'=>array('code'=>-1,'message'=>'该老师在<br>'.$tips['info'].'请手动解除关系后才可修改')));
	        	return false;
	      	}
        }

    	$res = $mUser->modifyUserClientInfo($clientInfo,$uid);
    	$res = $mUser->modifyUserClientAccount($clientAccount,$uid);
	    $rs = $mSchoolTeacher->modifySchoolTeacherBat($schoolTeacher_arr,$uid);
	    if($res){
	        echo json_encode(array('result'=>array('code'=>1,'message'=>'修改成功')));
	        return false;
	    } else {
	        echo json_encode(array('result'=>array('code'=>-1,'message'=>'系统繁忙')));
	        return false;
	    }
	}

    //得到学校的所有学科信息
	function showSubjectInfo($sid){
	    $schoolid = $sid;
	    $mSubject = ClsFactory::Create('Model.mSubjectInfo');
	    $subjectInfo = $mSubject->getSubjectInfoBySchoolid($schoolid);
        $subjectInfo = & $subjectInfo[$schoolid];
        
        foreach ($subjectInfo as $key=>$val) {
            $subInfo[$val['subject_id']] = $val['subject_name'];
	    }
	    return $subInfo;
	}
	
    //ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }
    
    function addTeacherBPhone(){
       $schoolid = $this->objInput->getInt("schoolid");
       
        if(!(self::checkUser($this->user['ams_account'], $schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }

       $page = $this->objInput->getInt("page");
       
       $page = max($page, 1);
       $limit = 100;
       $offset = ($page-1)*$limit;
       $pre = $page-1;
       $pagediv = "<font style='margin:0 10px 0 10px;' width='100px'>当前第{$page}页</font>";
       
       if($pre>=1) {
           $pagediv .= "<span><a href='/Amscontrol/Amsteacher/addTeacherBPhone/schoolid/{$schoolid}/page/{$pre}'>上一页</a>&nbsp;&nbsp;</span>";
       }else{
           $pagediv .= "<span>上一页&nbsp;&nbsp;</span>";
       }
       
       $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	   $schoolTeacherInfo = $mSchoolTeacher->getSchoolTeacherInfoBySchoolIdPage($schoolid, $offset, $limit+1); //该学校老师账号列表
	   if(count($schoolTeacherInfo)>$limit){
	       $end = $page+1;
	       $pagediv .= "<span><a href='/Amscontrol/Amsteacher/addTeacherBPhone/schoolid/{$schoolid}/page/{$end}'>下一页</a></span>";
	       array_pop($schoolTeacherInfo);
	   }else{
	       $pagediv .= "<span>下一页</span>";
	   }
	   
	   foreach( $schoolTeacherInfo as $id=>$teacherInfo){
	       $account_arr[] = $teacherInfo['client_account'];
	   }
   	   $mUser = ClsFactory::Create('Model.mUser');
       $userInfoList = $mUser->getUserByUid($account_arr);//老师信息列表
       $teacherList = array();
       $num = $offset+1;
       foreach($userInfoList as $client_account=>$userInfo){
           $teacherList[$userInfo['client_account']]['num'] = $num++;
           $teacherList[$userInfo['client_account']]['client_account'] = $userInfo['client_account'];
           $teacherList[$userInfo['client_account']]['client_name'] = $userInfo['client_name'];
       }
       $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
       $existPhoneList = $mBusinessphone->getbusinessphonebyalias_id($account_arr);//查询库中已经绑定的手机号
       
       $this->assign('page' , $page);
       $this->assign('pagediv' , $pagediv);
       $this->assign('existPhoneList' , $existPhoneList);
	   $this->assign('teacherList' , $teacherList);
       $this->assign('schoolid' , $schoolid);
       $this->display("teacherBusinessPhone");
    }

    function bindingTeacherPhone(){
        $schoolid          = $this->objInput->postInt("schoolid");
        $businessPhonelist = $this->objInput->postArr('businesPhone');   //用户提交的手机号列表
        $primaryPhoneList = $this->objInput->postArr('primaryPhone'); //数据库中原已绑定的手机号列表
        $clientAccountList = $this->objInput->postArr('client_account'); //账号列表
        $primary_phone_types = $this->objInput->postArr('primary_phone_types');
        $page = $this->objInput->postInt('page');
        
        if(!(self::checkUser($this->user['ams_account'], $schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
        
        
        
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');

        foreach($businessPhonelist as $key => $phone){ //检测表单中是否有重复的手机号
            if(!empty($phone)){
                $repeatkeyArr = array_keys($businessPhonelist , $phone);
                if(count($repeatkeyArr)>1){
                    $alertStr .= "更新失败！账号";
                   // echo "更新失败！账号";
                    foreach($repeatkeyArr as $key_1 => $val){
                        $alertStr .= "【{$clientAccountList[$val]}】，";
                    }
                    $alertStr .="对应的机号（{$phone}）相同！";
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Amscontrol/Amsteacher/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";
                    return false;
                }
            }
        }

        $phone_del = $phone_add = $accountPhone_add = $update_phone_info = array();
        foreach($primaryPhoneList as $key => $primaryPhone) {//提出变动的手机号
            if(!empty($primaryPhone)){                 //原来已经绑定过手机
                if($primaryPhone != $businessPhonelist[$key]){//手机号（主键）改变
                     $phone_del[] = $primaryPhone;   //删除被修改（包括置空）了的手机号
                }else{
                    $input_phone_type = $this->objInput->postInt("phone_type_".$clientAccountList[$key]);
                    if($primary_phone_types[$key] != $input_phone_type){
                         //不改变主键，即手机号，只改变其他信息
                         $update_data = array(
                         	'phone_type'=>$input_phone_type
                         );
                         $mBusinessphone->modifyPhoneInfo($update_data,$primaryPhoneList[$key]);
                    }
                }
            }
            if((!empty($businessPhonelist[$key]))&&($businessPhonelist[$key] != $primaryPhoneList[$key])){ //变动的手机及新添加的手机
                $current_user = $this->user['ams_account'];
                $phone_add[] = $businessPhonelist[$key];
                $account_add[] = $clientAccountList[$key];
                $accountPhone_add['START'][]=array(
                        'mphone_num'=>$businessPhonelist[$key],
    	            	'business_num'=>$clientAccountList[$key],
                    	'opening_time'=>date('Ymd'),
                		'wbp_log_flag'=> 1,              //log表手动标志
                        'client_account'=>$current_user,  //log表操作者账号
                        'phone_type'=> $this->objInput->postInt("phone_type_".$clientAccountList[$key])
                );
            }
        }
        $mUser = ClsFactory::Create('Model.mUser');
        $userList = $mUser->getUserBaseByUid($account_add);
        foreach($accountPhone_add['START'] as $key=> $info) {//查询用户姓名
            $client_account = $info['business_num'];
            $accountPhone_add['START'][$key]['mphone_user_name'] = $userList[$client_account]['client_name'];
        }
       if(!empty($phone_del) || !empty($phone_add) || !empty($accountPhone_add)){
            $transactionResult = $mBusinessphone -> bindingTransaction($phone_del , $phone_add , $accountPhone_add);
            if(!$transactionResult){
                echo "<script language='javascript'> alert('更新失败！请重新尝试'); window.location.href='/Amscontrol/Amsteacher/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";
            }
        }
        echo "<script language='javascript'> alert('更新成功！'); window.location.href='/Amscontrol/Amsteacher/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";
    }
}

