<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertOaHeader implements  InsertInterface {
    public function run($params, &$smarty) {
        import("@.Common_wmw.Constancearr");
    
        $mUser = ClsFactory::Create('Model.mUser');
        $user = $mUser->getOaCurrentUser();
        
        $school_info = reset($user['school_info']);
        $smarty->assign('school_logo_url', $school_info['school_logo_url']);
        $smarty->assign('school_name', $school_info['school_name']);
    
        $smarty->assign('user', $user);
        
    	//uc 退出设置
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	$uc_logout_url = $this->uc_client->get_uc_logout_url();
        $smarty->assign('uc_logout_url', $uc_logout_url);    
        
    	//uc server地址
    	$uc_domain = C('uc_domain');
    	$uc_server = '';
    	if (!empty($uc_domain)) {
    	    $uc_server = 'http://'.$uc_domain;
    	}
    	$smarty->assign('uc_server', $uc_server);        
        
        
        return $smarty->fetch('./Public/oa_header.html');
    }
}