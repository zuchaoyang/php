<?php
class UserinfosAction extends UcController {
    
    public function _initialize() {
       parent::_initialize();
       import("@.Common_wmw.Pathmanagement_sns");
    }
    
    /**
     * 
     *获取用户个人资料管理
     * 
     **/   
    public function UserBaseInfos() {
        $email = $this->user['client_email'];
        if(!empty($this->user['client_email'])) {
            $this->user['client_email'] = $this->rename_email($email);
        }
		
		$blood_type = Constancearr::client_bloodtype();
		
		
		$this->user['client_birthday'] = empty($this->user['client_birthday']) ? '' : $this->user['client_birthday'];
		$this->assign('blood_type', $blood_type);
		
        $this->assign('userinfo', $this->user);
        
        
        $this->display('user_baseinfo');
    }
    
    /**
     * 联系方式的管理
     */
    public function contact_manage() {
        $user_list = $this->user;
        $client_account = $user_list['client_account'];
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
        $PhoneInfos = $mBusinessphone->getbusinessphonebyalias_id($client_account);
        $phone_id = $PhoneInfos[$client_account]['account_phone_id2'];
        if(!empty($phone_id)) {
            $client_phone_id = $this->rename_phone_id($phone_id);
        }
        if(!empty($user_list['client_email'])) {
            $client_email = $this->rename_email($user_list['client_email']);
        }
        
        $user_list['phone_id'] = $client_phone_id;
        $user_list['client_email'] = $client_email;
        
        $this->assign('userinfo',$user_list);
        
        $this->display('user_contact');
            
    }
    
    /*
     * 修改联系方式
     */
    public function modify_contact() {
        $account = $this->getCookieAccount();
        $client_phone = $this->objInput->postStr('client_phone');
        $datarr = array(
            'client_phone' => $client_phone,
        	'upd_time'=>time(),
        );
        
        $mUser = ClsFactory::Create('Model.mUser');
        
        $resault = $mUser->modifyUserClientInfo($datarr,$account);
        
        if(!empty($resault)) {
            $this->showSuccess('操作成功','/Uc/Userinfos/contact_manage');
        } else {
            $this->showError('操作失败，请重新操作','/Uc/Userinfos/contact_manage');
        }
        
    }
    
    
     /**
     * 
     *用户头像的修改
     * 
     **/    
    public function head_photo() {
        $account = $this->getCookieAccount();
        $mUser = ClsFactory::Create('Model.mUser');
		$userInfo = $mUser->getUserByUid($account);
		
		$this->assign('photonameurl',Pathmanagement_sns::getHeadImg($account) . $userInfo[$account]['client_headimg']);
		$this->assign('user',$this->user);
		
		$this->display('user_headpic');
    }
    
    
    /**
     * 用户资料的修改
     * 
     **/
    
    public function modifyUserBaseInfo() {
        $account = $this->user['client_account'];
        
        $client_sex = $this->objInput->postInt('client_sex');
        $client_name = $this->objInput->postStr('client_name');
        $client_birthday = $this->objInput->postStr('client_birthday');
        $client_blood_type = $this->objInput->postStr('client_blood_type');
        $client_zodiac = $this->objInput->postInt('client_zodiac');
        $client_name = $this->objInput->postStr('client_name');
        $client_constellation = $this->objInput->postInt('client_constellation');
        
        $mUser = ClsFactory::Create('Model.mUser');
        $datarr = array(
            'client_sex' => $client_sex,
            'client_birthday' => $client_birthday,
            'client_blood_type' => $client_blood_type,
        	'client_zodiac' => $client_zodiac,
            'client_constellation'=> $client_constellation,
            'upd_time' => time(),
        );
        
        $result = $mUser->modifyUserClientInfo($datarr, $account);
        
        if($result && $this->user['client_type'] == CLIENT_TYPE_FAMILY) {
            $datarr = array(
               'client_name' => $client_name
           );
            
          $result1 = $mUser->modifyUserClientAccount($datarr, $account);
        }
        
        if($result || $result1) {
             $this->showSuccess("保存成功", "/Uc/Userinfos/UserBaseInfos");
        } else {
             $this->showError("操作失败", "/Uc/Userinfos/UserBaseInfos");
        }
    }
    
    
    /**
     * 
     *扩展资料的获取 
     * 
     **/
    
    public function getUserStretchInfo() {
       $client_type = intval($this->user['client_type']);
       if($client_type == CLIENT_TYPE_STUDENT) {
           $this->student_extend();
       } elseif($client_type == CLIENT_TYPE_TEACHER) {
           $this->teacher_extend();
       } else {
           $this->family_extend();
       }
    }
    
    /**
     * 展示学生的扩展信息
     */
    public function student_extend(){
       $family_info = $this->getFamilyInfoArr();
       
       //查询班级关于这个账号所在班级的所有老师的名字
       $class_code = key($this->user['class_info']);
       list($like_teachers, $like_subjects) = $this->getClassTeacherAndSubject($class_code);
       
       //转换用户的爱好信息
       $characters      = $this->parseStudentExtend(Constancearr::clienttemperament(), $this->user['client_character']);
       $interests       = $this->parseStudentExtend(Constancearr::clienthobby(), $this->user['client_interest']);
       $class_roles     = $this->parseStudentExtend(Constancearr::studentjob(), $this->user['client_classrole']);
       $cartoons        = $this->parseStudentExtend(Constancearr::cartoon(), $this->user['like_cartoon']);
       $games           = $this->parseStudentExtend(Constancearr::game(), $this->user['like_game']);
       $sports          = $this->parseStudentExtend(Constancearr::sports(), $this->user['like_movement']);
       $like_teachers   = $this->parseStudentExtend($like_teachers, $this->user['like_teacher']);
       $like_subjects   = $this->parseStudentExtend($like_subjects, $this->user['like_subject']);
       
       //处理学校信息
       if(isset($this->user['school_info'])) {
           $school_info = reset($this->user['school_info']);
           $this->user['school_name'] = $school_info['school_name'];
           
           unset($this->user['school_info']);
       }
       //处理班级信息
       if(isset($this->user['class_info'])) {
           $class_names = array();
           $grade_id = 0;
           foreach($this->user['class_info'] as $class_code=>$class_info) {
               $class_names[] = $class_info['class_name'];
               if(empty($grade_id)) {
                   $grade_id = intval($class_info['grade_id']);
               }
           }
           $this->user['class_name'] = implode(' ', $class_names);
           $this->user['grade_name'] = Constancearr::class_grade_id($grade_id);
           
           unset($this->user['class_info'], $this->user['client_class']);
       }
      
       $this->assign('like_subjects' , $like_subjects);
       $this->assign('like_teachers' , $like_teachers);
       $this->assign('sports', $sports);
       $this->assign('games', $games);
       $this->assign('cartoons', $cartoons);
       $this->assign('class_roles', $class_roles);
       $this->assign('interests', $interests);
       $this->assign('characters', $characters);
       
       $this->assign('family_info', $family_info);
       $this->assign('userinfo',$this->user);
       $this->display('student_extend');
    }
    
    public function family_extend(){
       $family_info = $this->getFamilyInfoArr();
       
       if(!empty($this->user['class_info'])) {
           $class_info = array_shift($this->user['class_info']);
       }
       
       if(!empty($this->user['school_info'])) {
           $school_info = array_shift($this->user['school_info']);
       }
       
       unset($this->user['class_info'],$this->user['school_infos']);
       
       $this->user['class_name'] = $class_info['class_name'];
       $this->user['grade_id_name'] = $class_info['grade_id_name']; 
       $this->user['school_name'] = $school_info['school_name'];
       
       $this->assign('userinfo',$this->user);
       $this->assign('client_trade',Constancearr::client_trade());
       $this->assign('family_info',$family_info);
       $this->display('parent_extend');
    }
    
    public function teacher_extend() {
       $client_account = $this->user['client_account'];
       
       //获取老师执教年和月
       $this->user['teach_time'] = date('Y-m', $this->user['teach_time']);
       //获取班级名称
       if(!empty($this->user['class_info'])) {
           $class_name_arr = array();
           foreach ($this->user['class_info'] as $key=>$val) {
               $class_name_arr[$key] = $val['class_name'];
           }
           $this->user['class_names'] = implode($class_name_arr, '，');
           
           unset($this->user['class_info'], $this->user['client_class']);
       }
       
       $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
       $subjectinfo_arr = $mSubjectInfo->getSubjectInfoByTeacherUids($client_account);
       $subjectinfo_list = & $subjectinfo_arr[$client_account];
       if(!empty($subjectinfo_list)) {
           //获取科目名称
           $subject_name_arr = array();
           foreach($subjectinfo_list as $subject_id=>$subject) {
               $subject_name_arr[$subject_id] = $subject['subject_name'];
           }
           $this->user['subject_names'] = implode($subject_name_arr, '，');
       }
       
       //学校信息
       if(!empty($this->user['school_info'])) {
           $school_info = reset($this->user['school_info']);
           $this->user['school_name'] = $school_info['school_name'];
           
           unset($this->user['school_info']);
       }
       
       $this->assign('userinfo', $this->user);
       $this->assign('client_title', Constancearr::client_title());
       $this->assign('teacherjob', Constancearr::client_job());
       
       $this->display('teacher_extend');
    }
    
    
    private  function getFamilyInfoArr() {
        
        $client_type = $this->user['client_type'];
        $login_account = $this->getCookieAccount();

        $familyarr = array();
        $FamilyAccountArr[$login_account] = $login_account;
        //获取家长账号头像和名字
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
         
        if($client_type == CLIENT_TYPE_FAMILY ) {
            $FamilyInfos = $mFamilyRelation->getFamilyRelationByFamilyUid($login_account);

            $FamilyAccountArr = array();
            $child_info = array_shift($FamilyInfos[$login_account]);
            $child_account = $child_info['client_account'];
            $FamilyAccountArr[$child_account] = $child_account;
            
            unset($child_info,$child_info);
        }else{
        	$child_account = $login_account;
        }

        $FamilyInfos = $mFamilyRelation->getFamilyRelationByUid($child_account);

        foreach ($FamilyInfos[$child_account] as $relation_id => $val) {
            $FamilyAccountArr[$val['family_account']] = $val['family_account'];
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $FamilyClientInfos = $mUser->getClientAccountById($FamilyAccountArr);
        $FamilyClientInfos[$child_account]['client_name']=$FamilyClientInfos[$child_account]['client_name'].'(孩子)';
        $FamilyClientInfos[$child_account]['family_type']='child';
        
        foreach($FamilyClientInfos as $account_key => & $account_info ) {
            if(!empty($account_info['client_headimg'])){
                $account_info['headimg'] = Pathmanagement_sns::getHeadImg($account_key) . $account_info['client_headimg'];
            }else{
                $account_info['headimg'] = '';
            }
        }
        
        $sort_family_list = array();
        $sort_family_list[2] = $FamilyClientInfos[$child_account];
        foreach($FamilyInfos[$child_account] as $relation_key=>$relation_val) {
            if($relation_val['family_type'] == '1') {
                $FamilyClientInfos[$relation_val['family_account']]['client_name'] = $FamilyClientInfos[$relation_val['family_account']]['client_name'].'(父亲)';

                if($login_account == $relation_val['family_account']){
                	$FamilyClientInfos[$relation_val['family_account']]['family_type']='login_father';
                }else{
                	$FamilyClientInfos[$relation_val['family_account']]['family_type']='father';
                }
                
                $sort_family_list[0] = $FamilyClientInfos[$relation_val['family_account']];
            }else if ($relation_val['family_type'] == '2'){
                $FamilyClientInfos[$relation_val['family_account']]['client_name'] = $FamilyClientInfos[$relation_val['family_account']]['client_name'].'(母亲)';
            	
                if($login_account == $relation_val['family_account']){
                	$FamilyClientInfos[$relation_val['family_account']]['family_type']='login_mother';
                }else{
                	$FamilyClientInfos[$relation_val['family_account']]['family_type']='mother';
                }
                
                $sort_family_list[1]=$FamilyClientInfos[$relation_val['family_account']];
            }
        } 
        
        unset($FamilyInfos,$FamilyClientInfos);
        ksort($sort_family_list);
        
        return !empty($sort_family_list) ? $sort_family_list : false;
    }
    
    
	/*
	 * 
	 * 个人扩展资料修改的录入
	 */
    
	//*******************老师资料
	public function modifyUserteacher(){
	    $account = $this->getCookieAccount();
	    $teach_time = $this->objInput->postStr('teach_time');
	    $teachertitle = $this->objInput->postStr('client_title');
	    $teacherjob = $this->objInput->postStr('client_job');
	    $upd_time=time();
	    
	    if(empty($teach_time)){
	        $this->showError("执教年月不能为空!", "/Uc/Userinfos/getUserStretchInfo");
	    }
	    //处理执教年月的数据格式
	    $teach_time_arr = explode("-", $teach_time);
	    if(count($teach_time_arr) < 3) {
	        $teach_time_arr[] = "01";
	    }
	    $teach_time = implode('-', $teach_time_arr);
	    if(strtotime($teach_time) === false) {
	        $this->showError("执教年月格式错误!", "/Uc/Userinfos/getUserStretchInfo");
	    }
	    
	    $kzteacher = array(
	        'teach_time'=> strtotime($teach_time),
	        'client_title' => $teachertitle,
	        'client_job' => $teacherjob,
	    	'upd_time' => $upd_time,
	    );
	    
		$mUser = ClsFactory::Create('Model.mUser');	    
		$modifystretch = $mUser->modifyUserClientInfo($kzteacher,$account);
		
	    if($modifystretch){
            $this->showSuccess("操作成功", "/Uc/Userinfos/getUserStretchInfo");
        }else{
            $this->showError("操作失败", "/Uc/Userinfos/getUserStretchInfo");
    	}
	}
	//****************家长资料
	public function modifyUserparent(){
	    $account = $this->getCookieAccount();
	    
	    $upd_time=time();
	    
	    $kzparent = array(
	        'client_trade' => $this->objInput->postStr('client_trade'),
	    	'job_address_name' => $this->objInput->postStr('job_address_name'),
	        'area_id' => $this->objInput->postInt('area_id'),
	        'client_address' => $this->objInput->postStr('client_address'),
	    	'upd_time' => $upd_time,
	    );
	    
		$mUser = ClsFactory::Create('Model.mUser');	    
		$modifystretch = $mUser->modifyUserClientInfo($kzparent,$account);
    	if($modifystretch){
           $this->showSuccess("保存成功", "/Uc/Userinfos/getUserStretchInfo");
	    } else{
            $this->showError("操作失败", "/Uc/Userinfos/getUserStretchInfo");
    	}
	}
	
    //*********************个人资料
	public function modifyUserstudent() {
	    $area_id = $this->objInput->postInt('area_id');
	    $client_address = $this->objInput->postStr('client_address');
	    
	    $client_character   = $this->objInput->postArr('client_character');
	    $client_interest   = $this->objInput->postArr('client_interest');
	    $client_classrole  = $this->objInput->postArr('client_classrole');
	    $like_teacher      = $this->objInput->postArr('like_teacher');
	    $like_subject      = $this->objInput->postArr('like_subject');
	    $like_cartoon      = $this->objInput->postArr('like_cartoon');
	    $like_game         = $this->objInput->postArr('like_game');
	    $like_movement     = $this->objInput->postArr('like_movement');
	    
	    //获取系统设置的合理的范围信息
        $class_code = key($this->user['class_info']);
        list($sys_like_teacher, $sys_like_subject) = $this->getClassTeacherAndSubject($class_code);
        //转换用户的爱好信息
        $sys_client_character  = Constancearr::clienttemperament();
        $sys_client_interest   = Constancearr::clienthobby();
        $sys_client_classrole  = Constancearr::studentjob();
        $sys_like_cartoon      = Constancearr::cartoon();
        $sys_like_game         = Constancearr::game();
        $sys_like_movement     = Constancearr::sports();
        
        //检测数据的合法性
        $client_character = array_intersect($client_character, array_keys($sys_client_character));
        $client_interest = array_intersect($client_interest, array_keys($sys_client_interest));
        $client_classrole = array_intersect($client_classrole, array_keys($sys_client_classrole));
        $like_teacher = array_intersect($like_teacher, array_keys($sys_like_teacher));
        $like_subject = array_intersect($like_subject, array_keys($sys_like_subject));
        $like_cartoon = array_intersect($like_cartoon, array_keys($sys_like_cartoon));
        $like_game = array_intersect($like_game, array_keys($sys_like_game));
        $like_movement = array_intersect($like_movement, array_keys($sys_like_movement));
	   
        $clientinfo_datas = array(
           'area_id' 		  => $area_id,
           'client_address' => $client_address,
           'client_character' => implode(',', (array)$client_character),
           'client_interest'  => implode(',', (array)$client_interest),
           'client_classrole' => implode(',', (array)$client_classrole),
           'like_teacher'     => implode(',', (array)$like_teacher),
           'like_subject'     => implode(',', (array)$like_subject),
           'like_cartoon'     => implode(',', (array)$like_cartoon),
           'like_game'        => implode(',', (array)$like_game),
           'like_movement'    => implode(',', (array)$like_movement),
           'upd_time'         => time(),
        );
        
       $account = $this->user['client_account'];
       
	   $mUser = ClsFactory::Create('Model.mUser');	
	   $effect_row = $mUser->modifyUserClientInfo($clientinfo_datas, $account);
	   
	   if(empty($effect_row)) {
	       $this->showError("保存失败!", "/Uc/Userinfos/getUserStretchInfo");
	   }
	   
       $this->showSuccess("保存成功!", "/Uc/Userinfos/getUserStretchInfo");
	}
	
    /**
     * 用户隐私设置
     * 
     **/
    public function AccountPrivacy() {
        $uid = $this->user['client_account'];
        
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');	
		$user_personconfig_list = $mPersonconfig->getPersonConfigByaccount($uid);
		$user_personconfig = & $user_personconfig_list[$uid];
		if($user_personconfig){
			$space_access = $user_personconfig['space_access'];
		}
		
		$space_access = in_array($space_access, array(WMW_ALLUSER, WMW_FRIENDS, WMW_MYSELEF)) ? $space_access : WMW_ALLUSER;
		
		$this->assign('userinfo', $this->user);
		$this->assign('client_space_access', $space_access);
		
		$this->display('user_access');
    }
    
    
    /**
     * 隐私设置处理方法
     * 
     **/
    public function SaveAccountPrivacy() {
		$space_access = $this->objInput->postStr('space_access');
		
		$space_access = in_array($space_access, array(WMW_ALLUSER, WMW_FRIENDS, WMW_MYSELEF)) ? $space_access : WMW_ALLUSER;
		
		$account = $this->user['client_account'];
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		//查找用户的空间设置信息是否存在
		$personconfig_list = $mPersonconfig->getPersonConfigByaccount($account);
		$personconfig = & $personconfig_list[$account];
		
		if(empty($personconfig)) {
		    $personconfig_datas = array(
		        'client_account' => $account,
    			'space_access' => $space_access,
    		);
    		$returndata = $mPersonconfig->addPersonConfig($personconfig_datas);
		} else {
    		$personconfig_datas = array(
    			'space_access' => $space_access,
    		);
    		$returndata = $mPersonconfig->modifyPersonConfig($personconfig_datas, $account);
		}
		
		
		if(empty($returndata)) {
		    $this->showError('隐私设置失败', "/Uc/Userinfos/AccountPrivacy");
		}
		
		$this->showSuccess('隐私设置成功', "/Uc/Userinfos/AccountPrivacy");
	}
	
	/**
	 * 
	 * 手机号的处理
	 */
	
	private function rename_phone_id($phone_id) {
	    $phone_replace = "/(1\d{1,2})\d\d(\d{0,2})/";
		$replacphone = "\$1****\$3";
		
		$phone_id = preg_replace($phone_replace,$replacphone,$phone_id);
		
		return !empty($phone_id) ? $phone_id : false;
	}
	
	/**
     *邮箱的处理 
     */
    private function rename_email($email) {
        $texta = substr($email,0,strpos($email,"@"));
		$textb = substr($email,strpos($email,"@"));
		
		if(strlen($texta)>2){
			$email = substr($texta,0,2)."******".$textb;
		}else{
			$email = $texta."******".$textb;
		}    

		return !empty($email) ? $email : false;
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
        
        $split = !empty($split) ? $split : " ";
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfo_list = $mSubjectInfo->getSubjectInfoByTeacherUidFromSchoolTeacher($uids);
        
        if(empty($subjectinfo_list)) {
            return false;
        }
        
        //合并教师科目信息
        $merge_subjectinfolist = array();
        if(!empty($subjectinfo_list)) {
            foreach($subjectinfo_list as $uid=>$list) {
                $subject_name_arr = array();
                foreach($list as $subject_id=>$subject) {
                    $subject_name_arr[] = $subject['subject_name'];
                }
                
                $first_subject = reset($list);
                $first_subject['subject_name'] = implode($split, $subject_name_arr);
                
                $merge_subjectinfolist[$uid] = $first_subject;
            }
        }
        
        return array($merge_subjectinfolist, $subjectinfo_list);
   }
   
   /**
    * 获取班级的教师和科目信息
    * @param $class_code
    */
   protected function getClassTeacherAndSubject($class_code) {
       if(empty($class_code)) {
           return false;
       }
       
       $mClientClass = ClsFactory::Create('Model.mClientClass');
       $clientclass_arr = $mClientClass->getClientClassByClassCode($class_code, array('client_type'=> CLIENT_TYPE_TEACHER));
       $clientclass_list = & $clientclass_arr[$class_code];
       //获取老师和科目的基本信息
       $teacheraccounts = array();
       if(!empty($clientclass_list)) {
           foreach ($clientclass_list as $clientclass){
               $teacheraccounts[] = $clientclass['client_account'];
           }
           
           unset($clientclass_list, $clientclass_arr);
       }
       $mUser = ClsFactory::Create('Model.mUser');
       $teacher_list = $mUser->getClientAccountById($teacheraccounts);
       list($merge_subject_list, $subject_list) = $this->getSubjectInfoByTeacherUidFromSchoolTeacher($teacheraccounts);
       
       //获取老师和科目的对应关系
       $like_teachers = array();
       if(!empty($teacher_list)) {
           foreach($teacher_list as $uid=>$user) {
               $subject_name = isset($merge_subject_list[$uid]) ? "-" . $merge_subject_list[$uid]['subject_name'] : "";
               $like_teachers[$uid] = $user['client_name'] . $subject_name;
           }
           
           unset($teacher_list, $merge_subject_list);
       }
        
       //最喜欢的课程
       $like_subjects = array();
       if(!empty($subject_list)) {
           foreach($subject_list as $uid=>$list) {
               foreach($list as $subject_id=>$subject) {
                   $like_subjects[$subject_id] = $subject['subject_name'];
               }
           }
           
           unset($subject_list);
       }
       
       return array($like_teachers, $like_subjects);
   }
   
    /**
     * 转换用户的扩展信息资料
     * @param $extends_list
     * @param $user_extends_str
     */
    protected function parseStudentExtend($extends_list, $user_extends_str) {
        if(empty($extends_list)) {
            return false;
        }
        
        $user_extends_list = !empty($user_extends_str) ? explode(',', $user_extends_str) : array();
        foreach($extends_list as $key=>$extends) {
            $extends_list[$key] = array(
                'id' => $key,
                'name' => $extends,
                'checked' => in_array($key, $user_extends_list) ? 'checked' : '',
            );
        }
        
        return $extends_list;
    }
}