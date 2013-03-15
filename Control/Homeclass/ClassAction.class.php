<?php

class ClassAction extends SnsController{

	public function _initialize() {
	    parent::_initialize(); 
	    import("@.Common_wmw.Pathmanagement_sns");
	    import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Date");
		
		$this->assign('chanelid',"chanel1");
	}

	//根据账号获取老师信息
	function getclientmessage(){
		$account = $this->objInput->getInt('account');
		$accounttype = $this->objInput->getStr('type');
		$mUser = ClsFactory::Create('Model.mUser');	 
		$client_typeUser = $this->user['client_type'];
		$uid = $this->getCookieAccount();
		$login_info = $mUser->getUserByUid($account);
		
		$mFamilyRelation= ClsFactory::Create('Model.mAccountrelation');
		$tmp_RSmRelation = $mFamilyRelation->getAccountRelationByAddAccount($uid);
		$RSmRelation = $tmp_RSmRelation[$uid];
		foreach($RSmRelation as $key=>$val){
			if($val['friend_account'] == $account){
				$havedis = 1;
			}
		}
		
		$class_code = key($login_info[$account]['class_info']);
		$school_id = key($login_info[$account]['school_info']);
		$school_name = $login_info[$account]['school_info'][$school_id]['school_name'];
		$class_name = $login_info[$account]['class_info'][$class_code]['class_name'];
		if($accounttype=="teacher"){
		$varOutStr = '<table width="260" border="0" cellpadding="3" cellspacing="3" height="120" align="center">';
		$varOutStr  = $varOutStr.'<tr>';
		$varOutStr  = $varOutStr."<td width='140' rowspan='3' align='center'><Img src='".Pathmanagement_sns::getHeadImg($account) . $login_info[$account]['client_headimg'] . "' onerror=this.src='".IMG_SERVER."/Public/images/head_pics.jpg' width='80' height=80></td>";
		$varOutStr  = $varOutStr.'<td>姓名：'.$login_info[$account]['client_name'].'</td>';
		$varOutStr  = $varOutStr.'</tr>';
		$varOutStr  = $varOutStr.'<tr><td>区域：'.$login_info[$account]['school_info'][$school_id]['area_id_name'].'</td></tr>';
		$varOutStr  = $varOutStr.'<tr><td>星座：'.$login_info[$account]['client_constellation_name'].'</td></tr>';
        
		$varOutStr  = $varOutStr.'<tr><td>&nbsp;&nbsp;教龄：'.$login_info[$account]['teach_time'].'</td><td>&nbsp;</td></tr>';
		$varOutStr  = $varOutStr.'<tr><td>&nbsp;&nbsp;职称：'.Constancearr::client_title($login_info[$account]['client_title']).'</td><td>&nbsp;</td></tr>';
		$varOutStr  = $varOutStr.'<tr><td>&nbsp;&nbsp;职务：'.Constancearr::client_job($login_info[$account]['client_job']).'</td></tr>';
		  $varOutStr  = $varOutStr.'<tr>';
		
			$varOutStr  = $varOutStr."<td align='center'><input type='button' name='Submit' value='+好友' class='inputbg2' onclick=\"javascript:tofriendAddlist('".$login_info[$account]['client_account']."','0');\"/></td>";
			$varOutStr  = $varOutStr."<td align='center'><input type='submit' class='inpusublong' name='Submit2' value='踩踩TA的空间' onclick=\"javascript:window.open('/Homeuser/Index/spacehome/spaceid/$account');\"/></td>";
		 $varOutStr  = $varOutStr.' </tr>';
		$varOutStr  = $varOutStr.'</table>';
		}elseif($accounttype=="student"){
		
		$varOutStr = '<table width="300" border="0" cellpadding="3" cellspacing="1" height="220">';
		  $varOutStr  = $varOutStr.'<tr>';
			$varOutStr  = $varOutStr."<td width='140' rowspan='3' align='center'><Img src='".Pathmanagement_sns::getHeadImg($account) . $login_info[$account]['client_headimg'] . "' onerror=this.src='".IMG_SERVER."/Public/images/head_pics.jpg' width='80' height=80></td>";
			$varOutStr  = $varOutStr.'<td>姓名：'.$login_info[$account]['client_name'].'</td>';
		$varOutStr  = $varOutStr.'</tr>';
		$varOutStr  = $varOutStr.'<tr><td>区域：'.$login_info[$account]['school_info'][$school_id]['area_id_name'].'</td></tr>';
		$varOutStr  = $varOutStr.'<tr><td>星座：'.$login_info[$account]['client_constellation_name'].'</td></tr>';
        
		$varOutStr  = $varOutStr.'<tr><td>&nbsp;&nbsp;学校：'.$school_name.'</td><td>&nbsp;</td></tr>';
		$varOutStr  = $varOutStr.'<tr><td>&nbsp;&nbsp;班级：'.$class_name.'</td><td>&nbsp;</td></tr>';
		  $varOutStr  = $varOutStr.'<tr>';
			
			if($this->getCookieAccount()==$login_info[$account]['client_account'] || $havedis==1 || $this->user['client_type']==CLIENT_TYPE_FAMILY){
				$btndisabed = "disabled";
				//javascript:window.location='/Homefriends/Friends/searchhy/addaccount/".$login_info[$account]['client_account']."';
			}


			$varOutStr  = $varOutStr."<td align='center'><input type='button' name='Submit' value='+好友' class='inputbg2' onclick=\"javascript:tofriendAddlist('".$login_info[$account]['client_account']."','0');\" ".$btndisabed."/></td>";

			
			
			$varOutStr  = $varOutStr."<td align='center'><input type='submit' class='inpusublong' name='Submit2' value='踩踩TA的空间' onclick=\"javascript:window.open('/Homeuser/Index/spacehome/spaceid/$account');\"/></td>";
		 $varOutStr  = $varOutStr.' </tr>';
		$varOutStr  = $varOutStr.'</table>';
		
		}else{
			echo "家长";
		}
		echo $varOutStr;
	}
	

	/*2012-01-12 lyt*/
	public function getStudentsfamily(){
		$account = $this->objInput->getInt('account');
		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
		$mUser = ClsFactory::Create('Model.mUser');	
		$login_info = $mUser->getUserByUid($account);
		$client_typeUser = $this->user['client_type'];

		
		$selectedarr = array();
		//关系类型
		$selectedarr['family_type'] = array(
    		'datas' => Constancearr::family_relationtype(),
		);
		
		$arrData = $mFamilyRelation->getFamilyRelationByUid($account);
		$arrData = array_shift($arrData);

		if($arrData){
			foreach($arrData as $key=>$val) {
				$family_uids[$val['family_account']] = $val['family_account'];
			}
			$familyInfoList = $mUser->getUserByUid($family_uids);
			unset($family_uids);

			foreach ($arrData as $key => $value) {
				if($familyInfoList[$value['family_account']]) { 
					
					$accountInfo = $familyInfoList[$value['family_account']];

					$arrData[$key] = array_merge($value , $accountInfo);
					$arrData[$key]['account_headpic_path'] = Pathmanagement_sns::getHeadImg($value['family_account']) . $accountInfo['client_heading'];

					$arrData[$key]['client_trade'] = Constancearr::client_trade($accountInfo['client_trade']);
					$arrData[$key]['client_constellation'] = $accountInfo['client_constellation'];
				}
			}
			
		}
		if($client_typeUser==CLIENT_TYPE_STUDENT){
			$this->assign('client_typeUser',"disabled");
		}
		$this->assign('student_headimg' , Pathmanagement_sns::getHeadImg($account) . $login_info[$account]['client_headimg']);
		$this->assign('student_client_name',$login_info[$account]['client_name']);
		$this->assign('selectedarr' , $selectedarr);
		
		$this->assign('familyarrData',$arrData);
		$this->assign('client_clientconstellation',Constancearr::client_constellation());
		
		$this->display('Studentsfamily');
	}

	
	// 班级

	/**
	 * 班级成员管理
	 * 已经完成
	 */
	public function clamembers() {
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);	
		$client_typeUser = $this->user['client_type'];
	    $teacherlist = $studentlist = $familylist = array();
	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code);
	    $clientclasslist = $clientclassarr[$class_code];
	    unset($clientclassarr);
	    //获取老师和家长的相关信息
	    $schooluids = array();
	    if(!empty($clientclasslist)) {
	        foreach($clientclasslist as $clientclass) {
	            $clienttype = intval($clientclass['client_type']);
	            $uid = $clientclass['client_account'];
	            if($clienttype == CLIENT_TYPE_STUDENT) {
	                $studentlist[$uid] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_TEACHER) {
	                $teacherlist[$uid] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_FAMILY) {
	                $familylist[$uid] = $uid;
	            }
	        }
	        unset($clientclasslist);
	    }
	    //获取老师和学生的基本信息
	    if(!empty($schooluids)) {
	        $schooluids = array_unique($schooluids);
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($schooluids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
                    if(isset($studentlist[$uid])) {
                        $studentlist[$uid] = array_merge($user , $studentlist[$uid]);
                    }elseif(isset($teacherlist[$uid])) {
                        $teacherlist[$uid] = array_merge($user , $teacherlist[$uid]);
						
						//班主任信息
						$teacher_admin_arr = array(1,3);
						if(in_array($teacherlist[$uid]['teacher_class_role'],$teacher_admin_arr)){
						    
							$this->assign('teacher_class_role_name' , $teacherlist[$uid]['client_name']);
							$this->assign('tpl_headteacher_account' , $teacherlist[$uid]['client_account']);
							
							$this->assign('teacher_class_role_img',Pathmanagement_sns::getHeadImg($teacherlist[$uid]['client_account']) . $teacherlist[$uid]['client_headimg']);
						}
                    }
	            }
	        }
			
	        unset($userlist , $schooluids);
	    }
		
		
	    $studentusers = array_keys($studentlist);
	    //获取学生的家长信息
	    if(!empty($studentusers)) {
			$this->assign('studentcountnums' , count($studentusers));
			
	        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	        $familyrelationlist = $mFamilyRelation->getFamilyRelationByUid($studentusers);
	        if(!empty($familyrelationlist)) {
	            foreach($familyrelationlist as $stu_uid=>$relationlist) {
	                $defaultfamilyaccount = 0;
	                if(isset($studentlist[$stu_uid]) && !empty($relationlist)) {
	                    foreach($relationlist as $relation) {
	                        $familyaccount = $relation['family_account'];
	                        //检测家长的账号是否存在
	                        if(!empty($familyaccount) && isset($familylist[$familyaccount])) {
                                $defaultfamilyaccount = $familyaccount;
	                            break;
	                        }
	                    }
	                }
					
	                $studentlist[$stu_uid]['family_account'] = $defaultfamilyaccount;
	            }
	        }
			$this->assign('familycountnums' , count($familylist));
	        unset($familylist);
	    }

	    //获取教师对应的科目信息,$anlicheng教师科目一对多的修改
	    if(!empty($teacherlist)) {
	        $teacheruids = array_keys($teacherlist);
            //todochecked
	        $subjectinfolist = $this->getSubjectInfoByTeacherUid($teacheruids, $class_code, "、");
	        if(!empty($subjectinfolist)) {
    	        foreach($teacherlist as $uid=>$teacher) {
    	            $teacher['subject_info'] = isset($subjectinfolist[$uid]) ? $subjectinfolist[$uid] : false;
    	            $teacherlist[$uid] = $teacher;
    	        }
	        }
	    }
	    
		$this->assign('studentlist' , $studentlist);
	    $this->assign('teacherlist' , $teacherlist);
	    $this->assign('leadertype' , Constancearr::classleader());
		if($client_typeUser==CLIENT_TYPE_FAMILY){
			$this->assign('client_typeUser',"disabled");
		}

	    $this->display('classStudents');
	}


	/**
	 * 1. 只显示教师对的科目信息，一个老师对应的只有一个科目信息；
	 */
	public function manageclamember(){
	    
	    $class_code = $this->objInput->getInt('class_code');
	    //检测页面提交的参数，如果返回的参数和传入的值不一样要处理
	    $class_code = $this->checkclasscode($class_code);
	    $teacherlist = $studentlist = $familylist = array();
	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code);
	    $clientclasslist = $clientclassarr[$class_code];
	    unset($clientclassarr);

	    //获取老师和家长的相关信息
	    $schooluids = array();
	    if(!empty($clientclasslist)) {
	        foreach($clientclasslist as $clientclass) {
	            $clienttype = intval($clientclass['client_type']);
	            $uid = $clientclass['client_account'];
	            if($clienttype == CLIENT_TYPE_STUDENT) {
	                $studentlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_TEACHER) {
	                $teacherlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_FAMILY) {
	                $familylist[$uid] = $uid;
	            }
	        }
	        unset($clientclasslist);
	    }

	    //获取老师和学生的基本信息
	    if(!empty($schooluids)) {
	        $schooluids = array_unique($schooluids);
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($schooluids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
                    if(isset($studentlist[$uid])) {
                        $studentlist[$uid] = array_merge($user , $studentlist[$uid]);
                    }elseif(isset($teacherlist[$uid])) {
                        $teacherlist[$uid] = array_merge($user , $teacherlist[$uid]);
                    }
	            }
	        }
	        unset($userlist , $schooluids);
	    }

	    $studentusers = array_keys($studentlist);
	    //获取学生的家长信息
	    if(!empty($studentusers)) {
	        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	        $familyrelationlist = $mFamilyRelation->getFamilyRelationByUid($studentusers);
	        if(!empty($familyrelationlist)) {
	            foreach($familyrelationlist as $stu_uid=>$relationlist) {
	                $defaultfamilyaccount = 0;
	                if(isset($studentlist[$stu_uid]) && !empty($relationlist)) {
	                    foreach($relationlist as $relation) {
	                        $familyaccount = $relation['family_account'];
	                        //检测家长的账号是否存在
	                        if(!empty($familyaccount) && isset($familylist[$familyaccount])) {
                                $defaultfamilyaccount = $familyaccount;
	                            break;
	                        }
	                    }
	                }
	                $studentlist[$stu_uid]['family_account'] = $defaultfamilyaccount;
	            }
	        }
	        unset($familylist);
	    }

	    //获取教师对应的科目信息,$anlicheng教师科目一对多
	    if(!empty($teacherlist)) {
	        $teacheruids = array_keys($teacherlist);
            //todochecked
	        $subjectinfolist = $this->getSubjectInfoByTeacherUid($teacheruids, $class_code, "、");
	        if(!empty($subjectinfolist) && !empty($teacherlist)) {
    	        foreach($teacherlist as $uid=>$teacher) {
    	            $teacher['subject_info'] = isset($subjectinfolist[$uid]) ? $subjectinfolist[$uid] : false;
    	            $teacherlist[$uid] = $teacher;
    	        }
	        }
	    }

		//班干部名称
		$this->assign('class_code' , $class_code);
		$this->assign('studentlist' , $studentlist);
		$this->assign('teacherlist' , $teacherlist);
		$this->assign('leadertype' , Constancearr::classleader());
		$this->assign('actionUrl' , "/Homeclass/Class/manageclamember/");
		
		$this->display('manageclamember');


	}
	
	//班级管理-当前班级学生列表
	function clamemberlist(){
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$page = $this->objInput->getInt('page');
		

	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code);
	    $clientclasslist = $clientclassarr[$class_code];

	    $schooluids = array();
	    if(!empty($clientclasslist)) {
	        foreach($clientclasslist as $clientclass) {
	            $clienttype = intval($clientclass['client_type']);
	            $uid = $clientclass['client_account'];
	            if($clienttype == CLIENT_TYPE_STUDENT) {
	                $studentlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            }
	        }
	        unset($clientclasslist);
	    }

	    if(!empty($schooluids)) {
	        $schooluids = array_unique($schooluids);
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($schooluids);
	    }
	    
		//count($userlist);
		if(!empty($userlist)){
			$studentInfo = array();
			foreach($userlist as $key=>$val) {
				if($val['client_sex']!=""){
					switch($val['client_sex']){
						case 0 :
							$val['client_sex'] = "女";
							break;
						case 1 :
							$val['client_sex'] = "男";
							break;
					}
				}
				else{
					$val['client_sex'] = "--";
				}
				
				switch($val['status']){
						case -1 :
							$val['stop_flag'] = "未激活";
							break;
						case 0 :
							$val['stop_flag'] = "已激活";
							break;
						case 1 :
							$val['stop_flag'] = "有时间冻结";
							break;
						case 2 :
							$val['stop_flag'] = "永久冻结";
							break;

				}
				switch($val['internet_status']){
					case 0 :
						$val['internet_status'] = "不在线";
						break;
					case 1 :
						$val['internet_status'] = "不在线";
						break;
				}

				$studentInfo[]=$val;

			}

		}

		$this->assign('classInfo',$studentInfo);
		$this->assign('class_code',$class_code);
		$this->assign('actionUrl' , "/Homeclass/Class/manageclamember/");
		$this->display('classManage');
	}

	/**
	 * 1. 用户类型
	 */
	//改变班级对象的类型

	/**
	 * 1. 老师的不允许修改科目；只是提供科目信息的显示；
	 * 2. 管理员对老师只能是：设置成管理员和取消管理员 ；
	 * 	      对学生可以用：改变角色，设为管理员，踢出班级；
	 * 3. 只区分管理员；与对应的其他无关；
	 *
	 * 所有的操作：改变角色，设为管理员，踢出班级、取消管理员
	 * 是通过ajax来请求的
	 */

	public function changetype(){
	    /*param : pre=>role,admin,qxadmin表示操作类型
	     * type: 是要存入数据库表的值，string
	     */
	    $client_class_id = $this->objInput->getInt('client_class_id');
	    $class_code = $this->objInput->getInt('class_code');
	    $toaccount = $this->objInput->getInt('toaccount');//表示要操作的对象id
	    $action = $this->objInput->getStr('action'); //操作类型
	    $type = $this->objInput->getInt('type');
	    //检测当前管理的班级是否合法
	    //$class_code = $this->checkclasscode($class_code);
	    if(empty($toaccount) || empty($client_class_id)) {
	       echo '操作错误';
	       return false;
	    }

	    $uid = $this->user['client_account'];
	    
		$mUser = ClsFactory::Create('Model.mUser');
		$mClientClass = ClsFactory::Create('Model.mClientClass');

	    $touserlist = $mUser->getUserBaseByUid($toaccount);
	    $touser = $touserlist[$toaccount];
	    unset($touserlist);
	    $toclienttype = intval($touser['client_type']);

		//判断在线用户权限是否是当前班级的管理员
		$clientclasslist = $this->user['client_class'];
		foreach($clientclasslist as $key=>$val) {
			if($val['class_code'] == $class_code){
				$class_admin = $val['class_admin'];
				//$client_class_id = $val['client_class_id'];
			}
		}
		
		//$class_admin = intval($clientclasslist[$class_code]['class_admin']);
		
		if($class_admin == 0) {
		    echo "您暂时没有任何管理权限!";
		    return false;
		}

		//要更新的数据不存在
		if(empty($client_class_id)) {
		    echo "您提交参数有误!";
		    return false;
		}

		//修改用户的角色只对学生有效
		if($action == 'role') {
		    if($toclienttype == CLIENT_TYPE_STUDENT) {
		        $type_str = Constancearr::classleader($type);
		        $type = $type && isset($type_str) ? $type : false;
		        if(!empty($type)) {
		            $dataarr = array(
		                'client_class_role' => $type,
		            	'upd_account' => $uid,
		                'upd_time' => time(),
		            );
		            $rs = $mClientClass->modifyClientClass($dataarr , $client_class_id);
		            if(!empty($rs)){
		            	echo "操作成功!";
		            }else{
		            	echo "操作失败!";
		            }
		        } else {
		            echo "您提交参数有误!";
		            return false;
		        }
		    }
		} elseif(in_array($action , array('admin' , 'canceladmin'))) {
		     //获取当前管理员的个数
	        $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('class_admin'=>IS_CLASS_ADMIN));
	        $clientclasslist = $clientclassarr[$class_code];
	        unset($clientclassarr);
	        $class_admin_nums = count($clientclasslist);

    		if($action == 'admin') {
    	        if($class_admin_nums < 3) {
    	            $dataarr = array(
    	                'class_admin' => IS_CLASS_ADMIN,
    	            	'upd_account' => $uid,
    	            	'upd_time' => time(),
    	            );
    	            $mClientClass->modifyClientClass($dataarr , $client_class_id);
    	            echo "<a href=\"javascript:changetype('canceladmin', '$toaccount', '$client_class_id', '$class_code');\">取消管理员</a>";
    	        } else {
    	            echo "当前管理员个数已经达到最大值!";
    	            return false;
    	        }
    		} elseif($action == 'canceladmin') {
    		    if($class_admin_nums > 1) {
        		    $dataarr = array(
        		        'class_admin' => NO_CLASS_ADMIN,
        		    	'upd_account' => $uid,
        		        'upd_time' => time(),
        		    );
        		    $mClientClass->modifyClientClass($dataarr , $client_class_id);
        		    echo "<a href=\"javascript:changetype('admin' , '$toaccount', '$client_class_id', '$class_code');\">设置管理员</a>";
    		    } else {
    		        echo "当前管理员个数小于1您不能退出!";
    		        return false;
    		    }
    		}
		}
	}

	

	//布置作业
	public function sethomework(){
	    $class_code = $this->objInput->getInt('class_code');
	    //检测页面的class_code参数
	    $class_code = $this->checkUrl();
	    $action = $this->objInput->getStr('action');
	    $action = !empty($action) && in_array($action , array('modify' , 'add')) ? $action : "add";
	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	    if($action == 'modify') {
	        $news_id = $this->objInput->getInt('news_id');
	        $homework_hash = $this->objInput->getStr('homework_hash');

	        if($homework_hash == $this->getHomeworkMd5($news_id)) {
	            $mNewsInfo = ClsFactory::Create('Model.mNewsInfo'); 
	            $newslist = $mNewsInfo->getNewsInfoById($news_id);
				//print_r($newslist);
	            $current_news = & $newslist[$news_id];
    	        if(empty($current_news)) {
    	            $this->showError("您访问的信息不存在", "/Homeclass/Class/showhomework");
    	            exit;
    	        }
	        } else {
	            $this->showError("您没有权限或者网页已过期", "/Homeclass/Class/showhomework");
	            exit;
	        }
	        //获取科目信息
	        $subject_id = intval($current_news['subject_id']);
	        $subjectlist = $mSubjectInfo->getSubjectInfoById($subject_id);
	        $current_subjectinfo = $subjectlist[$subject_id];
	        unset($subjectlist);
	        if(isset($current_subjectinfo)) {
	            $current_news['subject_info'] = $current_subjectinfo;
	        }
            //控制数据的正确显示
            if(empty($current_news['subject_info']['subject_name'])) {
                $current_news['subject_info']['subject_name'] = "未知科目";
            }
            //过期时间
            if(!empty($current_news['expiration_date']) && ($expiration_time = strtotime($current_news['expiration_date'])) !== false) {
                $current_news['expiration_date'] = date("Y-m-d" , $expiration_time);
            } else {
                $current_news['expiration_date'] = date("Y-m-d" , time() + 3600 * 24);
            }

	        //作业信息回显
	        $this->assign('current_news' , $current_news);
	        $this->assign('news_id' , $news_id);
	        $this->assign('homework_hash' , $homework_hash);
	    } elseif($action == 'add') {
	        //获取班级的基本老师信息
    	    $mClientClass = ClsFactory::Create('Model.mClientClass');
    		$clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type' => array(CLIENT_TYPE_TEACHER)));
    		$clientclasslist = $clientclassarr[$class_code];
    		unset($clientclassarr);

    		//老师科目
    		$teacheruids = array_keys($clientclasslist);
    		//判断当前用户的权限
    		if(empty($teacheruids) || (!empty($teacheruids) && !in_array($this->user['client_account'] , $teacheruids))) {
    		    $this->showError("您不是该班的任课教师", "/Homeclass/Myclass/index/class_code/$class_code");
    		    exit;
    		}
    		
    		$this->assign('expiration_date' , date('Y-m-d' , time() + 3600 *24));
	    }
	    
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
			$is_class_admin = true;
		}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
			$is_class_admin = true;
		}else{
			$is_class_admin = false;
		}
		//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
		if($is_class_admin) {
		    $subjectinfolist = $this->getTeacherSubjectList($class_code);
		} else {
		    //todochecked
		    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
		}
	    //科目列表信息
	    $this->assign('subjectinfolist' , $subjectinfolist);
	    $this->assign('is_class_admin' , $is_class_admin);
	    //获取当前用户所在学校的运营策略
	    $schoolinfo = array_shift($this->user['school_info']);
	    $schoolid = $schoolinfo['school_id'];
		$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略

		$this->assign('class_code' , $class_code);
		$this->assign('school_id' , $schoolid);
		$this->assign('is_modify' , $action == 'modify' ? true : false);
        $this->assign('operationStrategy' , $operationStrategy);
		$this->assign('actionUrl',"/Homeclass/Class/sethomework/");

		$this->display('sethomework');
	}
	


	/**
	 *
	 */
	//成绩管理页面
	public function cjtmanage(){
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
	    $classinfolist = $this->user['class_info'];
	    $schoollist = $this->user['school_info'];
		
		//搜索查找
		$end_exam_date= $this->objInput->postStr('end_exam_date');
	    $sr_subject_id = $this->objInput->postStr('subject_id');
	    $sr_exam_name = $this->objInput->postStr('exam_name');
	    $sr_exam_date = $this->objInput->postStr('exam_date');
		
        $pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=5; //每页数量
	    
	    //获取对应的学校id并检测数据的正确性
	    $schoolid = $classinfolist[$class_code]['school_id'];
	    if(!isset($schoollist[$schoolid])) {
	        $schoolid = 0;
	    }
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
	    //获取班级老师的科目信息
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
    		$is_class_admin = true;
    	}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
    		$is_class_admin = true;
    	}else{
    		$is_class_admin = false;
    	}
    	
    	$mExamInfo = ClsFactory::Create('Model.mExamInfo');
		//查询条件
		$firter = array(
			$sr_subject_id,
			$sr_exam_name,
			$sr_exam_date,
			$end_exam_date,
			$this->user['client_account']
		);
		$offset = ($page-1)*$pagesize;
		$RS_DATA_EXAM = $mExamInfo->getExamInfoByClassCode($schoolid, $class_code, $firter, $offset, $pagesize+1);
	    $is_class_admin = false;
		foreach($this->user['client_class'] as $key=>$val){
			if(intval($val['class_code']) == $class_code) {
				$is_class_admin = $val['class_admin'];
				break;
			}
		}
    	//$is_class_admin = !empty($this->user['client_class'][$class_code]['class_admin']) ? true : false;
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	if($is_class_admin) {
    	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	} else {
    	    //todochecked
    	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
    	}
    	
		$prvpageno = intval($page) ==1 ? 1 : $page-1;
		if(count($RS_DATA_EXAM) > $pagesize){
			array_pop($RS_DATA_EXAM);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href=\"javascript:srsubmit('{$class_code}','{$prvpageno}')\">上一页</a> | <a href=\"javascript:srsubmit('{$class_code}','{$nextpageno}')\">下一页</a></div>");
		$this->assign('pageno',$pageno);

		if (!empty($RS_DATA_EXAM)) {
			//通过学校id 获取该学校所有的科目
			$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
			$subjectinfo_list = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);
			$subjectinfo_list = $subjectinfo_list[$schoolid];
			foreach($RS_DATA_EXAM as $key=>$val) {
				foreach ($subjectinfo_list as $k=>$v) {
					if ($v['subject_id'] == $val['subject_id']) {
						$val['subject_name'] = $v['subject_name'];
					}
				}
			    $RS_DATA_EXAM[$key] = $val;
			}
			unset($subjectinfo_list);
		}
		
		$this->assign('exam_name' , $sr_exam_name);
	    $this->assign('exam_date', $sr_exam_date);
		$this->assign('end_exam_date' , $end_exam_date);
		
		$this->assign('examinfolist' , $RS_DATA_EXAM);
	    $this->assign('class_name_list', $classinfolist);
		$this->assign('school_id' , $schoolid);
		$this->assign('class_code' , $class_code);
		$this->assign('subject_id' , $sr_subject_id);
		$this->assign('subjectinfolist' , $subjectinfolist);
		$this->assign('actionUrl',"/Homeclass/Class/cjtmanage/");
		$this->display('cjtmanage');
	}

	//成绩发布页面
	public function cjtpublish(){
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    $classinfoarr = $mClassInfo->getClassInfoById($class_code);
	    $classinfolist = $classinfoarr[$class_code];
	    unset($classinfoarr);

	    //获取当前用户的班级列表信息
	    $myclasslist = &$this->user['class_info'];
	    //获取班级对应的学校id
	    $school_id = $classinfolist['school_info']['school_id'];
	    $school_id = isset($this->user['school_info'][$school_id]) ? $school_id : 0;

	    //学生列表信息
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>CLIENT_TYPE_STUDENT));
	    $clientclasslist = $clientclassarr[$class_code];
	    unset($clientclassarr);
	    //获取学生的基本信息
	    $mUser = ClsFactory::Create('Model.mUser');
	    if(!empty($clientclasslist)) {
	        $studentuids = array_unique(array_keys($clientclasslist));
			$studentlist = $mUser->getUserBaseByUid($studentuids);
	    }
	    //追加id属性
    	if(!empty($studentlist)) {
    	    $i = 0;
    	    foreach($studentlist as $uid=>$student) {
    	        if(empty($student['client_name'])) {
    	            unset($studentlist[$uid]);
    	            continue;
    	        }
    	        $student['id'] = $i++;
    	        $studentlist[$uid] = $student;
    	    }
	    }
		//获取班级老师的科目信息,$anlicheng处理教师信息一对多的情况 
		//判断当前用户是否是管理员
		$is_class_admin = false;
		foreach($this->user['client_class'] as $key=>$val){
			if(intval($val['class_code']) == $class_code) {
				$is_class_admin = $val['class_admin'];
				break;
			}
		}
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	if($is_class_admin) {
    	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	} else {
    	    //todochecked
    	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
    	}
    	
	    //todochecked
		//获取评语
		$schoolinfo = array_shift($this->user['school_info']);//获取该学校的运营策略
	    $schoolid = $schoolinfo['school_id'];
	    $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);

		$this->assign('myclasslist',$myclasslist);
		$this->assign('school_id' , $school_id);
		$this->assign('class_code' , $class_code);
		$this->assign('studentlist' , $studentlist);
		$this->assign('subjectinfolist' , $subjectinfolist);
        $this->assign('operationStrategy' , $operationStrategy);
		$this->assign('actionUrl' ,"Homeclass/Class/cjtpublish/");
		$this->display('cjtpublish');
	}

	//查找考试信息
	public function findexaminfo(){
	
	    $exam_name = $this->objInput->postStr('exam_name');
	    $exam_date = $this->objInput->postStr('exam_date');
		$end_exam_date= $this->objInput->postStr('end_exam_date');
	    $subject_id = $this->objInput->postStr('subject_id');
	    $school_id = $this->objInput->postInt('school_id');
	    $class_code = $this->objInput->postInt('class_code');

	    //获取当前用户的班级列表信息
	    $myclasslist = &$this->user['class_info'];
	    //检测班级信息的合法性
	    $class_code = $this->checkclasscode($class_code);
	    //必须条件
	    if(empty($subject_id)) {
	        //return false;
	    }
		$filters = array(
		    'class_code' => $class_code ? $class_code : false,
		    'school_id' => $school_id ? $school_id : false,
		    'exam_name' => $exam_name ? $exam_name : false,
		    'exam_date' => $exam_date ? $exam_date : false,
		);
		foreach($filters as $key=>$filter) {
		    if(empty($filter)) {
		        unset($filters[$key]);
		    }
		}
		$mExamInfo = ClsFactory::Create('Model.mExamInfo');
		$examinfoarr = $mExamInfo->getExamInfoBySubjectId($subject_id , $filters);
		$examinfolist = $examinfoarr[$subject_id];
		
		unset($examinfoarr);
		//用来显示用的
		//获取班级老师的科目信息
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
	    //获取班级老师的科目信息
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
    		$is_class_admin = true;
    	}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
    		$is_class_admin = true;
    	}else{
    		$is_class_admin = false;
    	}
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	if($is_class_admin) {
    	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	} else {
    	    //todochecked
    	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
    	}
		//todochecked
		//用来整理考试的信息
		$exam_subjectinfolist = array();
		if(!empty($subjectinfolist)) {
		    foreach($subjectinfolist as $subjectinfo) {
		        $exam_subjectinfolist[$subjectinfo['subject_id']] = $subjectinfo['subject_name_short'];
		    }
		}
		if(!empty($examinfolist)) {
		    foreach($examinfolist as $exam_id => $examinfo) {
		        $subjectid = intval($examinfo['subject_id']);
		        if(isset($exam_subjectinfolist[$subjectid])) {
		            $examinfo['subject_name'] = $exam_subjectinfolist[$subjectid];
		        }
		        $examinfolist[$exam_id] = $examinfo;
		    }
		    unset($exam_subjectinfolist);
		}
		//dump($examinfolist);
		$this->assign('class_list', $myclasslist);
		$this->assign('exam_name' , $exam_name);
		$this->assign('exam_date' , $exam_date);
		$this->assign('end_exam_date' , $end_exam_date);
		$this->assign('subject_id' , $subject_id);
		$this->assign('examinfolist' , $examinfolist);
		$this->assign('subjectinfolist', $subjectinfolist);
		$this->assign('school_id' , $school_id);
		$this->assign('class_code' , $class_code);
		$this->display('cjtmanage');
	}



	//查找考试信息
	public function delexaminfo(){
	    $exam_id = $this->objInput->getInt('exam_id');
	    $mStudentScore = ClsFactory::Create('Model.mStudentScore');
	    $studentscorearr = $mStudentScore->getStudentScoreExamId($exam_id);
	    $studentscorelist = $studentscorearr[$exam_id];
	    unset($studentscorearr);

	    if(!empty($studentscorelist)) {
	        $score_ids = array_keys($studentscorelist);
	        //删除学生成绩表中的记录,只能通过主键删除数据
	        $score_ids = array_unique($score_ids);
	        foreach($score_ids as $key=>$score_id) {
	            $mStudentScore->delStudentScore($score_id);
	            unset($score_ids[$key]);
	        }
	    }
	    //如果exam_id对应的成绩信息已经全部删除，则对应的删除成绩主表中的信息
	    if(empty($score_ids)) {
	        $mExamInfo = ClsFactory::Create('Model.mExamInfo');
	        $mExamInfo->delExamInfo($exam_id);
	    } else {
	        echo "您必须先删除该考试的全部学生成绩信息后才能删除该考试信息!";
	    }

		$this->findexaminfo();
	}

    /*
	 * 查询学生的基本成绩信息，并按照成绩降序排列
	 * 提取学生的学号，然后获取学生的基本信息
	 */
	function searchexaminfo($exam_id = 0,$nocj){
	    if($exam_id == 0) {
	        $exam_id = $this->objInput->getInt('exam_id');
	    }
	    //提取单个考试的全部信息
	    $mExamInfo = ClsFactory::Create('Model.mExamInfo');
	    $examinfoarr = $mExamInfo->getExamInfoById($exam_id);
	    $examinfolist = $examinfoarr[$exam_id];
		 unset($examinfoarr);

	    //这里的class_code的值要优先满足成绩信息中记录的class_code的值，页面有可能没有传回正确的值
	    $class_code = isset($examinfolist['class_code']) ? intval($examinfolist['class_code']) : 0;
	    if(empty($class_code)) {
	        $class_code = $this->objInput->getInt('class_code');
	        $class_code = $this->checkclasscode($class_code);
	    }
	    if(isset($examinfolist['student_score']) && !empty($examinfolist['student_score'])) {
	        $studentscorelist = $examinfolist['student_score'];
			$examstudentCount = count($examinfolist['student_score']);
	        unset($examinfolist['student_score']);
	    }

	    //获取学生的账号信息
	    if(!empty($studentscorelist)) {
			$mUser = ClsFactory::Create('Model.mUser');
			$studentuids = array_unique(array_keys($studentscorelist));
	        $userlist = $mUser->getUserBaseByUid($studentuids);
	    }
	    //统计学生成绩信息
	    if(!empty($studentscorelist)) {
	        $jgnums = $yxnums = $totalnums = $totalscores = 0;
	        foreach($studentscorelist as $uid=>$studentscore) {
	            //数据合并
	            if(isset($userlist[$uid]) && !empty($userlist[$uid])) {
	                $studentscore['client_name'] = $userlist[$uid]['client_name'];
					switch($userlist[$uid]['client_sex']){
						case 0 :
							$studentscore['client_sex'] = "女";
							break;
						case 1 :
							$studentscore['client_sex'] = "男";
							break;
					}
					
	            }
	            $score = $studentscore['exam_score'];
				//未参加人
				$lost_totalnums = 0;
				 if($score != '-1'){
					 $lost_totalnums++;
				 }
	            if($score != '-1'){
		            if($score >= $examinfolist['exam_bad']) {
		                $jgnums++;
		            }
		            if($score >= $examinfolist['exam_good']){
		                $yxnums++;
		            }
		            $totalnums++;
	            	$totalscores += $score;
	            }
	            $studentscorelist[$uid] = $studentscore;
	        }
	        $statlist = array(
	            'ks_totalnums' => $totalnums."/".$examstudentCount,
				'jg_nums' => $jgnums,
	            'yx_nums' => $yxnums,
	            'ave_score' => round($totalscores / $totalnums , 2),
	            'jg_rate' => round($jgnums / $totalnums ,4)*100,
	            'yx_rate' => round($yxnums / $totalnums,4)*100,
	        );
	        unset($userlist);
	    }
	    //对结果进行排序
	    if(!empty($studentscorelist)) {
	    	$num = 1;
	         foreach($studentscorelist as $uid=>$studentscore) {
	         	if($studentscore['exam_score'] != '0' && $studentscore['exam_score']!="-1"){
	         		$exam_score[$uid] = $studentscore['exam_score'];//排序数组
	         		$exam_score1[$uid] = $studentscore['exam_score'];//最大值，最小值数组
	         	}else{
	         		$exam_score[$uid] = '0';
	         	}
	        }
	        array_multisort($exam_score , SORT_DESC , $studentscorelist);
	        foreach($studentscorelist as $uid=>$studentscore){
	         		$studentscorelist[$uid]['num'] = $num++;
	        }
	    }
		
	    //统计成绩的最大值和最小值
	    $statlist['hscore'] = max($exam_score1);
	    $statlist['lscore'] = min($exam_score1);

		//判断当前用户是否是管理员
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
	    //获取班级老师的科目信息
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
    		$is_class_admin = true;
    	}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
    		$is_class_admin = true;
    	}else{
    		$is_class_admin = false;
    	}
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	if($is_class_admin) {
    	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	} else {
    	    //todochecked
    	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
    	}
//		//教师的科目
		//todochecked
		if(!empty($subjectinfolist) && is_array($subjectinfolist)) {
    		foreach ($subjectinfolist as $uid=>$tecInfo) {
    		    if ($tecInfo['subject_id'] == $examinfolist['subject_id']) {
    		        $subject_name = $tecInfo['subject_name'];
    		        break;
    		    }
    		}
		}
		
	    if(empty($totalscores) || $totalscores < 0) {
            $this->assign('is_show_daw',false);
        }else {
            $this->assign('is_show_daw',true);
        }
		$this->assign('subject_name' , $subject_name);
		$this->assign('exam_id',$exam_id);
		$this->assign('class_code' , $class_code);
		$this->assign('examinfolist' , $examinfolist);
		$this->assign('studentlist' , $studentscorelist);
		$this->assign('subjectinfolist' , $subjectinfolist);
		$this->assign('statlist',$statlist);
		
		return $statlist;//成绩通发送短信给家长时，需要告知最高最低以及平均分。
	}

	//查找学生成绩
	public function findstuscore(){
	    $school_id = $this->objInput->getInt('school_id');
		$this->searchexaminfo(0,'no');
		
		//获取当前用户的班级列表信息
	    $myclasslist = &$this->user['class_info'];
		//获取评语
		$mark = 1;
		$this->assign('mark',$mark);
		$this->assign('myclasslist' ,$myclasslist);
		$this->assign('school_id',$school_id);
		
		$this->display('cjtpublish');
	}

	//显示考试详细信息
	public function examdetailinfo(){
		$this->searchexaminfo(0, 'detail');
		
		$this->display('cjttestdetail');
	}
	
	//班级成绩统计
	function examscoredaw(){
		$exam_id = $this->objInput->getInt('examid');
	    if($exam_id == 0) {
	        $exam_id = $this->objInput->getInt('exam_id');
	    }

	    //提取单个考试的全部信息
	    $mExamInfo = ClsFactory::Create('Model.mExamInfo');
	    $examinfoarr = $mExamInfo->getExamInfoById($exam_id);
	    $examinfolist = $examinfoarr[$exam_id];
		//print_r($examinfolist);
		
	    //这里的class_code的值要优先满足成绩信息中记录的class_code的值，页面有可能没有传回正确的值
	    $class_code = isset($examinfolist['class_code']) ? intval($examinfolist['class_code']) : 0;
	    if(empty($class_code)) {
	        $class_code = $this->objInput->getInt('class_code');
	        $class_code = $this->checkclasscode($class_code);
	    }
	    if(isset($examinfolist['student_score']) && !empty($examinfolist['student_score'])) {

	        $studentscorelist = $examinfolist['student_score'];
			$examstudentCount = count($examinfolist['student_score']);
			
			
	        unset($examinfolist['student_score']);
	    }

		//判断当前用户是否是管理员
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
	    //获取班级老师的科目信息
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
    		$is_class_admin = true;
    	}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
    		$is_class_admin = true;
    	}else{
    		$is_class_admin = false;
    	}
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	if($is_class_admin) {
    	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	} else {
    	    //todochecked
    	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
    	}

		if(!empty($subjectinfolist) && is_array($subjectinfolist)) {
    		foreach ($subjectinfolist as $uid=>$tecInfo) {
    		    if ($tecInfo['subject_id'] == $examinfolist['subject_id']) {
    		        $subject_name = $tecInfo['subject_name'];
    		        break;
    		    }
    		}
		}

	    //统计学生成绩信息
	    if(!empty($studentscorelist)) {
	        $jgnums = $yxnums = $totalnums = $totalscores = 0;
			$nums1=0;$nums2=0;$nums3=0;$nums4=0;$nums5=0;
	        foreach($studentscorelist as $uid=>$studentscore) {
	            $score = $studentscore['exam_score'];
	            if($score != '0' && $score != '-1'){
		            if($score >=0 && $score<60) {
		                $nums1++;
		            }
		            if($score >=60 && $score<70) {
		                $nums2++;
		            }
		            if($score >=70 && $score<80) {
		                $nums3++;
		            }
		            if($score >=80 && $score<90) {
		                $nums4++;
		            }
		            if($score >=90) {
		                $nums5++;
		            }
				}
				$totalnums = $nums1 + $nums2 + $nums3 + $nums4 + $nums5;
	        }
			
	    }
		$dawvalue = $nums1.",".$nums2.",".$nums3.",".$nums4.",".$nums5;
	

		$outVar =  "<table width='600' border='0' cellpadding='5' cellspacing='5'>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td height='50' colspan='2'><span class='tag_title'><b>课程名称</b>：".$subject_name."&nbsp;&nbsp;&nbsp;&nbsp; <b>考试名称</b>：".$examinfolist['exam_name']."&nbsp;&nbsp;&nbsp;&nbsp;<b>考试时间</b>：".$examinfolist['exam_date']."</span>	</td>";
		 $outVar = $outVar. " </tr>";
		 $outVar = $outVar. " <tr>";
			$outVar = $outVar. "<td colspan='2'><table width='100%' border='0' cellpadding='5' cellspacing='1' bgcolor='#CCCCCC'>";
			  $outVar = $outVar. "<tr bgcolor='#efefef' align='center'>";
				$outVar = $outVar. "<td>0-59</td>";
				$outVar = $outVar. "<td>60-70</td>";
				$outVar = $outVar. "<td>70-80</td>";
				$outVar = $outVar. "<td>80-90</td>";
				$outVar = $outVar. "<td>90-100</td>";
				$outVar = $outVar. "<td>合计</td>";
			  $outVar = $outVar. "</tr>";
			  $outVar = $outVar. "<tr bgcolor='#FFFFFF'>";
				$outVar = $outVar. "<td align='center'>".$nums1."&nbsp;人</td>";
				$outVar = $outVar. "<td align='center'>".$nums2."&nbsp;人</td>";
				$outVar = $outVar. "<td align='center'>".$nums3."&nbsp;人</td>";
				$outVar = $outVar. "<td align='center'>".$nums4."&nbsp;人</td>";
				$outVar = $outVar. "<td align='center'>".$nums5."&nbsp;人</td>";
				$outVar = $outVar. "<td align='center'>".$totalnums."&nbsp;人</td>";
			 $outVar = $outVar. " </tr>";
			$outVar = $outVar. "</table></td>";
		 $outVar = $outVar. " </tr>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td width='200'><iframe src='/Homeclass/Class/examscoredawdata/arrvalue/".$dawvalue."' scrolling='no' frameborder='0' width='300' height='250'></iframe></td>";
			$outVar = $outVar. "<td valign='middle'><table width='100%' border='0' cellpadding='5' cellspacing='5'>";
			  $outVar = $outVar. "<tr><td><font color='#1E90FF'>■</font>&nbsp;0-59</td> </tr>";
			$outVar = $outVar. "<tr><td><font color='#2E8B57'>■</font>&nbsp;60-70</td> </tr>";
			$outVar = $outVar. "<tr><td><font color='#ADFF2F'>■</font>&nbsp;70-80</td> </tr>";
			$outVar = $outVar. "<tr><td><font color='#DC143C'>■</font>&nbsp;80-90</td> </tr>";
			$outVar = $outVar. "<tr><td><font color='#BA55D3'>■</font>&nbsp;90-100</td> </tr>";
			
			$outVar = $outVar. "</table></td>";
		  $outVar = $outVar. "</tr>";
		$outVar = $outVar. "</table>";
		echo $outVar;
		 unset($examinfoarr);
		
	}



	function examscoredawdata(){
		import("@.Common_wmw.Vendor.Jpgraph.jpgraph");
		import("@.Common_wmw.Vendor.Jpgraph.jpgraph_pie");
		$datavalue = $this->objInput->getStr('arrvalue');
		$data = explode(",",$datavalue);
		$graph = new PieGraph(350,250);
		$theme_class="DefaultTheme";
		$title = "";
		$graph->SetBox(true);
		// Create
		$p1 = new PiePlot($data);
		$graph->Add($p1);

		$p1->ShowBorder();
		$p1->SetColor('black');
		$p1->SetSliceColors(array('#1E90FF','#2E8B57','#ADFF2F','#DC143C','#BA55D3'));
		$graph->Stroke();

	}

	function myexamscoredawdata(){
		import("@.Common_wmw.Vendor.Jpgraph.jpgraph");
		import("@.Common_wmw.Vendor.Jpgraph.jpgraph_line");
		$dataksname = $this->objInput->getStr('ksname');
		$datakscore = $this->objInput->getStr('kscore');
		$dataavscore = $this->objInput->getStr('avscore');
		
		$arrdataksname = explode(",",$dataksname);
		$arrdatadatakscore = explode(",",$datakscore);
		$arrdatadataavscore = explode(",",$dataavscore);
		$datay1 = $arrdatadatakscore;
		$datay2 = $arrdatadataavscore;
		
		$width = 580;
		$height = 300;
		$graph = new Graph($width,$height);
		$graph->SetScale("textlin",0,120);

		$theme_class= new UniversalTheme;
		$graph->SetTheme($theme_class);

		$graph->SetBox(false);

		$graph->yaxis->HideZeroLabel();
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);

		$graph->ygrid->SetFill(false);

		$p1 = new LinePlot($datay1);
		$graph->Add($p1);

		$p2 = new LinePlot($datay2);
		$graph->Add($p2);


		$p1->SetColor("#000075"); //线的颜色
		$p1->SetWeight(2);
		$p1->mark->SetType(MARK_FILLEDCIRCLE); //节点样式
		$p1->mark->SetColor('#000075'); //节点外颜色
		$p1->mark->SetFillColor('#000075'); //节点内颜色
		$p1->SetCenter();


		$p2->SetColor("#FF00FF"); //线的颜色
		$p2->SetWeight(2);
		$p2->mark->SetType(MARK_FILLEDCIRCLE); //节点样式
		$p2->mark->SetColor('#FF00FF'); //节点外颜色
		$p2->mark->SetFillColor('#FF00FF'); //节点内颜色
		$p2->SetCenter();


		$graph->legend->SetFrameWeight(1);
		$graph->legend->SetColor('#4E4E4E','#00A78A');
		$graph->legend->SetMarkAbsSize(8);

		$graph->Stroke();
	}



	//成绩曲线图
	function achievementdaw(){
		$account = $this->getCookieAccount();
		
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	$subjectinfolist = $this->getTeacherSubjectList($class_code);
		$this->assign('subjectinfolist' , $subjectinfolist);
		$this->assign('class_code' , $class_code);
		
		$this->display('achievementdaw');
	}


	//我的成绩统计曲线图
	function myexamscoredaw(){
		//验证是否是当前登录用户问题：
		$account = $this->getCookieAccount();
		if($this->user['client_type']==CLIENT_TYPE_FAMILY){
			$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
			$reInfo = $mFamilyRelation->getFamilyRelationByFamilyUid($account);
			$account_info = array_shift($reInfo[$account]);
			$account=$account_info['client_account'];
		}
		$class_code = $this->checkclasscode($class_code);

		$subject_id = $this->objInput->getStr('subjectid');
		$sdate = $this->objInput->getStr('sdate');
		$edate = $this->objInput->getStr('edate');
		$classinfolist = $this->user['class_info'];
	    $schoollist = $this->user['school_info'];

		//获取科目名称
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		$subjectinfolist = $mSubjectInfo->getSubjectInfoById($subject_id);
		
		$mUser = ClsFactory::Create('Model.mUser');	
		$login_info = $mUser->getUserByUid($account);
		$user_client_name = $login_info[$account]['client_name'];


		$firter = $subject_id.",".$sr_exam_name.",".$sdate.",".$edate;
		$arrmExamInfo = array();
		$mExamInfo = ClsFactory::Create('Model.mExamInfo');
		$mExamInfoList = $mExamInfo->getExamInfoByClassCode($class_code,$firter);
		$mStudentScoreInfo = ClsFactory::Create('Model.mStudentScore');

		// 合并数据
		foreach ($mExamInfoList as $key => $value) {
			$mStudentScoreInfoList = "";
			$mStudentScoreInfoList = $mStudentScoreInfo->getStudentScoreByExamIdAccount($value["exam_id"],$account);
			$mStudentScoreInfoList = array_shift($mStudentScoreInfoList);
			if($mStudentScoreInfoList) { 
				$arrmExamInfo[$key] = array_merge($value , $mStudentScoreInfoList);
			}
		}
		
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		if($arrmExamInfo){
			foreach ($arrmExamInfo as $key => $value) {
				$subjectinfolist = $mSubjectInfo->getSubjectInfoById($value['subject_id']);
				$arrmExamInfo[$key]['subject_name'] = $subjectinfolist[$value['subject_id']]['subject_name'];

				$set_exam_id = $arrmExamInfo[$key]['exam_id'];
				$set_exam_score= "<td height='30' align='center'>".$arrmExamInfo[$key]['exam_score']."</td>";
				$Value_exam_score= $arrmExamInfo[$key]['exam_score'];
				$total_Value_exam_score=="" ? $total_Value_exam_score = $Value_exam_score : $total_Value_exam_score = $total_Value_exam_score.",".$Value_exam_score;

				$set_exam_name = "<td height='30' align='center'>".$arrmExamInfo[$key]['exam_name']."</td>";
				
				$Value_exam_name= $arrmExamInfo[$key]['exam_name'];
				$total_Value_exam_name=="" ? $total_Value_exam_name = $Value_exam_name : $total_Value_exam_name = $total_Value_exam_name.",".$Value_exam_name;
	
				$total_exname_td=="" ? $total_exname_td = $set_exam_name : $total_exname_td = $total_exname_td.$set_exam_name;
				$total_exam_score=="" ? $total_exam_score = $set_exam_score : $total_exam_score = $total_exam_score.$set_exam_score;
			
				$examinfoarr = $mExamInfo->getExamInfoById($arrmExamInfo[$key]['exam_id']);
				$examinfolist = $examinfoarr[$arrmExamInfo[$key]['exam_id']];
			    if(isset($examinfolist['student_score']) && !empty($examinfolist['student_score'])) {
					$studentscorelist = $examinfolist['student_score'];
					foreach($examinfolist['student_score'] as $key1=>&$val1){
						if($val1['exam_score']== -1){
							unset($examinfolist['student_score'][$key1]);
						}
					}
					$examstudentCount = count($examinfolist['student_score']);
					unset($examinfolist['student_score']);
				}

				//统计学生成绩信息
				if(!empty($studentscorelist)) {
					$jgnums = $yxnums = $totalnums = $totalscores = 0;
					$nums1=0;$nums2=0;$nums3=0;$nums4=0;$nums5=0;
					foreach($studentscorelist as $uid=>$studentscore) {
						$score = $studentscore['exam_score'];
						if($score != '0' && $score != '-1'){
							$totalscores += $score;
						}
						$totalnums = $nums1 + $nums2 + $nums3 + $nums4 + $nums5;
					}
					
				}
				//echo round($totalscores / $examstudentCount , 2);
				$set_av_score = "<td height='30' align='center'>".round($totalscores / $examstudentCount , 2)."</td>";
				$total_av_score=="" ? $total_av_score = $set_av_score : $total_av_score = $total_av_score.$set_av_score;

				$Value_av_score= round($totalscores / $examstudentCount , 2);
				$total_Value_av_score=="" ? $total_Value_av_score = $Value_av_score : $total_Value_av_score = $total_Value_av_score.",".$Value_av_score;
			}

		}

		$outVar =  "<table width='640' border='0' align='center' cellpadding='5' cellspacing='0'>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td width='100'>".$user_client_name."</td>";
			$outVar = $outVar. "<td width='100'>科目：".$subjectinfolist[$subject_id]['subject_name']."</td>";
			$outVar = $outVar. "<td align='center'>考试时间：".$sdate."---".$edate."</td>";
		 $outVar = $outVar. " </tr>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td colspan='3'>";
			$outVar = $outVar. "<table width='100%' border='0' cellpadding='0' cellspacing='1' bgcolor='#dddddd'>";
			  $outVar = $outVar. "<tr bgcolor='#efefef'>";
				$outVar = $outVar. "<td height='30'>&nbsp;</td>";
				$outVar = $outVar. "<td rowspan='3' valign='top'><table width='100%' border='0' cellpadding='0' cellspacing='0'>";
				  $outVar = $outVar. "<tr>". $total_exname_td. "</tr>";
				  $outVar = $outVar. "<tr>". $total_exam_score. "</tr>";
				  $outVar = $outVar. "<tr>". $total_av_score. "</tr>";
				$outVar = $outVar. "</table></td>";
			  $outVar = $outVar. "</tr>";
			  $outVar = $outVar. "<tr>";
				$outVar = $outVar. "<td height='30' align='center'>".$user_client_name."</td>";
				$outVar = $outVar. "</tr>";
			  $outVar = $outVar. "<tr>";
				$outVar = $outVar. "<td height='30' align='center'>班级平均分</td>";
				$outVar = $outVar. "</tr>";
			$outVar = $outVar. "</table></td>";
		  $outVar = $outVar. "</tr>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td colspan='2'>图列：我的分数<hr style='width:30px;color:#000075'></td> <td><hr style='width:30px;color:#FF00FF'>班级平均分</td>";
		  $outVar = $outVar. "</tr>";
		  $outVar = $outVar. "<tr>";
			$outVar = $outVar. "<td colspan='3'><iframe src='/Homeclass/Class/myexamscoredawdata?ksname=".urlencode($total_Value_exam_name)."&kscore=".$total_Value_exam_score."&avscore=".$total_Value_av_score."' scrolling='no' frameborder='0' width='600' height='350' style='padding:0px;margin:0px;'></iframe></td>";
		  $outVar = $outVar. "</tr>";
		$outVar = $outVar. "</table>";
		echo $outVar;
		 unset($examinfoarr);
		
	}


	//我的成绩统计
	function achievement(){
		$account = $this->getCookieAccount();
		$class_code = $this->checkclasscode($class_code);
		if($this->user['client_type']==CLIENT_TYPE_FAMILY){
			$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
			$reInfo = $mFamilyRelation->getFamilyRelationByFamilyUid($account);
			$account_info = array_shift($reInfo[$account]);
			$account=$account_info['client_account'];
		}
		$classinfolist = $this->user['class_info'];
	    $schoollist = $this->user['school_info'];
		
	    $pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=15; //每页数量
	    
		//搜索查找
		 $sr_subject_id = $this->objInput->postStr('subject_id');
		 $sr_exam_name = $this->objInput->postStr('exam_name');
		 $sr_exam_date = $this->objInput->postStr('exam_date');
		 $end_exam_date = $this->objInput->postStr('end_exam_date');
		
	    //获取对应的学校id并检测数据的正确性
	    $schoolid = $classinfolist[$class_code]['school_id'];
		
	    if(!isset($schoollist[$schoolid])) {
	        $schoolid = 0;
			exit;
	    }
    	//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
    	 $subjectinfolist = $this->getTeacherSubjectList($class_code);
    	
		//首先读取考试信息；参数当前用户所在学校ID 班级ID
		$firter = $sr_subject_id.",".$sr_exam_name.",".$sr_exam_date.",".$end_exam_date;
		$arrmExamInfo = array();
		$mExamInfo = ClsFactory::Create('Model.mExamInfo');
		$mExamInfoList = $mExamInfo->getExamInfoByClassCode($class_code,$firter);
		$mStudentScoreInfo = ClsFactory::Create('Model.mStudentScore');

		// 合并数据
		foreach ($mExamInfoList as $key => $value) {
			$mStudentScoreInfoList = "";
			$mStudentScoreInfoList = $mStudentScoreInfo->getStudentScoreByExamIdAccount($value["exam_id"],$account);
			$mStudentScoreInfoList = array_shift($mStudentScoreInfoList);
			if($mStudentScoreInfoList) { 
				$arrmExamInfo[$key] = array_merge($value , $mStudentScoreInfoList);
			}
		}
		
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		if($arrmExamInfo){
			foreach ($arrmExamInfo as $key => $value) {
				$subjectinfolist1 = $mSubjectInfo->getSubjectInfoById($value['subject_id']);
				$arrmExamInfo[$key]['subject_name'] = $subjectinfolist1[$value['subject_id']]['subject_name'];
			}
		}
		array_multisort($arrmExamInfo, SORT_DESC);
		$i=1;
		foreach($arrmExamInfo as $key=>&$val){
			$val['id'] = $i;
			$i++;
		}
		//分页
	    $newarr_newLog = array_slice($arrmExamInfo, ($page-1)*$pagesize, $pagesize+1);	
	    
		$webUrl = "/Homeclass/Class/achievement";
		
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newarr_newLog) > $pagesize){
			array_pop($newarr_newLog);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		unset($arrmExamInfo);
		$arrmExamInfo = $newarr_newLog;
		unset($newarr_newLog);
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href=\"javascript:srsubmit('{$prvpageno}')\">上一页</a> | <a href=\"javascript:srsubmit('{$nextpageno}')\">下一页</a></div>");
		$this->assign('pageno',$pageno);
		
		$this->assign('subject_id' , $sr_subject_id);
		$this->assign('exam_name' , $sr_exam_name);
		$this->assign('exam_date' , $sr_exam_date);
		$this->assign('end_exam_date' , $end_exam_date);
		
		$this->assign('school_id' , $schoolid);
		$this->assign('class_code' , $class_code);
		$this->assign('examinfolist' , $arrmExamInfo);
		$this->assign('subjectinfolist' , $subjectinfolist);

		$this->display('sns_achievement');
	}


	/**
	 * 录入班级学生成绩
	 * todolist: 没有增加对应的D和M层
	 */

	public function writestuscore(){
	    $exam_id = trim($this->objInput->postStr('exam_id'));
	    $school_id = intval($this->objInput->postStr('school_id'));
	    $class_code = intval($this->objInput->postStr('class_code'));
		//0立即发布 1 暂存
		$subtype = intval($this->objInput->getStr('subtype'));
	    $subject_id = intval($this->objInput->postStr('subject_id'));
		$exam_name = trim($this->objInput->postStr('exam_name'));
		$exam_date = date('Y-m-d' , strtotime(trim($this->objInput->postStr('exam_date'))));
		$client_account = $this->objInput->postArr('client_account');
		$client_name = $this->objInput->postArr('client_name');
		$sendMessage = $this->objInput->postStr('sendMessage');
		$operationStrategy = $this->objInput->postInt('operationStrategy');
        $add_account =$this->user['client_account'];
		$add_date = date("Y-m-d H:i:s"  , time());
		$nocj = $this->objInput->postArr('nocj');
		
		$exam_well = $this->objInput->postInt('exam_well');
		$exam_good = $this->objInput->postInt('exam_good');
		$exam_bad = $this->objInput->postInt('exam_bad');
		$upd_account = $add_account;
		$upd_date = $add_date;
		$exam_score = $this->objInput->postArr('exam_score');
		$score_py = $this->objInput->postArr('score_py');

		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $schoolSubjectInfo = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
        $schoolSubjectInfo = & $schoolSubjectInfo[$school_id]; 
        $subjectName = $schoolSubjectInfo[$subject_id]['subject_name'];

	
		$mExamInfo = ClsFactory::Create('Model.mExamInfo');
		$mStudentScore = ClsFactory::Create('Model.mStudentScore');

		$mFeed = ClsFactory::Create('Model.mFeed');
		
		if($exam_id){
			$exam_data=array(
				'exam_id' => $exam_id,
    			'school_id' => $school_id,
    			'class_code' => $class_code,
    			'subject_id' => $subject_id,
    			'exam_name' => $exam_name,
    			'exam_date' => $exam_date,
			    'exam_well' => $exam_well,
    			'add_account' => $add_account,
    			'add_date' => $add_date,
    			'upd_account' => $upd_account,
    			'upd_date' => $upd_date,
			    'exam_good' => $exam_good,
			    'exam_bad' => $exam_bad,
				'subtype' => $subtype
			);
			$mExamInfo->modifyExamInfo($exam_data,$exam_id);
			$mFeed->addClassFeed(intval($class_code),intval($add_account), intval($exam_id), CLASS_FEED_MARK, FEED_UPD, time());

				if($client_account){
					for($i=0;$i<count($client_account);$i++){
						if($nocj[$i] == 'yes'){
							$score_data=array(
							'exam_score'=>$exam_score[$i],
							'score_py'=>$score_py[$i],
							'upd_account'=>$add_account,
							'upd_date'=>$add_date
							);
						}else{
							$score_data=array(
							'exam_score'=>'-1',
							'score_py'=>'',
							'upd_account'=>$add_account,
							'upd_date'=>$add_date
							);
						}

						$mStudentScore->modifyStudentScore($score_data,$exam_id,$client_account[$i]);
					}
					
					//成绩更新成功，返回成绩管理
					$this->showSuccess("成绩更新成功，返回成绩管理", "/Homeclass/Class/cjtmanage/class_code/$class_code");
				}else{
					//没有成绩数据，返回成绩发布
					$this->showError("没有成绩数据", "/Homeclass/Class/cjtpublish/class_code/$class_code");
				}
		//增加
		}else{
			$exam_data = array(
				'school_id' => $school_id,
				'class_code' => $class_code,
				'subject_id' => $subject_id,
				'exam_name' => $exam_name,
				'exam_date' => $exam_date,
				'add_account' => $add_account,
				'add_date' => $add_date,
				'upd_account' => $upd_account,
				'upd_date' => $upd_date,
				'exam_well' => $exam_well,
				'exam_good'=> $exam_good,
				'exam_bad' => $exam_bad,
				'subtype' => $subtype
			);
			$exam_id = $mExamInfo->addExamInfo($exam_data , true);
			if($exam_id){
				//添加到动态表
				$mFeed->addClassFeed(intval($class_code),intval($add_account), intval($exam_id), CLASS_FEED_MARK, FEED_NEW, time());
				if($client_account){
					if ($sendMessage == 'on') {//checkbox勾选上了
						$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
						$familyRelations = $mFamilyRelation->getFamilyRelationByUid($client_account);//通过family_relation表获得家长的账号信息。
						$parentAccounts = $stu_parent = array();
						foreach ($familyRelations as $stuAccount => $parent) {//获得家长账号数组。
							foreach ($parent as $parentAccount) {
								$parentAccounts[] = $parentAccount['family_account'];//家长账号数组
								$stu_parent[$stuAccount][] = $parentAccount['family_account']; //学生与家长账号的对应关系。
							}
						}
						$mBusinesphone  = ClsFactory::Create('Model.mBusinessphone');
						$phone_list = $mBusinesphone->getbusinessphonebyalias_id($parentAccounts);//通过家长账号获得business_phones
					}
					foreach ($phone_list as $uid=>$phoneInfo) {
						if ($phoneInfo['business_enable'] != BUSINESS_ENABLE_YES) {
							unset($phone_list[$uid]);
					   }
					}
					$phone_list;
					
					for($i=0;$i<count($client_account);$i++){
							$phoneArr = array();
							if($nocj[$i] == 'yes'){
								$score_data=array(
									'client_account'=>$client_account[$i],
									'exam_score'=>$exam_score[$i],
									'score_py'=>$score_py[$i],
									'add_account'=>$add_account,
									'add_date'=>$add_date,
									'exam_id'=>$exam_id
								);
							}else{
								$score_data=array(
									'client_account'=>$client_account[$i],
									'exam_score'=>'-1',
									'score_py'=>'',
									'add_account'=>$add_account,
									'add_date'=>$add_date,
									'exam_id'=>$exam_id
								);
							}
					$mStudentScore->addStudentScore($score_data);
							$parentAccount_1 = $stu_parent[$client_account[$i]][0];
							$parentAccount_2 = $stu_parent[$client_account[$i]][1];;
							if(isset($phone_list[$parentAccount_1])){
								$phoneArr[] = $phone_list[$parentAccount_1]['phone_id'];
							}
							if(isset($phone_list[$parentAccount_2])){
								$phoneArr[] = $phone_list[$parentAccount_2]['phone_id'];
							}

							if (!empty($phoneArr)) {
								$message = $client_name[$i].'的家长，您的孩子在'.$subjectName.$exam_name.'的成绩为:'.$exam_score[$i].'，';
								$message .= $score_py[$i].' 班平均分:'.'ave_score'.'，最高分'.'hscore'.'，最低分'.'lscore';
								$sendArr[] = array('sms_send_mphone' => $phoneArr , 'sms_send_content' => $message );
							}
					}//for
					
					if(!empty($sendArr)) {
						$statlist = $this -> searchexaminfo($exam_id);
						import('@.Control.Api.Smssend.Smssendapi');
                        $smssendapi_obj = new Smssendapi();
                        //70859265//18620456699
						foreach ($sendArr as $key =>$send){
							$sms_send_content = str_replace('ave_score',$statlist['ave_score'],$send['sms_send_content']); //替换平均分
							$sms_send_content = str_replace('hscore',$statlist['hscore'],$sms_send_content); //替换最高分
							$sms_send_content = str_replace('lscore',$statlist['lscore'],$sms_send_content); //替换最低分
							$addSmsSendResult = $smssendapi_obj->send($send['sms_send_mphone'], $sms_send_content, $operationStrategy);
							if (!$addSmsSendResult) {//upd smssend
        	                    $this->showError("短信通知家长失败!", "/Homeclass/Class/cjtmanage/class_code/$class_code");
							}
						}

					}
					//成绩录入成功，返回成绩管理
				    $this->showSuccess("成绩录入成功，返回成绩管理", "/Homeclass/Class/cjtmanage/class_code/$class_code");
				}else{
					//没有成绩数据，返回成绩发布
                    $this->showError("没有成绩数据，返回成绩发布", "/Homeclass/Class/cjtpublish/class_code/$class_code");
				}

			}

		}

	}

	/**
	 * 通过班级编号获取班级的科目信息
	 * 如果已经拿到了教师的账号信息就不要调用该方法
	 * @param $class_code
	 */
	private function getTeacherSubjectList($class_code) {
	    if(empty($class_code)) {
	        return false;
	    }
	     //获取班级老师的科目信息
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>CLIENT_TYPE_TEACHER));
	    $clientclasslist = & $clientclassarr[$class_code];
	    if(!empty($clientclasslist)) {
	        $teacheruids  = array_unique(array_keys($clientclasslist));
	        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($teacheruids, $class_code);
	    }

	    return !empty($subjectinfolist) ? $subjectinfolist : false;
	}


	/**
	 * 显示作业信息列表
	 */
	public function showhomework() {
	     $class_code = $this->objInput->getInt('class_code');
	    //作业管理搜索
	    if(strtoupper($_SERVER['REQUEST_METHOD']) == "POST") {
	        $subject_id = $this->objInput->postInt('subject_id');
	        $expiration_date = $this->objInput->postStr('expiration_date');
	    } else {
	        $subject_id = $this->objInput->getInt('subject_id');
	        $expiration_date = $this->objInput->getStr('expiration_date');
	        $sortby = $this->objInput->getStr('sortby');
	    }
	    
	    $pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=15; //每页数量

	    //检测对应的班级信息是否正确
	    $class_code = $this->checkUrl();

	    //获取班级的基本老师信息
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
		$clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type' => array(CLIENT_TYPE_TEACHER)));
		$clientclasslist = & $clientclassarr[$class_code];

		//老师科目
		$teacheruids = array_keys($clientclasslist);
		//判断当前用户的权限
		if(empty($teacheruids) || (!empty($teacheruids) && !in_array($this->user['client_account'] , $teacheruids))) {
		    $this->showError("您不是该班的任课教师", "/Homeclass/Myclass/index/class_code/$class_code");
		    exit;
		}
        //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
		if($current_class_info['class_admin']==IS_CLASS_ADMIN){
			$is_class_admin = true;
		}elseif($current_class_info['teacher_class_role']==1 || $current_class_info['teacher_class_role']==3){
			$is_class_admin = true;
		}else{
			$is_class_admin = false;
		}
		//根据权限判断当前用户能够管理的科目信息,$anlicheng修改科目信息一对多
		if($is_class_admin) {
		    $subjectinfolist = $this->getTeacherSubjectList($class_code);
		} else {
		    //todochecked
		    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($this->user['client_account'], $class_code);
		}

		//处理相关的科目信息
		$subjectid_arr = array();
		//todochecked
		if(!empty($subjectinfolist)) {
		    foreach($subjectinfolist as $key=>$subject) {
		        $tmp_subject_id = intval($subject['subject_id']);
		        //统计涉及到的科目id信息
		        $subjectid_arr[] = $tmp_subject_id;
		        if($tmp_subject_id == $subject_id) {
		            $subject['selected'] = true;
		        }
		        $subjectinfolist[$key] = $subject;
		    }
		    $subjectid_arr && $subjectid_arr = array_unique($subjectid_arr); 
		}
		
		//校验subject_id的值
		if(!empty($subjectid_arr) && $subject_id) {
		    $subject_id = in_array($subject_id , $subjectid_arr) ? $subject_id : array_shift($subjectid_arr);
		} else {
		    $subject_id = 0;
		}
		$subject_id = !empty($subject_id) ? intval($subject_id) : 0;
		//搜索过滤条件拼装
	    $filters = array();
	    if($is_class_admin) {
	        $filters =  array(
	            'news_type' => NEWS_INFO_BJZY,
	            'subject_id' => $subject_id,
	        );
	    } else {
	        $add_account = $this->user['client_account'];
	        $filters =  array(
	        	'news_type' => NEWS_INFO_BJZY,
	            'subject_id' => $subject_id,
	            'add_account' => $add_account,
	        );
	    }
	    //处理是否提交科目过滤信息
	    if(empty($filters['subject_id']) && isset($filters['subject_id'])) {
	        unset($filters['subject_id']);
	    }
	    //获取作业信息列表
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $homework_arr = $mNewsInfo->getNewsInfoByClassCode($class_code , $filters);
	    $homework_list = & $homework_arr[$class_code];
	    //数据的有效期过滤
	    if(!empty($homework_list)) {
	        //获取当前比较的时间点
	        if (!empty($expiration_date)) { 
		        $base_time = !empty($expiration_date) ? strtotime($expiration_date) : 0;
		        $base_time = date("Y-m-d" , $base_time);
		        foreach($homework_list as $key=>$news) {
		            $add_time = !empty($news['add_date']) ? array_shift(explode(" " , trim($news['add_date']))) : 0;
		            $expiration_time = !empty($news['expiration_date']) ? array_shift(explode(" " , trim($news['expiration_date']))) : 0;
		            if((!empty($expiration_time) && $expiration_time < $base_time) || (!empty($add_time) && $add_time > $base_time)) {
		                unset($homework_list[$key]);
		            }
		        }
	        }
	        //数据排序，按照时间降序排列
	        if(!empty($homework_list)) {
	            foreach($homework_list as $key=>$news) {
	                $sortkeys[$key] = $news['add_date'];
	            }
	            array_multisort($sortkeys , SORT_DESC , $homework_list);
	        }
	    }
	    
	    //分页
	    $newarr_newLog = array_slice($homework_list, ($page-1)*$pagesize, $pagesize+1);	
	    
		$webUrl = "/Homeclass/Class/showhomework/class_code/{$class_code}";
		
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newarr_newLog) > $pagesize){
			array_pop($newarr_newLog);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		unset($homework_list);
		$homework_list = $newarr_newLog;
		unset($newarr_newLog);
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href=\"javascript:srsubmit('{$class_code}','{$prvpageno}')\">上一页</a> | <a href=\"javascript:srsubmit('{$class_code}','{$nextpageno}')\">下一页</a></div>");
		$this->assign('pageno',$pageno);
		 
        //追加作业的相关信息
        if(!empty($homework_list)) {
            //获取科目名称相关的信息
            $homework_subjectinfolist = array();
            if(!empty($subjectinfolist)) {
                foreach($subjectinfolist as $subject) {
                    $homework_subjectinfolist[$subject['subject_id']] = $subject;
                }
            }
            
            $now_date = date("Y-m-d" , time());
            foreach($homework_list as $news_id=>$news) {
                //数据加密，防止用户随意修改其他作业信息
                $news['homework_hash'] = $this->getHomeworkMd5($news['news_id']);
                $news['subject_name'] = isset($homework_subjectinfolist[$news['subject_id']]) ? $homework_subjectinfolist[$news['subject_id']]['subject_name_short'] : "未知科目";
                $news['add_date'] = date("Y-m-d" , strtotime($news['add_date']));

                //获取有效的时间格式
                if(empty($news['expiration_date']) && ($expiration_time = strtotime($news['expiration_date'])) === false) {
                    $expiration_date = false;
                }
                if(empty($expiration_date)) {
                    $news['expiration_date'] = "长期有效";
                    $news['status_val'] = "正常";
                } else {
                    $news['expiration_date'] = $expiration_date;
                    if($expiration_date >= $now_date) {
                        $news['status_val'] = "正常";
                    } else {
                        $news['status_val'] = "已过期";
                        $news['status_tag'] = 1;
                    }
                }
                $news['news_content'] = cutstr(strip_tags(nl2br($news['news_content'])) , 80 , true);
                $homework_list[$news_id] = $news;
            }
        }
       
	    $this->assign('class_code' , $class_code);
	    $this->assign('is_class_admin' , $is_class_admin);
	    $this->assign('expiration_date' , $expiration_date);
	    $this->assign('subjectinfolist' , $subjectinfolist);
	    $this->assign('homework_list' , $homework_list);
	    //$this->assign('pagelist' , $pagelist);
	    //$this->assign('sortbylist' , $sortbylist);
	    $this->display("showhomework");
	}
	/**
	 * 获取作业信息的MD5
	 * @param $news_id 作业id
	 */
	private function getHomeworkMd5($news_id) {
	    $md5_key = md5($this->user['client_account'] . $news_id . substr(strval(time()) , 0 , 6));
	    return substr($md5_key , 0 , 16);
	}

	/**
	 * 拼装查询条件
	 * @param unknown_type $dataarr
	 */
	private function http_build_url($dataarr = array()) {
	    if(empty($dataarr)) {
	        return "";
	    }
	    $str = "";
	    foreach((array)$dataarr as $key=>$val) {
	        $str .= "/$key/$val";
	    }
	    return !empty($str) ? $str : "";
	}

	/**
	 * 获取教师的科目信息并合并
	 * @param $uids
	 * @param $split
	 */
	private function getSubjectInfoByTeacherUid($uids, $class_code, $split = " ") {
	      if(empty($uids)) {
	          return false;
	      }
	      $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	      $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUid($uids, $class_code);
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
	
	

	
	public function showStudentwork(){
		$class_code = key($this->user['class_info']);
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=15; //每页数量

	    //获取作业信息列表
	    $filters = array('news_type'=>'BJZY');
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $homework_arr = $mNewsInfo->getNewsInfoByClassCode($class_code , $filters);
	    $homework_list = & $homework_arr[$class_code];
	    //数据的有效期过滤
	    if(!empty($homework_list)) {
	        //获取当前比较的时间点
//	        $base_time = !empty($expiration_date) ? strtotime($expiration_date) : time();
//	        $base_time = date("Y-m-d" , $base_time);
//	        foreach($homework_list as $key=>$news) {
//	            $add_time = !empty($news['add_date']) ? array_shift(explode(" " , trim($news['add_date']))) : 0;
//	            $expiration_time = !empty($news['expiration_date']) ? array_shift(explode(" " , trim($news['expiration_date']))) : 0;
//	            if((!empty($expiration_time) && $expiration_time < $base_time) || (!empty($add_time) && $add_time > $base_time)) {
//	                unset($homework_list[$key]);
//	            }
//	        }
	        //数据排序，按照时间降序排列
	        if(!empty($homework_list)) {
	            foreach($homework_list as $key=>$news) {
	                $sortkeys[$key] = $news['add_date'];
	            }
	            array_multisort($sortkeys , SORT_DESC , $homework_list);
	        }
	    }
	    //分页
	    $newarr_newLog = array_slice($homework_list, ($page-1)*$pagesize, $pagesize+1);	
	    
		$webUrl = "/Homeclass/Class/showStudentwork";
		$pageCount = ceil(count($homework_list)/$pagesize);
		
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newarr_newLog) > $pagesize){
			array_pop($newarr_newLog);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		unset($homework_list);
		$homework_list = $newarr_newLog;
		unset($newarr_newLog);
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
		$this->assign('pageno',$pageno);
	    
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		$school_id = key($this->user['school_info']);
		$subjectinfolist = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
		$subjectinfolist = & $subjectinfolist[$school_id];
		
        //追加作业的相关信息
        if(!empty($homework_list)) {
            //获取科目名称相关的信息
            $homework_subjectinfolist = array();
            if(!empty($subjectinfolist)) {
                foreach($subjectinfolist as $subject) {
                    $homework_subjectinfolist[$subject['subject_id']] = $subject;
                }
            }
            $now_date = date("Y-m-d" , time());
            foreach($homework_list as $news_id=>$news) {
                //数据加密，防止用户随意修改其他作业信息
                $news['homework_hash'] = $this->getHomeworkMd5($news['news_id']);
				$news['subject_name'] = $subjectinfolist[$news['subject_id']]['subject_name'];
                $news['subject_name'] = isset($homework_subjectinfolist[$news['subject_id']]) ? $homework_subjectinfolist[$news['subject_id']]['subject_name'] : "未知科目";
                
				$news['add_date'] = date("Y-m-d" , strtotime($news['add_date']));


                //获取有效的时间格式
                if(!empty($news['expiration_date']) && ($expiration_time = strtotime($news['expiration_date'])) !== false) {
                    $expiration_date = date("Y-m-d" , $expiration_time);
                } else {
                    $expiration_date = false;
                }
                if(empty($expiration_date)) {
                    $news['expiration_date'] = "长期有效";
                    $news['status_val'] = "正常";
                } else {
                    $news['expiration_date'] = $expiration_date;
                    if($expiration_date >= $now_date) {
                        $news['status_val'] = "正常";
                    } else {
                        $news['status_val'] = "已过期";
                        $news['status_tag'] = 1;
                    }
                }
                $news['news_content'] = cutstr(strip_tags(nl2br($news['news_content'])) , 80 , true);
                $homework_list[$news_id] = $news;
            }
        }
		
	    $this->assign('class_code' , $class_code);
	    $this->assign('expiration_date' , !empty($expiration_date) ? $expiration_date : date("Y-m-d" , time()));
	    $this->assign('subjectinfolist' , $subjectinfolist);
	    $this->assign('homework_list' , $homework_list);

		$this->display('showStudentwork');
	}


	function hoemworkview(){
		$workid = $this->objInput->getInt('workid');
		$class_code = $this->objInput->getInt('class_code');
	    //获取作业信息列表
		
		$mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
		$workresult = $mNewsInfo->getNewsInfoById($workid);
		$workresult = array_shift($workresult);
		$mUser = ClsFactory::Create('Model.mUser');
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		$subjectinfolist = $mSubjectInfo->getSubjectInfoById($workresult['subject_id']);

		$userlist = $mUser->getUserBaseByUid($workresult['add_account']);
		$client_name= $userlist[$workresult['add_account']]['client_name'];

		$this->assign('work_id',$workresult['news_id']);
		$this->assign('client_name',$client_name);
		$this->assign('work_date',date('Y-m-d',strtotime($workresult['add_date'])));
		$this->assign('work_expiration_date',date('Y-m-d',strtotime($workresult['expiration_date'])));
		$this->assign('work_subject_name',$subjectinfolist[$workresult['subject_id']]['subject_name']);
		$news_contentValue = $workresult['news_content'];
		$news_contentValue = WmwString::unhtmlspecialchars($news_contentValue);
		$this->assign('work_content',$news_contentValue);

		if($workresult['attachment']!=""){
			$filename = $workresult['attachment'];
			$this->assign('filename',$filename);
			$this->assign('encodefilename',base64_encode($filename));
		}
		
		$this->assign('class_code',$class_code);

		$this->display('hoemworkview');
	}

	function downloaddoc(){
		header("content-Type: text/html; charset=Utf-8"); 
		$file_name = $this->objInput->getStr('file');
		$file_name = base64_decode($file_name);
		$pathfilename = Pathmanagement_sns::uploadHomeWork() . $file_name;
		$down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
        $down_file->downfile($pathfilename);
	}
	
	/*班级成员*/
	function classStudents(){
		$class_code = key($this->user['client_class']);
	    $class_code = $this->checkclasscode($class_code);
	    $teacherlist = $studentlist = $familylist = array();
	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code);
	    $clientclasslist = $clientclassarr[$class_code];
	    unset($clientclassarr);

	    //获取老师和家长的相关信息
	    $schooluids = array();
	    if(!empty($clientclasslist)) {
	        foreach($clientclasslist as $clientclass) {
	            $clienttype = intval($clientclass['client_type']);
	            $uid = $clientclass['client_account'];
	            if($clienttype == CLIENT_TYPE_STUDENT) {
	                $studentlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_TEACHER) {
	                $teacherlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_FAMILY) {
	                $familylist[$uid] = $uid;
	            }
	        }
	        unset($clientclasslist);
	    }
		

	    //获取老师和学生的基本信息
	    if(!empty($schooluids)) {
	        $schooluids = array_unique($schooluids);
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($schooluids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
                    if(isset($studentlist[$uid])) {
                        $studentlist[$uid] = array_merge($user , $studentlist[$uid]);
                    }elseif(isset($teacherlist[$uid])) {
                        $teacherlist[$uid] = array_merge($user , $teacherlist[$uid]);
                    }
	            }
	        }
	        unset($userlist , $schooluids);
	    }
		
	
	    $studentusers = array_keys($studentlist);
			
	    //获取学生的家长信息
	    if(!empty($studentusers)) {
	        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	        $familyrelationlist = $mFamilyRelation->getFamilyRelationByUid($studentusers);
	        if(!empty($familyrelationlist)) {
	            foreach($familyrelationlist as $stu_uid=>$relationlist) {
	                $defaultfamilyaccount = 0;
	                if(isset($studentlist[$stu_uid]) && !empty($relationlist)) {
	                    foreach($relationlist as $relation) {
	                        $familyaccount = $relation['family_account'];
	                        //检测家长的账号是否存在
	                        if(!empty($familyaccount) && isset($familylist[$familyaccount])) {
                                $defaultfamilyaccount = $familyaccount;
	                            break;
	                        }
	                    }
	                }
	                $studentlist[$stu_uid]['family_account'] = $defaultfamilyaccount;
					
	            }
	        }
			
	        unset($familylist);
	    }

	    //获取教师对应的科目信息,$anlicheng教师科目一对多的修改
	    if(!empty($teacherlist)) {
	        $teacheruids = array_keys($teacherlist);
            //todochecked
	        $subjectinfolist = $this->getSubjectInfoByTeacherUid($teacheruids, $class_code, "、");
	        if(!empty($subjectinfolist)) {
    	        foreach($teacherlist as $uid=>$teacher) {
    	            $teacher['subject_info'] = isset($subjectinfolist[$uid]) ? $subjectinfolist[$uid] : false;
    	            $teacherlist[$uid] = $teacher;
    	        }
	        }
	    }

	    $this->assign('studentlist' , $studentlist);
	    $this->assign('teacherlist' , $teacherlist);
	    $this->assign('leadertype' , Constancearr::classleader());


		$school_id = $this->user['class_info'][key($this->user['client_class'])]['school_id'];
		$school_name = $this->user['school_info'][$school_id]['school_name'];
		$this->assign('tpl_school_Name',$school_name);
		$this->assign('tpl_gradeclass_Name',$this->user['class_info'][$class_code]['class_name']);
		$this->assign('tpl_grade_id_name',$this->user['class_info'][$class_code]['grade_id_name']);
		$this->assign('tpl_headteacher_account',$this->user['class_info'][$class_code]['headteacher_account']);

		$this->display('classStudents');


	}



	/*班级公告*/
	function Announcement(){
		$class_code = key($this->user['client_class']);
		$class_code = $this->checkclasscode($class_code);
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=15; //每页数量
	    //获取班级下面的公告信息
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJTG)));
	    //数据较大，引用传值，避免内存瞬间过大
	    $newsinfo_list = & $newsinfoarr[$class_code];
	    array_multisort($newsinfo_list , SORT_DESC);
	    
	    $newarr_newLog = array_slice($newsinfo_list, ($page-1)*$pagesize, $pagesize+1);	
		$webUrl = "/Homeclass/Class/Announcement";
		$pageCount = ceil(count($newsinfo_list)/$pagesize);
		
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newarr_newLog) > $pagesize){
			array_pop($newarr_newLog);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
		$this->assign('pageno',$pageno);
		//print_r($newsinfo_list);
		$this->assign('classnoticelist',$newarr_newLog);
		$this->assign('class_code',$class_code);

		$this->display('Announcement');
	}


	//公告查看
	function AnnouncementView(){

		$class_code = $this->objInput->getInt('class_code');
		$loginUserClassCode = key($this->user['client_class']);

		$newsid = $this->objInput->getInt('newsid');
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
		$mNewsInfoArr = $mNewsInfo->getNewsInfoById($newsid);
		$mNewsInfoArr = & $mNewsInfoArr[$newsid];
		
		//发布人信息
		$add_account = $mNewsInfoArr['add_account'];
		$mClientClass = ClsFactory::Create('Model.mClientClass');
		$addUserInfos = $mClientClass->getClientClassByUid($add_account);
		$addUserInfo = $addUserInfos[$add_account][$class_code];
		$mUser = ClsFactory::Create('Model.mUser');
		$ReturnClientinfo = $mUser->getUserBaseByUid($add_account);
		if ($ReturnClientinfo) { 
			$ReturnClientinfo = $ReturnClientinfo[$add_account];
			$add_client_name = $ReturnClientinfo['client_name'];
		}
		$addUserClassCode = $addUserInfo['class_code'];
		$teacher_class_role = $addUserInfo['teacher_class_role'];
		switch ($teacher_class_role) {
			case TEACHER_CLASS_ROLE_CLASSADMIN :
				$tearch_rols_name = "班主任";
				break;
			default:
				$tearch_rols_name = "老师";
				break;
		}

		$this->assign('client_type',$this->user['client_type']);
		$this->assign('mNewsInfolist',$mNewsInfoArr);
		$this->assign('addClientInfo',$add_client_name."(".$tearch_rols_name.")");
		$this->assign('class_code',$class_code);
		
		$this->display('AnnouncementView');
	}


//SNS 班级日志********************************************************************************

	function classJournal() {
		$log_account=$this->getCookieAccount();
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');
		
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$mUser = ClsFactory::Create('Model.mUser');

		//查询数据的总条数
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=7; //每页数量
	
		$classLogData = $mClasslog->getLogInfoByClassCode($class_code);
		$classLogData = $classLogData[$class_code];
		if($classLogData){
			$sortkeys = array();
			$newClassLogData = array();
			foreach ($classLogData as $classkey=>$classLogInfo) {
				$LogInfo = $mOjbectData->getPersonLogsById($classLogInfo['log_id']);
				if($LogInfo){
					$LogInfo = array_shift($LogInfo);
					$log_plun_count=$this->pluncontent($LogInfo['log_id']);
					$classLogInfo['log_name']=$LogInfo['log_name'];
					$classLogInfo['add_date']=$LogInfo['add_date'];
					$classLogInfo['add_account']=$LogInfo['add_account'];
					$classLogInfo['upd_date']=$LogInfo['upd_date'];
					$classLogInfo['read_count']=$LogInfo['read_count'];
					$classLogInfo['plun_count']=$log_plun_count;

					$logcontent = str_replace('&nbsp;','',WmwString::unhtmlspecialchars($LogInfo['log_content']));
					$logcontent = str_replace('<P></P>','',$logcontent);
					$classLogInfo['log_contentall']= strip_tags(cutstr(WmwString::unhtmlspecialchars($logcontent), 100, true));

					$mcInfo = $mUser->getUserBaseByUid($LogInfo['add_account']);
				
					$mcInfo = array_shift($mcInfo);
					$classLogInfo['headimg']=Pathmanagement_sns::getHeadImg($LogInfo['add_account']) . $mcInfo['client_headimg'];
					$classLogInfo['client_name']=$mcInfo['client_name'];


					$newClassLogData[] = $classLogInfo;
				}
			}
			
		}


		//排序日记
		array_multisort($newClassLogData ,SORT_DESC);
		
		$newarr_newLog = array_slice($newClassLogData, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeclass/Class/classJournal/class_code/".$class_code;
		$pageCount = ceil(count($newClassLogData)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newClassLogData) > $pagesize){
		$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");

		}

		$this->assign('mylog_list',$newarr_newLog);
		$this->findLogTypeData($log_account);
		$this->assign('uid',$this->getCookieAccount());
		$this->assign('pagecount',$pagecount);
		$this->assign('class_code',$class_code);

		$this->assign('log_account',$log_account);
		$this->assign('client_name',$this->user[client_name]);
		$this->assign('account' , $log_account);
		$this->assign('actionUrl',"/Homeclass/Class/classJournal/");
		//$show=$Page->show("/Homeclass/Class/classJournal/class_code/".$class_code."?");//显示分页
		$this->assign('pageinfo',$show);
		
		$this->display('sns_classJournal');
	}


	//查出关于每个日志的评论内容 2012-3-20 by lyt:
	public function pluncontent($log_id){
		$mLogplun = ClsFactory::Create('Model.mLogplun');
				
		return $mLogplun->getLogplunCountByLogid($log_id);
	}


	//日志类型公共方法
	public function findLogTypeData($log_account){
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		
		$result = $mLogtypes->getLogTypesByAddaccount($log_account);
		if(!$result){
			$this->adddefaultlogtype($log_account[$log_account]);
		}
		$this->assign('type_list',$result);
	}

	//添加系统默认日志类型
	function adddefaultlogtype($log_account){
		$data=Array(
			'logtype_name'=>'系统个人日志',
			'add_account'=>$log_account,
			'log_create_type'=>LOG_SYS_CREATE,
			'add_date'=>date("Y-m-d H:i:s",time())
		);
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		return $mLogtypes->addLogTypes($data);
	}
	
	//日志类型公共方法 2012-3-20 by lyt:
	public function log_type_do($log_account){
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		$result = $mLogtypes->getLogTypesByAddaccount($log_account);
		if(!$result){
			$this->adddefaultlogtype($log_account[$log_account]);
		}
		$this->assign('type_list',$result);
	}


	//班级日志查看 2012-3-20 by lyt:

	function classJournalview(){
		$log_account = $this->objInput->getInt('log_account');
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$log_id = $this->objInput->getInt('log_id');
		$log_id = $log_id > 0 ? $log_id : 0;
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$mClientClass = ClsFactory::Create('Model.mClientClass');
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$falgJournal = 0;
		$classLogData = $mClasslog->getClassLogByLogId($log_id);
		$classLogData = $classLogData[$log_id];
		if(!$class_code){
			$classLogData = array_shift($classLogData);
			$class_code = $classLogData['class_code'];
		}
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
		//获取日志的基本信息
		if($log_id) {
			$log_list = $mOjbectData->getPersonLogsById($log_id);
		    if(!empty($log_list) && is_array($log_list)) {
		        foreach($log_list as $log) {
		            if($log['log_id'] == $log_id) {
		                $log_info = $log;
		                $mUser = ClsFactory::Create('Model.mUser');
						$client_info =$mUser->getUserBaseByUid($log['add_account']);
						$client_info = array_shift($client_info);
						$log_info['log_contentall']=WmwString::unhtmlspecialchars($log_info['log_content']);
						$log_info['headimg']=$client_info['client_headimg_url'];
						$log_info['client_name']=$client_info['client_name'];
		                break;
		            }
		        }
				
		        unset($log_list);
		    }
		}
    	if(!empty($log_info)) {
			$log_account = intval($log_info['add_account']);
			$datas = array(
			    'read_count'=>"%read_count+1%"
			);
			$mOjbectData->modifyPersonLogs($datas, $log_id); //日志浏览 数加1
			
            //获取日志评论信息
			$mLogplun = ClsFactory::Create('Model.mLogplun');
			
			//$new_plun_list = $mLogplun->getLogplunByLogid($log_id);
			$new_plun_arr = $mLogplun->getLogplunByLogid($log_id);
    		$new_plun_list = & $new_plun_arr[$log_id];
    		
    		$icount = !empty($new_plun_list) ? count($new_plun_list) : 0;

    		//追加评论的用户信息
    		if(!empty($new_plun_list)) {
    		    
				$faceSearch=$faceReplace=array();
				$facelist = Constancearr::getfacelist();

				$uidarr = array();
    		    foreach($new_plun_list as $plun) {
    		        $uidarr[] = $plun['add_account'];
    		    }
    		    $uidarr = array_unique($uidarr);
    		    $mUser = ClsFactory::Create('Model.mUser');
    		    $userlist = $mUser->getUserBaseByUid($uidarr);
    		    foreach($new_plun_list as $key=>$plun) {
    		        $uid = $plun['add_account'];
					if(isset($userlist[$uid])) { 
    		            $plun['client_name'] = $userlist[$uid]['client_name'];
						$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
						$plun['plunheadimg']=Pathmanagement_sns::getHeadImg($uid) . $userlist[$uid]['client_headimg'];
					}
    		        $new_plun_list[$key] = $plun;

    		    }
    		    unset($userlist , $uidarr);
				foreach($new_plun_list as $plun_id => $logplun){
				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$new_plun_list[$plun_id]['plun_content'] = str_replace($faceSearch, $faceReplace, $new_plun_list[$plun_id]['plun_content']);
				}	
				}


    		}
        } else {
            //如果存在则退回到相应的好友日志列表
            $backto_account = $log_account ? $log_account : $this->user['client_account'];
	        $this->showError("您访问的日志不存在或者已经删除", "/Homeclass/Class/classJournal/class_code/$class_code");
		}
		$this->assign('falgJournal' , $this->findClassAdminClassRols($log_id,"log",$class_code));
		$this->assign('plun_list' , $new_plun_list);
		$this->assign('log_id' , $log_id);
		$this->assign('class_code' , $class_code);
		$this->assign('upload_latterbg' , IMG_SERVER.'/Public/latterbg/');
		$this->assign('log_info' , $log_info);
		$this->assign('count' , $icount);
		$this->assign('head_img' , $this->user['client_headimg_url']);
		$this->assign('friendaccount' , $this->user['client_account']);
		$this->assign('log_account' , $log_account);
		$this->display('sns_classJournalview');
	}
	
	
	//班级老师与信息用户之间的关系-查找是否为当前班级班主任或者管理员 2012-3-20 by lyt:
	public function findClassAdminClassRols($Infoid,$findtype,$class_code) {
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$mClientClass = ClsFactory::Create('Model.mClientClass');
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$falgJournal = 0;
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }
		
		switch($findtype) { 
			case "log" :
				if($class_code){
					$teacher_class_role = $current_class_info['teacher_class_role'];
					$class_admin = $current_class_info['class_admin'];
				}else{
					$classLogData = $mClasslog->getClassLogByLogId($Infoid);
					$classLogData = $classLogData[$Infoid];
					if($classLogData){
						$classLogData = array_shift($classLogData);
						$logInClassCode = $classLogData['class_code'];
					}
					$addUserInfo = $mClientClass->getClientClassByUid($this->getCookieAccount());
					$addUserInfo = array_shift($addUserInfo);
					$teacher_class_role = $addUserInfo[$logInClassCode]['teacher_class_role'];
					$class_admin = $addUserInfo[$logInClassCode]['class_admin'];
				}
				if($teacher_class_role==1 || $class_admin==1 || $teacher_class_role==3){
					$falgJournal = 1;
				}
				break;
			case "album" :
				if($class_code){
					$teacher_class_role = $current_class_info['teacher_class_role'];
					$class_admin = $current_class_info['class_admin'];
				}else{
					$classLogData = $mClassalbum->getClassLogByLogId($Infoid);
					$classLogData = $classLogData[$Infoid];
					if($classLogData){
						$classLogData = array_shift($classLogData);
						$logInClassCode = $classLogData['class_code'];
					}	
					if($teacher_class_role==1 || $class_admin==1 || $teacher_class_role==3){
						$falgJournal = 1;
					}	
				}
				break;
		}
		return $falgJournal;
	}

	
	//删除班级日志***** 操作：删除个人日志班级表映射关系 2012-3-20 by lyt:
	public function cancelClassLogShare(){
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$log_id = $this->objInput->getInt('log_id');
		$class_code = $this->objInput->getInt('class_code');
		
		$falgJournal = $this->findClassAdminClassRols($log_id,"log",$class_code);
		if($falgJournal==1){
			$class_log_arr = $mClasslog->findLogexistsByClassCodeLogid($log_id, $class_code);
        
	        foreach($class_log_arr as $key=>$val) {
	        	$mClasslog->cancelLogPush($key);
	        } 
			//$mClasslog->cancelLogPush_classcode($log_id,$class_code);
			$sucess_flag = true;
		} else { 
			$sucess_flag = false;
		}
	    if($sucess_flag) {
			$this->redirect('../Homeclass/Class/classJournal/class_code/'.$class_code);
	    } else {
            $this->showError("日志不存在或者您没有权限删除", "/Homeclass/Class/classJournal/class_code/$class_code");
	    }
	}


	/*添加班级日志评论内容 2012-3-20 by lyt:*/
	function addplun(){
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$log_account=$this->getCookieAccount();
		$type = $this->objInput->postStr('type');
		if(!$type){
			$type = $this->objInput->getStr('type');
		}
		
		$content = $this->objInput->postStr('msgcontent' , false);
		//$date=strtotime(date("Y-m-d H:i:s"));
		$log_id=$this->objInput->postStr('log_id');
		
		$datas = array(
		    'add_account' => $log_account,
		    'plun_content' => $content,
		    'add_date' => date('Y-m-d H:i:s', time()),
		    'log_id' => $log_id
		);
		
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		$result = $mLogplun->addLogplun($datas);
		
		
		if($result){
			if($type=='space'){
				$spaceid=$this->objInput->postInt('spaceid');
				$this->redirect("../Homeuser/Index/spacelogview/spaceid/{$spaceid}/log_id/$log_id");
			}
			if($type!=""){
				$this->redirect("../Homepzone/Pzonelog/look_mylog/log_id/$log_id");
			}else{
				$this->redirect("Class/classJournalview/class_code/{$class_code}/log_id/$log_id");
			}
		}
	}


//SNS 班级日志结束********************************************************************************

	//班级相册
	function classalbum(){
		$account = $this->getCookieAccount() ;
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$mUser = ClsFactory::Create('Model.mUser');
		
		//查询数据的总条数
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=25; //每页数量
		//查找相册列表
	
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');	 
		$mClassAlbum = ClsFactory::Create('Model.mClassalbum');	 
		$classAlbumData = $mClassAlbum->getAlbumInfoByClassCode($class_code);
		if(!empty($classAlbumData)){
			$sortkeys = array();
			$newClassalbumData = array();
			foreach ($classAlbumData[$class_code] as $classkey=>$classalbumval) {
				$album_ids[$classalbumval['album_id']] = $classalbumval['album_id'];
			}
			$albumInfo = $mAlbuminfo->getAlbumListByAlbumid($album_ids);
			if(!empty($albumInfo)) {
				foreach($albumInfo as $key1=>$val1){
					$log_plun_count=$this->pluncontent($val1['album_id']);
					$classalbumInfo['album_id']=$val1['album_id'];
					$classalbumInfo['album_name']=$val1['album_name'];
					$classalbumInfo['album_img'] = Pathmanagement_sns::getAlbum($val1['add_account']) . $val1['album_img'];
					$classalbumInfo['plun_count']=$log_plun_count;
					$classalbumInfo['add_account'] = $val1['add_account'];
					$classalbumInfo['add_date'] = $val1['add_date'];
					$add_accounts[$val1['add_account']] = $val1['add_account'];
					$newClassalbumData[] = $classalbumInfo;
				}
				$mcInfos = $mUser->getUserBaseByUid($add_accounts);
				foreach($newClassalbumData as $key=>& $value) {
				 	$value['client_name']=$mcInfos[$value['add_account']]['client_name'];
		            $sortkeys[$key] = $value['add_date'];
		        }
			}
		}
		
		array_multisort($sortkeys , SORT_DESC , $newClassalbumData);
		
		$newarr_newLog = array_slice($newClassalbumData, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeclass/Class/classalbum/class_code/".$class_code;
		$pageCount = ceil(count($newClassalbumData)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newClassalbumData) > $pagesize){
		$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");

		}
		$this->assign('xiangce_list',$newarr_newLog);
		$this->assign('class_code',$class_code);
		$this->assign('account',$account);
		$this->assign('log_account',$account);
		$this->assign('friendaccount',$this->getCookieAccount());
		
		$this->assign('ALBUM_SYS_CREATE' , ALBUM_SYS_CREATE);
		$this->assign('actionUrl' ,"/Homeclass/Class/classalbum/");

		$this->display('sns_classalbum');
	}

	
	/*进入相册管理*************************************************************************************************/
	public function xcmanager(){
		$xcid = $this->objInput->getInt('xcid');
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		//查询数据的总条数
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=24; //每页数量
		$account = $this->getCookieAccount();
		//指定相册ID下照片信息
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo_result = $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_photo_plun_result = &$photoinfo_result[$xcid];
		unset($photoinfo_result);
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		$mUser = ClsFactory::Create('Model.mUser');
		if($new_photo_plun_result){
			$albumPhotos = array();
			foreach($new_photo_plun_result as $key=>$val){
				$val['photo_urlall'] = Pathmanagement_sns::getAlbum($val['add_account']) . $val['photo_url'];
				$val['photo_min_urlall'] = Pathmanagement_sns::getAlbum($val['add_account']) . $val['photo_min_url'];
				$val['photo_name'] = str_replace($account."_","",$val['photo_name']);
				$val['photo_min_url'] = trim($val['photo_min_url']);
				
				$plun_nums = $mPhotoplun->getPhotoPlunCountByPhotoId($val['photo_id']);
				$val['plunnums'] = "(" . max(intval($plun_nums), 0) . ")";
				
				$albumPhotos[] = $val;

			}
		}
		$this->getUserJurisdiction($class_code);
		$xcinfo = $mAlbuminfo->getAlbumListByAlbumid($xcid);
		$xcinfo = array_shift($xcinfo);
		if($xcinfo) {
			$userarrlist = $mUser->getUserBaseByUid($xcinfo['add_account']);
		    $xcinfo['album_explain'] = htmlspecialchars_decode($xcinfo['album_explain']);
			$xcinfo['album_imgname']= trim($xcinfo['album_img']);
			$xcinfo['album_imgfm']= Pathmanagement_sns::getAlbum($xcinfo['add_account']) . $xcinfo['album_img'];
			$xcinfo['add_date']= date('Y-m-d H:i:s',$xcinfo['add_date']);
			$xcinfo['upd_date']= date('Y-m-d H:i:s',$xcinfo['upd_date']);
			$xcinfo['client_name']= $userarrlist[$xcinfo['add_account']]['client_name'];
		}
		array_multisort($albumPhotos , SORT_DESC);
		
		$newarr_newLog = array_slice($albumPhotos, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeclass/Class/xcmanager/class_code/".$class_code."/xcid/".$xcid;
		$pageCount = ceil(count($albumPhotos)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($albumPhotos) > $pagesize){
		$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");

		}

		$this->assign('photoinfo',$newarr_newLog);
		$this->assign('xcinfo',$xcinfo);
		$this->assign('account',$account);
		$this->assign('class_code',$class_code);
		$this->assign('photocount',count($albumPhotos));
		$this->assign('friendaccount',$this->getCookieAccount());
		$this->assign('xcid',$xcid);
		$this->assign('actionUrl','/Homeclass/Class/classalbum/');
		
		$this->display('sns_classxcmanager');
	}
	


	//进入相册单个照片浏览页面
	public function toxcphoto(){
		$LoinUserAccount = $this->getCookieAccount();
		$xcid = trim($this->objInput->getStr('xcid'));
		$photo_id = trim($this->objInput->getStr('photo_id'));
		$class_code= trim($this->objInput->getStr('class_code'));
		$class_code = $this->checkclasscode($class_code);

		//当前照片ID信息
		
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$arrphotoData = $mPhotosInfo->getPhotoInfoById($photo_id);
		if($arrphotoData){
			$arrphotoData = array_shift($arrphotoData);
			$arrphotoData['photo_urlbig']=$arrphotoData['photo_url'];
			$arrphotoData['photo_urlfm']=$arrphotoData['photo_min_url'];
			$arrphotoData['photo_url']=Pathmanagement_sns::getAlbum($arrphotoData['add_account']) . $arrphotoData['photo_url'];
			$this->assign('arrphotoData',$arrphotoData);
		}
		
		//相册内所有照片
		
		$mPhotosInfo	= ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo	= $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_photoinfo = &$photoinfo[$xcid];
		unset($photoinfo);
		$click_photo_num = 0;
		$photoCount = count($new_photoinfo);
		$newPhotoInfo = array();
		foreach($new_photoinfo as $pkye=>$val){
			if($val['photo_id'] == $photo_id){
				$click_photo_num = $i;
			}
			$val['photo_urlall']=Pathmanagement_sns::getAlbum($val['add_account']) . $val['photo_url'];
			$val['photo_min_urlall']=Pathmanagement_sns::getAlbum($val['add_account']) . $val['photo_min_url'];
			if(empty($val['photo_id'])){
                unset($val);			    
			}
			$newPhotoInfo[] = $val;
		}

		$this->assign('LoinUserAccount',$LoinUserAccount);
		$this->assign('photoinfo',$newPhotoInfo);
		$this->assign('xcid',$xcid);
		$this->assign('photo_id',$photo_id);
		$this->assign('class_code',$class_code);
		
		$this->display('sns_classalbumphoto');
	}

	
	//删除班级相册共享-
	public function deletexc(){
		$account = $this->getCookieAccount();
		$class_code = trim($this->objInput->getInt('class_code'));
		$class_code = $this->checkclasscode($class_code);
		$xcid = trim($this->objInput->getInt('xcid'));

		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$class_album_list = $mClassalbum->getAlbumInfoByalbumIdClassCode($xcid, $class_code);
		$class_album_info = current($class_album_list);
		
		$mClassalbum->delClassAlbum($class_album_info['class_album_id']);

		$this->redirect('Class/classalbum/class_code/'.$class_code);
	}
	




	//验证用户管理权限
	function getUserJurisdiction($class_code) { 
		$mClientClass = ClsFactory::Create('Model.mClientClass');
		$addUserInfo = $mClientClass->getClientClassByUid($this->getCookieAccount());
		$addUserInfo = array_shift($addUserInfo);
		
		foreach ($addUserInfo as $id => $info) {
			if($info['class_code'] == $class_code ){
				$teacher_class_role = $info['teacher_class_role'];
			    $class_admin = $info['class_admin'];
			    break;
			}
		}
		
		if($teacher_class_role==1 || $teacher_class_role==3 || $class_admin==1 ){
			
			$this->assign('falg_albumManager',1);
		} else {
			
			$this->assign('falg_albumManager',0);
		}

	}

	/**
	 * 检测当前的class_code参数是否正确
	 * @param $class_code
	 */
    private function checkclasscode($class_code = 0) {
	    if(empty($class_code)) {
	        $class_code = $this->objInput->getInt('class_code');
	        if(empty($class_code)) {
	            $class_code = $this->objInput->postInt('class_code');
	        }
	    }
	    $clientclasslist = $this->user['client_class'];
	    if(!empty($clientclasslist)) {
	        $class_code_list = array();
	        foreach($clientclasslist as $key=>$clientclass) {
	            $tmp_class_code = intval($clientclass['class_code']);
	            if($tmp_class_code > 0) {
	                $class_code_list[] = $tmp_class_code;
	            }
	        }
	    }
        if(!empty($class_code_list)) {
           $class_code_list = array_unique($class_code_list);
           $class_code = $class_code && in_array($class_code , $class_code_list) ? $class_code : array_shift($class_code_list);
        } else {
            $class_code = 0;
        }
	    return $class_code ? $class_code : false;
	}

	/**
	 * 检测当前的url的参数是否正确
	 */
	public function checkUrl($class_code = 0 , $direct_flag = true) {
	    if(empty($class_code)) {
	        $class_code = $this->objInput->getInt('class_code');
	    }
	    
	    $request_uri = $_SERVER['REQUEST_URI'];
	    $need_redirect = false;
	    if(stripos($request_uri , 'class_code') !== false) {
	        $need_redirect = true;
	    }
	    $class_code_list = array_keys($this->user['class_info']);
	    
	    $new_class_code = $class_code && in_array($class_code , $class_code_list) ? $class_code : array_shift($class_code_list);
	    $new_class_code = max($new_class_code , 0);
	    //判断链接是否需要重定位
	    if($direct_flag && $need_redirect && $new_class_code && $new_class_code !== $class_code) {
	        $pattern = '/class_code\/[^\/]+/i';
	        $request_uri = preg_replace($pattern , "class_code/$new_class_code" , $request_uri);
	        redirect($request_uri);
	        return false;
	    }
	    return $new_class_code;
	}

	

}

