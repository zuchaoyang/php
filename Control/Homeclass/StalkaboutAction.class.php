<?php

class StalkaboutAction extends SnsController{
	
    public function _initialize(){
        parent::_initialize(); 
        
		import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Date");

		$this->assign('chanelid',"chanel1");

	}

	/*我的新鲜事首页默认加载信息 更新于：2012-3-17 by lyt*/
	public function index(){
		$LoginUserAccount = $this->getCookieAccount();
		
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);

		$mData = ClsFactory::Create('Model.mPersonTalk');	
		
		$arrInfoData = $mData->getPersonTalkByAddAccount($LoginUserAccount);
		//$arrInfoData 降为二维
		$new_arrInfoData = array();
		if(!empty($arrInfoData)){
			foreach ($arrInfoData as $key=>$val) {
				foreach ($val as $k=>$v) {
					$new_arrInfoData[] = $v;
				}
			}
		}
		$arrInfoData = $new_arrInfoData;
		unset($new_arrInfoData);
		
		$faceSearch=$faceReplace=array();
		$facelist = Constancearr::getfacelist();
		$mUser = ClsFactory::Create('Model.mUser');
		if($arrInfoData){
			$sortkeys = array();
			for($i=0;$i<count($arrInfoData);$i++){
				$userlist = $mUser->getUserBaseByUid($arrInfoData[$i]['add_account']);
				$arrInfoData[$i]['account_headpic_path'] = $userlist[$arrInfoData[$i]['add_account']]['client_headimg_url'];
				$arrInfoData[$i]['client_name']= $userlist[$arrInfoData[$i]['add_account']]['client_name'];
				
				$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
			
				$arrInfoData[$i]['sign_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$arrInfoData[$i]['sign_content']);
				if(!strpos($arrInfoData[$i]['sign_content'],IMG_SERVER)){
   					$arrInfoData[$i]['sign_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$arrInfoData[$i]['sign_content']);
				}

				$arrInfoData[$i]['sign_content'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrInfoData[$i]['sign_content']);
				$arrInfoData[$i]['sign_content'] = str_replace("[/IMG]", "'>", $arrInfoData[$i]['sign_content']);
				$newKey = date('Y-m-d H:i:s',$arrInfoData[$i]['add_date']);
				$arrInfoData[$i]['add_datetime']=Date::formatedateparams($newKey);

				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					
					$arrInfoData[$i]['sign_content'] = str_replace($faceSearch, $faceReplace, $arrInfoData[$i]['sign_content']);
				}
				

			}//for

			foreach($arrInfoData as $key=>&$value) {
	            $sortkeys[$key] = $value['sign_id'];
	            if(!$value['comment_nums']){
	            	$value['comment_nums']=0;
	            }
	        }
			array_multisort($sortkeys , SORT_DESC , $arrInfoData);
			$newarr_talkInfo = array_slice($arrInfoData, 0, WMW_XXS_LIMIT);	
		}//if
		
		$this->assign('keyUserType',$this->user['client_type']);
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);
		$this->assign('datacount',$DataRows);
		$this->assign('class_code' ,$class_code);
		$this->assign('LoginUserAccount',$LoginUserAccount);
		
		$this->display('StalkAbout');
	}

	/*获取最新发布说说内容 更新于 2012-3-17 by lyt*/
	function getNewTalkMsg(){
		$gettype = trim(($this->objInput->postStr('gettype')));
		
		$LoginUserAccount = $this->getCookieAccount();
		$mUser = ClsFactory::Create('Model.mUser');
		
		switch($gettype){
			case "spacehome" : 
			case "sTalk" :
				$mSTalkAbount = ClsFactory::Create('Model.mPersonTalk');
				$return_m_state = $mSTalkAbount->getNewTalkcontentinfoByAddAccount($LoginUserAccount);

				if($return_m_state){
					$sign_content = $return_m_state['sign_content'];
					$add_account = $return_m_state['add_account'];
					$userlist = $mUser->getUserBaseByUid($add_account);
					$client_headimg_url = $userlist[$add_account]['client_headimg'];
					$account_headpic_path = Pathmanagement_sns::getHeadImg($add_account) . $client_headimg_url;
					$client_name = $userlist[$add_account]['client_name'];
				}
			break;
			case "sTalkBJ" :
				$mSTalkAbount = ClsFactory::Create('Model.mClasstalk');
				$return_m_state = $mSTalkAbount->getLastClassTalkcontentinfoByaccount($LoginUserAccount);

				if($return_m_state){
					$sign_content = $return_m_state['talk_content'];
					$add_account = $return_m_state['add_account'];
					$userlist = $mUser->getUserBaseByUid($add_account);
					$client_headimg_url = $userlist[$add_account]['client_headimg'];
					$account_headpic_path = Pathmanagement_sns::getHeadImg($add_account) . $client_headimg_url;
					$client_name = $userlist[$add_account]['client_name'];
				}
			break;
			
		}
		

	
		if(!$return_m_state){
			echo "failed";
			exit();
		}
		
		$facelist = Constancearr::getfacelist();
		$faceSearch=$faceReplace=array();
		if($facelist){
			foreach($facelist as $key => $val){
				$alt = str_replace("/", "", $facelist[$key]);
				$faceSearch[] = $facelist[$key];
				$faceReplace[] = "<img src=\"".IMG_SERVER."/Public/images/face/$key.gif\" width=\"22\" height=\"22\" alt=\"".$alt."\" />";
			}
		}

		$sign_content = str_replace($faceSearch, $faceReplace, $sign_content);
		$sign_content = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $sign_content);
		$sign_content = str_replace("[/IMG]", "'>", $sign_content);
		$sign_content = urlencode($sign_content);
		
		$comment_nums = $return_m_state[comment_nums];
		$sign_id = $return_m_state[sign_id];
		$add_date = $return_m_state[add_date];
		$add_datetime =  Date::formatedateparams(date('Y-m-d H:i:s',$add_date));
		echo "<data>";
		echo "<m_id>".$sign_id."<m_id>";
		echo "<m_userid>".$add_account."<m_userId>";
		echo "<m_msgcontent>".$sign_content."<m_msgcontent>";
		echo "<m_subdate>".$add_datetime."<m_subdate>";
		echo "<m_plnum>".$comment_nums."<m_plnum>";
		echo "<m_headimg>".$account_headpic_path."<m_headimg>";
		echo "<m_client_name>".$client_name."<m_client_name>";
		echo "<data>";
		
		exit();	
	}


	/*保存说说内容 更新于 2012-3-17 by lyt:*/
	function sTalkSaveComplete(){
		$personTalkContent = urldecode($this->objInput->postStr('msg'));
		$sendwhere = urldecode($this->objInput->postStr('sendwhere'));
		$LoginUserAccount = $this->getCookieAccount();
		
		if(get_magic_quotes_gpc()){
			$personTalkContent = stripslashes($personTalkContent);
		}
		$personTalkContent = htmlspecialchars($personTalkContent);
		$personTalkContent = str_replace("'", "&#039;", $personTalkContent);
		
		if(!$personTalkContent){
			echo "nomsg";exit();
		}

		$pic = $this->objInput->postStr('pic');
		
		$facelist = Constancearr::getfacelist();
		$faceSearch=$faceReplace=array();
		if($facelist){
			foreach($facelist as $key => $val){
				$alt = str_replace("/", "", $facelist[$key]);
				$faceSearch[] = $facelist[$key];
				$faceReplace[] = "<img src=\"".IMG_SERVER."/Public/images/face/$key.gif\" width=\"22\" height=\"22\" alt=\"".$alt."\" />";
			}
		}
		$personTalkContent = str_replace($faceSearch, $faceReplace, $personTalkContent);
		$photo = "";
		if($pic){
			$ext = substr($pic,strpos($pic, ".")+1, 3);
			$extarray = array("gif","jpg","png");
			if(in_array($ext, $extarray)){
				$filename = "p".$LoginUserAccount.time().".".$ext;
				$oldfilename = "p".$LoginUserAccount.".".$ext;
				$newfile = Pathmanagement_sns::uploadTalkIco() . $filename;
				$tmpfile = Pathmanagement_sns::uploadTalktmp() . $oldfilename;
				
				$setOption = array(
						array(
    						'scale'=>100,
    						'path'=>$newfile
						)
				);
				$imageObj = ClsFactory::Create('@.Common_wmw.WmwImage');
				$rs = $imageObj->scale($tmpfile, $setOption);
				if($rs){
					$photo = $filename;
				}	
			}	
		}
		if(!empty($photo)){
			$personTalkContent = $personTalkContent."[IMG]".$photo."[/IMG]";
		}	
		
		$mFeed = ClsFactory::Create('Model.mFeed');
		if(!empty($sendwhere)) { 
			switch($sendwhere){
				case "spacehome" : 
				case "sTalk" :

					$arrTalkData = array(
						'add_account' =>$LoginUserAccount,
						'add_date' =>time(),
						'sign_content' =>$personTalkContent,
						'comment_nums' =>0,
					);


					$mSTalkAbount = ClsFactory::Create('Model.mPersonTalk');	    
					$retrunNewId = $mSTalkAbount->addPersonTalk($arrTalkData,true);
					$result = $mFeed->addPersonFeed(intval($LoginUserAccount),intval($retrunNewId),PERSON_FEED_TALK,FEED_NEW,time());
				break;
				case "sTalkBJ" :
					//$class_code = key($this->user['client_class']);
					$class_code = trim(($this->objInput->postStr('sendclass_code')));
	
					$arrTalkData = array(
						'add_account' =>$LoginUserAccount,
						'add_date' =>time(),
						'talk_content' =>$personTalkContent,
						'class_code'  =>$class_code,
						'comment_nums' =>0,
					);
					$mClasstalk = ClsFactory::Create('Model.mClasstalk');	    
					$retrunNewId = $mClasstalk->addClassTalk($arrTalkData,true);
					$mFeed->addClassFeed(intval($class_code),intval($LoginUserAccount),intval($retrunNewId),CLASS_FEED_TALK,FEED_NEW,time());
					break;
			}
		}
	   if($retrunNewId){
	       echo "successed";
	   }else{
	       echo "failed";
	   }
		exit();
	}
	


	/*验证上传状态*/
	function ajaxUpCheck(){
		$ext = trim($_POST['fileext']);
		
		$extarray = array("gif","jpg","png");
		if(in_array($ext, $extarray)){
			echo "successed";
		}else{
			echo "系统仅支持gif jpg png类型";
		}
		exit();

	}
	
	/*说说图片上传*/
	function ajaxPhotoUpload(){
		if(is_uploaded_file($_FILES['pic']['tmp_name'])){
			$ext = substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'], ".")+1, 3);
			$extarray = array("gif","jpg","png","bmp");
			if(!in_array($ext, $extarray)){
				echo "type error";
				exit();
			}

			$filename = "p".$this->getCookieAccount().".".$ext;
			if(move_uploaded_file($_FILES['pic']['tmp_name'], Pathmanagement_sns::uploadTalktmp() . $filename)) {
				echo "successed";
			}

		}else{
			echo "failed";
		}
		exit();
	}
	
	/*发表评论个人说说*/
	function sTalkCommentSub(){
		$msg = urldecode($this->objInput->postStr('msg'));
		$placcount = urldecode($this->objInput->postStr('placcount'));
		$Talk_id = $this->objInput->postStr('msgid');
		if(!$msg){echo "nomsg";	exit();}
		
		
		$LoginUserAccount = $this->getCookieAccount();
		
		$timestamp = time();
		$mTalkComment = ClsFactory::Create('Model.mPersontalkcomment');	   
		$talkCommentData = array(
	        'sign_id' => $Talk_id,
    	    'add_date' => time(),
    	    'plun_content' => $msg,
			'add_account' => $LoginUserAccount ,
	    );	
		
		$return_m_state = $mTalkComment->addPersonTalkComment($talkCommentData);
		if($return_m_state){
			$mPersonTalk = ClsFactory::Create('Model.mPersonTalk');	   
			$mPersonTalk->modifyPersonTalk($Talk_id);

			echo "successed";
		}
		exit();
	}


	//评论列表
	function commentlist(){
		$Talk_id = $this->objInput->postInt('msgid');
		$mTalkobj = ClsFactory::Create('Model.mPersontalkcomment');	 
		$mUser = ClsFactory::Create('Model.mUser');	
		$facelist = Constancearr::getfacelist();
		$faceSearch=$faceReplace=array();
		$tmp_return_m_state = $mTalkobj->getPersonTalkCommentBySignId($Talk_id);
		
		$return_m_state = &$tmp_return_m_state[$Talk_id];
		unset($tmp_return_m_state);
		
		if($return_m_state){
			foreach($return_m_state as $plun_id => $info){
				$login_info = $mUser->getUserByUid($info['add_account']);
				$info['client_name'] = $login_info[$info['add_account']]['client_name'];
				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src=\"".IMG_SERVER."/Public/images/face/$key.gif\" width=\"22\" height=\"22\" alt=\"".$alt."\" />";
					}
				}
				$info['plun_content'] = str_replace($faceSearch, $faceReplace, $info['plun_content']);	
				echo "<div class=\"item\">";
				echo "<span class=\"name\">".$info['client_name'].":</span>".$info['plun_content'];
				echo "<span class=\"date\">".date('Y-m-d H:i:s',$info['add_date'])."</span>";
				echo "</div>";
			}
		}
	}

	//删除评论
	function deleteComment(){
		$LoginUserAccount = $this->getCookieAccount();
		$Talk_sayid = $this->objInput->getInt('sayid');
		$place = $this->objInput->getStr('place');

		$mTalkobj = ClsFactory::Create('Model.mPersonTalk');	  
		$return_m_state = $mTalkobj->delPersonTalk($Talk_sayid);
		$mPersontalkcomment = ClsFactory::Create('Model.mPersontalkcomment');

		
	    $plun_arr = $mPersontalkcomment->getPersonTalkCommentBySignId($Talk_sayid);
	    $plun_list = & $plun_arr[$Talk_sayid];
	    if(!empty($plun_list)) {
            foreach($plun_list as $plun_id=>$plun) {
                $mPersontalkcomment->delPersonTalkComment($plun_id);
            }
	    }

		$mFeed = ClsFactory::Create('Model.mFeed');				
		$mFeed->addPersonFeed(intval($LoginUserAccount),intval($Talk_sayid),PERSON_FEED_TALK,FEED_DEL,time());


		switch($place){
			case "home" :
				$this->redirect("Stalkabout/index");
				break;
			case "space" :
				$this->redirect("../Homeuser/Index/spacetalk/spaceid/".$LoginUserAccount);
				break;
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


	//更多动态加载
	function morefeedlist(){
		
		$nextLlimit = $this->objInput->getInt('nextLlimit');
		$account = $this->getCookieAccount();
		$mUser = ClsFactory::Create('Model.mUser');

		$mData = ClsFactory::Create('Model.mPersonTalk');	
		$arrInfoData = $mData->getPersonTalkByAddAccount($account);
		//$arrInfoData 降为二维
		$new_arrInfoData = array();
		if(!empty($arrInfoData)){
			foreach ($arrInfoData as $key=>$val) {
				foreach ($val as $k=>$v) {
					$new_arrInfoData[] = $v;
				}
			}
		}
		$arrInfoData = $new_arrInfoData;
		unset($new_arrInfoData);
		
		$faceSearch=$faceReplace=array();
		$facelist = Constancearr::getfacelist();
		

		if($arrInfoData){
			$sortkeys = array();
			for($i=0;$i<count($arrInfoData);$i++){
				$userlist = $mUser->getUserBaseByUid($arrInfoData[$i]['add_account']);
				$arrInfoData[$i]['account_headpic_path'] = $userlist[$arrInfoData[$i]['add_account']]['client_headimg_url'];
				$arrInfoData[$i]['client_name']= $userlist[$arrInfoData[$i]['add_account']]['client_name'];
				$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
				
				$arrInfoData[$i]['sign_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$arrInfoData[$i]['sign_content']);
				if(!strpos($arrInfoData[$i]['sign_content'],IMG_SERVER)){
   					$arrInfoData[$i]['sign_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$arrInfoData[$i]['sign_content']);
				}

				$arrInfoData[$i]['sign_content'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrInfoData[$i]['sign_content']);
				$arrInfoData[$i]['sign_content'] = str_replace("[/IMG]", "'>", $arrInfoData[$i]['sign_content']);
				$newKey = date('Y-m-d H:i:s',$arrInfoData[$i]['add_date']);
				$arrInfoData[$i]['add_datetime']=Date::formatedateparams($newKey);

				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$arrInfoData[$i]['sign_content'] = str_replace($faceSearch, $faceReplace, $arrInfoData[$i]['sign_content']);
				}
			}//for
	
	
			foreach($arrInfoData as $key=>&$value) {
	            $sortkeys[$key] = $value['sign_id'];
	            if(empty($value['comment_nums'])){
	            	$value['comment_nums']=0;
	            }
	        }

			array_multisort($sortkeys , SORT_DESC , $arrInfoData);
			$newarr_talkInfo = array_slice($arrInfoData, $nextLlimit, WMW_XXS_LIMIT+$nextLlimit);
			if($newarr_talkInfo){
				foreach($newarr_talkInfo as $key => $value){
					$htmldata ="<div class=\"sub_m_message\">";
						$htmldata =$htmldata."<div class=\"sub_m_message_l\">";
							$htmldata =$htmldata."<a href='/Homeuser/Index/spacehome/spaceid/".$value['add_account']."' title='访问Ta的空间' target='_blank'><img src=\"".$value['account_headpic_path']."\" width=\"60\" height=\"60\" onerror=\"this.src='".IMG_SERVER."/Public/images/head_pics.jpg'\"/></a> ";
						$htmldata =$htmldata."</div>";
						$htmldata =$htmldata."<div class=\"sub_m_message_r\">";
							$htmldata =$htmldata."<h3><span>".$value['add_datetime']."</span>".$value['client_name']."</h3>";
							$htmldata =$htmldata."<div class=\"sub_m_message_r_t\"></div>";
							$htmldata =$htmldata."<div class=\"sub_m_message_rt\"></div>";
							$htmldata =$htmldata."<div class=\"sub_m_message_rt1\">";
								$htmldata =$htmldata."<p>".$value['sign_content'];
							$htmldata =$htmldata."</div>";
							$htmldata =$htmldata."<div class=\"sub_m_message_rm\">
								<span><a href=\"#\" title=\"删除\" onclick=\"return deleteSay('".$value['sign_id']."','home');\" ><font color='#889DB6'>删除</font></a></span>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" title=\"评论\" onclick=\"return commentXXX(this, '".$value['sign_id']."','".$value['add_account']."');\" ><font color='#889DB6'>发表评论</font></a>&nbsp;&nbsp;&nbsp;<a href=\"#\" class='link1' title=\"查看评论\"  onclick=\"return listcomment(this, '".$value['sign_id']."');\">评论</a>(<span id=\"pcount_".$value['sign_id']."\">".$value['comment_nums']."</span>)↓";
							
							$htmldata =$htmldata."<div id=\"plist_".$value['sign_id']."\" class=\"plist\" style='display:none'></div>";
							$htmldata =$htmldata."</div>";
							
						$htmldata =$htmldata."</div>";

					$htmldata =$htmldata."<div class=\"kong\"></div>";
					$htmldata =$htmldata."</div>";

					if($Thtmldata == "") {
						$Thtmldata = $htmldata ;
					} else {
						$Thtmldata = $Thtmldata.$htmldata ;
					}
				}
			
				$nextLlimitValue = $nextLlimit+WMW_XXS_LIMIT;
			}
				
		}

		
		echo $Thtmldata;
		if(!empty($nextLlimitValue)) {
			echo "<script>document.getElementById('nextLlimit').value=".$nextLlimitValue.";</script>";
		}
	}



}

$facelist = array(
		0 => "/惊讶",
		1 => "/撇嘴",
		2 => "/色",
		3 => "/发呆",
		4 => "/得意",
		5 => "/大哭",
		6 => "/害羞",
		7 => "/闭嘴",
		8 => "/睡",
		9 => "/流泪",
		10 => "/尴尬",
		11 => "/发怒",
		12 => "/调皮",
		13 => "/呲牙",
		14 => "/微笑",
		15 => "/难过",
		16 => "/酷",
		17 => "/冷汗",
		18 => "/抓狂",
		19 => "/吐",
		20 => "/偷笑",
		21 => "/可爱",
		22 => "/白眼",
		23 => "/傲慢",
		24 => "/饥饿",
		25 => "/困",
		26 => "/惊恐",
		27 => "/流汗",
		28 => "/憨笑",
		29 => "/大兵",	
		30 => "/奋斗",
		31 => "/咒骂",
		32 => "/疑问",
		33 => "/嘘",
		34 => "/晕",
		35 => "/折磨",
		36 => "/衰",
		37 => "/骷髅",
		38 => "/敲打",
		39 => "/再见",
		40 => "/擦汗",
		41 => "/抠鼻",
		42 => "/鼓掌",
		43 => "/糗大了",
		44 => "/坏笑",
		
		45 => "/左哼哼",
		46 => "/右哼哼",
		47 => "/哈欠",
		48 => "/鄙视",
		49 => "/委屈",
		50 => "/快哭了",
		51 => "/阴险",
		52 => "/亲亲",
		53 => "/吓",
		54 => "/可怜",
		55 => "/菜刀",
		56 => "/西瓜",
		57 => "/啤酒",
		58 => "/篮球",
		59 => "/乒乓",
);
