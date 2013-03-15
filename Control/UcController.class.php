<?php
import('@.Common_wmw.ThinkOAuth2');
class UcController extends FrontController {

    protected $oauth2 = NULL;    
    
    public function _initialize(){
    	parent::_initialize(); 
    	import("@.Control.Insert.InsertUcFunc", null, ".php");    
        $this->oauth2 = new ThinkOAuth2();
    }
    
    /**
     * 初始化当前的Uc用户信息
     */
    protected function initCurrentUser() {
        $uid = $this->getCookieAccount();
        
        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getUserByUid($uid);
        $this->user = & $userlist[$uid];
    }

}