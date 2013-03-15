<?php
include_once WEB_ROOT_DIR . "/Control/Sns/Insert/InsertInterface.php";
class InsertSnsHeader implements InsertInterface {
    public function run($params, & $smarty) {
        
        $RmUser = ClsFactory::Create("RModel.mUserVm");
        $userinfo = $RmUser->getCurrentUser();
        
        $head_pic_url = $userinfo['client_headimg_url'];
        $smarty->assign('client_name', $userinfo['client_name']);
        $smarty->assign('uid', $userinfo['client_account']);
        $smarty->assign('head_pic', $head_pic_url);
        
    	//uc 退出设置
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	$uc_index_url = $this->uc_client->get_uc_index_url();
    	$uc_logout_url = $this->uc_client->get_uc_logout_url();
    	
    	$smarty->assign('uc_index_url', $uc_index_url);
        $smarty->assign('uc_logout_url', $uc_logout_url);        
        
        
    	return $smarty->fetch("./Public/sns_header.html");
    }
}