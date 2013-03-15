<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertUcHeader implements  InsertInterface {
    public function run($params, &$smarty) {
        //检测用户信息是否已经设置
        if(!(method_exists($smarty, 'get_template_vars') && $smarty->get_template_vars('user'))) {
            //如果没有重新加载用户信息
            $mUser = ClsFactory::Create('Model.mUser');
            $user = $mUser->getOaCurrentUser();
            
            $smarty->assign('user', $user);
        }
        
        
        // 安全退出
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	$uc_logout_url = $this->uc_client->get_uc_logout_url();
    	
        $smarty->assign('uc_logout_url', $uc_logout_url);

        // 返回我们网
        $wmw_server = C('WMW_SERVER');
        $zscy_server = C('ZSCY_SERVER');

        $smarty->assign('wmw_server', $wmw_server);
        $smarty->assign('zscy_server', $zscy_server);
        
        return $smarty->fetch('./Uc/uc_header.html');
    }
}