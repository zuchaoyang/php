<?php
class PublishAction extends SnsController {
    
    public function _initialize(){
        import('@.Common_wmw.Pathmanagement_sns');
        parent::_initialize();
    }
    
    /*
     * 班级作业
     */
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $client_account = $this->getCookieAccount();
        $subject_infos = $this->getSubjectInfoByClientAccout($client_account);
        
        $class_code = $this->checkoutClassCode($class_code);
        //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user,$class_code);
        $access_list = $context->getUserAccessList();
        if(!$access_list['is_show_fbzy']) {
            $this->redirect('/Sns/ClassHomework/Published/index/class_code/' . $class_code);
        }
        $this->assign('class_code',$class_code);
        $this->assign('access_list',$access_list);
        $this->assign('subject_infos',$subject_infos);
        $this->display('publish');
    }
    
    /*
     * 发布班级作业信息及短信
     */
    public function write_homework() {
        $homeworkContent = $this->objInput->postStr('content');
        $homeworktime = $this->objInput->postStr('homeworkdate');
        $accept_list = $this->objInput->postArr('accept_list');
        $client_account = $this->getCookieAccount();
        $subject_id = $this->objInput->postInt('subject_id');
        $is_sms = $this->objInput->postInt('is_sms');
        $class_code = $this->objInput->getInt('cl ass_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        
        $y = date('Y');
        $m = date('m');
        $d = date('d');
        $h = date('H:i:s');
         //班级作业附件上传
        if(!empty($_FILES['file_name']['name'])) {
            import('@.Common_wmw.WmwUpload');
            $uploadObject = new WmwUpload();
            $options = array(
                'allow_type' => array('excel','txt','ppt','doc','docx','pdf','rar','zip','xls','wps','pptx'),
                'attachmentspath' => Pathmanagement_sns::uploadHomework() . $client_account . '/'."$y/$m/$d", //解析规则例：attchment/homework/11070004/2012/12/03/*.txt
                'renamed' => true,
                'ifresize' => true,
                //文件上传类的大小使用的单位是:kb，在这里需要转换
                'max_size' => 8192,
            );
            
            $upload_rs = $uploadObject->upfile('file_name', $options);
            if(empty($upload_rs)) {
                exit;
            }
        }
        
       
        //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user);
        $access_list = $context->getUserAccessList();
        if($is_sms == 1 && $access_list['is_send']) {
             $is_sms_status = true;
        }
        
        //实例化对象
        $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        
        foreach($accept_list as $class_code_key=>$client_account_val) {
            $client_account_arr = explode(',',$client_account_val);
            $dataarr = array(
                'content' => $homeworkContent,
                'class_code' => $class_code_key,
                'end_time' => strtotime($homeworktime),
                'accepters' => count($client_account_arr).'人',
                'add_account' => $client_account,
                'add_time' => strtotime("$y-$m-$d $h"),
                'subject_id' => $subject_id,
            	'attachment' => $upload_rs['getfilename'],
            );
            $is_sms_status ? $dataarr['is_sms'] = 1 : "";
            
            //班级作业入库
          
            $homework_id = $mClassHomework->addHomework($dataarr,true);
            
            //发送对象表入库
            $dataarr = array();
            foreach ($client_account_arr as $account) {
                $dataarr_send[] = array(
                    'homework_id' => $homework_id,
                    'client_account' => $account,
                    'add_time' => time()
                );
            }
            
            
            $resault_classhomework_send = $mClassHomeworkSend->addHomeworkSend($dataarr_send);
            
             //判断是否发送短信
            if($is_sms == 1 && $access_list['is_send'] && !empty($homework_id)) {
                $resault_send = $this->SendHomework($client_account_arr,$homeworkContent,$homework_id);   
            }
            
        }
        
        $mMsgHomeworkList = ClsFactory::Create("RModel.Msg.mStringHomework");
        $mMsgHomeworkList->publishMsg($homework_id, 'homework');
        
        $this->showSuccess('班级作业发布成功!','/Sns/ClassHomework/Publish/index/class_code/' . $class_code);
    }
    
    /*
     * 根据班级id获取班级学生信息
     */
    public function student_info_json() {
        $class_code = $this->objInput->postInt('class_code');
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $class_student_infos = $mClientClass->getStudentInfoByClassCodeAndType($class_code,CLIENT_TYPE_STUDENT);
        $client_accounts = array();
        foreach($class_student_infos as $client_class_id=>$val) {
            $client_accounts[] = $val['client_account'];
        }
        
        $mClientInfo = ClsFactory::Create('Model.mUser');
        $client_infos = $mClientInfo->getClientAccountById($client_accounts);
        
        foreach($client_infos as $account=>$val) {
            $val['client_headimg'] = Pathmanagement_sns::getHeadImg($account).$val['client_headimg'];
            $client_infos[$account] = $val;
        }
        
        if(empty($client_infos)) {
            $this->ajaxReturn($client_infos, '获取学生列表失败', -1, 'json');
        }
        
        $this->ajaxReturn($client_infos, '获取学生列表成功', 1, 'json');
    }

    /*
     * 发布作业后补发短信
     */
    public function SendReissue() {
        $homework_id = $this->objInput->postInt('homework_id');
        if(empty($homework_id)) {
            $this->ajaxReturn(null, '作业信息不存在，短信发送失败!', -1, 'json');
        }
        
        //获取对应的作业信息是否存在
        $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
        $homework_list = $mClassHomework->getClassHomeworkById($homework_id);
        $homework = & $homework_list[$homework_id];
        if(empty($homework)) {
           $this->ajaxReturn(null, '作业信息不存在，短信发送失败!', -1, 'json');
        }
        
        //判断用户权限
        $class_code = $homework['class_code'];
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user, $class_code);
        $access_list = $context->getUserAccessList();
        if(!$access_list['is_send']) {
            $this->ajaxReturn(null, '您暂时没有权限发送该作业的短信消息!', -1, 'json');
        }
        
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        $homeworksend_arr = $mClassHomeworkSend->getHomeworkSendByhomeworkid($homework_id);
        $homeworksend_list = & $homeworksend_arr[$homework_id];
        
        //获取作业的接受对象信息
        $accepters_accounts = array();
        if(!empty($homeworksend_list)) {
            foreach($homeworksend_list as $homeworksend) {
                $accepters_accounts[] = $homeworksend['client_account'];
            }
            unset($homeworksend_list, $homeworksend_arr);
        }
        
       $homeworkcontent = $homework['content'];
       $resault_send = $this->SendHomework($accepters_accounts,$homeworkcontent,$homework_id);
       
       if(!empty($resault_send)) {
           $datarr = array(
               'is_sms' => 1
           );
           
           $update_classhomeworksend = $mClassHomework->modifyHomework($datarr,$homework_id);
           $this->ajaxReturn($update_classhomeworksend, '发送短信成功', 1, 'json');
       } else{
           $this->ajaxReturn(null, '发送短信失败', -1, 'json');
       }
    }
    
     /*
     * 根据科目id和帐号取出班级列表
     */
    public function class_info_json() {
        $subject_id = $this->objInput->postInt('subject_id');
        
        if(empty($subject_id)) {
            $this->ajaxReturn(null, '科目信息不能为空!', -1, 'json');
        }
        
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $class_teacher_list = $mClassTeacher->getClassInfoByuidAndsubjectid($this->user['client_account'], $subject_id);
        
        if(empty($class_teacher_list)) {
            $this->ajaxReturn(null, '该科目下没有任何班级!', -1, 'json');
        }
        
        //查询班级id
        $class_codes = array();
        foreach($class_teacher_list as $class_teacher) {
            $class_codes[] = $class_teacher['class_code'];
        }
        
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $class_info_list = $mClassInfo->getClassInfoBaseById($class_codes);
        if(empty($class_info_list)) {
            $this->ajaxReturn(null, '获取班级列表失败', -1, 'json');
        }
        
        $this->ajaxReturn($class_info_list, '获取班级列表成功', 1, 'json');
    }
    
    /*
     * 发送短信入库公用方法
     */
    private function SendHomework($accepters_accounts,$homeworkContent,$homework_id) {
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
			$ClassHomeworkContent = strip_tags(WmwString::unhtmlspecialchars($homeworkContent));
			
			import('@.Control.Api.Smssend.Smssendapi');
            $smssendapi_obj = new Smssendapi();
            foreach($phone_list as $account_phone_id1=> $send) {
                $addSmsSendResult = $smssendapi_obj->send($send['account_phone_id2'], $ClassHomeworkContent, $operationStrategy);
            }
            
            return !empty($addSmsSendResult) ? $addSmsSendResult : false;
         }
    
    /*
     * 根据老师帐号获取科目列表
     */
    private  function getSubjectInfoByClientAccout($client_account) {
        $mClassTeacher  = ClsFactory::Create('Model.mSchoolTeacher');
        $subject_relation_infos = $mClassTeacher->getSchoolTeacherByTeacherUid($client_account);
        //获取当前老师所班级id及科目id
        $subject_ids = array();
        foreach($subject_relation_infos[$client_account] as $val) {
            $subject_ids[] = $val['subject_id'];
        }
        
        if(!empty($subject_ids)) {
            $mSubject_info = CLsFactory::Create('Model.mSubjectInfo');
            $subject_infos = $mSubject_info->getSubjectInfoById($subject_ids);
        }
        
        return !empty($subject_infos) ? $subject_infos : false;
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
    
}