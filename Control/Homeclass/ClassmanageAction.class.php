<?php
/**
 * 1. 班级管理
 *
 * @author Administrator
 *
 */
class ClassmanageAction extends SnsController{

        const  STUDENT_TYPE = 0 ;
        const  TEACHER_TYPE = 1 ;
        const  FAMILY_TYPE = 2 ;
        const  MASTER_TYPE = 3 ;
        const  ADMIN_TYPE = 4 ;
        const  SCHOOLADMIN_TYPE = 5 ;


	public function _initialize() {
	    parent::_initialize(); 
	    import("@.Common_wmw.Pathmanagement_ams");
	    
		$this->assign('chanelid',"chanel1");
		$this->assign('username', $this->user['client_name']);
	}

	function index(){
		$mUser = ClsFactory::Create('Model.mclassManage');
		$classInfo = $mUser->getclassList();
		 
		$this->assign('classInfo',$classInfo);
		
		$this->display('classManage');
	}
	
	function manage(){
        $spaceid_account = $this->objInput->getInt('id');
        $class_code = $this->objInput->getInt('class_code');
        $mUser = ClsFactory::Create('Model.mUser');	

        $temperament = Constancearr::clienttemperament();
        $clienthobby = Constancearr::clienthobby();
        $studentjob = Constancearr::studentjob();
        $cartoon = Constancearr::cartoon();
        $game = Constancearr::game();
        $sports = Constancearr::sports();
        $client_trade = Constancearr::client_trade();

        if(empty($class_code)){
			$class_code = key($this->user['class_info']);
		}
		
		$login_info = $mUser->getUserByUid($spaceid_account);
	    $checkstr = explode(',',$login_info[$spaceid_account]['client_character']);
		
		$arr1=array();
		$p=0;
		foreach($temperament as $key=>$value){
		  foreach($checkstr as $key1=>$value1){
			  if($key==$value1){
				  $arr1[$p]['name']=$value;
			   }
		  }
		   $p++;
		}

		$checkstr2 = explode(',',$login_info[$spaceid_account]['client_interest']);
		$arr2=array();
		$p=0;
		foreach($clienthobby as $key=>$value){
		  foreach($checkstr2 as $key1=>$value1){
			  if($key==$value1){
				  $arr2[$p]['name']=$value;
			   }
		  }
		   $p++;
		}
	
		$checkstr3 = explode(',',$login_info[$spaceid_account]['client_classrole']);
		$arr3=array();
		$p=0;
		foreach($studentjob as $key=>$value){
		  foreach($checkstr3 as $key1=>$value1){
			  if($key==$value1){
				  $arr3[$p]['name']=$value;
			   }
		  }
		   $p++;
		}
		
	   $mClientClass = ClsFactory::Create('Model.mClientClass');
       $classClientInfo = $mClientClass->getClientClassByClassCode($class_code,array('client_type'=> CLIENT_TYPE_TEACHER));
       foreach ($classClientInfo as $val){
          foreach ($val as $value){
              $teacheraccounts[] = $value['client_account'];
          }
       }
       $getTeacherName = $mUser->getUserBaseByUid($teacheraccounts);
       //根据学校的id查询科目信息
       //todochecked
	   $checkstr4 = explode(',',$login_info[$spaceid_account]['like_teacher']);
	   
        $arr4=array();
        $p=0;
        foreach($getTeacherName as $key=>$value){
           foreach($checkstr4 as $key1=>$value1){
              if($key==$value1){
                 $arr4[$p]['name']=$value['client_name'];
               }
          }
          $p++;
        }
	    
       $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
       //************最喜欢的课程
	   $checkstr5 = explode(',',$login_info[$spaceid_account]['like_subject']);
	   $school_subjects = $mSubjectInfo->getSubjectInfoById($checkstr5);
       $arr5=array();
       $p=0;
       foreach($school_subjects as $key=>$value){
           foreach($checkstr5 as $value1){
             if($value['subject_id']==$value1) {
                 $arr5[$p]['name']=$value['subject_name'];
               }

           }
          $p++;
       }
       
       //********最喜欢的游戏
	   $checkstr6 = explode(',',$login_info[$spaceid_account]['like_game']);
        $arr6=array();
        $p=0;
        foreach($game as $key=>$value){
          foreach($checkstr6 as $key1=>$value1){
              if($key==$value1){
                 $arr6[$p]['name']=$value;
               }
          }
          $p++;
      }
		
		//********最喜欢的动漫
	   $checkstr7 = explode(',',$login_info[$spaceid_account]['like_cartoon']);
		$arr7=array();
		$p=0;
		foreach($cartoon as $key=>$value){
		   foreach($checkstr7 as $key1=>$value1){
              if($key==$value1){
                 $arr7[$p]['name']=$value;
               }
		  }
		  $p++;
	  }

	//********最喜欢的运动
   $checkstr8 = explode(',',$login_info[$spaceid_account]['like_movement']);
	$arr8=array();
	$p=0;
	foreach($sports as $key=>$value){
	   foreach($checkstr8 as $key1=>$value1){
		  if($key==$value1){
			 $arr8[$p]['name']=$value;
		   }
	  }
	  $p++;
  }
		
		

		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
		$mUser = ClsFactory::Create('Model.mUser');	
			
		
		//关系类型
		$selectedarr = array();
		$selectedarr['family_type'] = array(
    		'id' => intval($family['family_relation']['family_type']),
    		'datas' => Constancearr::family_relationtype(),
		);
		$arrData = $mFamilyRelation->getFamilyRelationByUid($spaceid_account);
		$arrData = array_shift($arrData);
		//print_r($arrData);
		if($arrData){
    		foreach($arrData as $key=>&$val){
				$login_info_family = $mUser->getUserByUid($val['family_account']);
				$val['family_email'] = $login_info_family[$val['family_account']]['client_email'];
				$val['client_phone'] = $login_info_family[$val['family_account']]['client_phone'];
				$val['client_trade'] = $login_info_family[$val['family_account']]['client_trade_name'];
				$val['job_address_name'] = $login_info_family[$val['family_account']]['job_address_name'];
    		}
		}
		
		$this->assign('selectedarr' , $selectedarr);
		$this->assign('familyarrData',$arrData);
		$this->assign('temperament',$arr1);
		$this->assign('clienthobby',$arr2);
		$this->assign('studentjob',$arr3);
		$this->assign('studentjob',$arr3);
		$this->assign('like_teacher',$arr4);
		$this->assign('like_subject',$arr5);
		$this->assign('like_game',$arr6);
		$this->assign('like_cartoon',$arr7);
		$this->assign('like_movement',$arr8);
		$school_id = $login_info[$spaceid_account]['class_info'][$class_code]['school_id'];
		$this->assign('grade_id_name',$login_info[$spaceid_account]['class_info'][$class_code]['grade_id_name']);
		$this->assign('class_name',$login_info[$spaceid_account]['class_info'][$class_code]['class_name']);
		$this->assign('school_name',$login_info[$spaceid_account]['school_info'][$school_id]['school_name']);

		$this->assign('class_info',$value1);
		$this->assign('person_info',$login_info[$spaceid_account]);
		$this->assign('school_info',$value);
		$this->assign('account',$spaceid_account);
		$this->assign('class_code',$class_code);
		
		$this->display('classManageInformation');
	}
	
	/**
	 * 获取教师的科目信息并合并
	 * @param  $uids
	 * @param  $split
	 */
    private function getSubjectInfoByTeacherUidFromSchoolTeacher($uids, $split = " ") {
        if(empty($uids)) {
            return false;
        }
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidFromSchoolTeacher($uids);
        //合并教师科目信息
        if(!empty($subjectinfolist)) {
            $tmp_subjectinfolist = array();
            foreach($subjectinfolist as $uid=>$list) {
                $tmp_arr = array();
                $subject_name = $comma = "";
                $flag = false;
                foreach($list as $subject_id=>$subject) {
                    $subject_name .= $comma . $subject['subject_name'];
                    $comma = !empty($split) ? $split : " ";
                    if(!$flag) {
                        $tmp_arr = $subject;
                        $flag = true;
                    }
                }
                $subject_name && $tmp_arr['subject_name'] = $subject_name;
                $tmp_subjectinfolist[$uid] = $tmp_arr;
            }
            $subjectinfolist = & $tmp_subjectinfolist;
        }
        
        return !empty($subjectinfolist) ? $subjectinfolist : false;
   }
    
    //显示班级管理页面
    function classManager() {
        $schoolId = key($this->user['school_info']);
        $class_code = $this->objInput->getInt('class_code');
        $headerTeacherUid = $this->user['client_account'];
        import("@.Common_wmw.Constancearr");
        
        foreach($this->user['client_class'] as $classInfo) {
            if($classInfo['class_code'] == $class_code) {
                $client_class_id = $classInfo['client_class_id'];
            }
        }
        
        $gradeId = $this->user['class_info'][$class_code]['grade_id'];
        if(!($this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSADMIN || $this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSBOTH)) {
	        $this->showError('您没有权限操作，请重新登录', '/Homeclass/Myclass/index/class_code/'.$class_code);
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
        $this->assign('schoolid', $schoolId);
        $this->assign('class_code', $class_code);
        $this->assign('headerteacherName', $headerTeacherName);
        $this->assign('grade_name', Constancearr::class_grade_id($gradeId));
        $this->assign('class_name', $classInfo['class_name']);
        $this->display('classmanager');
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
    
    
    //维护班级信息
    function vindicateSchoolClassInfo (){
        $schoolId = key($this->user['school_info']);
        $classCode = $this->objInput->getInt('class_code');
        $uid = $this->user['client_account'];
        
        foreach($this->user['client_class'] as $classInfo) {
            if($classInfo['class_code'] == $classCode) {
                $client_class_id = $classInfo['client_class_id'];
            }
        }
        $gradeid = $this->user['class_info'][$classCode]['grade_id'];
        if(!($this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSADMIN || $this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSBOTH)) {
	        $this->showError('您没有权限操作，请重新登录', "/Homeclass/Classmanage/classManager/class_code/$classCode");
	        return ;
	    }
	    $userid = $this->objInput->getInt('uid');
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
        $this->assign('uid',$userid);
        $this->assign('schoolid',$schoolId);
        $this->assign('subjectInfo',$subjectInfo);
        $this->assign('teacherInfo',$teacherInfo);
        $this->assign('gradeName',$gradeName);
        $this->display('classinfovindicate');
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
	
	
    //显示班级学生及家长的账号列表&&
    public function showClassClient(){
        $classCode = $this->objInput->getInt('classCode'); //获得班级编号
        $stop_flag = $this->objInput->getInt('stop_flag');
        
        $schoolid = key($this->user['school_info']);
        $loginer = $this->user['client_account'];
        
        foreach($this->user['client_class'] as $classInfo) {
            if($classInfo['class_code'] == $classCode) {
                $client_class_id = $classInfo['client_class_id'];
            }
        }
        $gradeid = $this->user['class_info'][$classCode]['grade_id'];
        if(!($this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSADMIN || $this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSBOTH)) {
	        $this->showError('您没有权限操作，请重新登录', "/Homeclass/Classmanage/classManager/class_code/$classCode");
	        return ;
	    }
        
        
        $stop_flag_arr = array(-1,0,2,3);
        if(empty($stop_flag) || !in_array(intval($stop_flag),(array)$stop_flag_arr))
            $stop_flag = 0;

        $stamp       = $this->objInput->getStr('stamp');            //打印标志
        $excelflag   = $this->objInput->getStr('excel');
        
        $mClientClass  = ClsFactory::Create('Model.mClientClass');
        $result   = $mClientClass->getClientClassByClassCode($classCode,array('client_type'=>0));
        $classAccounts = array();
        foreach($result[$classCode] as $key=>$classClientList){  //获取班级所有会员账号，包括学生家长和老师
             if(!empty($classClientList['client_account']))
                 $classAccounts[] = $classClientList['client_account'];
        }
        $mUser = ClsFactory::Create('Model.mUser');
        $clientInfo = $mUser->getUserBaseByUid($classAccounts);//选出会员信息，以获取姓名
        if(!empty($clientInfo)) {
            $tmp_userlist = array();
            foreach($clientInfo as $key=>$val) {
               if(isset($val['status'])) {
                   $tmp_userlist[$key] = $val;
               }
           }
           $clientInfo = & $tmp_userlist;
        }
    	if(!empty($classAccounts)){
    		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
    		$familyRelations = $mFamilyRelation->getFamilyRelationByUid($classAccounts);//通过family_relation表获得学生与家长的对应关系
		}

        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略
		$this->assign('operationStrategy', $operationStrategy);
		$uidarr = array();
		if(!empty($familyRelations)) {
            foreach($familyRelations as $key=>$info_aar){
                foreach($info_aar as $info) {
                    $uidarr[$key][]= $info['family_account'];
                }
            }
		}
        $p=0;
		$names = array();

        foreach ($familyRelations as $childAccount=>$parentList){
            if($p%27 == 0 && $p != 0){
                $uidarr[$childAccount]['print'] = 'true';
            }

            //罗列学生家长新数组，供给excel用-----[$names]
            if($clientInfo[$childAccount]['client_name']){
            	$names[$p][]=$clientInfo[$childAccount]['client_name'];
            	$names[$p][]=$childAccount;
	            foreach($parentList as $key=>$val) {
	                $names[$p][]=$val['family_account'];
	            }
            }
            
            if($clientInfo[$childAccount]['client_name']!=''){
                $uidarr[$childAccount]['child_name'] = $clientInfo[$childAccount]['client_name'];
                $uidarr[$childAccount]['stop_flag'] = $clientInfo[$childAccount]['status'];
            }else{
            	unset($uidarr[$childAccount]);
            }
            
            $p++;
        }
        $this->assign('uidarr',$uidarr);
        $this->assign('classCode',$classCode);
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$loginer);
        if($excelflag == 'excel') {
	        $title = $this->objInput->postArr('title');
	        $index = 1;
	        $new_student_list[$index++] =$title;
	        foreach($names as $key=>$data) {
	            $new_student_list[$index++] = $data;
	        }

	        $excel_datas[0] = array(
	            'title' => '学生账号信息',
	            'cols' => 4,
	            'rows' => count($new_student_list),
	            'datas' => $new_student_list,
	        );
	        $excel_pre = "import_student_list_" . date('Ymd', time()) . "_"; 
	        $pFileName = Pathmanagement_ams::uploadExcel() . uniqid($excel_pre) . ".xls";
	        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        	$HandlePHPExcel->saveToExcelFile($excel_datas, $pFileName);
	        $HandlePHPExcel->export($pFileName,$excel_datas[0]['title']);
	        unset($excel_datas);
	    }elseif($stamp == 'stamp') {
            $userId = $this->user['client_account'];
            $schoolModel = ClsFactory::Create('Model.mSchoolInfo');
            $schoolinfo_arr = $schoolModel->getSchoolInfoByNetManagerAccount($userId);
            $schoolInfo = & $schoolinfo_arr[$userId];
            if(!$schoolInfo){
                $schoolInfo=$mUser->getUserByUid($userId);
                $schoolInfo = $schoolInfo[$userId]['school_info'];
            }

            $filters = array(
                'client_type'=>CLIENT_TYPE_STUDENT
            );
            $clientstuInfo = $mClientClass->getClientClassByClassCode($classCode,$filters);
            $filters = array(
                'client_type'=>CLIENT_TYPE_FAMILY
            );
            $clientparentInfo = $mClientClass->getClientClassByClassCode($classCode,$filters);
            $mClassInfo = ClsFactory::Create('Model.mClassInfo');
            $classInfo = $mClassInfo->getClassInfoById($classCode);

            $this->assign('parentnum',count($clientparentInfo[$classCode]));
            $this->assign('stunum',count($clientstuInfo[$classCode]));
            $this->assign('schoolinfo',$schoolInfo[$schoolid]);
            $this->assign('class_name',$classInfo[$classCode]['class_name']);
	        $this->display('dyclassClientManage');
	    } else {
        	$this->display('classClientManage');
	    }
    }
    
    function saveUpdate() {
    	$oldheaderId = $this->objInput->postInt('oldheader');
        $headTeacherUid = $this->objInput->postInt('headteacher');
        $className = $this->objInput->postStr('className');
        $teacherinfo = $this->objInput->postStr('teacherinfo');
        $schoolId = $this->objInput->postInt('schoolid');
        $gradeId = $this->objInput->postInt('grade');
        $class_code = $this->objInput->postInt('classcode');
        
        $uid = $this->user['client_account'];
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
                    'add_time' => time(),
                    'add_account' => $uid,
                    'upd_account' => $uid,
                    'upd_time' => time(),
                );
            
                $mClassTeacher->addClassTeacher($class_teacher_data);
                //如果不存在则增加教师和班级之间的关系
                if(!isset($clientclass_list[$new_teacherid])) {
                    $clientclass_data = array(
                        'client_account' => $new_teacherid,
                        'class_code' => $class_code,
                        'teacher_class_role' => TEACHER_CLASS_ROLE_CLASSTEACHER,
                        'class_admin' => NO_CLASS_ADMIN,
                        'add_time' => time(),
                        'add_account' => $uid,
                        'upd_account' => $uid,
                        'upd_time' => time(),
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
            foreach($del_arr as $teacher) {
                $old_teacherid = $teacher['old_teacherid'];
                $class_teacher_id = $teacher['class_teacher_id'];
                $client_class_id = $teacher['client_class_id'];
                
                $teacher_relation_arr[$old_teacherid][] = $client_class_id;
                //删除class_teacher表中的数据
                $mClassTeacher->delClassTeacher($class_teacher_id);
            }
            unset($del_arr);
            
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
                    'upd_time' => time(),
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
                        'add_time' => time(),
                        'add_account' => $uid,
                        'upd_account' => $uid,
                        'upd_time' => time(),
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
        
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classinfo_arr = $mClassInfo->getClassInfoBaseById($class_code);
        $classinfo = & $classinfo_arr[$class_code];
        $old_headTeacherUid = intval($classinfo['headteacher_account']);
        
        //更新班级相关信息
        $class_info_data = array(
            'add_account' => $uid,
            'add_time' => time(),
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
                    'upd_time' => time(),
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
                    'upd_time' => time(),
                );
                $mClientClass->modifyClientClass($client_class_data, $client_class_id);
            }
        } else {
            $client_class_data = array(
                'client_account' => $headTeacherUid,
                'class_code' => $class_code,
                'teacher_class_role' => $teacher_class_role,
                'class_admin' => IS_CLASS_ADMIN,
                'add_time' => time(),
                'add_account' => $uid,
                'upd_account' => $uid,
                'upd_time' => time(),
                'client_type' => CLIENT_TYPE_TEACHER,
            );
            $mClientClass->addClientClass($client_class_data);
            
            //todolist
//            import('@.Common_wmw.functions', null, '.php');
//            moniter_control($this->user, __METHOD__ . ":addClientClass", 1);
            
        }
        
        $this->redirect("Classmanage/classManager/schoolid/$schoolId/class_code/$class_code/gradeid/$gradeId/uid/$headTeacherUid");
    }
    
    //保存添加跳转页面
    function saveAdd(){
        $headTeacherUid     = $this->objInput->postStr('headteacher');
        $className          = $this->objInput->postStr('className');
        $schoolId           = $this->objInput->postInt('schoolid');
        $gradeId            = $this->objInput->postInt('gradeid');
        $teacherInfo        = htmlspecialchars_decode($this->objInput->postStr('teacherinfo'));
        
        $uid = $this->user['client_account'];
	    //保证json串的正确解析
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $teacherInfo = stripslashes($teacherInfo);
        }
        $teacherInfoList = json_decode($teacherInfo, true);

        $dateTime = date('Y-m-d H:i:s');
        
        $upgrade_year = date('Y');
        if(intval(date('m')) < UPGRADE_MONTH){
        	$upgrade_year = intval($upgrade_year)-1;
        }
        
        $classInfo = array(
            'add_time'=>time(),
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
                    'add_time' => time(),
                	'upd_time' => time(),
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
    
    //检查班级名称是否重复
    function checkClassName(){
        $schoolId = $this->objInput->getInt('schoolid');
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
    
    //根据科目id的不同返回不同科目的老师
    function showTeacherInfoBySubjectId(){
        $schoolId = $this->objInput->getInt('schoolid');
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
    
    //前往批量添加页面
     Public function goToLotsAdd() {
         $classCode = $this->objInput->getInt('cid'); //获得班级编号
         $gradeid   = $this->objInput->getInt('gradeid');
         $schoolid  = $this->objInput->getInt('schoolid');
         $uid       = $this->objInput->getInt('uid');

         $this->assign('classCode',$classCode);
         $this->assign('gradeid',$gradeid);
         $this->assign('schoolid',$schoolid);
         $this->assign('uid',$uid);
         $this->display('lotaddstudents');
     }
     
    //批量添加学生
     public function addLotsStu() {
  		 $mUser = ClsFactory::Create('Model.mUser');
		 $mClientClass = ClsFactory::Create('Model.mClientClass');
         $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
         $isbetchname = $this->objInput->postStr("isbetch");
         if(!empty($isbetchname)&&$isbetchname == 'betch'){
             $namestr = $this->objInput->postStr("name");

             $stu_arr = explode("\n",trim($namestr));
             foreach($stu_arr as $key=>&$val){
                 if(!empty($val)){
                     $stu_names[] = trim($val);
                 }
             }
         }else{
             $stu_names = $this->objInput->postArr('name');
         }
         
         $cid = $this->objInput->getInt('cid');//获得班级编号
         $schoolid  = $this->objInput->getInt('schoolid');
         $uid       = $this->objInput->getInt('uid');
         foreach($this->user['client_class'] as $classInfo) {
            if($classInfo['class_code'] == $cid) {
                $client_class_id = $classInfo['client_class_id'];
            }
         }
         $gradeid = $this->user['class_info'][$cid]['grade_id'];
         $loginer = $this->user['client_account'];
         if(!($this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSADMIN || $this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSBOTH)) {
	        $this->showError('您没有权限操作，请重新登录', "/Homeclass/Classmanage/classManager/class_code/$cid");
	        return ;
	     }
	     import("@.Common_wmw.WmwString");
         $new_names=array();
         foreach($stu_names as $key=>$val) {
             if(!empty($val) && $val!='') {
                 if(strlen($val)>2 || strlen($val)<30) {
                    $val = WmwString::unhtmlspecialchars($val);
                    $val = WmwString::delhtml($val);
                    $val = addslashes ($val);
                    $new_names[] = $val;
                 }else {
                     echo "<script>alert('输入有误,重新输入');</script>";
                     echo "<script>history.go(-1);</script>";
                     die;
                 }
             }
         }
         $currentAccount=$this->user['client_account'];
         $client_account = $client_info = $family_relation = $client_class = array();     //定义空数组
         foreach($new_names as $key=>$val) {
             $account = array();
             $getaccount = $this->getAccount($account);
             $account[0] = $getaccount;
             $firstchar = WmwString::getfirstchar($val);

             //学生信息
             $client_account['stu_account']=array(
                 'client_account' => $getaccount,
                 'client_password' => MD5_PASSWORD,
             	 'client_name' => $val,
                 'client_type' => CLIENT_TYPE_STUDENT,
                 'status' => CLIENT_STOP_FLAG,
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             //$mUser->addUserClientAccount($client_account['stu_account']);
             $client_info['stu_info']=array(
                 'client_account' => $getaccount,
                 'client_firstchar' => $firstchar,
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             //$mUser->addUserClientInfo($client_info['stu_info']);
             //父亲信息
             $fatherAccount = $this->getAccount($account);
             $account[1] = $fatherAccount;
             $client_account['father_account']=array(
                 'client_account' => $fatherAccount,
                 'client_password' => MD5_PASSWORD,
             	 'client_name' => '父亲',
                 'client_type' => CLIENT_TYPE_FAMILY,
             	 'status' => CLIENT_STOP_FLAG,
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             //$mUser->addUserClientAccount($client_account['father_account']);
             $client_info['father_info']=array(
                 'client_account' => $fatherAccount,
                 'client_firstchar' => '',
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             //母亲的信息
             $motherAccount = $this->getAccount($account);
             $account[2] = $motherAccount;
             $client_account['mother_account']=array(
                 'client_account' => $motherAccount,
                 'client_password' => MD5_PASSWORD,
                 'client_name' => '母亲',
                 'client_type' => CLIENT_TYPE_FAMILY,
                 'status' => CLIENT_STOP_FLAG,
                 'add_time' => time(),
                 'upd_time' => time(),
             );

             $client_info['mother_info']=array(
                 'client_account' => $motherAccount,
                 'client_firstchar' => '',
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             //班级关系信息
             $client_class['stu']=array(
                 'client_account'=>$getaccount,
                 'class_code'=>$cid,
                 'add_time'=>time(),
             	 'upd_time'=>time(),
                 'add_account'=>$currentAccount,
                 'client_type'=>CLIENT_TYPE_STUDENT
             );
             //$mClientClass->addClientClass($client_class['stu']);
             $client_class['father']=array(
                 'client_account'=>$fatherAccount,
                 'class_code'=>$cid,
                 'add_time'=>time(),
             	 'upd_time'=>time(),
                 'add_account'=>$currentAccount,
                 'client_type'=>CLIENT_TYPE_FAMILY
             );
             //$mClientClass->addClientClass($client_class['father']);
             $client_class['mother']=array(
                 'client_account'=>$motherAccount,
                 'class_code'=>$cid,
                 'add_time'=>time(),
             	 'upd_time'=>time(),
                 'add_account'=>$currentAccount,
                 'client_type'=>CLIENT_TYPE_FAMILY
             );
             //家庭关系信息
             $family_relation['father'] = array(
                 'client_account'=>$getaccount,
                 'family_account'=>$fatherAccount,
                 'family_type'=>1,
                 'add_account'=>$currentAccount,
                 'add_time'=>time()
             );
             $family_relation['mother'] = array(
                 'client_account'=>$getaccount,
                 'family_account'=>$motherAccount,
                 'family_type'=>2,
                 'add_account'=>$currentAccount,
                 'add_time'=>time()
             );
             if($mUser->addUserClientAccountBat($client_account)){
                $clientadds = $mUser->addUserClientInfoBat($client_info);
             }
             if($clientadds){
             	$clientclassadds = $mClientClass->addClientClassBat($client_class);
             }
             if($clientclassadds){
             	$mFamilyRelation->addFamilyRelationBat($family_relation);
             }
         }
         $this->redirect('/Classmanage/showclassClient/uid/'.$uid.'/classCode/'.$cid.'/gradeid/'.$gradeid.'/schoolid/'.$schoolid."/stop_flag/0");
     }
     
//得到不同的账号
     private function getAccount($accountArr) {
     	 $mUser = ClsFactory::Create('Model.mUser');
         $getaccount = $mUser->createNewUid();

         if(in_array($getaccount,$accountArr)) {
             $this->getAccount($accountArr);
         }else {
             return $getaccount;
         }
     }

    /*
     * 展示学生及其家长账号状态
     * author: Luan, 2011-08-17
     */
    public function showClassClientState(){
        $classCode = $this->objInput->getInt('cid'); //获得班级编号
        $gradeid   = $this->objInput->getInt('gid');
        $schoolid  = $this->objInput->getInt('sid');
        $uid       = $this->objInput->getInt('uid');
        $stop_flag       = $this->objInput->getInt('stp');
		foreach($this->user['client_class'] as $classInfo) {
            if($classInfo['class_code'] == $classCode) {
                $client_class_id = $classInfo['client_class_id'];
            }
         }
         $gradeid = $this->user['class_info'][$classCode]['grade_id'];
         $loginer = $this->user['client_account'];
        if(!($this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSADMIN || $this->user['client_class'][$client_class_id]['teacher_class_role'] == TEACHER_CLASS_ROLE_CLASSBOTH)) {
	        $this->showError('您没有权限操作，请重新登录', "/Homeclass/Classmanage/classManager/class_code/$classCode");
	        return ;
	     }

        $childAccount  = $this->objInput->getInt('cac');//获得学生账号

		$mUser = ClsFactory::Create('Model.mUser');
		$childInfo = $mUser->getUserBaseByUid($childAccount);//选出这个学生的的详细信息
		$childStopFlag = Constancearr::stop_flag($stop_flag);//学生账号状态

		$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略

        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
    	$familyAccountRelation = $mFamilyRelation->getFamilyRelationByUid($childAccount);//获得账号关系

    	$parentAccountStatus_1 = array();
    	$parentAccountStatus_1 = array();
        if(!empty($familyAccountRelation)){ //存在家长账号时
            $mBusinessPhone = ClsFactory::Create('Model.mBusinessphone');

    		$parent_1            = array_shift($familyAccountRelation[$childAccount]);
            $parentAccount_1     = $parent_1['family_account'];
            $parentAccountBase_1 = $mUser->getUserBaseByUid($parentAccount_1);
            $phoneInfo_1 = $mBusinessPhone -> getbusinessphonebyalias_id($parentAccount_1);   //家长1手机信息
        	$parent_2            = array_shift($familyAccountRelation[$childAccount]);
            $parentAccount_2     = $parent_2['family_account'];
            $parentAccountBase_2 = $mUser->getUserBaseByUid($parentAccount_2);
            $phoneInfo_2 = $mBusinessPhone -> getbusinessphonebyalias_id($parentAccount_2);  //家长2手机信息

            if (!empty($phoneInfo_1)) {
                $parentAccountStatus_1 = array(
                        'mark'              => 0, //状态标志位
                    	'client_account'    => $parentAccount_1,
                        'business_enable'		 =>$phoneInfo_1[$parentAccount_1]['business_enable'],
            			'phone_status'      => $phoneInfo_1[$parentAccount_1]['phone_status'],
                        'stop_flag' =>     $parentAccountBase_1[$parentAccount_1]['status'],

                        'business_enable_message' => Constancearr::business_enable($phoneInfo_1[$parentAccount_1]['business_enable']),
            			'phone_status_message'   => Constancearr::phone_status($phoneInfo_1[$parentAccount_1]['phone_status']),
                        'stop_flag_message'		    => Constancearr::stop_flag($parentAccountBase_1[$parentAccount_1]['status']),

                        'client_phone' 		=> $phoneInfo_1[$parentAccount_1]['phone_id'],
                        'phone_create_time' => date('Y-m-d H:i:s',$phoneInfo_1[$parentAccount_1]['business_enable_time']),
                );
            }else { //手机号不存在
                $parentAccountStatus_1 = array(
                        'mark'              => 0, //状态标志位
                    	'client_account'    => $parentAccount_1,
                        'business_enable'		 => 0,
            			'phone_status'      => 0,
                        'stop_flag' =>     $parentAccountBase_1[$parentAccount_1]['status'],
                        'business_enable_message' => Constancearr::business_enable(0),
            			'phone_status_message'   => Constancearr::phone_status(0),
                        'stop_flag_message'		    => Constancearr::stop_flag($parentAccountBase_1[$parentAccount_1]['status']),
                        'client_phone' 		=> '',
                        'phone_create_time' => '',
                );
            }
            if (!empty($phoneInfo_2)) {
                $parentAccountStatus_2 = array(
                        'mark'              => 0, //状态标志位
                    	'client_account'    => $parentAccount_2,
                        'business_enable'		 =>$phoneInfo_2[$parentAccount_2]['business_enable'],
            			'phone_status'      => $phoneInfo_2[$parentAccount_2]['phone_status'],
                        'stop_flag' =>     $parentAccountBase_2[$parentAccount_2]['status'],

            			'business_enable_message' => Constancearr::business_enable($phoneInfo_2[$parentAccount_2]['business_enable']),
            			'phone_status_message'  => Constancearr::phone_status($phoneInfo_2[$parentAccount_2]['phone_status']),
                        'stop_flag_message'	=> Constancearr::stop_flag($parentAccountBase_2[$parentAccount_2]['status']),

                        'client_phone' 		=> $phoneInfo_2[$parentAccount_2]['phone_id'],
                        'phone_create_time' => date('Y-m-d H:i:s',$phoneInfo_2[$parentAccount_2]['business_enable_time']),

                );
            }else {//手机号不存在
                $parentAccountStatus_2 = array(
                        'mark'              => 0, //状态标志位
                    	'client_account'    => $parentAccount_2,
                        'business_enable'		 => 0,
            			'phone_status'      => 0,
                        'stop_flag' =>     $parentAccountBase_2[$parentAccount_2]['status'],
                        'business_enable_message' => Constancearr::business_enable(0),
            			'phone_status_message'   => Constancearr::phone_status(0),
                        'stop_flag_message'		    => Constancearr::stop_flag($parentAccountBase_2[$parentAccount_2]['status']),
                        'client_phone' 		=> '',
                        'phone_create_time' => '',
                );
            }
           if($operationStrategy == OPERATION_STRATEGY_DEFAULT){//默认运营策略（无策略） 只显示账号状态
    		    $parentAccountStatus_1['mark'] = 1;
    		    $parentAccountStatus_2['mark'] = 1;
    		}else {//非默认运营策略
//    		        if($parentAccountStatus_1[])
    		        if($parentAccountStatus_1['business_enable'] == BUSINESS_ENABLE_NO){// 未开通手机
    		            $parentAccountStatus_1['mark'] = 2;
    		        }else if($parentAccountStatus_1['business_enable'] == BUSINESS_ENABLE_YES){ //开通手机 时显示手机状态（正常/欠费停机）
    		            $parentAccountStatus_1['mark'] = 3;
    		        }else if ($parentAccountStatus_1['business_enable'] == BUSINESS_ENABLE_CLOSE) { //取消了手机业务
    		            $parentAccountStatus_1['mark'] = 4;
    		        }
    				if($parentAccountStatus_2['business_enable'] == BUSINESS_ENABLE_NO){
    				    $parentAccountStatus_2['mark'] = 2;
    		        }else if($parentAccountStatus_2['business_enable'] == BUSINESS_ENABLE_YES){
    		            $parentAccountStatus_2['mark'] = 3;
    		        }else if ($parentAccountStatus_2['business_enable'] == BUSINESS_ENABLE_CLOSE) {
    		            $parentAccountStatus_2['mark'] = 4;
    		        }
    		}
        }//end if(!empty($familyAccountRelation))
		$this->assign('classCode',$classCode);
		$this->assign('gradeid',$gradeid);
		$this->assign('schoolid',$schoolid);
		$this->assign('uid',$uid);

		$this->assign('childAccount',$childAccount);
		$this->assign('childName' , $childInfo[$childAccount]['client_name']);
		$this->assign('childStopFlag', $childStopFlag);

 		$this->assign('parentAccountStatus_1',$parentAccountStatus_1);
        $this->assign('parentAccountStatus_2',$parentAccountStatus_2);

        $this->display('showClassClientState');
    }
    
    //批量学生姓名录入
    public function batchinputinfo(){
        $classCode = $this->objInput->getInt('cid'); //获得班级编号
         $gradeid   = $this->objInput->getInt('gradeid');
         $schoolid  = $this->objInput->getInt('schoolid');
         $uid       = $this->objInput->getInt('uid');

         $this->assign('classCode',$classCode);
         $this->assign('gradeid',$gradeid);
         $this->assign('schoolid',$schoolid);
         $this->assign('uid',$uid);
         $this->display('batchinputinfo');
    }
    
    //移出班级成员方法
    public function remove_client(){
        $child_account = $this->objInput->getStr('uid');
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $FamilyRelation = $mFamilyRelation->getFamilyRelationByUid($child_account);
        $del_uid = array(
            $child_account
        ); 
        foreach($FamilyRelation[$child_account] as $uid => $family) {
            $del_uid[] = $family['family_account'];
        }
        $mClientClass = ClsFactory::Create("Model.mClientClass");
        $ClientClass_list = $mClientClass->getClientClassByUid($del_uid);
        foreach($ClientClass_list as $uid => $client_info) {
            foreach($client_info as $client_class_id => $client_class_info) {
                $resault = $mClientClass->delClientClass($client_class_id);
            }
        }
        echo $resault;
    }
    
    //修改班级成员账号管理中的姓名
   public function update_name(){
       $user_name = $this->objInput->postStr('user_name');
       $child_account = $this->objInput->postStr('child_account');
       
       if(!empty($user_name)){
           $datarr =array(
               'client_name' => $user_name,
           	   'upd_time' => time(),
           );
           $mUser = ClsFactory::Create('Model.mUser');
           $result = $mUser->modifyUserClientAccount($datarr , $child_account);
           echo $result;
       }else{
            echo false;
       }
   }

}
