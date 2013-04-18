<?php
class PublishAction extends SnsController {
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index() {
        //获取用户的管理权限
        $class_code = $this->objInput->getInt('class_code');
        import('@.Control.Sns.ClassNotice.Ext.NoticeContext');
        $context = new NoticeContext($this->user,$class_code);
        $access_list = $context->getUserAccessList();
        if(!$access_list['is_show_fbgg']) {
            $this->redirect('/Sns/ClassNotice/Published/index/class_code/' . $class_code);
        }
        $this->assign('class_code',$class_code);
        $this->assign('access_list',$access_list);
        $this->display('publish');
    }
    
    /*
     * 发布公告
     */
    public function write_notice() {
        $notice_title = $this->objInput->postStr('notice_title');
        $notice_content = $this->objInput->postStr('content');
        $is_sms = $this->objInput->postInt('is_sms');
        $class_code = $this->objInput->getInt('class_code');
        $class_code_arr = array_keys($this->user['class_info']);
        
        if(empty($class_code) || !in_array($class_code,$class_code_arr)) {
            $class_code = reset($class_code_arr);
        }
       
        $datarr = array(
                'class_code' => $class_code,
                'notice_title' => $notice_title,
                'notice_content' => $notice_content,
                'add_account' => $this->user['client_account'],
                'add_time' => time(),
        );
        
        if(!empty($is_sms) && $is_sms == 1) {
            $datarr['is_sms'] = $is_sms;
        }
        
        
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $notice_id = $mClassNotice->addClassNotice($datarr,true);
        
        if(!empty($notice_id) && $is_sms == 1) {
            $send_content = $notice_title.$notice_content;
            $send = $this->notice_send($class_code,$send_content);    
        }
        if(!empty($notice_id)) {
            $mMsgNoticeList = ClsFactory::Create("RModel.Msg.mStringNotice");
            $mMsgNoticeList->publishMsg($notice_id, 'notice');
            $this->showSuccess('发布公告成功！','/Sns/ClassNotice/Published/index/class_code/' . $class_code);
        } else {
            $this->showError('发布公告失败！','/Sns/ClassNotice/Publish/index/class_code/' . $class_code);
        }
    }
    
    
    /**
     * 公告列表中的发布短信功能
     */
    public function notice_list_send() {
        $notice_id = $this->objInput->getInt('notice_id');
        $class_code = $this->objInput->getInt('class_code');
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $class_notice_list = array_shift($mClassNotice->getClassNotice($notice_id));
        
        $send_content = $class_notice_list['notice_title'].$class_notice_list['notice_content'];
        
        if(!empty($send_content)) {
            $send = $this->notice_send($class_notice_list['class_code'],$send_content);
            $datarr = array();
            $datarr['is_sms'] = 1;
            $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
            $resault = $mClassNotice->modifyClassNotice($datarr,$notice_id);
        }
        
        if(!empty($resault)) {
            $this->showSuccess('短信发布成功！','/Sns/ClassNotice/Published/index/class_code/' . $class_code);
        }else {
            $this->showError('短信发布失败！','/Sns/ClassNotice/Published/index/class_code/' . $class_code);
        }
        
    }
    
 	/*
     * 编辑器上传图片通用方法
     */
    public function uploadPath() {
        import("@.Common_wmw.Pathmanagement_sns");
        $uploadPath = Pathmanagement_sns::uploadXheditor();
        $showPath = Pathmanagement_sns::getXheditorimgPath();
        import('@.Control.Api.XheditorApi');
        $uploadobj = new XheditorApi();
        $uploadobj->upload($uploadPath,$showPath);
    }
    
    
    /**
     * 发布公告的短信
     */
    
    private function notice_send($class_code,$send_content) {
            //获取当前学校的运营策略    
            $operationStrategy = $this->getOperationStrategy();
            
            $mClientClass = ClsFactory::Create('Model.mClientClass');
            $student_infos = $mClientClass->getStudentInfoByClassCodeAndType($class_code,CLIENT_TYPE_STUDENT);
            $student_accounts = array();
            
            foreach($student_infos as $client_class_id=>$val) {
                $student_accounts[] = $val['client_account'];
            }
            
            //获取家长帐号信息
            $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
            $FamilyInfos = array_shift($mFamilyRelation->getFamilyRelationByUid($student_accounts));
            
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
			$ClassNoticeContent = strip_tags(WmwString::unhtmlspecialchars($send_content));
			
			//调用短信api发送短信
            import('@.Control.Api.Smssend.Smssendapi');
            $smssendapi_obj = new Smssendapi();
            foreach($phone_list as $account_phone_id1=> $send) {
                $addSmsSendResult = $smssendapi_obj->send($send['account_phone_id2'], $send_content, $operationStrategy);
            }
            
            return !empty($addSmsSendResult) ? $addSmsSendResult : false;
     }
    
    
    
    
	/**
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