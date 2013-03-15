<?php
class MailbookAction extends SnsController{
    public function _initialize(){
        import('@.Common_wmw.Pathmanagement_sns');
        parent::_initialize();
    }
    
    public function publish_send() {
        $class_code = $this->objInput->getInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
        
        $student_infos = $this->getStudentsListByClassCode($class_code);
        
        $client_accounts = array_keys($student_infos);
        
        $student_infos = $this->getParentInfosByAccount($client_accounts);
        
        foreach($student_infos as $student_account => $val) {
            if($val[0]['phone_id'] =='' && $val[1]['phone_id'] == '') {
                unset($student_infos[$student_account]);
            }
        }
        
        $this->assign('student_infos',$student_infos);
        $this->assign('class_code',$class_code);
        $this->display('publish');
    }
    
    /***********************************************通过学生帐号发送短信***********************************************/
    /**
     * 通过班级id获取学生列表
     */
    public function getStudentsListByClassCode($class_code) {
        $class_code = $this->checkoutClassCode($class_code);
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $class_student_infos = $mClientClass->getStudentInfoByClassCodeAndType($class_code,CLIENT_TYPE_STUDENT);
        $client_accounts = array();
        foreach($class_student_infos as $client_class_id=>$val) {
            $client_accounts[] = $val['client_account'];
        }
        
        $mClientInfo = ClsFactory::Create('Model.mUser');
        $student_account_infos = $mClientInfo->getClientAccountById($client_accounts);
        
        return !empty($student_account_infos) ? $student_account_infos : false;
    }
    
    
    /************************************************通过手机号发送短信***********************************************/
    /**
     * json根据班级id获取班级学生信息及家长信息列表
     */
    public function index() {
        $length = 5;
        $class_code = $this->objInput->getInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        
        $offset = ($page-1) * $length;
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $class_student_infos = $mClientClass->getStudentInfoByClassCodeAndType($class_code,CLIENT_TYPE_STUDENT,$offset,$length+1);
        $next = 'false';
        if(count($class_student_infos) > $length) {
            array_pop($class_student_infos);
            $next = 'true';
        }
        
        $client_accounts = array();
        foreach($class_student_infos as $client_class_id=>$val) {
            $client_accounts[] = $val['client_account'];
        }
        
        
        $new_info_arr = $this->getParentInfosByAccount($client_accounts);
        
        $this->assign('class_code',$class_code);
        $this->assign('next',$next);
        $this->assign('page',$page);
        $this->assign('new_info_arr',$new_info_arr);
        $this->display('list');
    }
    
    
    //根据学生帐号获取父母信息列表
    private function getParentInfosByAccount($client_accounts) {
    $mClientInfo = ClsFactory::Create('Model.mUser');
        $student_account_infos = $mClientInfo->getClientAccountById($client_accounts);
        
        
        $student_info = $mClientInfo->getClientInfoById($client_accounts);
        
        $new_student_arr = array();
        foreach($student_account_infos as $account =>$account_info) {
            $new_student_arr[$account] = array_merge($account_info,$student_info[$account]);
        }
        
        
        //依据学生帐号获取家长信息
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $FamilyInfos = $mFamilyRelation->getFamilyRelationByUid($client_accounts);
        
        //获取家长帐号及中间学生和家长转换条件
        $parents_accounts = array();
        $family_links = array();
        foreach($FamilyInfos as $sutdent_accout=> $relationinfo) {
            foreach($relationinfo as $relation_id=> $familyinfo) {
                $parents_accounts[] = $familyinfo['family_account'];
                $family_links[$sutdent_accout][$familyinfo['family_account']] = $familyinfo['family_account'];
            }
        }
        
        //获取家长信息
        $parents_infos = $mClientInfo->getClientInfoById($parents_accounts);
        $parents_account_infos = $mClientInfo->getClientAccountById($parents_accounts);
        //依据帐号查询家长手机号
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
        $parents_phone = $mBusinessphone->getbusinessphonebyalias_id($parents_accounts);
        
        $new_parents_arr = array();
        foreach($parents_account_infos as $account=>$account_info) {
            $new_parents_arr[$account] = array_merge($account_info,$parents_account_infos[$account]);
            $new_parents_arr[$account]['phone_id'] = $parents_phone[$account]['account_phone_id2'];
        }
        
        
        $new_info_arr = array();
        foreach($new_student_arr as $account=>$val) {
            foreach($family_links[$account] as $family_account) {
                $val[] = $new_parents_arr[$family_account];
            }
            
            if($val['client_sex'] == 1) {
                $val['client_sex'] = '男';
            } else {
                $val['client_sex'] = '女';
            }
            
            $val['client_name'] = $val['client_name'];
            $val['client_birthday'] = date('Y-m-d',$val['client_birthday']);
            $val['client_headimg_img'] = Pathmanagement_sns::getHeadImg($account).$val['client_headimg'];
            $new_info_arr[$account] = $val;
        }
        
        return !empty($new_info_arr) ? $new_info_arr : array();
    }
    
    /**
    * 通讯录群发短信
    */
    public function maillist_send() {
        $class_code        = $this->objInput->getInt('class_code');
        $sms_content       = $this->objInput->postStr('sms_content');
        $selected_accounts = $this->objInput->postArr('selected_accounts');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限!', -1, 'json');
        }
        
        if(empty($selected_accounts)) {
            $this->ajaxReturn(null, '请选择发送对象!', -1, 'json');
        }
        
        //检测数据是否是 该班级的
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code);
        
        $client_class_list = & $client_class_arr[$class_code];
        $class_client_accounts = array_keys($client_class_list);
        
        $selected_accounts = array_intersect((array)$selected_accounts, (array)$class_client_accounts);
        
        if(empty($selected_accounts)) {
            $this->ajaxReturn(null, '请选择发送对象!', -1, 'json');
        }
        
        if(!$this->maillist_send_do($selected_accounts, $sms_content)) {
            $this->ajaxReturn(null, '发送短信失败！', -1, 'json');
        }
        
        $this->ajaxReturn(null, '发送短信成功！', 1, 'json');
    }
    
    
	/**
     * 通过手机号发送短信
     */
    public function maillist_send_phone() {
        $class_code = $this->objInput->getInt('class_code');
        
        $parent_account = $this->objInput->postInt('parent_account');
        if(empty($parent_account)) {
            exit;
        }
        
        $sms_content = $this->objInput->postStr('sms_content');
        
        //获取当前学校的运营策略    
        $operationStrategy = $this->getOperationStrategy();
        $mBusinesphone  = ClsFactory::Create('Model.mBusinessphone');
		$phone_list = array_shift($mBusinesphone->getbusinessphonebyalias_id($parent_account));
		
        import('@.Common_wmw.WmwString');
		$sms_content = strip_tags(WmwString::unhtmlspecialchars($sms_content));
		
        import('@.Control.Api.Smssend.Smssendapi');
        $smssendapi_obj = new Smssendapi();
        $addSmsSendResult = $smssendapi_obj->send($phone_list['account_phone_id2'], $sms_content, $operationStrategy);
        
        if(!empty($addSmsSendResult)) {
            $this->ajaxReturn($addSmsSendResult, '发送短信成功！', 1, 'json');
        } else {
            $this->ajaxReturn(null, '发送短信失败！', -1, 'json');
        }
    }
    
    
 	/**
     * 通过学生帐号发送短信入库公用方法
     */
    private function maillist_send_do($accepters_accounts,$sms_content) {
        //获取当前学校的运营策略    
        $operationStrategy = $this->getOperationStrategy();
        
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $FamilyInfos = array_shift($mFamilyRelation->getFamilyRelationByUid($accepters_accounts));
        //获取家长帐号
        $parents_account = array();
        foreach($FamilyInfos as $relation_id=>$relationinfo) {
            $parents_account[] = $relationinfo['family_account'];
        }
        
        //通过家长账号获得business_phones
		$mBusinesphone  = ClsFactory::Create('Model.mBusinessphone');
		$phone_list = $mBusinesphone->getbusinessphonebyalias_id($parents_account);
		//屏蔽短信内容样式及图片
		import('@.Common_wmw.WmwString');
		$sms_content = strip_tags(WmwString::unhtmlspecialchars($sms_content));
		
		import('@.Control.Api.Smssend.Smssendapi');
        $smssendapi_obj = new Smssendapi();
        foreach($phone_list as $account_phone_id1=> $send) {
            $addSmsSendResult = $smssendapi_obj->send($send['account_phone_id2'], $sms_content, $operationStrategy);
        }
        
        return !empty($addSmsSendResult) ? $addSmsSendResult : false;
    }
    
 	/*
     * 公共方法获取当前用户的运营策略
     */
    private function getOperationStrategy () {
        //获取当前用户所在学校的运营策略
	    $schoolinfo = array_shift($this->user['school_info']);
	    $schoolid = $schoolinfo['school_id'];
		$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略
		
		return !empty($operationStrategy) ? $operationStrategy : false;
    }
    
}


