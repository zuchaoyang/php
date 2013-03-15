<?php
include_once WEB_ROOT_DIR . "/Control/Sns/Insert/InsertInterface.php";

class InsertSnsTip implements InsertInterface {
    public function run($params, & $smarty) {
        
        $class_code = $params['class_code'];
        
        $RmUser = ClsFactory::Create("RModel.mUserVm");
        $userinfo = $RmUser->getCurrentUser();
        
        $class_info = !empty($class_code) ? $userinfo['class_info'][$class_code] : reset($userinfo['class_info']);
        $class_list = $userinfo['class_info'];
        $headteacher_info = $RmUser->getUserBaseByUid($class_info['headteacher_account']);
        $class_info['headteacher_name'] = $headteacher_info[$class_info['headteacher_account']]['client_name'];
        
        $is_admin = false;
        if($class_info["headteacher_account"] == $userinfo['client_account']){
            $is_admin = true;
        }
        
        $school_info = reset($userinfo['school_info']);
        
        $smarty->assign('is_admin', $is_admin);
        $smarty->assign('class_list', $class_list);
        $smarty->assign('class_info', $class_info);
        $smarty->assign('school_name', $school_info['school_name']);
        
    	return $smarty->fetch("./Public/sns_class_header.html");
    }
}