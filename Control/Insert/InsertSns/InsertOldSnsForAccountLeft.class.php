<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertSnsForAccountLeft implements InsertInterface {
    
    public function run($params, & $smarty) {
        $class_code = $params['class_code'];
    	$mUser = ClsFactory::Create('Model.mUser');
    	$LoginUserId =  $mUser->getHomeCookieAccount();
    	
    	$UserInfo = $mUser->getUserByUid($LoginUserId);
    	$UserInfo = array_shift($UserInfo);
    	$school_info = array_shift($UserInfo['school_info']);
    	if(empty($class_code)){
    		$class_code = key($UserInfo['class_info']);
    	}
    	foreach($UserInfo['client_class'] as $key=>$val) {
    		if($val['class_code'] == $class_code){
    			$var_teacher_class_role =  $val['teacher_class_role']; //班主任
    			break;
    		}
    	}
    	//$var_teacher_class_role =  $UserInfo['client_class'][$class_code]['teacher_class_role']; //班主任
    	
    	//goto Oa start
        $client_type= intval($UserInfo['client_type']);
    	$goto_oa = false;
    	if($client_type == CLIENT_TYPE_TEACHER) {
    	    $mDepartmentMembers = ClsFactory::Create('Model.mDepartmentMembers');
    	    $rs = $mDepartmentMembers->getDepartmentMembersByUid($LoginUserId);
    	    if(!empty($rs)) {
    	        $goto_oa = true;
    	    }
    	}
    	$smarty->assign('goto_oa', $goto_oa);
    	//goto Oa end
    	$school_operation_strategy = array_shift($UserInfo['school_info']);
    	if(in_array($school_operation_strategy['operation_strategy'],array(2))){
    		$smarty->assign('operation_strategy',true);
    	}
    	if(!empty($class_code)){
    	    $class_code_url = "/class_code/$class_code";
    	    $smarty->assign('class_code_url', $class_code_url);
    	}
    	$smarty->assign('var_teacher_class_role', $var_teacher_class_role);
    	$smarty->assign('UserInfo', $UserInfo);
    	$smarty->assign('school_info',$school_info);
    	$smarty->assign('class_code', $class_code);
    	
    	//uc server地址
    	$uc_domain = C('uc_domain');
    	$uc_server = '';
    	if (!empty($uc_domain)) {
    	    $uc_server = 'http://'.$uc_domain;
    	}
    	$smarty->assign('uc_server', $uc_server);
    	
    	return $smarty->fetch("./Public/account_left.html");
    }
}