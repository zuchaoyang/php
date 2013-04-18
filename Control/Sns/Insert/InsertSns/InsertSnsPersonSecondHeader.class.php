<?php
include_once WEB_ROOT_DIR . "/Control/Sns/Insert/InsertInterface.php";
class InsertSnsPersonSecondHeader implements InsertInterface {
    public function run($params, & $smarty) {
        
        $uid = $params['uid'];
        $RmUser = ClsFactory::Create("RModel.mUserVm");
        $userinfo = reset($RmUser->getUserBaseByUid($uid));
        $head_pic_url = $userinfo['client_headimg_url'];
        $smarty->assign('client_name', $userinfo['client_name']);
        $smarty->assign('space_uid', $userinfo['client_account']);
        $smarty->assign('head_pic', $head_pic_url);
        
    	return $smarty->fetch("./Public/sns_person_space_header.html");
    }
}