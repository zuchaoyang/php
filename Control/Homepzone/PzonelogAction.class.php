<?php
class PzonelogAction extends SnsController{
    
	public function _initialize(){
	    parent::_initialize(); 
		import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Date");
		
		$this->assign('chanelid',"chanel1");
		
	}
	
	//我的日志浏览默认显示所有
	//todolist C层逻辑存在很多不合理的地方
	public function mylogindex(){
		$add_account = $this->getCookieAccount();
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
		
		$log_type = $this->objInput->getStr('logtype');
		$log_type = !empty($log_type) ? $log_type : 0;

		$pageno = trim($this->objInput->getInt('pageno'));
		$page = empty($pageno) ? 1 : $pageno;//页码
		$pagesize=7; //每页数量

		$this->findLogTypeData($add_account);
		$mPersonlogs = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	 

		$filters['log_status'] = 1;
		if(!empty($log_type)) {
		   $filters['log_type'] = $log_type; 
		}
		
		$log_arr = $mPersonlogs->getPersonLogsByAddaccount($add_account, $filters);
		$LogInfoArrData = & $log_arr[$add_account];
		    
		if($LogInfoArrData){
			$sortkeys = array();
			$newLog = array();
			foreach($LogInfoArrData as $key=>$val){
			    if(empty($val['log_type'])) {
		            $log_by_type_count[$val['log_type']] = 0;
    		    }else{
    		        $log_by_type_count[$val['log_type']] += 1;
    		    }
			
				$log_plun_count=$this->pluncontent($val['log_id']);
				$val['plun_count']=$log_plun_count;
				$logcontent = str_replace('&nbsp;','',WmwString::unhtmlspecialchars($val['log_content']));
				$logcontent = str_replace('<P></P>','',$logcontent);
				$val['log_contentall']= strip_tags(cutstr(WmwString::unhtmlspecialchars($logcontent), 100, true));
				$RsmLogtypes = $mLogtypes->getLogTypesById($val['log_type']);
				$RsmLogtypes = array_shift($RsmLogtypes);
				$val['logtype_name'] = $RsmLogtypes['logtype_name'];
				$newLog[] = $val;
			}
			unset($val);

			 foreach($newLog as $key=>$value) {
	            $sortkeys[$key] = $value['add_date'];
	        }
			//排序日记
			array_multisort($sortkeys , SORT_DESC , $newLog);
			
			$newarr_newLog = array_slice($newLog, ($page-1)*$pagesize, $pagesize);	
			$webUrl = "/Homepzone/Pzonelog/mylogindex/class_code/".$class_code;
			$pageCount = ceil(count($newLog)/$pagesize);
			intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
			intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
			if(count($newLog) > $pagesize){
			$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");

			}

			$this->assign('pageno',$pageno);
		}
	
		//日记分类
		$log_types = $mLogtypes->getLogTypesByAddaccount($add_account);
		
		$this->assign('log_by_type_count',$log_by_type_count);
		$this->assign('typelist',$log_types[$add_account]);
		$this->assign('log_type',$log_type);
		$this->assign('mylog_list',$newarr_newLog);
		$this->assign('class_code',$class_code);
		$this->assign('uid',$this->getCookieAccount());
		
		$this->assign('actionUrl','/Homepzone/Pzonelog/mylogindex/');

		$this->display('mylog');
	}
	
	

	//我的日记草稿箱
	function logDraft(){
		$log_account=$this->getCookieAccount();
		$log_type = $this->objInput->getStr('logtype');
		$pagecount = 7;
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	 
		$class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);

		$filters = array(
		    'log_status' => 0,
		);
		
		$log_arr = $mOjbectData->getPersonLogsByAddaccount($log_account, $filters);
		$logViewData = & $log_arr[$log_account];
		
		if($logViewData){
			$sortkeys = array();
			 foreach($logViewData as $key=>$value) {
	            $sortkeys[$key] = $value['log_id'];
	        }
		}
		array_multisort($sortkeys , SORT_DESC , $logViewData);
			
		$this->assign('mylog_list',$logViewData);
		$this->assign('uid',$this->getCookieAccount());
		$this->assign('pagecount',$pagecount);
		$this->assign('class_code',$class_code);
		$this->assign('log_account',$log_account);
		
		$this->display('logDraft');
	}


	//阅读日志和评论的详细信息
	public function look_mylog(){
		$log_id = $this->objInput->getInt('log_id');
		$logtype= $this->objInput->getInt('logtype');
		$class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);

		$log_id = $log_id > 0 ? $log_id : 0;
		
		$mUser =ClsFactory::Create('Model.mUser');
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
		if($log_id) {
			$log_list = $mOjbectData->getPersonLogsById($log_id);
		    if(!empty($log_list) && is_array($log_list)) {
		        foreach($log_list as $log) {
		            if($log['log_id'] == $log_id) {
		                $log_info = $log;
						$client_infos = $mUser->getUserBaseByUid($log_info['add_account']);
						$client_infos = &$client_infos[$log_info['add_account']];
						$log_info['log_contentall']= WmwString::unhtmlspecialchars($log_info['log_content']);
						$log_info['headimg']= Pathmanagement_sns::getHeadImg($log_info['add_account']) . $client_infos['client_headimg'];
						$log_info['client_name']=$client_infos['client_name'];
		                break;
		            }
		        }
		        unset($log_list);
		    }
		}
		
		//日志存在则该值重新赋值，保证数据的一致性
    	if(!empty($log_info)) {
    	    $log_account = intval($log_info['add_account']);
    	    $datas = array(
			    'read_count'=>"%read_count+1%"
			);
			$mOjbectData->modifyPersonLogs($datas, $log_id); //日志浏览 数加1
			
            //获取日志评论信息
			$mLogplun = ClsFactory::Create('Model.mLogplun');
			
			//tolist 注释代码
			$new_plun_arr = $mLogplun->getLogplunByLogid($log_id);
    		$new_plun_list = & $new_plun_arr[$log_id];
			
    		$icount = !empty($new_plun_list) ? count($new_plun_list) : 0;
    		
			//追加评论的用户信息
    		if(!empty($new_plun_list)) {
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
				$faceSearch=$faceReplace=array();
				$facelist = Constancearr::getfacelist();

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
            $this->showError("您访问的日志不存在或者已经删除", "/Homepzone/Pzonelog/mylogindex");
        }
		$this->assign('plun_list' , $new_plun_list);
		$this->assign('logtype' , $logtype);
		$this->assign('class_code' , $class_code);
		$this->assign('log_id' , $log_id);
		$this->assign('log_info' , $log_info);
		$this->assign('count' , $icount);
		$this->assign('head_img' , $this->user['client_headimg_url']);
		$this->assign('friendaccount' , $this->user['client_account']);
		$this->assign('log_account' , $log_account);
		
		$this->display('look_mylog');
	}
	

	//获取日记分类及分类下所有日记数量
	function getLogTypeListAndLogNums($add_account,$log_status){
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
		$mLogtypesData = $mLogtypes->getLogTypesByAddaccount($add_account);
		$new_mLogtypesData = $mLogtypesData[$add_account];
		unset($mLogtypesData);
		foreach($new_mLogtypesData as $key=>$val){
			$filters = array(
				'log_status' => 1,
				'log_type' => $val['logtype_id'],
			);
				
			//$logCountDataNums = $mOjbectData->getLogInfoAllByaccount($add_account,$filters);
			$log_arr = $mOjbectData->getPersonLogsByAddaccount($add_account, $filters);
		    $logCountDataNums = & $log_arr[$add_account];
			
			if($logCountDataNums){
				$val['logtype_count']=  count($logCountDataNums);
			}else{
				$val['logtype_count']=  0;
			}
			$new_mLogtypesData[$key] = $val;
		}
		return $new_mLogtypesData;
	}



	//查出关于每个日志的评论内容
	public function pluncontent($log_id){
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		return $mLogplun->getLogplunCountByLogid($log_id);
	}


	//日志类型公共方法
	public function findLogTypeData($log_account){
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		$tmp_result = $mLogtypes->getLogTypesByAddaccount($log_account);
		
		if(empty($tmp_result)) {
		    $this->adddefaultlogtype($log_account);
		    $tmp_result = $mLogtypes->getLogTypesByAddaccount($log_account);
		}
		
		$result = $tmp_result[$log_account];
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
	
	//写日志的首页和编辑回显页
	public function writelog(){
		$log_account = $this->getCookieAccount();
		$logid=trim($this->objInput->getInt('logid'));
		$logtype = $this->objInput->getInt('logtype');
		$mClasslog = ClsFactory::Create('Model.mClasslog');
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
		if($this->objInput->getStr('draft')){
			$this->assign('draft','/draft/draft');
		}
		if($logid){
			$flag=0;
			$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
			$log_list = $mOjbectData->getPersonLogsById($logid);
			$log_list = array_shift($log_list);
			$this->chkLogOwner($logid);

			if(!empty($log_list['contentbg'])){
				$log_list['contentbg'] = IMG_SERVER.'/Public/latterbg/' . $log_list['contentbg'];
			}
			
			$this->assign('log_list',$log_list);
			$this->assign('flag',$flag);
			$this->assign('logid',$logid);
			
			$existsInfo = $mClasslog->getClassLogByLogId($logid);
			$existsInfo = $existsInfo[$logid];
			if($existsInfo){
				$this->assign('push_class',1);
			}
		}
		$this->findLogTypeData($log_account);
	
		$client_type = $this->user['client_type'];
		$myclasslist = &$this->user['class_info'];
		
		$newmyclasslist = array();
		foreach($myclasslist as $key =>$val){
			$finddata = $mClasslog->findLogexistsByClassCodeLogid($logid,$val[class_code]);
			if($finddata){
				$val['classcodechk'] = "checked";
			}else{
				$val['classcodechk'] = "";
			}
			$newmyclasslist[] = $val;
		}
		
		//dump($newmyclasslist);

		$this->assign('client_type',$client_type);
		$this->assign('myclasslistnew',$newmyclasslist);
		$this->assign('log_account',$log_account);
		$this->assign('logtype',$logtype);
		$this->assign('class_code',$class_code);
		$this->display('writelog');
	}

	
	//发表日志和修改日志的方法
	public function writelog_do(){
		$log_account = $this->getCookieAccount();
		$flag=$this->objInput->getStr('flag');
		$logid=$this->objInput->postStr('logid');
		$logtype = $this->objInput->postStr('log_type');
		$ContentBg = $this->objInput->postStr('ContentBg');
		$ContentBg = end(explode('/',$ContentBg));
		$class_code = $this->objInput->postInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$savetype = $this->objInput->postStr('savetype');
		$btnSaveId = $this->objInput->postStr('btnSaveId');
		
		$push_class = $this->objInput->postStr('push_class');
		$teacher_push_class = $this->objInput->postArr('teacher_push_class');
		
		!empty($teacher_push_class) ? $shareType=$teacher_push_class : '';
		if(empty($teacher_push_class)){
		    $shareType = $class_code;
		}

		$push_class =="on" ? $shareCmd = 1 : $shareCmd =0; //分享或取消分享标志
		$btnSaveId != "" ? $cmdAction = 1 : $cmdAction=0;  //$cmdAction 1 直接发布 0 保存草稿
		$title=trim($this->objInput->postStr('title'));
		$logcontent = trim($this->objInput->postStr('content'),false);
		if(empty($title) || empty($logcontent)){
			echo "<script>alert('信息输入不完整');window.history.go(-1);</script>";
			exit;
		}

		$date=strtotime(date("Y-m-d H:i:s"));
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
		$addLogData = array(
			'log_name' =>$title,
			'log_content' =>$logcontent,
			'add_account' =>$log_account,
			'add_date' =>date('Y-m-d H:i:s'),
			'upd_date' =>date('Y-m-d H:i:s'),
			'log_type' =>$logtype,
			'contentbg' =>str_replace("/","",str_replace(IMG_SERVER.'/Public/latterbg/',"",$ContentBg)),
		);			
	
		$logtype!="" && $logtype!=0 ? $backtype="/logtype/".$logtype : $backtype=""; 
		if($logid!=""){
			if($cmdAction==0){
				$mOjbectData->modifyPersonLogs($addLogData,$logid);
                $this->showSuccess("已保存到草稿箱!", "/Homepzone/Pzonelog/logDraft/class_code/$class_code");
			}else{
				$addLogData['log_status']=1;
				$mOjbectData->modifyPersonLogs($addLogData,$logid);
					
				$this->thisLogShareDo($shareCmd,$shareType,$logid,$class_code,"UPD");

				$mFeed = ClsFactory::Create('Model.mFeed');
				$mFeed->addPersonFeed(intval($log_account),intval($logid),PERSON_FEED_LOG,FEED_UPD,time());
				if($this->objInput->getStr('draft')){
                    $this->showSuccess("日志发布成功!", "/Homepzone/Pzonelog/mylogindex/draft/draft" . $backtype . "/class_code/$class_code");
				}else{
                    $this->showSuccess("日志编辑成功!", '/Homepzone/Pzonelog/mylogindex'.$backtype . "/class_code/$class_code");
				}
			}
		}else{
			if($cmdAction==1){
				$addLogData['log_status']=1;
				$addLogData['read_count'] = 0;
				$ins_id = $mOjbectData->addPersonLogs($addLogData, true);
				if($ins_id) { 
					$this->thisLogShareDo($shareCmd,$shareType,$ins_id,$class_code,"NEW");
					$mFeed = ClsFactory::Create('Model.mFeed');
					$mFeed->addPersonFeed(intval($log_account),intval($ins_id),PERSON_FEED_LOG,FEED_NEW,time());
				}
                $this->showSuccess('日志发布成功!', "/Homepzone/Pzonelog/mylogindex/class_code/$class_code");
			}else{
				$addLogData['log_status']=0;
				$mOjbectData->addPersonLogs($addLogData, true);
                $this->showSuccess("已保存到草稿箱!", "/Homepzone/Pzonelog/logDraft/class_code/$class_code");
			}
			
		}

	}


	
	//日志分享-删除日记需取消分享映射
	public function thisLogShareDo($shareCmd,$shareClass,$logid,$classCode,$recType){
		if(empty($logid)){
			return false;
		}
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$client_type = ($this->user['client_type']);
		$account = $this->getCookieAccount();	
		if($recType=="NEW"){
			$CLASS_FEED_TYPE = FEED_NEW;
		}else{
			$CLASS_FEED_TYPE = FEED_UPD;
		}

		switch($client_type){
			case 0 :
				switch($shareCmd){
					case 1 : 
						$classLogInfoData = array(
							'log_id' =>$logid,
							'class_code' =>$shareClass,
							'add_time' =>time()
						);	
						$is_exits = $mClasslog->findLogexistsByClassCodeLogid($logid, $classCode);
						if(empty($is_exits)) {
							$mClasslog->addClassLogInfo($classLogInfoData);
						}
						$mFeed = ClsFactory::Create('Model.mFeed');				
						$mFeed->addClassFeed(intval($classCode),intval($account),intval($logid),CLASS_FEED_LOG,$CLASS_FEED_TYPE,time());
						break;

					case 0 :
						$class_log_arr = $mClasslog->getClassLogByLogId($logid);
						foreach($class_log_arr[$logid] as $key=>$val){
						    $mClasslog->delClassLog($key);
						}
						break;
				}				
				break;
			case 1 :
			    switch($shareCmd){
					case 1 :
			            $class_log_arr = $mClasslog->getClassLogByLogId($logid);
						foreach($class_log_arr[$logid] as $key=>$val){
						    $mClasslog->delClassLog($key);
						}
        			    if(is_array($shareClass) && $shareCmd==1){
        					foreach($shareClass as $keycode=>$arrClassCodeData){
        						$classLogInfoData = array(
        							'log_id' =>$logid,
        							'class_code' =>$arrClassCodeData,
        							'add_time' =>time()
        						);
    							$mClasslog->addClassLogInfo($classLogInfoData);
        						$mFeed = ClsFactory::Create('Model.mFeed');				
        						$mFeed->addClassFeed(intval($arrClassCodeData),intval($account),intval($logid),CLASS_FEED_LOG,$CLASS_FEED_TYPE,time());
        					}
        				}
						break;

					case 0 :
						$class_log_arr = $mClasslog->getClassLogByLogId($logid);
						foreach($class_log_arr[$logid] as $key=>$val){
						    $mClasslog->delClassLog($key);
						}
						break;
				}				
				

			break;
		}
	}

	//删除日志和对日志所有评论
	public function del_log(){
	    
		$log_id = $this->objInput->getInt('log_id');
		$log_type = $this->objInput->getInt('log_type');
	    $log_status = $this->objInput->getStr('log_status');
		$class_code = $this->objInput->postInt('class_code');
		$class_code = $this->checkclasscode($class_code);

	    $log_id = $log_id > 0 ? $log_id : 0;
	    $sucess_flag = false;
	    
		if($log_id) {
	        $this->chkLogOwner($log_id);
			
			$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
			$mOjbectData->delPersonLogs($log_id);//删除日记

			$mLogplun = ClsFactory::Create('Model.mLogplun');
			$mLogplun->delLogplunByLogId($log_id);//删除评论

			$mClasslog = ClsFactory::Create('Model.mClasslog');
			$Classlogstate = $mClasslog->delClassLog($log_id);//删除分享

			$mFeed = ClsFactory::Create('Model.mFeed');				
			$mFeed->addPersonFeed(intval($this->getCookieAccount()),intval($log_id),PERSON_FEED_LOG,FEED_DEL,time());
			if($Classlogstate){
				$mFeed->addClassFeed(intval($class_code),intval($this->getCookieAccount()),intval($log_id),CLASS_FEED_LOG,FEED_DEL,time());
			}


			$sucess_flag = true;
	    }

		if($sucess_flag) {
			if($log_status==1){
				if($log_type!=0){
                    $this->showSuccess("日志删除成功正在返回分类列表", "/Homepzone/Pzonelog/mylogindex/logtype/$log_type");
				} else {
                    $this->showSuccess("日志删除成功正在返回日记首页", "/Homepzone/Pzonelog/mylogindex/class_code/$class_code");
				}
			}else{
				$this->redirect('Pzonelog/logDraft');
			}
	    } else {
            $this->showError("日志不存在或者您没有权限删除", "/Homepzone/Pzonelog/mylogindex/class_code/$class_code");
	    }
	}

	//日志评论发布
	public function plun(){
		
		$log_account=$this->getCookieAccount();
		$friendaccount=trim($this->objInput->getStr('friendaccount'));
		$content = $this->objInput->postStr('logplun' , false);
		$log_id=$this->objInput->getStr('log_id');

		$date=strtotime(date("Y-m-d H:i:s"));
		
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		$plundata = array(
	        'add_account' => $log_account,
    	    'plun_content' => content,
    	    'add_date' => date('Y-m-d H:i:s',$date),
			'log_id' => $log_id ,
	    );	
		$result = $mLogplun->addLogplun($plundata);
		if($result){
            $this->showSuccess("评论发布成功!", "/Homepzone/Pzonelog/logDraft/class_code/$class_code");
		}else{
            $this->showError("评论发布失败!", "/Homepzone/Pzonelog/look_mylog/log_id/$log_id");
		}
	}


	//日志分类管理
	public function manage_log_type(){

		$log_account=$this->objInput->getStr('log_account');
		$log_account=$this->getCookieAccount();
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);

	    $mLogtypes = ClsFactory::Create('Model.mLogtypes');
		//$result = $mLogtypes->getLogTypesByAddaccount($log_account,1);
		$mPersonlogs = ClsFactory::Create('Model.mPersonlogs');
		$log_list = $mPersonlogs->getPersonLogsByAddaccount($log_account);
		$log_list = array_shift($log_list);
		$log_by_type_count = array();
		foreach ($log_list as $log_id=>$log_val) {
		    if(empty($log_val['log_type'])) {
		        $log_by_type_count[$log_val['log_type']] = 0;
		    }else{
		        $log_by_type_count[$log_val['log_type']] += 1;
		    }
			
		}
		unset($log_list);
		$result = $mLogtypes->getLogTypesByAddaccount($log_account);
		$result = array_shift($result);
		
		$this->assign('log_count',$log_by_type_count);
		$this->assign('log_account',$log_account);
		$this->assign('class_code',$class_code);
		$this->assign('log_type',$result);
		$this->assign('LOG_SYS_CREATE',LOG_SYS_CREATE);
		$this->display('manage_log_type');
	}


	//添加日志的分类
	public function add_log_type(){
	
		$type_name = $this->objInput->postStr('type_name');
		$log_account = $this->getCookieAccount();

		$log_id = $this->objInput->getStr('log_id');
		$url = $this->objInput->getStr('backurl');
		
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');
		if($mLogtypes->checkNameIsExist($log_account,$type_name)){
            $this->showError("发生错误：分类名称重复!", "/Homepzone/Pzonelog/manage_log_type");
		}
		
		$datas = array(
		    'logtype_name' => $type_name,
		    'add_account' => $log_account,
		    'add_date' => date('Y-m-d H:i:s', time()),
		    'log_create_type' => LOG_USER_CREATE,
		);
		$result = $mLogtypes->addLogTypes($datas);
		
		if($result){
			if($log_id){
				$this->redirect('Pzonelog/writelog/log_id/'.$log_id);
                $this->showSuccess("分类添加成功!", "/Homepzone/Pzonelog/writelog/log_id/$log_id");
			} else {
				if($url!=""){
                    $this->showSuccess("分类添加成功!", "/Homepzone/Pzonelog/manage_log_type");
				}else{
                    $this->showSuccess("分类添加成功!", "/Homepzone/Pzonelog/writelog");
				}
			}
			
		}else{
			echo "添加失败";
		}
	}

	//删除日志分类
	public function del_log_type(){
		$logtype_id=$this->objInput->getStr('logtype_id');
		$log_account=$this->getCookieAccount();
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	
		$result = $mLogtypes->getLogTypesById($logtype_id);
		$result = array_shift($result);
		$logTypeUser = $result['add_account'];

		if(!empty($logtype_id) && $logTypeUser==$this->getCookieAccount()){
				
			$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
			$logtypers = $mLogtypes->getLogTypesByAddaccountAndCreatetype($log_account, LOG_SYS_CREATE);
			$new_logtypes = &$logtypers[$log_account];
			unset($logtypers);
			
			$new_logtypes = array_shift($new_logtypes);
			if(empty($new_logtypes)){
				//在删除日志类型时没有系统默认日志类型则创建一个默认日志类型
				$sys_log_id = $this->adddefaultlogtype($log_account,true);
			} else {
				$sys_log_id = $new_logtypes['logtype_id'];
			}
			
			//删除日志类型时转移日志到系统默认日志类型
    	    $log_arr = $mOjbectData->getPersonLogsByAddaccount($log_account, array('log_type' => $logtype_id));
    	    $log_list = & $log_arr[$log_account];
    	    if(!empty($log_list)) {
    	        $datas = array(
    	            'log_type' => $sys_log_id,
    	        );
    	        foreach($log_list as $log_id=>$log) {
    	            $mOjbectData->modifyPersonLogs($datas, $log_id);
    	        }
    	    }
			
			$result = $mLogtypes->delLogTypes($logtype_id);
            $this->showSuccess("分类删除完成!", "/Homepzone/Pzonelog/manage_log_type/log_account/$log_account");
		} else {
			exit;
		}
		
	}


	//保存日志分类修改
	public function update_logtype_do(){
		$log_account=$this->objInput->getStr('log_account');
		$logtype_name=$this->objInput->postStr('logtype_name');
		$logtype_id=$this->objInput->postStr('logtype_id');

		$mLogtypes = ClsFactory::Create('Model.mLogtypes');
		if($mLogtypes->checkNameIsExist($log_account, $logtype_name)){
            $this->showError("发生错误：分类名称重复!", "/Homepzone/Pzonelog/manage_log_type");
		}

		//$result = $mLogtypes->modifyLogTypes($logtype_name,$logtype_id);
		$datas = array(
            'logtype_name' => $logtype_name,
		);
		$result = $mLogtypes->modifyLogTypes($datas, $logtype_id);
		
		if($result) {
            $this->showSuccess("分类修改成功!", "/Homepzone/Pzonelog/manage_log_type/log_account/$log_account");
		} else {
            $this->showError("分类修改失败!", "/Homepzone/Pzonelog/manage_log_type/log_account/$log_account");
		}

	}

	//删除评论
	public function delplun(){
		$log_id=$this->objInput->getStr('log_id');
		$class_code=$this->objInput->getStr('class_code');
		$log_account=$this->objInput->getStr('log_account');
		$plun_id=$this->objInput->getStr('plun_id');
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		
		$this->chkLogOwner($log_id);//当前日志归属人
		$mLogplun->delLogplun($plun_id);
		if($mLogplun) {
            $this->showSuccess("评论删除成功", "/Homepzone/Pzonelog/look_mylog/class_code/$class_code/log_id/$log_id");
		}
	}

	//获取信纸
	function getlatterbg(){
		$mLogStationery = ClsFactory::Create('Model.mLogStationery');
		$logLatter  = $mLogStationery->getAllLogStationery();
		 foreach($logLatter as $latterKey) {
			echo "<li><img src='" . IMG_SERVER.'/Public/latterbg/' . $latterKey['sty_url'] . "' width='60' height='60' onclick=\"changePager( '" . IMG_SERVER.'/Public/latterbg/' . $latterKey['sty_url']."')\"'></li>";
		 }
	}
	

	//公用方法：日志所有者验证
	public function chkLogOwner($logid) { 
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');
		$LogData = $mOjbectData->getPersonLogsById($logid);
		if ($LogData) {
			$LogData = array_shift($LogData);
			if ($LogData['add_account'] == $this->getCookieAccount()){
				return true;
			} else {
                $this->showError("操作权限错误!", "/Homepzone/Pzonelog/mylogindex");
			}
		} else {
            $this->showError("日志不存在或者您没有权限删除", "/Homepzone/Pzonelog/mylogindex");
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



}
