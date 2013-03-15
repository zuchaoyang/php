<?php
class FriendsAction extends SnsController{
	const LENGTH = 10;
	const SERLENGTH = 64;
	public function _initialize(){
		parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");
		
		$this->assign('chanelid',"chanel3");
	}
	
	//好友搜索
	public function ConditionsMatch(){
		$srkey_encode = $this->objInput->getStr('srkey');
		$srkey = urldecode($srkey_encode);
		$client_class = array_shift($this->user['client_class']);
		$class_code = $client_class['class_code'];
		$client_typeUser = $this->user['client_type'];
		$page = $this->objInput->getInt('page');
		if(empty($page)){
			$page = 1;
		}
		$offset = ($page-1)*self::SERLENGTH;
		$limit = self::SERLENGTH+1;
		if(!empty($srkey)){
    		$mUser = ClsFactory::Create('Model.mUser');
    		$newUserInfo = $mUser->getUserByUsername($srkey,$offset,$limit);
    		
    		if(empty($newUserInfo)) {
    		    $newUserInfo = $mUser->getClientAccountById($srkey);
    		}
    		if($newUserInfo){
    			foreach($newUserInfo as $kyes=>$vallist){
    				$newUserInfo[$kyes]['account_headpic_path'] = Pathmanagement_sns::getHeadImg($vallist['client_account']) . $vallist['client_headimg'];
    				if ($client_typeUser==CLIENT_TYPE_STUDENT){
    					if($vallist['client_type']==CLIENT_TYPE_FAMILY){
    						unset($newUserInfo[$kyes]);
    					}
    				}
    				if ($client_typeUser==CLIENT_TYPE_FAMILY){
    					if(intval($vallist['client_type'])==CLIENT_TYPE_STUDENT){
    						unset($newUserInfo[$kyes]);
    					}
    				}
    			}
    		}
    		$webUrl = "/Homefriends/Friends/ConditionsMatch";
    		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
    		if(count($newUserInfo) > self::SERLENGTH){
    			array_pop($newUserInfo);
    			$nextpageno = $page+1;
    		}else{
    			$nextpageno = $page;
    		}
    		$srkey1 = urlencode($srkey_encode);
    		$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/page/".$prvpageno."?srkey={$srkey1}'>上一页</a> | <a href='".$webUrl."/page/".($nextpageno)."?srkey={$srkey1}'>下一页</a></div>");
    		$this->assign('account',$this->getCookieAccount());
    		$this->assign('listRSDATA',$newUserInfo);
    		$this->assign('pageinfo_count',count($newUserInfo));
		}
		$this->assign('outhsrkey',$srkey);
		
		$this->display('ConditionsMatch');
	}


	
	//发送好友消息等待通过 --不测试时还原使用
	public function addfriendrequest(){
		
		$addurl= trim($_REQUEST['addurl']);
		$client_account = trim($this->objInput->postStr('reacccount'));//要操作执行添加的好友
		$add_account=$this->getCookieAccount();  //请求人

		$mAccountrelation = ClsFactory::create('Model.mAccountrelation');

		$RSmRelation = $mAccountrelation->getaccountrelationbyuid($add_account, null, null, array('friend_account'=>$client_account));
		if(!empty($RSmRelation)) {
			if($addurl==""){
				echo "disabled";exit;
			}else{
				echo "<script>alert('你们已经是好友了');window.history.go(-1);</script>";
				exit;
			}
		}
		//要加好友账号身份
		$thisUseType = $this->objInput->postStr('thisUseType');
		//在好友添加之前可验证身份之间是否可以互相加为好友
	
		$add_date = date('Y-m-d H:i:s',time());

		//原SQL 包含 and upd_account is null" 2012-01-14 由LYT去掉
		//查找是否已经发送过好友请求
		
		//to do 
		$news_info = ClsFactory::Create('Model.mNewsInfo');
		$news_list = $news_info->getNewsInfoByToUid($client_account);
		$news_rs1 = array();
		if(!empty($news_list)) {
    		foreach($news_list as $key=>$val) {
    			if($val['news_type'] == 'HY' && $val['add_account'] == $add_account) {
    				$news_rs1[$key] = $val;
    			} else {
    				unset($news_list[$key]);
    			}
    		}
		}
		if(!empty($news_rs1)){
			echo "haverequest";exit;
		}else{
			
			if($addurl==""){
				$request_content=$this->objInput->postStr('msg');
			}else{
				$request_content=$this->objInput->postStr('request_content');
			}
			
			$request_content = urldecode($request_content);
			if($request_content != ""){
				$request_content = '(附加信息：'.$request_content.")";
			}
			$news_content="请求加您为好友".$request_content;
			$newsdata=array(
				'news_type'=>'HY',
				'news_toaccount'=>$client_account,
				'news_content'=>$news_content,
				'add_account'=>$add_account,
				'add_date'=>$add_date
			);
			 
			$news_rs2 = $news_info->addNewsInfo($newsdata);
			if($news_rs2){
				echo "addfriendok";exit;
			}else{
				echo "error";exit;
			}
		}
	}
	
	
}
?>
