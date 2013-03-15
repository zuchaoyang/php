<?php
class AmsclasslistAction  extends AmsController{
    //作者：郭学文
    protected $is_school = true;
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
	    $this->assign('username', $this->user['ams_name']);
    }
    
	//显示学校班级的列表
	function showClassList() {
	    $schoolId = $this->user['schoolinfo']['school_id'];
	    $gradeId = $this->objInput->getInt('gradeId');
	   
	    if(!($this->checkLoginerInSchool($this->user['ams_account'], $schoolId, $gradeId))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    
	    $mUser = ClsFactory::Create('Model.mUser');
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    
	    $classInfom = array();
        $filters = array(
            'grade_id'=>$gradeId
        );
        $clientClassInfo_list = $mClassInfo->getClassInfoBySchoolId($schoolId, $filters);
        $clientClassInfo = & $clientClassInfo_list[$schoolId];
        if(!empty($clientClassInfo)) {
            //获取老师名字信息
            foreach ($clientClassInfo as $clientinfo) {
                $uids[] = $clientinfo['headteacher_account'];
            }
            $teacher_list = $mUser->getClientAccountById($uids);
            
            import("@.Control.Api.Upgrade.Core.reflectClassInfo");
            
            foreach($clientClassInfo as $class_code=>$classinfo) {
                $class_update_obj = new reflectClassInfo($class_code);
                
                $is_need = $class_update_obj->needUpgrade();
                $is_Graduate = $class_update_obj->isGraduate();
                if($is_need && $is_Graduate) {
                    $classinfo['is_up'] = true;
                }
                $headteacher_account = $classinfo['headteacher_account'];
                $classinfo['headTercherName'] = isset($teacher_list[$headteacher_account]) ? $teacher_list[$headteacher_account]['client_name'] : '暂无';
                $classinfo['client_account'] = $headteacher_account;
                $classinfo['secret_key'] = $class_update_obj->getSecretKey();
                $classInfom[] = $classinfo;
                
                unset($clientClassInfo[$class_code]);
            }
            unset($teacher_list);
        }
	    
	    //添加相应的科目信息
	    //$anlicheng 调整教师科目一对多
	    if(!empty($classInfom)) {
	        //获取所有老师的账号信息
    	    $teacher_uids = array();
    	    foreach($classInfom as $classinfo) {
    	        $teacher_uids[] = $classinfo['client_account'];
    	    }
    	    $subjectinfo_arr = $this->getSubjectInfoByTeacherUids($teacher_uids);
    	    
    	    //追加教师担任的科目信息
    	    if(!empty($subjectinfo_arr)) {
    	        foreach($classInfom as $key=>$classinfo) {
    	            $client_account = $classinfo['client_account'];
    	            $classinfo['subname'] = isset($subjectinfo_arr[$client_account]) ? $subjectinfo_arr[$client_account] : '--';
    	            $classInfom[$key] = $classinfo;
    	        }
    	    }
	    }
	    $this->assign('uid', $this->user['ams_account']);
	    $this->assign('schoolid', $schoolId); 
	    $this->assign('totalClasses', count($classInfom));
	    $this->assign('gradeName', Constancearr::class_grade_id($gradeId));
	    $this->assign('classInfo', $classInfom);
	    $this->display('classlist');
    }
    
    //显示班级管理页面
    function classManager() {
        $schoolId = $this->user['schoolinfo']['school_id'];
        $class_code = $this->objInput->getInt('classCode');
        $gradeId = $this->objInput->getInt('gradeid');
        $headerTeacherUid = $this->objInput->getInt('uid');
        
        $uid = $this->user['ams_account'];
        if(!($this->checkLoginerInSchool($this->user['ams_account'], $schoolId, $gradeId, $class_code))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    
	    $filters = array(
	        'client_type' => array(
	            CLIENT_TYPE_STUDENT,
	            CLIENT_TYPE_FAMILY,
	        ),
	    );
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
        $clientclass_arr = $mClientClass->getClientClassByClassCode($class_code, $filters);
        $clientclass_list = & $clientclass_arr[$class_code];
        
        $uids = array();
        if(!empty($clientclass_list)) {
            foreach($clientclass_list as $key=>$clientclass) {
                $uids[] = $clientclass['client_account'];
            }
            unset($clientclass_list[$key]);
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        
        $stunum = $parentnum = 0;
        if(!empty($uids)) {
            $uids = array_unique($uids);
            $userlist = $mUser->getUserBaseByUid($uids);
            if(!empty($userlist)) {
                foreach($userlist as $uid=>$user) {
                    if(isset($user['status']) && $user['status'] > 0) {
                        continue;
                    }
                    $client_type = intval($user['client_type']);
                    if($client_type == CLIENT_TYPE_STUDENT) {
                        $stunum ++;
                    } else if($client_type == CLIENT_TYPE_FAMILY) {
                        $parentnum ++;
                    }
                    unset($userlist[$uid]);
                }
            }
        }
        
        //todolist不合理的用法
        $this->showTeacherList($schoolId, $class_code);
        
        //班主任基本信息
		$teacher_list = $mUser->getUserBaseByUid($headerTeacherUid);
		$teacherInfo = & $teacher_list[$headerTeacherUid];
        $headerTeacherName = $teacherInfo['client_name'];
        
	    
	    //获取班级信息
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classInfo_list = $mClassInfo->getClassInfoById($class_code);
        $classInfo = & $classInfo_list[$class_code];
        
        $this->assign('parentnum', $parentnum);
        $this->assign('stunum', $stunum);
        $this->assign('headerTeacherUid', $headerTeacherUid);
        $this->assign('gradeid', $gradeId);
        $this->assign('grade_id', $gradeId);
        $this->assign('schoolid', $schoolId);
        $this->assign('uid', $headerTeacherUid);
        $this->assign('class_code', $class_code);
        $this->assign('headerteacherName', $headerTeacherName);
        $this->assign('grade_name', Constancearr::class_grade_id($gradeId));
        $this->assign('class_name', $classInfo['class_name']);
        $this->display('classmanager');
    }
    
    //添加班级
    function addClass(){
        $schoolId = $this->user['schoolinfo']['school_id'];
        $uid = $this->user['ams_account'];
        if(!($this->checkLoginerInSchool($uid,$schoolId))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
        $mschool = ClsFactory::Create('Model.mSchoolInfo');
        $schoolInfo = $mschool->getSchoolInfoById($schoolId);
        $schoolType = $schoolInfo[$schoolId]['school_type'];
        $gradeType = $schoolInfo[$schoolId]['grade_type'];
        $gradeList = $this->gradelists($schoolType, $gradeType);
        
        $teacherInfo = $this->showTeacherInfo($schoolId);
        $subjectInfo = $this->showSubjectInfo($schoolId);
        
        $this->assign('subjectInfo',$subjectInfo);
        $this->assign('teacherInfo',$teacherInfo);
        $this->assign('gradeList',$gradeList);
        $this->assign('schoolid',$schoolId);
        $this->display('addclassInfo');
    }
    
    //维护班级信息
    function vindicateSchoolClassInfo (){
        $schoolId = $this->user['schoolinfo']['school_id'];
        $classCode = $this->objInput->getInt('classcode');
        $gradeid = $this->objInput->getInt('gradeid');
        $uid = $this->user['ams_account'];
        if(!($this->checkLoginerInSchool($uid,$schoolId,$gradeid,$classCode))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
        $this->showTeacherList($schoolId,$classCode);
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $SchoolInfo = $mSchoolInfo->getSchoolInfoById($schoolId);
        $schoolType = $SchoolInfo[$schoolId]['school_type'];
        $gradeType = $SchoolInfo[$schoolId]['grade_type'];
        $gradeName = $this->gradelists($schoolType,$gradeType);
        $teacherInfo = $this->showTeacherInfo($schoolId);
        $subjectInfo = $this->showSubjectInfo($schoolId);
        
        //获取班级的相关信息
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classinfo_arr = $mClassInfo->getClassInfoBaseById($classCode);
        $classInfo = & $classinfo_arr[$classCode];
        $className = $classInfo['class_name'];
        //补全教师的相关信息
        if(!empty($teacherInfo)) {
            foreach($teacherInfo as $uid=>$user) {
                if($uid == intval($classInfo['headteacher_account'])) {
                    $user['selected'] = true;
                    $oldheader = $uid;
                }
                $teacherInfo[$uid] = $user;
            }
        }
        //重新组织gradeName的数据
        if(!empty($gradeName)) {
            $new_gradeName = array();
            $grade_id = intval($classInfo['grade_id']);
            
            foreach($gradeName as $id=>$name) {
                $new_gradeName[$id]['name'] = $name;
                if($grade_id && $id == $grade_id) {
                    $new_gradeName[$id]['selected'] = true;
                }
            }
            $gradeName = & $new_gradeName;
        }
        $this->assign('oldheader',$oldheader);
        $this->assign('classcode',$classCode);
        $this->assign('classname',$className);
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolId);
        $this->assign('subjectInfo',$subjectInfo);
        $this->assign('teacherInfo',$teacherInfo);
        $this->assign('gradeName',$gradeName);
        $this->display('classinfovindicate');
    }
    
    //检查班级名称是否重复
    function checkClassName(){
        $schoolId = $this->user['schoolinfo']['school_id'];
        $classcode = $this->objInput->getStr('classcode');
        $className = $this->objInput->getStr('className');
        $filters = array(
            'class_name'=>$className
        );
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classInfo = $mClassInfo->getClassInfoBySchoolId($schoolId,$filters);
        if(empty($classInfo[$schoolId])){
            $result = array('error'=>array('code'=>1,'message'=>'班级名称可用'));
        }else{
            if(!empty($classInfo[$schoolId][$classcode])){
                $result = array('error'=>array('code'=>1,'message'=>'班级名称可用'));
            }else{
                $result = array('error'=>array('code'=>-1,'message'=>'班级名称重复'));
            }
        }
        echo json_encode($result);
    }
    
    /**
     * 通过班级号获取教师的相关信息
     * @param $class_code
     */
    private function getClientClassByClassCode($class_code) {
        if(empty($class_code)) {
            return false;
        }
        //获取client_class表中对应班级的所有老师信息
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $clientclass_arr = $mClientClass->getClientClassByClassCode($class_code, array('client_type'=>CLIENT_TYPE_TEACHER));
        $clientclass_list = & $clientclass_arr[$class_code];
        
        return !empty($clientclass_list) ? $clientclass_list : false;
    }
    
    private function getClassTeacherByUid($uids, $filters = array()) {
        if(empty($uids)) {
            return false;
        }
        //获取教师班级的科目和班级的关系
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_arr = $mClassTeacher->getClassTeacherByUid($uids, $filters);
        
        return !empty($classteacher_arr) ? $classteacher_arr : false;
    }
    
    function saveUpdate() {
    	$oldheaderId = $this->objInput->postInt('oldheader');
        $headTeacherUid = $this->objInput->postInt('headteacher');
        $className = $this->objInput->postStr('className');
        $teacherinfo = $this->objInput->postStr('teacherinfo');
        
        $schoolId = $this->objInput->postInt('schoolid');
        $gradeId = $this->objInput->postInt('grade');
        $class_code = $this->objInput->postInt('classcode');
        
        $uid = $this->user['ams_account'];
        if(!($this->checkLoginerInSchool($uid, $schoolId, $gradeId, $class_code))) {
            self::amsLoginTipMessage('您没有权限操作，请重新登录');
            return ;
        } elseif(empty($teacherinfo)) {
            return false;
        }
        if($oldheaderId != $headTeacherUid){
        	$mSquadron = ClsFactory::Create('Model.mSquadron');
        	if($mSquadron->getSquadronById($class_code)){
        		$squadron_arr = array(
	    			'wmw_uid'=>$headTeacherUid,
	    			'db_updatetime' => date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->modifySquadron($squadron_arr, $class_code);
        	}
    	}
        //保证json串数据的合法解析
        $teacherinfo = htmlspecialchars_decode($teacherinfo);
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $teacherinfo = stripslashes($teacherinfo);
        }
        $teacherinfolist = json_decode($teacherinfo, true);

        //分离不同类型的操作
        $del_arr = $add_arr = $upd_arr = array();
        if(!empty($teacherinfolist['data'])) {
            foreach($teacherinfolist['data'] as $teacher) {
                if($teacher['type'] == 'add') {
                    $add_arr[] = $teacher;
                } elseif($teacher['type'] == 'del') {
                    $del_arr[] = $teacher;
                } elseif($teacher['type'] == 'upd') {
                    $upd_arr[] = $teacher;
                }
            }
            unset($teacherinfolist, $teacherinfo);
        }
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        
        $now_time = time();
        
        //添加数据
        if(!empty($add_arr)) {
            //获取client_class表中对应班级的所有老师信息
            $clientclass_list = $this->getClientClassByClassCode($class_code);
            foreach($add_arr as $teacher) {
                $new_teacherid = intval($teacher['new_teacherid']);
                if($new_teacherid <= 0) {
                    continue;
                }
                $class_teacher_data = array(
                    'client_account' => $teacher['new_teacherid'],
                    'class_code' => $class_code,
                    'subject_id' => $teacher['subjectid'],
                    'add_time' => $now_time,
                    'add_account' => $uid,
                    'upd_account' => $uid,
                    'upd_time' => $now_time,
                );
            
                $mClassTeacher->addClassTeacher($class_teacher_data);
                //如果不存在则增加教师和班级之间的关系
                if(!isset($clientclass_list[$new_teacherid])) {
                    $clientclass_data = array(
                        'client_account' => $new_teacherid,
                        'class_code' => $class_code,
                        'teacher_class_role' => TEACHER_CLASS_ROLE_CLASSTEACHER,
                        'class_admin' => NO_CLASS_ADMIN,
                        'add_time' => $now_time,
                        'add_account' => $uid,
                        'upd_account' => $uid,
                        'upd_time' => $now_time,
                        'client_type' => CLIENT_TYPE_TEACHER,
                    );
                    $add_flag = $mClientClass->addClientClass($clientclass_data);
                    
                    //todolist
//                    import('@.Common_wmw.functions', null, '.php');
//                    moniter_control($this->user, __METHOD__ . ":addClientClass", 1);
                    
                    //避免数据的重复添加
                    if(!empty($add_flag)) {
                        $clientclass_list[$new_teacherid] = $clientclass_data;
                    }
                }
            }
            unset($clientclass_list);
        }
        
        //删除相关数据
        if(!empty($del_arr)) {
            $teacher_relation_arr = array();
            
            //更新redis 操作的client_account记录:
            $del_client_accounts = array();
            
            foreach($del_arr as $teacher) {
                $old_teacherid = $teacher['old_teacherid'];
                $class_teacher_id = $teacher['class_teacher_id'];
                $client_class_id = $teacher['client_class_id'];
                
                $teacher_relation_arr[$old_teacherid][] = $client_class_id;
                //删除class_teacher表中的数据

                //更新Redis数据
                $class_teacher = $mClassTeacher->getClassTeacherById($class_teacher_id);
                if (!empty($class_teacher)) {
                    $class_teacher = reset($class_teacher);
                    $del_client_accounts[] = $class_teacher['client_account'];
                }
                
                $mClassTeacher->delClassTeacher($class_teacher_id);
            }
//            unset($del_arr);
            
            //调用的是对M层进行了再次封装的函数
            $clientclass_list = $this->getClientClassByClassCode($class_code);
            $classteacher_arr = $this->getClassTeacherByUid(array_keys($clientclass_list), array('class_code' => $class_code));
   
            //删除在该班没有任课的教师信息
            if(!empty($teacher_relation_arr)) {
                foreach($teacher_relation_arr as $client_account=>$list) {
                    //新班主任的信息暂时保留
                    if(!isset($classteacher_arr[$client_account]) && $client_account != $headTeacherUid) {
                        $list = array_unique($list);
                        foreach($list as $client_class_id) {
                            $mClientClass->delClientClass($client_class_id);
                        }
                    }
                }
            }
        }
        
        //数据更新
        if(!empty($upd_arr)) {
            $old_teacherid_arr = $new_teacherid_arr = array();
            foreach($upd_arr as $teacher) {
                $old_teacherid = $teacher['old_teacherid'];
                $new_teacherid = $teacher['new_teacherid'];
                
                $old_teacherid_arr[$old_teacherid] = $old_teacherid;
                $new_teacherid_arr[$new_teacherid] = $new_teacherid;
                
                $class_teacher_data = array(
                    'client_account' => $new_teacherid,
                    'class_code' => $class_code,
                    'subject_id' => $teacher['subjectid'],
                    'upd_time' => $now_time,
                    'add_account' => $uid,
                    'upd_account' => $uid,
                );
                $mClassTeacher->modifyClassTeacher($class_teacher_data, $teacher['class_teacher_id']);
            }
            
            //获取现有数据库表中存在的数据
            $clientclass_list = $this->getClientClassByClassCode($class_code);
            $exists_teacherid_arr = array_keys($clientclass_list);
            
            //要增加的新记录
            $add_diff = array_diff($new_teacherid_arr, $exists_teacherid_arr);
            if(!empty($add_diff)) {
                foreach($add_diff as $client_account) {
                    $clientclass_data = array(
                        'client_account' => $client_account,
                        'class_code' => $class_code,
                        'teacher_class_role' => TEACHER_CLASS_ROLE_CLASSTEACHER,
                        'class_admin' => NO_CLASS_ADMIN,
                        'add_time' => $now_time,
                        'add_account' => $uid,
                        'upd_account' => $uid,
                        'upd_time' => $now_time,
                        'client_type' => CLIENT_TYPE_TEACHER,
                    );
                    $mClientClass->addClientClass($clientclass_data);
                }
                
                //todolist
//                import('@.Common_wmw.functions', null, '.php');
//                moniter_control($this->user, __METHOD__ . ":addClientClass", count($add_diff));
            }
            
            //查找所有的old_teacher_arr是否在class_teacher中是否存在关系
            $classteacher_list = $mClassTeacher->getClassTeacherByUid($old_teacherid_arr, array('class_code'=>$class_code));
           
            $del_diff = array();
            if(!empty($classteacher_list) && !empty($old_teacherid_arr)) {
                foreach($old_teacherid_arr as $tuid) {
                    if(empty($classteacher_list[$tuid])) {
                        $del_diff[] = $tuid;
                    }
                }
            }
            
            $client_class_arr = $mClientClass->getClientClassByUid($del_diff);
            $del_client_class_list = array();
		    if ( !empty($client_class_arr) ) {
		    	foreach ( $client_class_arr as $key=>$list ) {
		    		foreach ($list as $key1=>$val) {
		    			$del_client_class_list[$val['client_account']][$val['class_code']] =$val; 
		    		}
		    		
		    	}	
		    }

		    unset($client_class_arr);
            if(!empty($del_client_class_list)) {
                foreach($del_client_class_list as $client_account=>$class_list) {
                    //删除对应班级下的关系
                    if(isset($class_list[$class_code]) && $client_account != $headTeacherUid) {
                        $client_class_id = intval($class_list[$class_code]['client_class_id']);
                        $mClientClass->delClientClass($client_class_id);
                    }
                }
            }
        }
        
        
        //删除所有 有班级关系（client_class）且不在本班级任课的老师(class_teacher) 班主任不删出;
        $clientclass_list = $this->getClientClassByClassCode($class_code);
        $classteacher_arr = $this->getClassTeacherByUid(array_keys($clientclass_list), array('class_code' => $class_code));
        if(!empty($clientclass_list)) {
	        foreach($clientclass_list as $client_account=>$list) {
	        //新班主任的信息暂时保留
		        if(!isset($classteacher_arr[$client_account]) && $client_account != $headTeacherUid) {
			        $list = array_unique($list);
			        foreach($list as $client_class_id) {
			        	$mClientClass->delClientClass($client_class_id);
			        }
		        }
	        }
        }
        
        
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classinfo_arr = $mClassInfo->getClassInfoBaseById($class_code);
        $classinfo = & $classinfo_arr[$class_code];
        $old_headTeacherUid = intval($classinfo['headteacher_account']);
        
        //更新班级相关信息
        $class_info_data = array(
            'add_account' => $uid,
            'add_time' => $now_time,
            'headteacher_account' => $headTeacherUid,
            'class_name' => $className,
            'grade_id' => $gradeId
        );
        //检测如果以前的班主任不任课则删除
        $mClassInfo->modifyClassInfo($class_info_data, $class_code);
        
        //获取当前管理员应该有的班级状态
        $class_teacher_arr = $mClassTeacher->getClassTeacherByUid(array($headTeacherUid, $old_headTeacherUid), array('class_code'=>$class_code));
        //如果新旧班主任不是同一个人
        if($headTeacherUid !== $old_headTeacherUid) {
            //如果该教师不担任该班的其他教学,删除
            $client_class_arr = $mClientClass->getClientClassByUid($old_headTeacherUid);
            
            $clientclass_arr = array();
		    if ( !empty($client_class_arr) ) {
		    	foreach ( $client_class_arr as $key=>$list ) {
		    		foreach ($list as $key1=>$val) {
		    			$clientclass_arr[$val['client_account']][$val['class_code']] =$val; 
		    		}
		    		
		    	}	
		    }
		    unset($client_class_arr);
            
            $client_class_id = intval($clientclass_arr[$old_headTeacherUid][$class_code]['client_class_id']);
            if(empty($class_teacher_arr[$old_headTeacherUid])) {
                $mClientClass->delClientClass($client_class_id);
            } else {
                $client_class_data = array(
                    'teacher_class_role' => TEACHER_CLASS_ROLE_CLASSTEACHER,
                	'class_admin' => NO_CLASS_ADMIN,
                	'upd_account' => $uid,
                    'upd_time' => $now_time,
                );
                $mClientClass->modifyClientClass($client_class_data, $client_class_id);
            }
        }
        
        $class_role_both = !empty($class_teacher_arr[$headTeacherUid]) ? true : false;
        $teacher_class_role = $class_role_both ? TEACHER_CLASS_ROLE_CLASSBOTH : TEACHER_CLASS_ROLE_CLASSADMIN;
        
        //检测班主任是否在班级信息中存在
        $client_class_arr = $mClientClass->getClientClassByUid($headTeacherUid);
        
        $headteacher_arr = array();
	    if ( !empty($client_class_arr) ) {
	    	foreach ( $client_class_arr as $key=>$list ) {
	    		foreach ($list as $key1=>$val) {
	    			$headteacher_arr[$val['client_account']][$val['class_code']] =$val; 
	    		}
	    		
	    	}	
	    }
	    unset($client_class_arr);
        
        
        
        $current_client_class = & $headteacher_arr[$headTeacherUid][$class_code];
        
        if(!empty($current_client_class)) {
            $client_class_id = intval($current_client_class['client_class_id']);
            //当前用户的状态需要更新
            if($teacher_class_role != $current_client_class['teacher_class_role']) {
                $client_class_data = array(
                    'teacher_class_role' => $teacher_class_role,
                	'class_admin' => IS_CLASS_ADMIN,
                    'upd_account' => $uid,
                    'upd_time' => $now_time,
                );
                $mClientClass->modifyClientClass($client_class_data, $client_class_id);
            }
        } else {
            $client_class_data = array(
                'client_account' => $headTeacherUid,
                'class_code' => $class_code,
                'teacher_class_role' => $teacher_class_role,
                'class_admin' => IS_CLASS_ADMIN,
                'add_time' => $now_time,
                'add_account' => $uid,
                'upd_account' => $uid,
                'upd_time' => $now_time,
                'client_type' => CLIENT_TYPE_TEACHER,
            );
            $mClientClass->addClientClass($client_class_data);
            
            //todolist
//            import('@.Common_wmw.functions', null, '.php');
//            moniter_control($this->user, __METHOD__ . ":addClientClass", 1);
            
        }
        
        
        //===========================更新redis============================================================
        
        $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
        // 更新redis 新旧班主任的Client_class;        
        if($headTeacherUid !== $old_headTeacherUid) {
            // 更新redis 新旧班主任的Client_class;
            
            $mHashClientClass->getClientClassbyUid($headTeacherUid, true);
	        $mHashClientClass->getClientClassbyUid($old_headTeacherUid, true);            
        }
        
        // 更新redis 教师帐号班级信息 包括add_arr del_arr upd_arr
        if(!empty($add_arr)) {
            foreach($add_arr as $teacher) {
                $new_teacherid = intval($teacher['new_teacherid']);
                if($new_teacherid <= 0) {
                    continue;
                }
                $mHashClientClass->getClientClassbyUid($new_teacherid, true);   
            }
        }      
        
        //删除相关数据

        if(!empty($del_client_accounts)) {
            
            foreach($del_client_accounts as $client_account) {
                 $mHashClientClass->getClientClassbyUid($client_account, true);   
            }
        }        
        
        if(!empty($upd_arr)) {
            $teacherid_arr = array();
            foreach($upd_arr as $teacher) {
                $mHashClientClass->getClientClassbyUid($teacher['old_teacherid'], true);   
                $mHashClientClass->getClientClassbyUid($teacher['new_teacherid'], true);
            }
        }        

        //更新redis  班级实体 班级老师
        $mHashClass = ClsFactory::Create('RModel.Common.mHashClass');
        $mHashClass->getClassById($class_code, true);         
        
        $mSetClassTeacher = ClsFactory::Create('RModel.Common.mSetClassTeacher');
        $class_teacher = $mSetClassTeacher->getClassTeacherById($class_code, true);
        
        $this->redirect("Amsclasslist/classManager/schoolid/$schoolId/classCode/$class_code/gradeid/$gradeId/uid/$headTeacherUid");
    }
    
    //保存添加跳转页面
    function saveAdd(){
        $headTeacherUid     = $this->objInput->postStr('headteacher');
        $className          = $this->objInput->postStr('className');
        $schoolId           = $this->objInput->postInt('schoolid');
        $gradeId            = $this->objInput->postInt('gradeid');
        $teacherInfo        = htmlspecialchars_decode($this->objInput->postStr('teacherinfo'));
        
        $uid = $this->user['ams_account'];
        if(!($this->checkLoginerInSchool($uid,$schoolId,$gradeId))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    //保证json串的正确解析
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $teacherInfo = stripslashes($teacherInfo);
        }
        $teacherInfoList = json_decode($teacherInfo, true);

        $dateTime = date('Y-m-d H:i:s');
        $time = time();
        $upgrade_year = date('Y');
        if(intval(date('m')) < UPGRADE_MONTH){
        	$upgrade_year = intval($upgrade_year)-1;
        }
        
        $classInfo = array(
            'add_time'=>$time,
            'school_id'=>$schoolId,
            'class_name'=>$className,
            'grade_id'=>$gradeId,
            'add_account'=>$uid,
            'add_date'=>$dateTime,
            'headteacher_account'=>$headTeacherUid,
        	'upgrade_year'=>$upgrade_year,
        );
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classcode = $mClassInfo->addClassInfo($classInfo, true);
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        //保存相应的班级用户信息
        $clientclass_arr = $mClientClass->getClientClassByClassCode($classcode, array('client_type'=>CLIENT_TYPE_TEACHER));
        $clientclass_list = & $clientclass_arr[$classcode];
        
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $headflag = false;        //标示班主任是否担任科目教学
        foreach ((array)$teacherInfoList['data'] as $key=>$teacherinfo) {
            if($teacherinfo['type'] !== 'add') continue;
            
            $teacherid = $teacherinfo['new_teacherid'];
            //增加class_teacher表中相关记录
            $classteacher_data = array(
                'client_account' => $teacherid,
                'class_code' => $classcode,
                'subject_id' => $teacherinfo['subjectid'],
                'add_time' => time(),
                'add_account' => $uid,
                'upd_time' => time(),
                'upd_account' => $uid,
            );
            $mClassTeacher->addClassTeacher($classteacher_data);
            //增加client_class表中的数据
            if(!isset($clientclass_list[$teacherid])) {
                $clientClassInfo = array(
                    'client_account' => $teacherid,
                    'class_admin' => NO_CLASS_ADMIN,
                    'class_code' => $classcode,
                    'teacher_class_role' => TEACHER_CLASS_ROLE_CLASSTEACHER,
                    'add_time' => $time,
                	'upd_time' => $time,
                    'add_account' => $uid,
                    'client_type' => CLIENT_TYPE_TEACHER,
                );
                $add_flag = $mClientClass->addClientClass($clientClassInfo);
             
                 
                //todolist
//                import('@.Common_wmw.functions', null, '.php');
//                moniter_control($this->user, __METHOD__ . ":addClientClass", 1);
                
                
                if($add_flag) {
                    $clientclass_list[$teacherid] = $clientClassInfo;
                }
            }
            
            //班主任是否担任教学工作
            if(!$headflag && $teacherid == $headTeacherUid) {
               $headflag = true;
            }
        }
        //判断对应的班主任信息是否存在
        $client_class_arr = $mClientClass->getClientClassByUid($headTeacherUid);
        
        $head_clientclass_arr = array();
	    if ( !empty($client_class_arr) ) {
	    	foreach ( $client_class_arr as $key=>$list ) {
	    		foreach ($list as $key1=>$val) {
	    			$head_clientclass_arr[$val['client_account']][$val['class_code']] =$val; 
	    		}
	    	}	
	    }
	    unset($client_class_arr);
        
        $head_clientclass_list = & $head_clientclass_arr[$headTeacherUid];
        //按照不同的方式组织数据
        if(!empty($head_clientclass_list)) {
            $tmp_arr = array();
            foreach((array)$head_clientclass_list as $clientclass) {
                $tmp_arr[$clientclass['class_code']] = $clientclass;
            }
            $head_clientclass_list = & $tmp_arr;
        }
        $teacher_class_role = $headflag ? TEACHER_CLASS_ROLE_CLASSBOTH : TEACHER_CLASS_ROLE_CLASSADMIN;
        if(!empty($head_clientclass_list[$classcode])) {
            $client_class_id = intval($head_clientclass_list[$classcode]['client_class_id']);
            $clientclass_data = array(
                'teacher_class_role' => $teacher_class_role,
                'class_admin' => IS_CLASS_ADMIN,
            	'upd_account' => $uid,
                'upd_time' => time(),
            );
            $mClientClass->modifyClientClass($clientclass_data, $client_class_id);
        } else {
            $clientclass_data = array(
                'client_account' => $headTeacherUid,
                'class_code' => $classcode,
                'teacher_class_role' => $teacher_class_role,
                'class_admin' => IS_CLASS_ADMIN,
                'add_time' => time(),
                'upd_time' => time(),
                'add_account' => $uid,
                'upd_account' => $uid,
                'client_type' => CLIENT_TYPE_TEACHER,
            );
            $mClientClass->addClientClass($clientclass_data);
            
            //todolist
//            import('@.Common_wmw.functions', null, '.php');
//            moniter_control($this->user, __METHOD__ . ":addClientClass", 1);
        }
        
        $this->redirect("Amsclasslist/showClassList/schoolId/$schoolId/gradeId/$gradeId");
    }
    
    //根据科目id的不同返回不同科目的老师
    function showTeacherInfoBySubjectId(){
        $schoolId = $this->user['schoolinfo']['school_id'];
        $subjectId = $this->objInput->getInt('subjectid');
        
        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $filters = array(
            'subject_id'=>$subjectId
        );
        $teacherInfo = $mSchoolTeacher->getSchoolTeacherInfoBySchoolId($schoolId,$filters);
        foreach ($teacherInfo[$schoolId] as $schoolId=>$teacherSchoolinfo) {
            $uids[] = $teacherSchoolinfo['client_account'];
        }
		$mUser = ClsFactory::Create('Model.mUser');        
		$userinfo = $mUser->getUserByUid($uids);
        foreach ($userinfo as $uid=>$userinfo) {
            $userName[$userinfo['client_account']] = $userinfo['client_name'];
            $uName [] =array(
                'uid'=>$userinfo['client_account'],
                'uName'=>$userinfo['client_name']
            );
        }
        
        if($teacherInfo && $userinfo){
            $result = array('error'=>array('code'=>1,'message'=>'系统繁忙'),'data'=>$uName);
        }else{
            $result = array('error'=>array('code'=>-1,'message'=>'此科目下无老师'));
        }
        echo json_encode($result);
    }
    
    function showTeacherList($schoolid, $class_code) {
        if(empty($schoolid) || empty($class_code)) {
            return false;
        }
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $clientclass_arr = $mClientClass->getClientClassByClassCode($class_code, array('client_type' => CLIENT_TYPE_TEACHER));
        $clientclass_list = & $clientclass_arr[$class_code];
        if(!empty($clientclass_list)) {
            $tmp_clientclass_list = array();
            foreach($clientclass_list as $client_class) {
                $tmp_clientclass_list[$client_class['client_account']] = $client_class;
            }
            $clientclass_list = & $tmp_clientclass_list;
        }
        
        $teacher_uids = array_keys($clientclass_list);
        
        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getUserBaseByUid($teacher_uids);
        
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_list = $mClassTeacher->getClassTeacherByUid($teacher_uids, array('class_code'=>$class_code));
        
        $subjectid_arr = array();
        if(!empty($classteacher_list)) {
            foreach($classteacher_list as $uid=>$list) {
                foreach($list as $classteacher) {
                    $subject_id = intval($classteacher['subject_id']);
                    $subjectid_arr[$subject_id] = $subject_id; 
                }
            }
        }
        //获取科目的相关信息 
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfo_list = $mSubjectInfo->getSubjectInfoById($subjectid_arr);
        
        $new_classteacher_list = array();
        if(!empty($classteacher_list)) {
            foreach($classteacher_list as $uid=>$list) {
                $username = isset($userlist[$uid]) ? $userlist[$uid]['client_name'] : "";
                $client_class_id = isset($clientclass_list[$uid]) ? $clientclass_list[$uid]['client_class_id'] : 0;
                foreach($list as $classteacher) {
                    $subject_id = intval($classteacher['subject_id']);
                    $subject_name = isset($subjectinfo_list[$subject_id]) ? $subjectinfo_list[$subject_id]['subject_name'] : "";
                    $arr = array(
                        'teacherId' => $uid,
                        'teacherName' => $username,
                        'subject_id' => $subject_id,
                        'subjectName' => $subject_name,
                        'client_class_id' => $client_class_id,
                        'class_teacher_id' => $classteacher['class_teacher_id'],
                    );
                    $new_classteacher_list[] = $arr;
                }
            }
        }
        $this->assign('teacherInfom', $new_classteacher_list);
    }
    
    
    /**
     * 显示已存在的老师的列表
     */
    function showTeacherInfo($schoolid) {
        if(empty($schoolid)) {
            return false;
        }
        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $schoolteacher_arr = $mSchoolTeacher->getSchoolTeacherInfoBySchoolId($schoolid);
        $schoolteacher_list = & $schoolteacher_arr[$schoolid];
        $uids = array();
        if(!empty($schoolteacher_list)) {
            foreach($schoolteacher_list as $schoolteacher) {
                $uids[] = intval($schoolteacher['client_account']);
            }
        }
        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getUserBaseByUid($uids);
        
        $teacherinfo_list = array();
        if(!empty($userlist)) {
            foreach($userlist as $uid=>$user) {
                $teacherinfo_list[$uid]['username'] = $user['client_name'];
            }
        }
        return !empty($teacherinfo_list) ? $teacherinfo_list : false;
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
    
	//年级列表
	private function gradelists($schooltype, $grade_type){
		$grade_lists = Constancearr::class_grade_id();
		if($schooltype ==1){
		    if($grade_type == 1) {
		        $grade_list = array_slice($grade_lists,0,6,true);
		    }else{
		        $grade_list = array_slice($grade_lists,0,5,true);
		    }
		}elseif($schooltype ==2){
			$grade_list = array_slice($grade_lists,6,3,true);
			if($grade_type == 2) {
			    $grade_list[13] = $grade_lists[13];
			}
		}elseif($schooltype ==3){
			$grade_list = array_slice($grade_lists,9,3,true);
		}
		return !empty($grade_list)?$grade_list:false;
	}
    
    //ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }
    
	/**
	 * 获取教师的科目信息并合并
	 * @param  $uids
	 * @param  $split
	 */
    private function getSubjectInfoByTeacherUids($uids, $split = " ") {
        if(empty($uids)) {
            return false;
        }
        
        $split = !empty($split) ? $split : " ";
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUids($uids);
        
        //合并教师科目信息
        $new_subjectinfo_list = array();
        if(!empty($subjectinfolist)) {
            foreach($subjectinfolist as $uid=>$list) {
                $subject_names = array();
                foreach($list as $subject_id=>$subject) {
                    $subject_names[] = $subject['subject_name'];
                }
                
                $new_subjectinfo_list[$uid] = implode($split, $subject_names);
                unset($subjectinfolist[$uid]);
            }
        }
        
        return !empty($new_subjectinfo_list) ? $new_subjectinfo_list : false;
   }
}
