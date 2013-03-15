<?php
class MyfriendAction extends SnsController {
    protected $user = array();
	public function _initialize(){
		parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");
    }
	
	//我的主页
	public function myhome(){
	    $account = $this->objInput->getInt('user_account');
	    if(empty($account)) {
		    $account = $this->getCookieAccount();
	    }
		$this->redirect('/Myfriend/index/account/'.$account);
	}
	//在线好友
	public function onlinefriends($account){
		$account_relation_Model = ClsFactory::Create('Model.mAccountrelation');
		$User_Model = Clsfactory::Create('Model.mUser');
		$friends = array();
		$i=1;
		while(1){
    		$friendinfos = $account_relation_Model->getaccountrelationbyuid($account,($i-1)*100,100);
    		foreach ($friendinfos as $key=>$friendinfo) {
    		    $frienduids[] = $friendinfo['friend_account'];
    		}
    		if(!$friendinfos||count($friends)>=10){
    		    break;
    		}
    		$i++;
    		$userinfos = $User_Model->getUserBaseByUid($frienduids);
    		foreach ($userinfos as $uid=>$userinfo) {
    		    if($userinfo['internet_status'] == 1){
    		         array_unshift($friends,array(
    		                        'friend_account'=>$uid,
    		                        'internet_status'=>$userinfo['internet_status'],
    		                        'client_headimg'=>$userinfo['client_headimg'],
    		                        'client_name'=>$userinfo['client_name']
    		                    ));
    		    }else{
    		        unset($userinfos[$uid]);
    		    }
    		   
    		}
		}
		return $friends;
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
