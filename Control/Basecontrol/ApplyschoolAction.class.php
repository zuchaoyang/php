<?php
class ApplyschoolAction extends BmsController {
    const LENGTH=15;
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Pathmanagement_bms");

		//判断用户是否登录
		$checklogin = ClsFactory::Create('Model.mBmsAccount');
		$this->assign('baseinfo',$this->user);
	}
	
	public function showSchoolApp(){
	     $this->assign('schooltype',Constancearr::school_type());
	     $this->assign('gradetype',Constancearr::grade_type());
	     $this->assign('resources',Constancearr::school_resource_advantage());
	     $this->display('showSchoolApp');
    }
    //上传附件
    public function uploadFile(){
		if ( isset( $_FILES['scanFile']['name'] ) && $_FILES['scanFile']['name'] != "" )
		{
			
			$up_init = array(
					'attachmentspath' => Pathmanagement_bms::uploadSchoolScan(),
					'renamed' => true,
					'allow_type' => array('jpg','gif','png','bmp')
			);
			
			$uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
            $uploadObj->_set_options($up_init);
            $up_rs = $uploadObj->upfile('scanFile');
            
			$data['schoolscan_url'] = '/'.str_replace(WEB_ROOT_DIR . '/', '', $up_rs['getfilename']);
		    return $data['schoolscan_url'];
		}
    }
    
    public function checkNewUrl(){
        $newUrl = $this->objInput->postStr('newWebUrl');
        if(!strpos($newUrl,'.wmw.cn')){
            $newUrl.=".wmw.cn";
        }
        $is_available = 1;
        
        $mSchool = ClsFactory::Create('Model.mSchoolInfo');
        $school_info_list = $mSchool->checkUrl($newUrl, $limit=1);
        if(!empty($school_info_list)) {
            $is_available = -1;
        } else {
            $mOldSchoolUrl = ClsFactory::Create('Model.mOldSchoolUrl');
        	$old_school_list = $mOldSchoolUrl->checkOldSchoolUrlForUrlIsExist($newUrl);
            if(!empty($old_school_list)) {
                $is_available = -1;
            }
        }
        
        $message = $is_available ?  '网址已存在' : '网址可用';
        $json = array(
              'result'=>array(
                      'code'=>$is_available,
                      'message' => $message 
              ),
              'data'=>''
        );
        echo json_encode($json);
    }

    //学校申请表入库前在后天的验证。成功后跳到成功反馈页面
    public function addSchoolInfo(){
       $typeErr = 0;
       $school_scan = $this->uploadFile();
       
       if(!$school_scan){
       	
           $typeErr = -1;
       }else{
            //将用户输入信息组建成信息数组：
            $data = array(
                //学校基本资料
               	'school_name'        => $this->objInput->postStr('schoolName'),
                'area_id'        	 => $this->objInput->postStr('area_id'),//省市区
                'school_address'     => $this->objInput->postStr('schoolAddress_Content'),

                'post_code'      	 => $this->objInput->postStr('zipCode'),//邮编有的是以0开头，故用str接收。
                'school_create_date' => substr($this->objInput->postStr('createSchoolDate'),0,7).'-00',
                'school_type'    	 => $this->objInput->postInt('schoolType'),
                'grade_type'    	 => $this->objInput->postInt('gradeType'),
                'resource_advantage' => $this->objInput->postInt('schoolGrade'),
                'school_master'      => $this->objInput->postStr('principal'),
                'contact_person'     => $this->objInput->postStr('contact'),

                //师生情况
                'class_num'          => $this->objInput->postInt('classNum'),
                'teacher_num'        => $this->objInput->postInt('teachNum'),
                'student_num'        => $this->objInput->postInt('studentNum'),

                //学校网络负责人
                'net_manager'        => $this->objInput->postStr('personInCharge'),
                'net_manager_phone'  => $this->objInput->postStr('PICcontact'),
                'net_manager_email'  => $this->objInput->postStr('setMail'),

                //校园门户网站申请
               	'school_url_old'     => $this->objInput->postStr('oldWebUrl'),
        		'school_url_new'     => $this->objInput->postStr('newWebUrl').'.wmw.cn',

                //《教育信息化公共服务平台申请表》扫描件
                'school_scan'        => $school_scan,   
                'add_date'           => date("Y-m-d H:i:s"),
            	'upd_date'           => date("Y-m-d H:i:s"),
                'add_account'        => $this->user['base_account'],
                'add_time'           => time()
            );
            if(!empty($data)){//数据验证：
                $sn_length = WmwString::mbstrlen($data['school_name']);
                if(empty($data['school_name'])||$sn_length<2||$sn_length>20){
                   echo "校名不能为空，且长度应在2-20之间";
                   return false;
                }
                //街道地址长度
                $sm_length = WmwString::mbstrlen($data['school_address']);
                if($sm_length<2||$sm_length>50){
                   echo "街道地址长度应在2-30之间";
                   return false;
                }
                // 邮编验证 六位数字
                $preg_postcode = '/\d{6}/';
                if(preg_match($preg_postcode,$data['post_code'])==false){
                    echo "邮编格式不正确";
                    return false;
                }

                //校长名称长度
                $sm_length = WmwString::mbstrlen($data['school_master']);
                if($sm_length<2||$sm_length>50){
                   echo "校长名称不能为空，且长度应在2-30之间";
                    return false;
                }
                //学校电话验证
                /*$preg_phone = '/(^(0[0-9]{2,3})?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$)|(^((\(\d{3}\))|(\d{3}\-))?(1[358]\d{9})$)/';
                if((preg_match($preg_phone,$data['contact_person'])==false)){
                    echo "学校电话不则格式不正确";
                    return false;
                }*/
                $sm_length = WmwString::mbstrlen($data['contact_person']);
                if($sm_length<8||$sm_length>11){
                    echo "电话格式不正确";
                    return false;
                }
                //验证纯数字输入
                $preg_num = '/\d/';
                if((preg_match($preg_num,$data['class_num'])==false)||(preg_match($preg_num,$data['teacher_num'])==false)||(preg_match($preg_num,$data['student_num'])==false)){
                    echo "师生情况请填入数字";
                    return false;
                }
                //网络负责人名称长度
                $nm_length = WmwString::mbstrlen($data['net_manager']);
                if($nm_length<2||$nm_length>30){
                   echo "网络负责人名称长度应在2-30之间";
                   return false;
                }
                //网络负责人电话验证
                /* if((preg_match($preg_phone,$data['net_manager_phone'])==false)){
                    echo "网络负责人电话格式不正确";
                    return false;
                }*/
                $sm_length = WmwString::mbstrlen($data['net_manager_phone']);
                if($sm_length<8||$sm_length>11){
                    echo "电话格式不正确";
                    return false;
                }
                //验证邮箱格式：
                //$preg_email =  "/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/";
                $preg_email = "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/";
                if( preg_match($preg_email,$data['net_manager_email'])==false){
                    echo "网络负责人邮箱格式不正确";
                    return false;
                }
                //验证学校原网址
                //$preg_url = "/^(http:\/\/){0,1}w{3}\.[\w-]+\.(com|net|org|gov|cc|biz|info|cn)(\.(cn|hk))*$/";
                $preg_url = "/^(http(s)?:\/\/)?([\w]+\.)+[\w]+([\w-.\/?%&=]*)?$/";
                //^(http[s]?:\\/\\/)?([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$
                if(empty($data['school_url_old'])){
                    $data['school_url_old']='www.wmw.cn';
                }elseif(preg_match($preg_url,$data['school_url_old']) == false){
                    echo "原校园网址格式不正确";
                    return false;
                }

                //新网址申请验证，未填写时跳过
                if($data['school_url_new']=='.wmw.cn'){
                    $data['school_url_new']='无';
                 }else{
                  //$newurl_preg  = '/^(http:\/\/){0,1}[w{3}]\.[\w-]*.*(\.wmw\.(cn))*$/';
                    $newurl_preg = "/^([\w-]+\\.)+[\w-]+([\w-.\/?%&=]*)?$/";
                    if(preg_match($newurl_preg,$data['school_url_new']) == false){
                    echo "申请的新网址格式不正确";
                    return false;
                 }
                }
            }//end if($data!='')

            //插入school_info
            $mSchool = ClsFactory::Create('Model.mSchoolInfo');
            //schoolid 需要返回插入数据的id
            $schoolid = $mSchool->addSchoolInfo($data, true);
            $school_flag = '失败，请重新申请';
            if(!$schoolid) $typeErr = -2;
            if($schoolid){
            	//插入school_request表
    	        $mSchoolRequest = ClsFactory::Create('Model.mSchoolRequest');
    	        $rData = array('school_id'=>$schoolid,'add_account'=>$data['add_account'],'add_time'=>$data['add_time']);
    	        $add_result = $mSchoolRequest->addSchoolRequest($rData);
    	        if($mSchoolRequest){
    	        	$this->assign('school_name' , $data['school_name']);
    	        	$school_flag = '成功，请等待审批';
    	       	}
            }
        }
        $this->assign('typeErr',$typeErr);
        $this->assign('school_flag' , $school_flag);
        $this->display('addSchoolBackInfo');
    }

     public function schoolinfolist(){
        $this->display('schoolinfolist');
    }

    //通过现在登录的用户查出他所录入的学校信息
	function getSchool(){
	    $mSchool = ClsFactory::Create( 'Model.mSchoolInfo' );
	    $length = self::LENGTH;
	    $curpage = '';
	    $schoolname = $this->objInput->postStr('schoolname');
	    $page = $this->objInput->getInt('page');
	    $page = max($page,1);
	    $prepage = $page-1;
	    $prepage = max($prepage,1);
	    $offset = ($page-1)*$length;
	    $schoolInfo = $mSchool->getAllSchoolInfo($offset, $length + 1, $this->user['base_account'], array(0,1,2), $schoolname );
	    if (count($schoolInfo) <= $length){
	    	$endpage = $page;
	        $curpage = 'end';
	    }else{
	    	$endpage = $page+1;
	    	array_pop($schoolInfo);
	    }
	    
	    foreach ($schoolInfo as $key=>$val){
            $schoolIds[] = $val['school_id'];
	    }

	    $schoolInf = $mSchool->getSchoolInfoById($schoolIds);
	    if(!empty($schoolname)){
	    	$this->assign('schoolname', $schoolname);
	    }
	    foreach ($schoolIds as $id=>$schoolid) {
	    	if($schoolInf[$schoolid]){
	    		$schoolInf[$schoolid]=$schoolInf[$schoolid];
	    	}
	    }
	    if(empty($schoolInf)){
	    	$schoolInf='';
	    }

	    $this->assign('curpage',$curpage);
	    $this->assign('schoolInfo',$schoolInf);
	    $this->assign('page',$page);
	    $this->assign('prepage',$prepage);
	    $this->assign('endpage',$endpage);
	    $this->display('appschoolmanage1');
	}

	//显示没有通过审核的原因
	function showRefuseReason(){
	    $mSchool = ClsFactory::Create('Model.mSchoolInfo');
	    $schoolInfo = $mSchool->getSchoolInfoById($this->objInput->getInt('sid'));
	    if(!empty($schoolInfo[$this->objInput->getInt('sid')]['refuse_reason'])){
	        $refusreason = array('error'=>array('code'=>1,'message'=>'系统繁忙'),'data'=>array('refusereason'=>$schoolInfo[$this->objInput->getInt('sid')]['refuse_reason']));
	    }else{
	        $refusreason = array('error'=>array('code'=>1,'message'=>'系统繁忙'));
	    }
	    echo json_encode($refusreason);
	}

	//显示学校的扫描件
	function showScanningCopy(){
	    $mSchool = ClsFactory::Create('Model.mSchoolInfo');
	    $sid = $this->objInput->getInt('sid');
	    $schoolInfo = $mSchool->getSchoolInfoById($sid);
	    $imgsrc = $schoolInfo[$sid]['school_scan'];
		if(!file_exists(WEB_ROOT_DIR.$imgsrc)){
	    	echo "没有找到附件！";
	    	return false;
	     }
	    $this->assign('imgsrc',$imgsrc);
	    $this->display('appshowscanningcopy');
	}


	//显示学校的详细信息
	function getSchoolInfo(){
	     $mSchool = ClsFactory::Create('Model.mSchoolInfo');
	     $sid = $this->objInput->getInt('sid');
	     $schoolInfo = $mSchool->getSchoolInfoById($sid);
	     $info = array(
	     				'school_name'=>'',
	     				'school_address'=>'',
	     				'post_code'=>'',
	     				'school_create_date'=>'',
	     				'school_type'=>'',
	     				'resource_advantage'=>'',
	                    'school_master'=>'',
                	    'contact_person'=>'',
                	    'class_num'=>'',
                	    'teacher_num'=>'',
                	    'student_num'=>'',
                	    'net_manager'=>'',
                	    'net_manager_phone'=>'',
                	    'net_manager_email'=>'',
                	    'school_url_old'=>'',
                	    'school_url_new'=>'',
	     );

         $addressList = getAreaNameList($schoolInfo[$sid]['area_id']);

         $info['school_id']=$schoolInfo[$sid]['school_id'];
         $info['school_name']=$schoolInfo[$sid]['school_name'];
         $info['school_address']=$addressList['province'].$addressList['city'].$addressList['county'].$schoolInfo[$sid]['school_address'];
         $info['post_code']=$schoolInfo[$sid]['post_code'];
         $info['school_create_date']=$schoolInfo[$sid]['school_create_date'];
		 
         $info['school_type']=Constancearr::school_type($schoolInfo[$sid]['school_type']);
         $info['grade_type']=Constancearr::grade_type($schoolInfo[$sid]['grade_type']);
         $info['resource_advantage']=Constancearr::school_resource_advantage($schoolInfo[$sid]['resource_advantage']);

         $info['school_master']=$schoolInfo[$sid]['school_master'];
         $info['contact_person']=$schoolInfo[$sid]['contact_person'];
         $info['class_num']=$schoolInfo[$sid]['class_num'];
         $info['teacher_num']=$schoolInfo[$sid]['teacher_num'];
         $info['student_num']=$schoolInfo[$sid]['student_num'];
         $info['net_manager']=$schoolInfo[$sid]['net_manager'];
         $info['net_manager_phone']=$schoolInfo[$sid]['net_manager_phone'];
         $info['net_manager_email']=$schoolInfo[$sid]['net_manager_email'];
         $info['school_url_old']=$schoolInfo[$sid]['school_url_old'];
         $info['school_url_new']=$schoolInfo[$sid]['school_url_new'];
         $info['add_date']=$schoolInfo[$sid]['add_date'];
		 if(!file_exists(WEB_ROOT_DIR.$schoolInfo[$sid]['school_scan'])){
	    	$info['school_scan'] = 'no';
	     }else{
	     	$info['school_scan']=$schoolInfo[$sid]['school_scan'];
	     }
		 if($schoolInfo[$sid]['school_status'] != 1){
		 	$this->assign('flag','no');
		 }
    	 //处理学校的建校日期的显示问题
         if(!empty($info['school_create_date'])) {
             $school_create_date = $info['school_create_date'];
             list($year , $month , $day) = explode("-" , $school_create_date);
             $info['school_create_date'] = "$year-$month";
         }
		 $this->assign('schoolInfo',$info);
		 
		 $this->display('schoolinfo');
	}
	
	function resetPwd(){
		$mSchool = ClsFactory::Create('Model.mSchoolInfo');
	    $sid = $this->objInput->getInt('sid');
	    $schoolInfo = $mSchool->getSchoolInfoById($sid);
	    $this->assign('schoolInfo',$schoolInfo[$sid]);
	    
		$this->display('resetpwd');
	}
	
	//修改密码
	function changePwd(){
	    $net_manager_account = $this->objInput->getStr('net_manager_account');
		$newPwd = rand(100000,999999);
	    $uPwd = array(
	        'ams_password' => md5($newPwd),
	    );
	    $mSchool = ClsFactory::Create('Model.mSchoolInfo');
	    $mAmsAccount = ClsFactory::Create("Model.mAmsAccount");
	    $schoolInfo = $mSchool->getSchoolInfoById($this->objInput->getInt('sid'));
	    $email = $schoolInfo[$this->objInput->getInt('sid')]['net_manager_email'];
	    $schoolName = $schoolInfo[$this->objInput->getInt('sid')]['school_name'];
	    $res = $mAmsAccount->modifyAmsAccount($uPwd,$net_manager_account);

	    if ($res) {

	        $emailContent = '您好，'.$schoolName.'的校园管理员账号('.$net_manager_account.')的密码已重置,';
	        $emailContent .= '新密码是：'.$newPwd.'如有问题，请拨打客服电话：'.WMW_CS_PHONE;

	        //查询申请该学校的基地账号
	        $schoolid = key($schoolInfo);
            $mSchoolRequest = ClsFactory::Create('Model.mSchoolRequest');
            $schoolRequestinfo = $mSchoolRequest->getSchoolRequestBySchool_id($schoolid);
            $schoolRequestinfo = reset($schoolRequestinfo[$schoolid]);
            $add_account = $schoolRequestinfo['add_account'];
            
            //通过账号查询该基地的邮箱
            $mBmsAccount = ClsFactory::Create('Model.mBmsAccount');
            $base_account_info = $mBmsAccount->getUserInfoByUid($add_account);
            $base_account_info = array_shift($base_account_info);
            $base_email = $base_account_info['base_email'];

            $emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
            
	        if(($base_email == $email) || ($base_email == "")){
	            $send = $emailObj->send($email,$emailContent);
	        }else{
	            $send = $emailObj->send($email,$emailContent);
	            $base_send = $emailObj->send($base_email,$emailContent);//同时向基地发送重置后的邮件
	        }

	        if ($send) {
	            $this->assign('schoolName',$schoolName);
	            $this->assign('email',$email);
	            $this->display('appcommit');
	        }
	    }
	}
	
	function phonebinding(){
	    $sid = $this->objInput->getInt('sid');
	    if(empty($sid)){
	    	echo '系统错误，请重新操作！';
	    	die;
	    }
	    $this->getgradeclasslist($sid);
	    
		$this->display('phonebinding');
	}
	
	function getgradeclasslist($sid){
		$mSchool = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchool->getSchoolInfoById($sid);
		$schoolInfo = $schoolInfo[$sid];
		$schooltype = $schoolInfo['school_type'];
		$gradetype = $schoolInfo['grade_type'];
	    $slice_gradelist = $this->gradelists($schooltype, $gradetype);
	    
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    $classlist = $mClassInfo->getClassInfoBySchoolId($sid);
	    $this->assign('slice_gradelist',$slice_gradelist);
	    $this->assign('schoolInfo',$schoolInfo);
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
	
	function geclasslist($classCode,$grade_id,$sid,$true){
		if(empty($grade_id) || empty($sid)){
			$grade_id = $this->objInput->postInt('gradeid');
			if(empty($grade_id)){
				$grade_id = $this->objInput->postStr('gradeid');
			}
			$sid = $this->objInput->postInt('sid');
		}
		$classlist_str = '';
		if($grade_id === 'qxz'){
			$classlist_str.="<option value=''>请选择班级</option>";
		}else{
			$flag = true;
			$mClassInfo = ClsFactory::Create('Model.mClassInfo');
			$classlist = $mClassInfo->getClassInfoBySchoolId($sid);
			
			foreach($classlist[$sid] as $key=>&$val){
				if($val['grade_id']!=$grade_id){
					unset($classlist[$sid][$key]);
				}else{
					$flag = false;
					$classlist_str .= "<option value='{$val['class_code']}'";
					if($classCode == $val['class_code']){$classlist_str .= 'selected';}
					$classlist_str .=">{$val['class_name']}</option>";
				}
			}
			if($flag){
				$classlist_str .= "<option value=''>无班级</option>";
			}
			unset($classlist);
		}
		if($true){
			return $classlist_str;
		}else{
			echo $classlist_str;
		}
		
	}
	//老师手机号绑定
	function addTeacherBPhone(){
       $schoolid = $this->objInput->getInt("sid");
	   if(empty($schoolid)){
           $schoolid = $this->objInput->getInt("schoolid"); 
       }
       $page = $this->objInput->getInt("page");
       
       $page = max($page, 1);
       $limit = 100;
       $offset = ($page-1)*$limit;
       $pre = $page-1;
       $pagediv = "<font style='margin:0 10px 0 10px;' width='100px'>当前第{$page}页</font>";
       if($pre>=1) {
           $pagediv .= "<span><a href='/Basecontrol/Applyschool/addTeacherBPhone/sid/{$schoolid}/page/{$pre}'>上一页</a></span>&nbsp;&nbsp;";
       }else{
           $pagediv .= "<span>上一页</span>&nbsp;&nbsp;";
       }
       
       $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	   $schoolTeacherInfo = $mSchoolTeacher->getSchoolTeacherInfoBySchoolIdPage($schoolid, $offset, $limit+1); //该学校老师账号列表
	   
	   if(count($schoolTeacherInfo)>$limit){
	       $end = $page+1;
	       $pagediv .= "<span><a href='/Basecontrol/Applyschool/addTeacherBPhone/sid/{$schoolid}/page/{$end}'>下一页</a></span>";
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
       $this->display("lssjhbd");
    }
    
    //老师绑定处理
	function bindingTeacherPhone(){
        $schoolid = $this->objInput->postInt("schoolid"); 
        if(empty($schoolid)){
        	$schoolid = $this->objInput->getInt("schoolid"); 
        }
        $businessPhonelist = $this->objInput->postArr('businesPhone');   //用户提交的手机号列表
        $primaryPhoneList = $this->objInput->postArr('primaryPhone'); //数据库中原已绑定的手机号列表 
        $clientAccountList = $this->objInput->postArr('client_account'); //账号列表
        $primary_phone_types = $this->objInput->postArr('primary_phone_types');
        $page = $this->objInput->postInt('page');
        
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone'); 
        
        //运营策略数组
        $mSchool = ClsFactory::Create('Model.mSchoolInfo');
        $schoolInf = $mSchool->getSchoolInfoById($schoolid);
		$schoolInf = reset($schoolInf);  //只取一个 也只能是一个
	    $operatenum = $schoolInf['operation_strategy'];
	    unset($schoolInf);

	    
        //检测手机号是否合法  运营策略   没有运营策略的都不合法不可绑定
		if (intval($operatenum) <= 1) {
			$alertStr = "该学校不支持手机号绑定！";
            echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";
            return false;
		} 
		
		//得到检测后的手机号array('sucess'=>array(),'error'=>array())
		$check_array = $mBusinessphone->checkPhoneNumByOperator($operatenum,$businessPhonelist);
        foreach($businessPhonelist as $key => $phone){ 
            if(!empty($phone)){
                $repeatkeyArr = array_keys($businessPhonelist , $phone);
                if(!empty($check_array['error'][$phone])){
                	
                	//检测表单中是否有非法手机号
                	$alertStr .= "更新失败！账号";
                	foreach($repeatkeyArr as $key_1 => $val) {
                        $alertStr .= "【{$clientAccountList[$val]}】，";
                    }
                    
                    $alertStr .="对应的机号（{$phone}）是非法手机号！";
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addParentBPhone/schoolid/$schoolid//page/{$page}/stop_flag/0';</script>";
                    
                    return false;
                    
                } elseif (count($repeatkeyArr)>1){
                	
                	//检测表单中是否有重复的手机号
                    $alertStr .= "更新失败！账号";
                    foreach($repeatkeyArr as $key_1 => $val){
                        $alertStr .= "【{$clientAccountList[$val]}】，";
                    }
                    
                    $alertStr .="对应的机号（{$phone}）相同！";
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";
                    
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
                         
						 // todo 更新手机号类型有错误
                         $mBusinessphone->modifyPhoneInfo($update_data, $primaryPhoneList[$key]);              
                    }
                }
            } 
            if((!empty($businessPhonelist[$key]))&&($businessPhonelist[$key] != $primaryPhoneList[$key])){ //变动的手机及新添加的手机
                $current_user = $this->user['base_account'];
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
            $transactionResult = $mBusinessphone->bindingTransaction($phone_del , $phone_add , $accountPhone_add);
            if(!$transactionResult){
                echo "<script language='javascript'> alert('更新失败！请重新尝试'); window.location.href='/Basecontrol/Applyschool/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>"; 
            }
        }
        echo "<script language='javascript'> alert('更新成功！'); window.location.href='/Basecontrol/Applyschool/addTeacherBPhone/schoolid/$schoolid/page/{$page}';</script>";    
    }
    //学生家长手机号绑定
	function addParentBPhone(){
		
        $classCode = $this->objInput->getInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->objInput->getInt('schoolid');
        $stop_flag = $this->objInput->getInt('stop_flag');
        $classlist = $this->geclasslist($classCode,$gradeid,$schoolid,true);
        if(empty($schoolid)){
        	echo '系统出错，请重新操作！';
        	die;
        }
        $this->getgradeclasslist($schoolid);
        if(empty($stop_flag))
            $stop_flag = 0;
        $mClientClass  = ClsFactory::Create('Model.mClientClass');
        $result   = $mClientClass->getClientClassByClassCode($classCode,array('client_type'=>0));
        $classAccounts = array();
        foreach($result[$classCode] as $key=>$classClientList){  //获取班级所有会员账号，包括学生家长和老师
             if(!empty($classClientList['client_account'])) {
             	$classAccounts[] = $classClientList['client_account'];
             }
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
                    $names[$p][] = $key;
                    $account_arr[] = $val["family_account"];
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
        $this->assign('classlist',$classlist);
        $this->display("phonebinding");
    }
    
	//学生手机号绑定处理action
	function bindingParentPhone(){
        $schoolid  = $this->objInput->postInt('schoolid');
        $classCode = $this->objInput->postInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->postInt('gradeid');
        $businessPhonelist = $this->objInput->postArr('businesPhone');   //用户提交的手机号列表
        $primaryPhoneList = $this->objInput->postArr('primaryPhone'); //数据库中原已绑定的手机号列表
        $clientAccountList = $this->objInput->postArr('client_account'); //账号列表
        
        $primary_phone_types = $this->objInput->postArr('primary_phone_types');
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');

	    //运营策略
        $mSchool = ClsFactory::Create('Model.mSchoolInfo');
        $schoolInf = $mSchool->getSchoolInfoById($schoolid);
		$schoolInf = reset($schoolInf);  //只取一个 也只能是一个
	    $operatenum = $schoolInf['operation_strategy'];
	    unset($schoolInf);

        //检测手机号是否合法  运营策略   没有运营策略的都不合法不可绑定
		if (intval($operatenum) <= 1) {
			$alertStr = "该学校不支持手机号绑定！";
            echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addTeacherBPhone/schoolid/$schoolid';</script>";
            return false;
		}
 
		//得到检测后的手机号array('sucess'=>array(),'error'=>array())
		$check_array = $mBusinessphone->checkPhoneNumByOperator($operatenum,$businessPhonelist);
        foreach($businessPhonelist as $key => $phone){ //检测表单中是否有重复的手机号
            if(!empty($phone)){
                $repeatkeyArr = array_keys($businessPhonelist , $phone);
                if(!empty($check_array['error'][$phone])){
                	$alertStr .= "更新失败！账号";
                   // echo "更新失败！账号";
                    foreach($repeatkeyArr as $key_1 => $val){
                        $alertStr .= "【{$clientAccountList[$val]}】，";
                    }
                    $alertStr .="对应的机号（{$phone}）是非法手机号！";
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addParentBPhone/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
                    return false;
                }elseif(count($repeatkeyArr)>1){
                    $alertStr .= "更新失败！账号";
                   // echo "更新失败！账号";
                    foreach($repeatkeyArr as $key_1 => $val){
                        $alertStr .= "【{$clientAccountList[$val]}】，";
                    }
                    $alertStr .="对应的机号（{$phone}）相同！";
                    echo "<script language='javascript'> alert('{$alertStr}'); window.location.href='/Basecontrol/Applyschool/addParentBPhone/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
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
                $current_user = $this->user['base_account'];
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
                echo "<script language='javascript'> alert('更新失败！请重新尝试'); window.location.href='/Basecontrol/Applyschool/addParentBPhone/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
            }
        }
        echo "<script language='javascript'> alert('更新成功！');window.location.href='/Basecontrol/Applyschool/addParentBPhone/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0';</script>";
    }
	function schooltj(){
		$mCount = ClsFactory::Create('Model.mCount');
	    $sid = $this->objInput->getInt('sid');
	    $schoolTj = $mCount->getCountBySchoolId($sid);
	    
	    $this->assign('people', $schoolTj['people']);
	    $this->assign('phone', $schoolTj['phone']);
	    
	    $this->assign('sid', $sid);
	    
		$this->display('schooltj');
	}
	
	//将班级数据以excel保存
	public function echoxml(){
        $schoolid=$this->objInput->getInt('sid');//获得该学校的ID
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');  
		$result1 = $mSchoolInfo->getSchoolInfoById($schoolid);
		
	 	$mClassInfo = ClsFactory::Create('Model.mClassInfo');
	 	$mUser = ClsFactory::Create('Model.mUser');
		$result = $mClassInfo->getClassInfoBySchoolId($schoolid);
		$classList = array_shift($result);//将数组降维，获得二维班级列表
	    foreach ($classList as $classCode=>$classInfo){
	        $classCodes[] =  $classCode;
	        $classinfoarr[$classCode]=$classInfo['class_name'];
	    } 
	 	$mClientClass  = ClsFactory::Create('Model.mClientClass');
	 	unset($result);
        $result   = $mClientClass->getClientClassByClassCode($classCodes,array('client_type'=>CLIENT_TYPE_STUDENT));
        $classAccounts = array();
        foreach($result as $key=>& $classClientList){  //获取班级所有会员账号，包括学生家长和老师
        	foreach($classClientList as $classkey=>& $classlistinfo){
        		if(!empty($classlistinfo['client_account'])&&$key==$classlistinfo['class_code']&&in_array($classlistinfo['client_type'],array(CLIENT_TYPE_STUDENT,CLIENT_TYPE_FAMILY))){
        			$classAccounts[$key][] = $classlistinfo['client_account'];
        		}else{
        			unset($classClientList[$classkey]);
        		}
        	}
        }
    	if(!empty($classAccounts)){
    		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
    		foreach($classAccounts as $classcodekey1=>& $classuseraccounts1){
    			$tmp_familyRelations[$classcodekey1] = $mFamilyRelation->getFamilyRelationByUid($classuseraccounts1);//通过family_relation表获得学生与家长的对应关系
    		}
    		
    		if(!empty($tmp_familyRelations)) {
    		    $familyRelations = array();
    		    foreach($tmp_familyRelations as $classcodekey1 => $info_arr) {
    		        foreach($info_arr as $uid => $info_list) {
    		            foreach($info_list as $info) {
    		                $familyRelations[$classcodekey1][$uid][$info['family_account']] = $info;
    		            }
    		        }
    		    }
    		}
    		
    		foreach($familyRelations as $classcodekey2=>&$classuseraccounts2){
    			foreach($classuseraccounts2 as $stukey=>&$familyinfo1){
    				foreach($familyinfo1 as $familykey1=>&$familyval1){
    					$classAccounts[$classcodekey2][] = $familyval1['family_account'];
    				}
    			}
    		}
    		$mBusinessphone  = ClsFactory::Create('Model.mBusinessphone');
    		foreach($classAccounts as $classcodekey=>& $classuseraccounts){
    			$clientInfo[$classcodekey] = $mUser->getUserBaseByUid($classuseraccounts);//选出会员信息，以获取姓名
    			$phone= $mBusinessphone->getbusinessphonebyalias_id($classuseraccounts);
    			if($phone){
    				$phones[$classcodekey]= $phone;
    			}
    			unset($phone);
    		}
		}
		$new_info_array = array();

		$famili_relation_keys = array_keys(Constancearr::family_relationtype());
		foreach($familyRelations as $codekey=>& $familyinfos){
			$familyinfo = array();
			foreach($familyinfos as $client_key=>& $clientval){
				if($clientInfo[$codekey][$client_key]['client_name'] == ''){
					unset($clientInfo[$codekey][$client_key],$familyinfos[$client_key]);
					continue;
				}
				$familyinfo[$client_key]['classname']=$classinfoarr[$codekey];
				$familyinfo[$client_key]['client_name']=$clientInfo[$codekey][$client_key]['client_name'];
				$familyinfo[$client_key]['client_account']=$client_key;
				$i = 1;
				foreach($clientval as $familykey=>& $familyval){
					if(in_array(intval($familyval['family_type']),$famili_relation_keys)){
						if($i == 1 && !empty($clientInfo[$codekey][$familykey]['client_account'])){
							$familyinfo[$client_key]['mother_name']="{$clientInfo[$codekey][$familykey]['client_name']}";
							$familyinfo[$client_key]['mother_account']="{$clientInfo[$codekey][$familykey]['client_account']}";
							if($phones[$codekey][$familykey]['phone_type']==2){
								$familyinfo[$client_key]['mother_new_phone']="{$phones[$codekey][$familykey]['phone_id']}";
								$familyinfo[$client_key]['mother_old_phone']='';
							}elseif($phones[$codekey][$familykey]['phone_type']==1){
								$familyinfo[$client_key]['mother_new_phone']='';
								$familyinfo[$client_key]['mother_old_phone']="{$phones[$codekey][$familykey]['phone_id']}";
							}else{
								$familyinfo[$client_key]['mother_new_phone']='';
								$familyinfo[$client_key]['mother_old_phone']='';
							}
							$i++;
						}elseif(!empty($clientInfo[$codekey][$familykey]['client_account'])){
							$familyinfo[$client_key]['father_name']="{$clientInfo[$codekey][$familykey]['client_name']}";
							$familyinfo[$client_key]['father_account']="{$clientInfo[$codekey][$familykey]['client_account']}";
							if($phones[$codekey][$familykey]['phone_type']==2){
								$familyinfo[$client_key]['father_new_phone']="{$phones[$codekey][$familykey]['phone_id']}";
								$familyinfo[$client_key]['father_old_phone']='';
							}elseif($phones[$codekey][$familykey]['phone_type']==1){
								$familyinfo[$client_key]['father_new_phone']='';
								$familyinfo[$client_key]['father_old_phone']="{$phones[$codekey][$familykey]['phone_id']}";
							}else{
								$familyinfo[$client_key]['father_new_phone']='';
								$familyinfo[$client_key]['father_old_phone']='';
							}
							$i=1;
						}
					}else{
						$familyinfo[$client_key]['mother_name']='';
						$familyinfo[$client_key]['mother_account']='';
						$familyinfo[$client_key]['mother_new_phone']='';
						$familyinfo[$client_key]['mother_old_phone']='';
						$familyinfo[$client_key]['father_name']='';
						$familyinfo[$client_key]['father_account']='';
						$familyinfo[$client_key]['father_new_phone']='';
						$familyinfo[$client_key]['father_old_phone']='';
					}
					
				}
			}
			$new_info_array[$codekey] = $familyinfo;
			unset($familyinfo);
		}
		
		$header = array("班级","学生姓名","学生账号","家长姓名1","家长账号1","新办手机号1","老用户手机号1","家长姓名2","家长账号2","新办手机号2","老用户手机号2");
	    $index = 1;
        $new_student_list[$index++] =$header;
        
		foreach($new_info_array as $key=>$val) {
	   		foreach($val as $key1=>$val1){
	   			$i=0;
	   			foreach($val1 as $val2){
	   				$val3[$i++] = $val2;
	   			}
	   			$new_student_list[$index++] = $val3;
	   		}
	   		unset($new_info_array[$key]);
	   	}
		unset($new_info_array);
        $excel_datas[0] = array(
            'title' => '学校学生账号信息',
            'cols' => 11,
            'rows' => count($new_student_list),
            'datas' => $new_student_list,
        );
        $excel_pre = "import_student_list_" . date('Ymd', time()) . "_"; 
        $pFileName = Pathmanagement_bms::uploadExcel() . uniqid($excel_pre) . ".xls";
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        $HandlePHPExcel->saveToExcelFile($excel_datas, $pFileName);
        unset($excel_datas);
        $HandlePHPExcel->export($pFileName,$result1[$schoolid]['school_name']);
	}

}
