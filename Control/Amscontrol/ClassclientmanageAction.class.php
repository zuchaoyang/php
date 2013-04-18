<?php
class ClassclientmanageAction extends AmsController{
    /* 展示班级成员账号列表
     * author: Luan ，date: 2011-08-17
     */
    
    protected $is_school = true;
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Pathmanagement_ams");
	    $this->assign('username', $this->user['ams_name']);
    }
    
    //检测用户是否在此学校及此班
    private function checkUser($uid,$schoolid) {
        $classUser = $this->checkLoginerInSchool($uid,$schoolid);
        if($classUser) {
            return true;
        }else {
            return false;
        }
    }
    
    //显示班级学生及家长的账号列表&&
    public function showClassClient(){
        $classCode = $this->objInput->getInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $stop_flag = $this->objInput->getInt('stop_flag');
        $stop_flag_arr = array(-1,0,2,3);
        if(empty($stop_flag) || !in_array(intval($stop_flag),(array)$stop_flag_arr))
            $stop_flag = 0;

        $stamp       = $this->objInput->getStr('stamp');            //打印标志
        $excelflag   = $this->objInput->getStr('excel');
		$loginer = $this->user['ams_account'];
		if(!(self::checkUser($loginer,$schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
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
        $this->assign('uid',$uid);
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
            $userId = $this->user['ams_account'];
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

    //批量学生姓名录入
    public function batchinputinfo(){
        $classCode = $this->objInput->getInt('cid'); //获得班级编号
         $gradeid   = $this->objInput->getInt('gradeid');
         $schoolid  = $this->user['schoolinfo']['school_id'];
         $uid       = $this->objInput->getInt('uid');

         $this->assign('classCode',$classCode);
         $this->assign('gradeid',$gradeid);
         $this->assign('schoolid',$schoolid);
         $this->assign('uid',$uid);
         $this->display('batchinputinfo');
    }


    //前往批量添加页面
     Public function goToLotsAdd() {
         $classCode = $this->objInput->getInt('cid'); //获得班级编号
         $gradeid   = $this->objInput->getInt('gradeid');
         $uid = $this->objInput->getInt('uid');
         
         $this->assign('classCode',$classCode);
         $this->assign('gradeid',$gradeid);
         $this->assign('schoolid',$this->user['schoolinfo']['school_id']);
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
         $gradeid   = $this->objInput->getInt('gradeid');
         $schoolid  = $this->user['schoolinfo']['school_id'];
         $uid       = $this->objInput->getInt('uid');
         $loginer = $this->user['ams_account'];
		 if(!(self::checkUser($loginer,$schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	     }
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
         $currentAccount=$this->user['ams_account'];
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
             
             $client_info['stu_info']=array(
                 'client_account' => $getaccount,
                 'client_firstchar' => $firstchar,
                 'add_time' => time(),
                 'upd_time' => time(),
             );
             
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
             
             $client_class['father']=array(
                 'client_account'=>$fatherAccount,
                 'class_code'=>$cid,
                 'add_time'=>time(),
             	 'upd_time'=>time(),
                 'add_account'=>$currentAccount,
                 'client_type'=>CLIENT_TYPE_FAMILY
             );
             
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
             	
             	//todolist
//             	import('@.Common_wmw.functions', null, '.php');
//             	moniter_control($this->user, __METHOD__ . ":addClientClassBat", count($client_class));
             }
             if($clientclassadds){
             	$mFamilyRelation->addFamilyRelationBat($family_relation);
             	
             	//todolist
//             	import('@.Common_wmw.functions', null, '.php');
//             	moniter_control($this->user, __METHOD__ . ":addFamilyRelationBat", count($family_relation));
             }
         }
         
         
         //===========================更新redis============================================================
         $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
	     $mSetClassFamily = ClsFactory::Create('RModel.Common.mSetClassFamily');
	     $class_student = $mSetClassStudent->getClassStudentById($cid, true);
	     $class_family = $mSetClassFamily->getClassFamilyById($cid, true);
         
         $this->redirect('/Classclientmanage/showClassClient/uid/'.$uid.'/classCode/'.$cid.'/gradeid/'.$gradeid.'/schoolid/'.$schoolid."/stop_flag/0");
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
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $stop_flag       = $this->objInput->getInt('stp');
		$loginer = $this->user['ams_account'];
        if(!(self::checkUser($loginer,$schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
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
    
    //ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }
    
    function addParentBPhone(){
        $classCode = $this->objInput->postInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->postInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->postInt('uid');
        $stop_flag = $this->objInput->getInt('stop_flag');
        if(empty($stop_flag))
            $stop_flag = 0;

		$loginer = $this->user['ams_account'];
		if(!(self::checkUser($loginer,$schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略

        $mClientClass  = ClsFactory::Create('Model.mClientClass');
        $result   = $mClientClass->getClientClassByClassCode($classCode,array('client_type'=>0));
        $classAccounts = array();
        foreach($result[$classCode] as $key=>$classClientList){  //获取班级所有会员账号，包括学生家长和老师
             if(!empty($classClientList['client_account'])) $classAccounts[] = $classClientList['client_account'];
        }
    	if(!empty($classAccounts)){
    		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
    		$familyRelations = $mFamilyRelation->getFamilyRelationByUid($classAccounts);//通过family_relation表获得学生与家长的对应关系
		}
		$mUser = ClsFactory::Create('Model.mUser');
		$p=0;
		$names = array();

        foreach ($familyRelations as $childAccount=>$parentList){
                if($p%27 == 0 && $p != 0){
                    $familyRelations[$childAccount]['print'] = 'true';
                }

                //罗列学生家长新数组，供给excel用-----[$names]
                $clientInfo = $mUser->getUserBaseByUid($childAccount);
                $names[$p][]=$clientInfo[$childAccount]['client_name'];
                $names[$p][]=$childAccount;
                foreach($parentList as $key=>$val) {
                    $names[$p][]=$val['family_account'];
                    $account_arr[] = $val['family_account'];
                }
                if(!empty($clientInfo[$childAccount]['client_name']) && $val['status'] == $stop_flag){
                    $familyRelations[$childAccount]['child_name'] = $clientInfo[$childAccount]['client_name'];//将学生姓名放入对应的关系数组中
                    $p++;
                }else{
                    unset($familyRelations[$childAccount]);
                }
            }
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
        $existPhoneList = $mBusinessphone->getbusinessphonebyalias_id($account_arr);//查询库中已经绑定的手机号
        $this->assign('familyRelations' , $familyRelations);
        $this->assign('existPhoneList' , $existPhoneList);
        $this->assign('classCode',$classCode);
        $this->assign('gradeid',$gradeid);
        $this->assign('uid',$uid);
        $this->assign('schoolid',$schoolid);
        $this->assign('operationStrategy', $operationStrategy);
        $this->display("studentBusinessPhone");
    }
    
    function bindingParentPhone(){
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $classCode = $this->objInput->postInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->postInt('gradeid');
        $uid       = $this->objInput->postInt('uid');
        $businessPhonelist = $this->objInput->postArr('businesPhone');   //用户提交的手机号列表
        $primaryPhoneList = $this->objInput->postArr('primaryPhone'); //数据库中原已绑定的手机号列表
        $clientAccountList = $this->objInput->postArr('client_account'); //账号列表
        $primary_phone_types = $this->objInput->postArr('primary_phone_types');
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
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/amscontrol/Classclientmanage/showclassClient/uid/$uid/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
                    return false;
                }
            }
        }

        $phone_del = $phone_add = $accountPhone_add = array();
        foreach($primaryPhoneList as $key => $primaryPhone) {//提出变动的手机号
            if(!empty($primaryPhone)){                 //原来已经绑定过手机
                if($primaryPhone != $businessPhonelist[$key]){
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
                $account_add[] = $clientAccountList[$key]; //涉及到新绑定的用户账号
                $accountPhone_add['START'][]=array(
                        'mphone_num'=>$businessPhonelist[$key],
    	            	'business_num'=>$clientAccountList[$key],
                    	'opening_time'=>date("Y-m-d H:i:s"),
                        'opening_time'=>date('Ymd'),
                		'wbp_log_flag'=> 1,              //log表手动标志
                        'client_account'=>$current_user,  //log表操作者账号
                        'phone_type'=>$this->objInput->postInt('phone_type_'.$clientAccountList[$key])//手机类型2012-02-27
                );
            }
        }
        $mUser = ClsFactory::Create('Model.mUser');
        $userList = $mUser->getUserBaseByUid($account_add);
        foreach($accountPhone_add['START'] as $key=> $info) {//查询用户姓名
            $client_account = $info['business_num'];
            $accountPhone_add['START'][$key]['mphone_user_name'] = $userList[$client_account]['client_name'];
        }

        if(!empty($phone_del) || !empty($phone_add) ||!empty($accountPhone_add)){
            $transactionResult = $mBusinessphone -> bindingTransaction($phone_del , $phone_add , $accountPhone_add);
            if(!$transactionResult){
                echo "<script language='javascript'> alert('更新失败！请重新尝试'); window.location.href='/amscontrol/Classclientmanage/showclassClient/uid/$uid/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
            }
        }
        echo "<script language='javascript'> alert('更新成功！');window.location.href='/amscontrol/Classclientmanage/showclassClient/uid/$uid/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
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
           
         $mHashClient = ClsFactory::Create('RModel.Common.mHashClient');
         $client_base = $mHashClient->getClientbyUid($child_account, true); 
           echo $result;
       }else{
            echo false;
       }
   }

    //移出班级成员方法
    public function remove_client(){
        $child_account = $this->objInput->getStr('uid');
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $FamilyRelation = $mFamilyRelation->getFamilyRelationByUid($child_account);
        
        $m = ClsFactory::Create('Model.mClientClass');
        $datas =  array_shift($m->getClientClassByUid($child_account));          
        
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
        
        
        //===========================更新redis============================================================

        $client_info = reset($datas);
        $class_code = $client_info['class_code'];

        $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
        
        $client_class_info =  $mHashClientClass->getClientClassbyUid($child_account);


        //更新学生redis  mHashClientClass
        $mHashClientClass->getClientClassbyUid($child_account, true);
        //更新学生家长帐号，也是 mHashClientClass
        foreach($del_uid as $uid) {
            $mHashClientClass->getClientClassbyUid($uid, true);
        }
        //更新班级成员，包括 学生和家长.
        $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
        $mSetClassFamily = ClsFactory::Create('RModel.Common.mSetClassFamily');        
       
        $mSetClassStudent->delClassStudentByMember($class_code, array($child_account));
        $mSetClassFamily->delClassFamilyByMember($class_code, $del_uid);

        echo $resault;
    }
}