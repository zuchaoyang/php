<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertSnsForSpaceHeader implements InsertInterface {
    
    public function run($params, & $smarty) {
        $mUser = ClsFactory::Create('Model.mUser');
    	$LoginUserId =  $mUser->getHomeCookieAccount();//todolist
    	$UserInfo = $mUser->getUserByUid($LoginUserId);
    	$UserInfo = & $UserInfo[$LoginUserId];
    	$client_type = $UserInfo['client_type'];
    	
    	$tab_class_list = false;
    	$myclasslist = array();
    	if ($client_type == CLIENT_TYPE_TEACHER) {
    		$tab_class_list = true;
    		//获取当前用户的班级列表信息
    		$myclasslist = & $UserInfo['class_info'];
    	}

    	//空间皮肤列表
		$mPersonspaceskin = ClsFactory::Create('Model.mPersonspaceskin');	  
		$arrSkinData = $mPersonspaceskin->getPersonSpaceSkinByClientType($client_type);
		$new_arrSkinData = & $arrSkinData[$client_type];
		
		$smarty->assign('skinlistData',$new_arrSkinData);
		$smarty->assign('tab_class_list',$tab_class_list);
		$smarty->assign('myclasslist' , $myclasslist);

		
    	//uc 退出设置
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	$uc_logout_url = $this->uc_client->get_uc_logout_url();
        $smarty->assign('uc_logout_url', $uc_logout_url);		
		
    	return $smarty->fetch("./Public/space_header.html");
    }
}