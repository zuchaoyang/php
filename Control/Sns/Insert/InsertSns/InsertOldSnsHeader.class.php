<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertOldSnsHeader implements InsertInterface {
    public function run($params, & $smarty) {
        $class_code = $params['class_code'];
    	$mUser = ClsFactory::Create('Model.mUser');
    	$LoginUserId =  $mUser->getHomeCookieAccount();//todolist
    
    	$UserInfo = $mUser->getUserByUid($LoginUserId);
    	$UserInfo = array_shift($UserInfo);
    	
    	if(empty($class_code)){
    		$class_code = key($UserInfo['class_info']);
    	}

    	$tab_class_list = false;
    	if ($UserInfo['client_type'] == CLIENT_TYPE_TEACHER) {
    		$tab_class_list = true;
    		//获取当前用户的班级列表信息
    		$myclasslist = & $UserInfo['class_info'];
    	}
    	
    	$school_id = $UserInfo['class_info'][$class_code]['school_id'];
    	$grade_id_name = $UserInfo['class_info'][$class_code]['grade_id_name'];
    	$class_name = $UserInfo['class_info'][$class_code]['class_name'];
    	$school_name = $UserInfo['school_info'][$school_id]['school_name'];
    	
    	$smarty->assign('tab_class_list',$tab_class_list);
    	$smarty->assign('tpl_school_Name',$school_name);
    	$smarty->assign('tpl_class_name',$class_name);
    	$smarty->assign('tpl_grade_id_name',$grade_id_name);
    	$smarty->assign('tpl_class_code',$class_code);
    	$smarty->assign('myclasslist' , $myclasslist); 
    	$smarty->assign('UserInfo', $UserInfo);
    	
    	//uc 退出设置
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	$uc_index_url = $this->uc_client->get_uc_index_url();
    	$uc_logout_url = $this->uc_client->get_uc_logout_url();
    	
    	$smarty->assign('uc_index_url', $uc_index_url);
        $smarty->assign('uc_logout_url', $uc_logout_url);
    	
    	return $smarty->fetch("./Public/publicHeader.html");
    }
}
