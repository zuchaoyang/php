<?php
class NewsAction extends SnsController{
	public function _initialize(){
	    parent::_initialize(); 
		import("@.Common_wmw.Pathmanagement_sns");

		$this->assign('chanelid',"chanel4");
	}
	

	//最新消息提示
	public function zxnews(){
		$this->zuinews();
		$this->display('zxnews');
	}
	
	//忽略最新消息方法
	public function zxqx(){
		$news_type = $this->objInput->getStr('news_type');
		$news_id = $this->objInput->getInt('news_id');
		
		if($news_type){
			if($news_type == "HY"){
				$this->addfriend();
			}
		}
		$this->qx($news_id);
		$this->redirect('News/zxnews');
	}
	
	//忽略好友的请求消息处理方法
	function qx($news_id){
	    if(empty($news_id)) {
		    $news_id = $this->objInput->getStr('news_id');
	    }
	    $news_id = intval($news_id);
	    $news_id = $news_id > 0 ? $news_id : 0; 
		if(!empty($news_id)) {
    		$NewsInfo = ClsFactory::Create('Model.mNewsInfo');
    		$resault = $NewsInfo->delNewsInfo($news_id);
		}
	}
	
	

	//最新消息
	function zuinews(){
		$account=$this->getCookieAccount();
		$mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
		$mUser = ClsFactory::Create('Model.mUser');
        
        $new_newsData = $mNewsInfo->getNewsInfoByToUid($account);
		$new_newsData = array_shift($new_newsData);

		if($new_newsData){
			$user_account = array();
			foreach($new_newsData as $key=>$val){
				$user_account[$val['add_account']] = $val['add_account'];
			}
			$userlist = $mUser->getUserBaseByUid($user_account);
			unset($user_account);
			$newmFriendData = array();
			foreach($new_newsData as $key=>$val){
				if(in_array($val['news_type'],array('XT' , 'HY', 'FXT'))) {
					$val['client_name'] = $userlist[$val['add_account']]['client_name'];
					$val['headimg']=Pathmanagement_sns::getHeadImg($val['add_account']) . $userlist[$val['add_account']]['client_headimg'];
					$newmFriendData[$key] = $val;
				}else{
					unset($new_newsData[$key]);
				}
				
			}
			unset($val);
		}
		$this->assign('news_count',count($new_newsData));
		$this->assign('newsf',$newmFriendData);

	}


	//添加好友
	function addfriend(){
		$news_type=$this->objInput->getStr('news_type');
		$news_id=$this->objInput->getStr('news_id');
		
		$NewsInfo = ClsFactory::Create('Model.mNewsInfo');
		$newsinfo = $NewsInfo->getNewsInfoById($news_id);
		$newsinfo = array_shift($newsinfo);
		
		$news_toaccount=$newsinfo['news_toaccount'];
		$add_account=$newsinfo['add_account'];
		$mRelation = ClsFactory::Create('Model.mAccountrelation');
		$upd_date=date('Y-m-d H:i:s');
		$mUser = ClsFactory::Create('Model.mUser'); 
		$userInfo = $mUser->getUserBaseByUid($news_toaccount);
		$friendName = $userInfo[$news_toaccount]['client_name'];
		$date=array(
			'news_id'=>$news_id,
			'upd_date'=>$upd_date,
			'upd_account'=>$news_toaccount
		);
		
		$date1=array(
			'client_account'=>$news_toaccount,
			'friend_account'=>$add_account,
			'add_account'=>$news_toaccount,
			'add_date'=>$upd_date
		);
		$date2=array(
			'client_account'=>$add_account,
			'friend_account'=>$news_toaccount,
			'add_account'=>$add_account,
			'add_date'=>$upd_date
			
		);
		$data3=array(
		    'news_type'=>NEWS_INFO_XT_FRIEND,
		    'news_toaccount'=>$add_account,
			'add_account'=>$news_toaccount,
		    'news_content'=>$friendName.'同意了您的好友请求',
			'add_date'=>$upd_date,
		    'add_time'=>time()
		);

        $mRelation->addAccountRelation($date1);	
		$mRelation->addAccountRelation($date2);
		$NewsInfo->modifyNewsInfo($date,$news_id);
		$NewsInfo->addNewsInfo($data3);
		
	}
}

