<?php

class OaController extends FrontController {
	/*
	 * 构造函数
	 * 用于smarty引入头文件
	 */
	public function __construct() {
		parent::__construct();
		import("@.Control.Insert.InsertOaFunc", null,".php");
	}
	
    /**
     * 初始化Oa的当前用户信息
     */
    protected function initCurrentUser() {
        $uid = $this->getCookieAccount();
        
        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getUserByUid($uid);
        $this->user = & $userlist[$uid];
        
	    //获取用户所在部门的基本信息
        $mDptMember = ClsFactory::Create('Model.mDepartmentMembers');
        $dpt_member_arr = $mDptMember->getDepartmentMembersByUid($uid, GET_OA_DPT_MEMBER_WITH_ACCESS);
        $dpt_member_list = & $dpt_member_arr[$uid];
        
        $dpt_ids = $role_access_arr = array();
        if (!empty($dpt_member_list)) {
            foreach($dpt_member_list as $dpt_member) {
                $role_access_arr[] = $dpt_member['access_name_arr'];
                $dpt_id = $dpt_member['dpt_id'];
                $dpt_ids[] = $dpt_id;
            } 
            unset($dpt_member_list);
        }
        
        $dpt_list = array();
        if (!empty($dpt_ids)) {
            $mDpt = ClsFactory::Create('Model.mDepartment');
            $dpt_list = $mDpt->getDepartmentById($dpt_ids);
        }
        $this->user['dpt_list'] = & $dpt_list;
        
        //处理用户的权限问题
        $total_access_list = array();
        if (!empty($role_access_arr)) {
            foreach($role_access_arr as $access_list) {
                foreach($access_list as $key=>$val) {
                    if (!isset($total_access_list[$key])) {
                        $total_access_list[$key] = $val;
                    }
                }
            }
        }
        $this->user['access_name_arr'] = $total_access_list;
    }
	
    protected function getSuccessTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_success_tips.html";
    }
    
    protected function getErrorTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_error_tips.html";
    }
}
