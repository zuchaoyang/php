<?php
class FriendsmanageAction extends SnsController{
	public function _initialize(){
        parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");

		$this->assign('chanelid',"chanel3");
	}
	
	public function index(){
		$groupid = $this->objInput->getInt('groupid');
	    $client_account = $this->getCookieAccount();
	   	$searchKey = $this->objInput->postStr('searchKey');
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=10; //每页数量

		$filters = array(
			'friend_group' => $groupid,
		);	

		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$tmp_mFriendData = $mAccountrelation->getAccountRelationByAddAccount($client_account);
		$mFriendData = $tmp_mFriendData[$client_account];
		
		$friend_accounts_list = array();
		if(!empty($mFriendData)) {
    		foreach($mFriendData as $val) {
    		    $friend_accounts_list[] = $val['friend_account'];
    		}
		}
		//好友的分组
		$this->friend_group_count_list($mFriendData);
		if($mFriendData){
			$sortkeys = array();
			$newLog = array();
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($friend_accounts_list);
			foreach($mFriendData as $key=>$val){
				$val['client_name'] = $userlist[$val['friend_account']]['client_name'];
				$val['headimg']=Pathmanagement_sns::getHeadImg($val['friend_account']) . $userlist[$val['friend_account']]['client_headimg'];

				$groupdata = $mClientgroup->getClientGroupById($val['friend_group']);
				if($groupdata){
					$groupdata = array_shift($groupdata);
					$val['group_name'] = $groupdata['group_name'];
				}
				

				$newmFriendData[] = $val;
				
			}
			unset($val);

			foreach($newmFriendData as $key=>$value) {
	            $sortkeys[$key] = $value['add_date'];
	        }

			array_multisort($sortkeys , SORT_DESC , $newmFriendData);
			$newarr_FriendData = array_slice($newmFriendData, ($page-1)*$pagesize, $pagesize+1);
			if(count($newarr_FriendData) > $pagesize){
				array_pop($newarr_FriendData);
				$f=true;
			}
			$webUrl = "/Homefriends/Friendsmanage/index";
			$f ? $nextpageno = $page+1 : $nextpageno = $page;
			intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
			$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
			$this->assign('pageno',$pageno);
		}
		
		$this->assign('name','搜索姓名');
		$this->assign('friend_list',$newarr_FriendData);
		$this->assign('addclient_account',$client_account);
		
		$this->display("FriendManage");

	}

	
	//好友的分组列表
	public function friendgroup(){
		
		$client_account=$this->getCookieAccount();
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$client_group_info = $mClientgroup->getClientGroupByUid($client_account);
		//数据 维度处理 3维 变 2维 （只有一个账号的分组列表 不用循环处理）
		$client_group_info = &$client_group_info[$client_account];
		if($client_group_info){
			$newgrouparr = array();
			$friendnumsdata = $mAccountrelation->getAccountRelationByAddAccount($client_account);
			foreach($client_group_info as $key =>$val){
				$val['friendnums']="0";
				if($friendnumsdata){
				    foreach($friendnumsdata as $friend) {
    				    if($friend['friend_group']==$val['group_id']){
    						$val['friendnums'] = count($friendnumsdata);
    					}else{
    						$val['friendnums'] = "0";
    					}
				    }
				}
			
				$newgrouparr[] = $val;
			}
		}
		
		return $newgrouparr;
	}


	//修改好友分组
	function modifyfriendgroup(){
		$group_id = $this->objInput->getStr('group_id');
		$objData = ClsFactory::Create('Model.mClientgroup');
		$groupdata = $objData->getClientGroupById($group_id);
		if($groupdata){
			$groupdata = array_shift($groupdata);
			$this->assign('group_name',$groupdata['group_name']);
		}

		$this->assign('group_id',$group_id);
		
		$this->display("modifyfriendgroup");
	}
	
	function modifyfriendgroupdo(){
		$group_name=trim($this->objInput->postStr('group_name'));
		$group_id=trim($this->objInput->postInt('group_id'));
		$cookie_account = $this->getCookieAccount();
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$data=array(
			'group_name'=>$group_name,
		);	

		$objData = $mClientgroup->getClientGroupByGroupName($group_name,$cookie_account);
		if($objData){
		}else{
			$mClientgroup->modifyClientGroup($data,$group_id);
		}
		echo "<div style='font-size:18px;color:#9C0D3F;float:center;margin-top:20px;text-align:center;font-weight:bold;'><img src='".IMG_SERVER."/Public/images/new/Ok.jpg'>好友分组名称修改成功</div>";
		echo "<script>setTimeout('parent.location.reload();parent.tb_remove();',1000);</script>";

	}


	//更改好友分组
	function changefriendgroup(){
		$group_id=trim($this->objInput->getStr('group_id'));
		$fid=trim($this->objInput->getStr('fid'));
		$client_group_info =$this->friendgroup();
		$this->assign('group_list',$client_group_info);
		$this->assign('cur_group_id',$group_id);
		$this->assign('fid',$fid);
		
		$this->display("changefriendgroup");
	}

	function modifyFriendGroupByfriendaccount(){
		$groupcheck=trim($this->objInput->postStr('groupcheck'));
		$fid=trim($this->objInput->postInt('fid'));
		
		$client_account = $this->getCookieAccount();
		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$data=array(
			'friend_group'=>$groupcheck,
		);
	

		$mAccountrelation->modifyAccountRelationByCompositeKey($data,$client_account,$fid);
		echo "<div style='font-size:18px;color:#9C0D3F;float:center;margin-top:20px;text-align:center;font-weight:bold;'><img src='".IMG_SERVER."/Public/images/new/Ok.jpg'>好友分组设置成功</div>";
		echo "<script>setTimeout('parent.location.reload();parent.tb_remove();',1000);</script>";
	}	

	public function friend_group_count_list($mFriendData){
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$client_group_info = $mClientgroup->getClientGroupByUid($this->user['client_account']);
		//数据 维度处理 3维 变 2维 （只有一个账号的分组列表 不用循环处理）
		$client_group_info = &$client_group_info[$this->user['client_account']];
		
		$group_list=array();
		foreach($mFriendData as $key=>&$val){
			if($val['friend_group']){
				$group_list[$val['friend_group']][$val['friend_account']] = $val;
			}else{
				$group_list['default'][$val['friend_account']] = $val;
			}
		}
		if($client_group_info){
			foreach($client_group_info as $key1=>&$val){
				$val['friendnums'] = count($group_list[$val['group_id']]);
			}
			$default=array('group_id'=>0,'group_name'=>"默认未分组",'friendnums'=>count($group_list['default']));
			array_unshift($client_group_info,$default);
		}else{
			$client_group_info = array();
			$default=array('group_id'=>0,'group_name'=>"默认未分组",'friendnums'=>count($group_list['default']));
			array_unshift($client_group_info,$default);
		}
		$this->assign('group_list',$client_group_info);
	}

	//查找好友	
	public function findfriend(){
		$searchKey = $this->objInput->postStr('searchKey');
		if(empty($searchKey)){
			$searchKey = $this->objInput->getStr('searchKey');
		}
		$groupid = $this->objInput->getInt('groupid');
		if(empty($groupid)){
			$groupid = 0;
		}
	    $client_account = $this->getCookieAccount();
	   	
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=10; //每页数量

		$filters = array(
			'friend_group' => $groupid,
		);	

		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		//echo $searchKey;
		$tmp_mFriendData = $mAccountrelation->getAccountRelationByAddAccount($client_account);
		$mFriendData = $tmp_mFriendData[$client_account];
		$friend_accounts_list = array();
		if(!empty($mFriendData)) {
		    foreach($mFriendData as $val) {
		        $friend_accounts_list[] = $val['friend_account'];
		    }
		}
		$this->friend_group_count_list($mFriendData);
		if($mFriendData){
			$sortkeys = array();
			$newLog = array();
			 $mUser = ClsFactory::Create('Model.mUser');
			 $userlist = $mUser->getUserBaseByUid($friend_accounts_list);
			foreach($mFriendData as $key=>$val){
				$val['client_name'] = $userlist[$val['friend_account']]['client_name'];
				$val['headimg']=Pathmanagement_sns::getHeadImg($val['friend_account']) . $userlist[$val['friend_account']]['client_headimg'];

				$groupdata = $mClientgroup->getClientGroupById($val['friend_group']);
				if($groupdata){
					$groupdata = array_shift($groupdata);
					$val['group_name'] = $groupdata['group_name'];
				}
	
				if($groupid!==''){
					if ($val['friend_group']==$groupid){
						if(!empty($searchKey)){
							if($searchKey==$val['client_name']){
								$newmFriendData[] = $val;
							}else{
								unset($val);
							}
						}else{
							$newmFriendData[] = $val;
						}
						
					}
				}elseif($searchKey==$val['client_name']){
					$newmFriendData[] = $val;
				}
				if($val){
					unset($val);
				}
			}
			foreach($newmFriendData as $key=>$value) {
	            $sortkeys[$key] = $value['add_date'];
	        }
			$pageCount = ceil(count($newmFriendData)/$pagesize);
			array_multisort($sortkeys , SORT_DESC , $newmFriendData);
			$newarr_FriendData = array_slice($newmFriendData, ($page-1)*$pagesize, $pagesize);	
			$webUrl = "/Homefriends/Friendsmanage/findfriend/groupid/$groupid";
			
			intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
			intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
			if(!empty($searchKey)){
				$this->assign('name',$searchKey);
				$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."?searchKey=".$searchKey."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."?searchKey=".$searchKey."'>下一页</a></div>");
			}else{
				$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
			}
			
			$this->assign('pageno',$pageno);
		}
		if($groupid!==''){
			$this->assign('curr_url',"/groupid/{$groupid}");
		}else{
			$this->assign('curr_url',"");
		}
		if($searchKey==''){
			$this->assign('name','搜索姓名');
		}
	
		$this->assign('friend_list',$newarr_FriendData);

		$this->assign('addclient_account',$client_account);
		
		$this->display("FriendManage");
	}


	//添加和修改好友分组
	public function addgroup(){
		$cookie_account = $this->getCookieAccount();
		$groupid = trim($this->objInput->postStr('groupid'));
		$groupname = trim($this->objInput->postStr('groupname'));

		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$objData = $mClientgroup->getClientGroupByGroupName($groupname,$cookie_account);
		if($objData){
            $this->showError("错误:分组创建失败-名称重复", "/Homefriends/Friendsmanage/index");
		}else{
			$data=array(
				'client_account'=>$cookie_account,
				'group_name'=>trim($this->objInput->postStr('groupname')),
				'group_type'=>1,
				'add_account'=>$cookie_account,
				'add_date'=>date('Y-m-d H:i:s',time())
			);
				
			$addgroup = $mClientgroup->addClientGroup($data,true);
		
			if($addgroup){
                $this->showSuccess("分组创建成功", "/Homefriends/Friendsmanage/index");
			}else{
                $this->showError("错误:分组创建失败", "/Homefriends/Friendsmanage/index");
			}
		}
	}


	//删除好友分组
	public function delgroup(){
		$cookie_account = $this->getCookieAccount();
		$groupid=$this->objInput->getStr('groupid');
		$friend_account = $this->objInput->getStr('friend_account');
		
		$mClientgroup = ClsFactory::Create('Model.mClientgroup');
		$mClientgroup->delClientGroup($groupid);
		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$mAccountrelation->modifyAccountRelationByCompositeKey(array('friend_group'=>'0'), $cookie_account, $friend_account, array('group_id' => $groupid));

		$this->redirect("../Homefriends/Friendsmanage/index");
	}


	//改变好友关系
	public function changfriendcon(){
		$login_account = $this->getCookieAccount();
		$friend_account = $this->objInput->postStr('friendacount');
		$friend_groupid = $this->objInput->postStr('groupid'); 
		
		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
		$dataarr = array(
		    'friend_group'=>$friend_groupid
		);
		
		$saveInfo = $mAccountrelation->modifyAccountRelationByCompositeKey($dataarr, $login_account, $friend_account, $filter);

		if(!$saveInfo){
			echo 'error';
		}
	}


	//解除好友关系
	public function remfriend() {
	    $friend_account = $this->objInput->getStr('friend_account');
	    
		$cookie_account = $this->getCookieAccount();
		$account_relation_Model = ClsFactory::Create('Model.mAccountrelation');
		$account_relation_info = $account_relation_Model->delAccountRelationByCompositeKey($cookie_account, $friend_account, $filters);
		$account_relation_info1 = $account_relation_Model->delAccountRelationByCompositeKey($friend_account, $cookie_account, $filters);
		if(!empty($account_relation_info) && !empty($account_relation_info1)){

		    $mNewInfo = ClsFactory::Create('Model.mNewsInfo');
			$filters = array(
			    'add_account' => $friend_account,
			    'news_type' => 'HY',
			);
			$newinfo_arr = $mNewInfo->getNewsInfoByToUid($cookie_account, $filters);
			$newinfo_list = & $newinfo_arr[$cookie_account];
			if(!empty($newinfo_list)) {
			    foreach($newinfo_list as $news_id=>$news) {
			        $mNewInfo->delNewsInfo($news_id);
			    }
			}
			$this->showSuccess("好友解除成功", "/homefriends/friendsmanage/findfriend");
		}else{
			$this->showError("好友解除失败", "/homefriends/friendsmanage/findfriend");
		}
		
	}













	
}
