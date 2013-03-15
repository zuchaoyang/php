<?php
class AmssquadronAction extends AmsController {
    
    protected $is_school = true;
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Pathmanagement_ams");
        
        $this->assign('username', $this->user['client_name']);
    }
    
	//得到当前登录用户的账号todolist
    public function getCurrentAccount() {
		$mUser = ClsFactory::Create('Model.mUser');
        return $mUser->getHomeCookieAccount();//todolist
    }
    
	//检测用户是否在此学校及此班
    private function checkUser($uid,$schoolid,$gradeid,$classid) {
		$mUser = ClsFactory::Create('Model.mUser');
        $classUser = $mUser->checkLoginerInClass($uid,$schoolid,$gradeid,$classid);

        return !empty($classUser) ? true : false;
    }
    
    /**
     * 展示中队的基本信息
     */
    public function showSquadron() {
    	$classCode = $this->objInput->getInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $stop_flag = $this->objInput->getInt('stop_flag');
        
	    $mSquadron = ClsFactory::Create('Model.mSquadron');
    	$mUser = ClsFactory::Create('Model.mUser');
    	$mClassInfo  = ClsFactory::Create('Model.mClassInfo');
    	
	    $squadronUsers = $mSquadron->getSquadronMemberDutiesAllBySquadronId($classCode);
	    if($gradeid == 1){
	    	$arr = array(5,6);
        	$classInfo   = $mClassInfo->getClassInfoBySchoolId($schoolid);
        	$schoolclassInfo = array();
        	foreach($classInfo[$schoolid] as $classkey=>$classval){
        		if(in_array($classval['grade_id'],$arr)){
        			$needclassInfo[$classkey] = $classval;
        		}
        	}
	    	$classcodeArr = array();
	    	foreach($needclassInfo as $grade=>$gradeval){
	    		$classcodeArr[] = $gradeval['class_code']; 
	    	}
	    	$mTeam = ClsFactory::Create('Model.mTeam');
	    	$teamInfo = $mTeam->getTeamBaseBySquadronId($classcodeArr);
	    	$teamIds = array();
	    	foreach($teamInfo as $teamInfos){
	    		foreach($teamInfos as $teamkey=>$teamval){
	    			$teamIds[] = $teamkey;
	    		}
	    	}
	    	$teammembers = $mTeam->getTeamNumberDutiesByTeamId($teamIds);
	    	
	        $uidarr  = array();
	        foreach ($teammembers as $teammemberinfo){
	        	foreach($teammemberinfo as $teamkey=>$memberinfo){
	        		if(($memberinfo['team_duties_id'] == '1' || $memberinfo['team_duties_id'] == '2') && $memberinfo['client_account'] != ''){
	        			$uidarr[$memberinfo['client_account']]['client_account'] = $memberinfo['client_account'];
	                	$uidarr[$memberinfo['client_account']]['client_name'] = $memberinfo['client_name'];
	        		}
	        	}
	        }
	        $smallmembers = $mSquadron->getSquadronMemberDutiesAllBySquadronId($classcodeArr);
    		foreach ($smallmembers as $squadronmemberinfo){
	        	foreach($squadronmemberinfo as $squadronkey=>$memberinfo){
	        		if(($memberinfo['squadron_duties_id'] == '1' || $memberinfo['squadron_duties_id'] == '2') && $memberinfo['client_account'] != ''){
	        			$uidarr[$memberinfo['client_account']]['client_account'] = $memberinfo['client_account'];
	                	$uidarr[$memberinfo['client_account']]['client_name'] = $memberinfo['client_name'];
	        		}
	        	}
	        }
	        //本队小辅导员
	        $smallfdy = '';
	        foreach($squadronUsers[$classCode] as $values){
	        	if($values['squadron_duties_id'] == 3){
	        		$smallfdy = $values;
	        	}
	        }
	        
	        $this->assign('smallfdy',$smallfdy);
	        $this->assign('uidarr',$uidarr);
	    }
        
        $new_arr = array();
        
    	$squadronInfo = $mSquadron->getSquadronById($classCode);
        $classinfo = $mClassInfo->getClassInfoBaseById($classCode);
        $userInfo = $mUser->getUserBaseByUid($classinfo[$classCode]['headteacher_account']);
        $new_arr['squadronname'] = $squadronInfo[$classCode]['squadron_name'];
        if($squadronInfo[$classCode]['wmw_uid']){
        	$userInfo = $mUser->getUserBaseByUid($squadronInfo[$classCode]['wmw_uid']);
        	$new_arr['username'] = $userInfo[$squadronInfo[$classCode]['wmw_uid']]['client_name'];
        } else {
        	$userInfo = $mUser->getUserBaseByUid($classinfo[$classCode]['headteacher_account']);
        	$new_arr['username'] = $userInfo[$classinfo[$classCode]['headteacher_account']]['client_name'];
        }
        
        //logo处理
        $loginname = $squadronInfo[$classCode]['squadron_logo'];
        $explode_logo = explode('.',$loginname);
        $small_logo_name = $explode_logo[0].'_small.'.$explode_logo[1];
        
        $pic_url = Pathmanagement_ams::getSquadronLogo($classCode) . $small_logo_name;
        $this->assign('logourl',$pic_url);
        $this->assign('squadron',$new_arr);
        $this->assign('squadronUsers',$squadronUsers[$classCode]);
        $this->assign('classCode',$classCode);
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$uid);
        
    	$this->display('squadronset');
    }
    
    public function squadronList(){
    	$classCode = $this->objInput->getInt('classCode'); //获得班级编号
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $stop_flag = $this->objInput->getInt('stop_flag');
        
        $mSquadron = ClsFactory::Create('Model.mSquadron');
        $mClientClass  = ClsFactory::Create('Model.mClientClass');
        $mUser = ClsFactory::Create('Model.mUser');
        $mClassInfo  = ClsFactory::Create('Model.mClassInfo');
        
        $new_arr = array();
        $squadronInfo = $mSquadron->getSquadronById($classCode);
        $new_arr['squadronname'] = $squadronInfo[$classCode]['squadron_name'];
        
        $squadronUsers = $mSquadron->getSquadronMemberDutiesAllBySquadronId($classCode);
        
        //处理两个副队长
        $dutiesflag = 1;
     	foreach($squadronUsers[$classCode] as $clientkey=>&$clientval){
        	if($clientval['squadron_duties_id'] == '2'){
        		$clientval['dutiesflag'] = $dutiesflag;
        		$dutiesflag++;
        	}
        }
        if(!$stop_flag){
        	$stop_flag = 0;
        }
    	
        $result   = $mClientClass->getClientClassByClassCode($classCode);
        $classAccounts = array();
        foreach($result[$classCode] as $key=>$classClientList){  //获取班级所有会员账号
             if(!empty($classClientList['client_account']) && $classClientList["client_type"] == 0) {
                 $classAccounts[] = $classClientList['client_account'];
             }
        }
        
        $clientInfo = $mUser->getUserBaseByUid($classAccounts);//选出会员信息，以获取姓名
        //stop_flag的过滤处理
        if(!empty($clientInfo)) {
            $tmp_userlist = array();
            foreach($clientInfo as $key=>$val) {
                if(isset($val['status']) && $val['status'] <= $stop_flag) {
                    $tmp_userlist[$key] = $val;
                }
            }
            $clientInfo = & $tmp_userlist;
        }
        
        $uidarr  = array();
        foreach ($clientInfo as $account=>$accountInfo){
            if(empty($clientInfo[$account]['client_name'])){
                unset($uidarr[$account]);
            }else{
            	$uidarr[$account]['client_account'] = $accountInfo['client_account'];
            	$uidarr[$account]['client_name'] = $accountInfo['client_name'];
            	foreach($squadronUsers[$classCode] as $squadronkey=>$squadronval){
            		if($account == $squadronval['client_account'] && $squadronval['squadron_duties_id'] != 1 && $squadronval['squadron_duties_id'] != 2 && $squadronval['squadron_duties_id'] != 3){
            			$uidarr[$account]['squadron_duties_name'] = $squadronval['squadron_duties_name'];
            			$uidarr[$account]['flag'] = 'ture';
            			break;
            		}
            	}
            }
        }
        $classinfo = $mClassInfo->getClassInfoBaseById($classCode);
    	if($squadronInfo[$classCode]['wmw_uid']){
        	$userInfo = $mUser->getUserBaseByUid($squadronInfo[$classCode]['wmw_uid']);
        	$new_arr['username'] = $userInfo[$squadronInfo[$classCode]['wmw_uid']]['client_name'];
        	$new_arr['account'] = $classinfo[$squadronInfo[$classCode]['wmw_uid']];
        }else{
        	$userInfo = $mUser->getUserBaseByUid($classinfo[$classCode]['headteacher_account']);
        	$new_arr['username'] = $userInfo[$classinfo[$classCode]['headteacher_account']]['client_name'];
        	$new_arr['account'] = $classinfo[$classCode]['headteacher_account'];
        }
        //logo处理
        $loginname = $squadronInfo[$classCode]['squadron_logo'];
        $explode_logo = explode('.',$loginname);
        $small_logo_name = $explode_logo[0].'_small.jpg';
        $pic_url = Pathmanagement_ams::getSquadronLogo($classCode) . $small_logo_name;
        
        
        $this->assign('logourl',$pic_url);
        $this->assign('clientInfo',$clientInfo);
        $this->assign('uidarr',$uidarr);
        $this->assign('squadron',$new_arr);
        $this->assign('squadronUsers',$squadronUsers[$classCode]);
        
        $this->assign('classCode',$classCode);
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$uid);
        
    	$this->display('squadronsetlist');
    }
    
    public function formsub(){
    	$mSquadron = ClsFactory::Create('Model.mSquadron');
    	$classCode = $this->objInput->postInt('classCode');
    	$squadronname = $this->objInput->postStr('squadronname');
    	$useraccount = $this->objInput->postInt('useraccount');
    	$uid = $this->objInput->postInt('uid');
    	$gradeid   = $this->objInput->postInt('gradeid');
        $schoolid  = $this->objInput->postInt('schoolid');
        
    	$zd = $this->objInput->postInt('zd');
    	$fzd1 = $this->objInput->postInt('fzd1');
    	$fzd2 = $this->objInput->postInt('fzd2');
    	$prezd = $this->objInput->postInt('prezd');
    	$prefzd1 = $this->objInput->postInt('prefzd1');
    	$prefzd2 = $this->objInput->postInt('prefzd2');
    	$prezdid = $this->objInput->postInt('prezdid');
    	$prefzd1id = $this->objInput->postInt('prefzd1id');
    	$prefzd2id = $this->objInput->postInt('prefzd2id');
    	$logourl = $this->objInput->postStr('logourl');
    	$presquadronname = $this->objInput->postStr('presquadronname');
    	//其他委员相关的json串
    	$duties_name_json = $this->objInput->postStr('duties_name_json');
    	//其他中队成员职位的设置
    	$other_member_list = $this->dealDutiesNameJson($duties_name_json, 10);
    	$squadronUsers = $mSquadron->getSquadronMemberDutiesBySquadronId($classCode);
    	$needarr = array();
    	foreach($squadronUsers[$classCode] as $key=>$val){
    		if($val['squadron_duties_id'] != 1 && $val['squadron_duties_id'] != 2 && $val['squadron_duties_id'] != 3){
    			$needarr[$key]=$val;
    			$keyarr[] = $key;
    		}
    	}
    	if($squadronname){
	    	if(!$presquadronname){
		    	//验证中队名称是否重复
	    		$mClassInfo = ClsFactory::Create("Model.mClassInfo");
	    		$classinfo = $mClassInfo->getClassInfoBySchoolId($schoolid);
	    		$squadron_ids = array_keys($classinfo[$schoolid]);
	    		$squadronInfos = $mSquadron->getSquadronById($squadron_ids);
	    		foreach($squadronInfos as $squadronvalue){
	    			if($squadronname == $squadronvalue['squadron_name']){
	    				self::amsLoginTipMessage("中队名称已存在，<a href='javascript:history.go(-1);'>返回</a>");
	    				return false;
	    			}
	    		}
	    		$squadron_arr = array(
	    			'squadron_id'=>$classCode,
	    			'squadron_name' => $squadronname,
	    			'wmw_uid'=>$useraccount,
	    			'db_createtime' => date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->addSquadron($squadron_arr);
	    	} elseif($presquadronname != $squadronname){
		    	//验证中队名称是否重复
	    		$mClassInfo = ClsFactory::Create("Model.mClassInfo");
	    		$classinfo = $mClassInfo->getClassInfoBySchoolId($schoolid);
	    		$squadron_ids = array_keys($classinfo[$schoolid]);
	    		$squadronInfos = $mSquadron->getSquadronById($squadron_ids);
	    		foreach($squadronInfos as $squadronvalue){
	    			if($squadronname == $squadronvalue['squadron_name']){
	    				self::amsLoginTipMessage("中队名称已存在，<a href='javascript:history.go(-1);'>返回</a>");
	    				return false;
	    			}
	    		}
	    		$squadron_arr = array(
	    			'squadron_name' => $squadronname,
	    			'db_updatetime' => date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->modifySquadron($squadron_arr, $classCode);
	    	}
	    	
	    	//logo图片上传
	    	if ( isset( $_FILES['file']['name'] ) && $_FILES['file']['name'] != "" ) {
	        	$upimg = $this->upload('file', $picname, $classCode,array('jpg','gif','png','bmp'),160,160);
	        	
	        	$squadron_logo = array();
	        	$squadron_logo['squadron_logo'] = pathinfo($upimg['filename'], PATHINFO_BASENAME);
	        	
	        	if(!$upimg){
	        		$this->amsLoginTipMessage("logo图片上传失败");
	        		return false;
	        	}
	        	
	        	$mSquadron->modifySquadron($squadron_logo, $classCode);
	        	
	        	//将原来图片删除，保存现在图片
	        	$this->clearOldSquadronLogo($classCode);
	    	}
    	}
    	//添加中队长
    	if(!$prezd){
    		$dataarr = array(
    			'squadron_id'=>$classCode,
    			'wmw_uid'=>$zd,
    			'squadron_duties_id'=>1,
    			'db_createtime'=>date('Y-m-d H:i:s', time())
    		);
    		$mSquadron->addSquadronMemberDuties($dataarr);
    	}elseif($zd != $prezd){
    		$dataarr = array(
    			'wmw_uid'=>$zd,
    			'db_updatetime'=>date('Y-m-d H:i:s', time())
    		);
    		$mSquadron->modifySquadronMemberDuties($dataarr,$prezdid);
    	}
    	//添加副中队长1
    	if($fzd1){
	    	if(!$prefzd1){
	    		$dataarr = array(
	    			'squadron_id'=>$classCode,
	    			'wmw_uid'=>$fzd1,
	    			'squadron_duties_id'=>2,
	    			'db_createtime'=>date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->addSquadronMemberDuties($dataarr);
	    	}elseif($fzd1 != $prefzd1 ){
	    		$dataarr = array(
	    			'wmw_uid'=>$fzd1,
	    			'db_updatetime'=>date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->modifySquadronMemberDuties($dataarr,$prefzd1id);
	    	}
    	}
    	//添加副中队长2
    	if($fzd2){
	    	if(!$prefzd2){
	    		$dataarr = array(
	    			'squadron_id'=>$classCode,
	    			'wmw_uid'=>$fzd2,
	    			'squadron_duties_id'=>2,
	    			'db_createtime'=>date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->addSquadronMemberDuties($dataarr);
	    	}elseif($fzd2 != $prefzd2){
	    		$dataarr = array(
	    			'wmw_uid'=>$fzd2,
	    			'db_updatetime'=>date('Y-m-d H:i:s', time())
	    		);
	    		$mSquadron->modifySquadronMemberDuties($dataarr,$prefzd2id);
	    	}
    	}
    	if(empty($needarr)){
    		foreach($other_member_list as $key=>$val){
    			$addarr[] = array(
	    			'squadron_id'=>$classCode,
	    			'wmw_uid'=>$key,
	    			'squadron_duties_id'=>$val,
	    			'db_createtime'=>date('Y-m-d H:i:s', time())
    			);
    		}
    		$mSquadron->addSquadronMemberDutiesBat($addarr);
    	}elseif(!empty($other_member_list)){
    		$mSquadron->delSquadronMemberDutiesBat($keyarr);
    		foreach($other_member_list as $key=>$val){
    			$addarr[] = array(
	    			'squadron_id'=>$classCode,
	    			'wmw_uid'=>$key,
	    			'squadron_duties_id'=>$val,
	    			'db_createtime'=>date('Y-m-d H:i:s', time())
    			);
    		}
    		$mSquadron->addSquadronMemberDutiesBat($addarr);
    	}
    	$this->redirect("Amssquadron/showSquadron/uid/$uid/classCode/$classCode/gradeid/$gradeid/schoolid/$schoolid/stop_flag/0");
    }
    
     /*$inputname：input表单的名字
     *$name:生成上传文件的名字
     *$url:上传的文件存放的位置
     *$allow_type:允许上传的类型的数组，默认=====array('jpg','gif','png','bmp');
     */

    private function upload($inputname, $name , $url = '', $allow_type = array('jpg','gif','png','bmp'), $resize_width = 150, $resize_height = 90) {
        if (isset ( $_FILES [$inputname] ) && $_FILES [$inputname] != "") {
        	$up_init = array (
        		'attachmentspath' => Pathmanagement_ams::uploadSquadronLogo($url),
                'renamed' => true, 
                'newname' => $name, //不包含扩展名
                'ifresize' => true, 
                'resize_width' => $resize_width,
                'resize_height' => $resize_height,
        	    'allow_type' => $allow_type
            );
        
            if (file_exists ( Pathmanagement_ams::uploadSquadronLogo($url) . $name . '.jpg' )){
                unlink ( Pathmanagement_ams::uploadSquadronLogo($url) . $name . '.jpg' );
                unlink ( Pathmanagement_ams::uploadSquadronLogo($url) . $name.'_small.jpg' );
            }
            
            $uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
            $uploadObj->_set_options($up_init);
            $up_rs = $uploadObj->upfile($inputname);
            if (empty ( $up_rs )) {
                return false;
            }
            return $up_rs;
        }
        return false;
    }
    
    public function setSmallCounselor(){
    	$mSquadron = ClsFactory::Create('Model.mSquadron');
    	$squadroncode = $this->objInput->postInt('squadroncode');
    	$clientaccount = $this->objInput->postInt('clientaccount');
    	$smallId = $this->objInput->postInt('smallId');
    	$id = $this->objInput->postInt('id');
    	if(!$smallId){
    		$dataarr = array(
    			'squadron_id'=>$squadroncode,
    			'wmw_uid'=>$clientaccount,
    			'squadron_duties_id'=>3,
    			'db_createtime'=>date('Y-m-d H:i:s', time())
	    	);
	    	$mSquadron->addSquadronMemberDuties($dataarr);
	    	echo json_encode(array('error'=>array('code'=>1,'message'=>'添加成功')));
    	}elseif($smallId != $clientaccount){
    		$dataarr = array(
    			'wmw_uid'=>$clientaccount,
    			'db_updatetime'=>date('Y-m-d H:i:s', time())
	    	);
	    	$mSquadron->modifySquadronMemberDuties($dataarr,$id);
	    	echo json_encode(array('error'=>array('code'=>1,'message'=>'修改成功!')));
    	}else{
    		echo json_encode(array('error'=>array('code'=>-1,'message'=>'保存失败!')));
    	}
    }
	//ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }

    public function dealDutiesNameJson($duties_name_json, $name_len = 10) {
        if(empty($duties_name_json)) {
            return false;
        }
        
        $name_len = max($name_len, 0);
        $duties_name_json = htmlspecialchars_decode($duties_name_json);
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $duties_name_json = stripslashes($duties_name_json);
        }
        $duties_name_arr = json_decode($duties_name_json, true);
        
        if(empty($duties_name_arr)) {
            return false;
        }
           
        $name_arr = $uid_duties_name_arr = array();
        foreach($duties_name_arr as $duties) {
            $wmw_uid = intval($duties['wmw_uid']);
            $duties_name = trim(htmlspecialchars($duties['duties_name']));
            //字符串截取
            $name_len && $duties_name = cutstr($duties_name, $name_len, false);
            //建立用户uid到对应的职责名的关系
            $uid_duties_name_arr[$wmw_uid] = md5($duties_name);
            $name_arr[] = $duties_name;
        }
        //保证唯一，避免插入时sql执行出错
        $name_arr = array_unique($name_arr);
        
        $add_squadron_duties_list = $squadron_duties_list = array();
        $mSquadron = ClsFactory::Create('Model.mSquadron');
        $squadron_duties_list = $mSquadron->getSquadronDutiesByNames($name_arr);
        
        if(!empty($squadron_duties_list)) {
            $tmp_squadron_duties_list = array();
            foreach($squadron_duties_list as $id=>$squadron_duties) {
                $tmp_squadron_duties_list[$id] = $squadron_duties['squadron_duties_name'];
            }
            $squadron_duties_list = & $tmp_squadron_duties_list;
        }
        
        if(!empty($name_arr)) {
            $name_add_arr = array();
            foreach($name_arr as $name) {
                if(!in_array($name, $squadron_duties_list)) {
                    $name_add_arr[] = $name;
                }
            }
            if(!empty($name_add_arr)) {
                $now_date = date('Y-m-d H:i:s', time());
                $add_data_arr = array();
                foreach($name_add_arr as $name) {
                    $squadron_duties_data = array(
                        'squadron_duties_name' => $name,
                        'db_createtime' => $now_date,
                        'db_updatetime' => $now_date,
                    );
                    $add_data_arr[] = $squadron_duties_data;
                }
                //批量增加数据
                $mSquadron->addSquadronDutiesBat($add_data_arr);
                $add_squadron_duties_list = $mSquadron->getSquadronDutiesByNames($name_add_arr);
                //数据重组
                if(!empty($add_squadron_duties_list)) {
                    $tmp_add_arr = array();
                    foreach($add_squadron_duties_list as $id=>$squadron_duties) {
                        $tmp_add_arr[$id] = $squadron_duties['squadron_duties_name'];
                    }
                    $add_squadron_duties_list = $tmp_add_arr;
                }
            }
        }
        
        //合并数组，数据的来源有两部分
        $squadron_duties_list = !empty($squadron_duties_list) ? $squadron_duties_list : array();
        if(!empty($add_squadron_duties_list)) {
            foreach($add_squadron_duties_list as $id=>$duties_name) {
                $squadron_duties_list[$id] = $duties_name;
            }
        }
        //重新整理数据格式,保证对应的索引是字符串并且是唯一的
        $new_squadron_duties_list = array();
        if(!empty($squadron_duties_list)) {
            foreach($squadron_duties_list as $id=>$duties_name) {
                $md5_key = md5($duties_name);
                $new_squadron_duties_list[$md5_key] = $id;
            }
        }
        
        //处理数据返回的格式
        $return_arr = array();
        if(!empty($uid_duties_name_arr)) {
            foreach($uid_duties_name_arr as $wmw_uid=>$md5_key) {
                $id = isset($new_squadron_duties_list[$md5_key]) ? $new_squadron_duties_list[$md5_key] : 0;
                if($id > 0) {
                    $return_arr[$wmw_uid] = $id;
                }
            }
        }
        
        return !empty($return_arr) ? $return_arr : false;
    }
    
    /**
     * 清理过期的中队logo文件
     * @param $squadron_id
     */
    public function clearOldSquadronLogo($squadron_id) {
        if(empty($squadron_id)) {
            return false;
        }
        
        $mSquadron = ClsFactory::Create('Model.mSquadron');
        $squadron_list = $mSquadron->getSquadronById($squadron_id);
        $squadron = & $squadron_list[$squadron_id];
        
        $squadron_logo = $squadron['squadron_logo'];
        if(!empty($squadron_logo)) {
            //获取当前的合法文件列表信息
            list($base_filename, $suffix) = explode(".", $squadron_logo);
            $legal_filenames = array(
                $squadron_logo,
                $base_filename . "_small." . $suffix,
            );
            
            $dir_path = Pathmanagement_ams::getSquadronLogo() . $squadron_id;

            if(is_dir($dir_path) && is_readable($dir_path)) {
                $dir = dir($dir_path);
                while(($file = $dir->read()) !== false) {
                    //忽略目录和当前最新的文件列表
                    if(in_array($file, array('.', '..')) || in_array($file, $legal_filenames)) {
                        continue;
                    }
                    
                    @ unlink($dir_path . "/" . $file);
                }
            }
        }
    }
    
}
