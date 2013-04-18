<?php
class PublishedAction extends SnsController {
    
    public function _initialize(){
        import('@.Common_wmw.Pathmanagement_sns');
        parent::_initialize();
    }
    
	/**
     * 依据当前帐号获取班级作业列表
     */
    public function index() {
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('您暂时没有权限查看该班的作业信息', '/Sns/ClassHomework/Published/index');
        }
        
         //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user,$class_code);
        $access_list = $context->getUserAccessList();
        //获取班级的科目列表信息
        $subject_infos = $this->getSubjectInfosByClientAccount($this->user['client_account']);
        
        $this->assign('access_list',$access_list);
        $this->assign('class_code', $class_code);
        $this->assign('user', $this->user);
        $this->assign('subject_infos', $subject_infos);
        
        $this->display('list');
    }
    
    /**
     * 根据帐号获取科目信息
     */
    public function getSubjectInfosByClientAccount($client_account) {
        if(empty($client_account)) {
            $client_account = $this->getCookieAccount();
        }
        
        //$class_code = $this->checkoutClassCode();
        //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user);
        $access_list = $context->getUserAccessList();
        
        //老师帐号获取科目列表
        if($access_list['is_teacher']) {
           $subject_infos = $this->getSubjectByTeacher($client_account);
        }
        
        //根据学生帐号获取科目列表
        if($access_list['is_student']) {
            $subject_infos = $this->getSubjectByStudent();
        }
        
        if($access_list['is_family']) {
            $subject_infos = $this->getSubjectByFamily($client_account);
        }
        
        return !empty($subject_infos) ? $subject_infos : false;
    }
    
    /**
     * json异步加载信息
     * 
     */
    public function getHomeworklistAjax() {
        //class_code是基本的参数
        $class_code  = $this->objInput->getInt('class_code');
        //加载的附加参数是通过post方式传递的
        $page        = $this->objInput->postInt("page");
        $subject_id  = $this->objInput->postInt('subject_id');
        $search_type = $this->objInput->postStr('search_type');
        $start_time  = $this->objInput->postStr('start_time');
        $end_time    = $this->objInput->postStr('end_time');
        
        $page = max(1, $page);
        $start_time = !empty($start_time) ? strtotime($start_time) : 0;
        $end_time = !empty($end_time) ? strtotime($end_time)+ 86400 : time();
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限查看该班的作业信息!', -1, 'json');
        }
        
        $wherearr = array();
        
        $wherearr[] = "class_code='$class_code'";
        
        if(!empty($subject_id)) {
            $wherearr[] = "subject_id='$subject_id'";
        }
        
        if($search_type == 'FBZY') {
            if(!empty($start_time)) {
                $wherearr[] = "add_time>='$start_time'";
            }
            if(!empty($end_time)) {
                 $wherearr[] = "add_time<='$end_time'";
            }
        }elseif($search_type == 'JZY') {
             if(!empty($start_time)) {
               $wherearr[] = "end_time>='$start_time'";
            }
            if(!empty($end_time)) {
                 $wherearr[] = "end_time<='$end_time'";
            }
        }
        
        $perpage = 10;
        $offset = ($page -1) * $perpage;
        
        $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
        $homework_list = $mClassHomework->getClassHomework($wherearr, 'homework_id desc', $offset, $perpage);
        $homework_list = $this->ConvertHomeworkInfos($homework_list);
        
        if(empty($homework_list)) {
           $this->ajaxReturn(null, '没有更多作业了!', -1, 'json');
        }
        
        $this->ajaxReturn($homework_list, '获取对象成功!', 1, 'json');
    }
    
	/**
     * 班级作业附件下载
     */
    public function download_file() {
       $homework_id = $this->objInput->getInt('homework_id');
       $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
       $HomeworkInfos = array_shift($mClassHomework->getHomeworkByIds($homework_id));
       $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
       $subject_info = array_shift($mSubjectInfo->getSubjectInfoById($HomeworkInfos['subject_id']));
       $filename = Pathmanagement_sns::getHomework() . $HomeworkInfos['add_account'] . '/' . date('Y/m/d',$HomeworkInfos['add_time']) . $HomeworkInfos['attachment'];
       $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
       $down_file->downfile($filename, $subject_info['subject_name'].'作业下载');
    }
    
    //根据班级作业id查询发送对象列表
    public function accepters_json() {
        $homeworkid = $this->objInput->postInt('homework_id');
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        
        $accepters_list = $mClassHomeworkSend->getHomeworkSendByhomeworkid($homeworkid);
        $mUser = ClsFactory::Create('Model.mUser');
        $countarr = array(
        	'no_view_list'=>array(),
        	'viewed_list'=>array(),
            'no_view_num'=>0,
            'viewed_num'=>0
        );
        
        $new_accepters = $accepters_list[$homeworkid];
        foreach($new_accepters as $id=>$val) {
            $client_name_arr = array_shift($mUser->getClientAccountById($val['client_account']));
            $val['client_name'] = $client_name_arr['client_name'];
            
            if($val['is_view'] == 0) {
                $countarr['no_view_list'][$id] = $val;
                $countarr['no_view_num'] = ++$countarr['no_view_num'];
            }elseif($val['is_view'] == 1) {
                $countarr['viewed_list'][$id] = $val;
                $countarr['viewed_num'] = ++$countarr['viewed_num'];
            }
        }
        
        
        $accepters_list[$homeworkid] = $countarr;
        if(!empty($accepters_list)) {
            $this->ajaxReturn($countarr, '获取对象成功', 1, 'json');
        } else {
            $this->ajaxReturn($countarr, '获取对象失败', -1, 'json');
        }
    }
    
	/**
     * 老师帐号获取科目列表
     */
    private function getSubjectByTeacher($client_account) {
        $mClassTeacher  = ClsFactory::Create('Model.mSchoolTeacher');
        $subject_relation_infos = $mClassTeacher->getSchoolTeacherByTeacherUid($client_account);
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
    
    
    /**
     * 学生帐号获取科目列表
     */
    private function getSubjectByStudent() {
        $class_code = key($this->user['class_info']);
        
        //获取班级科目
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $subject_infos = $mClassTeacher->getClassTeacherByClassCode($class_code);
        
        $subject_ids = array();
        foreach($subject_infos[$class_code] as $class_teacher_id=>$val) {
            $subject_ids[] = $val['subject_id'];
        }
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subject_infos = $mSubjectInfo->getSubjectInfoById($subject_ids);
        
        return !empty($subject_infos) ? $subject_infos : false;
    }
    
    
    /**
     * 家长帐号获取学生帐号
     */
     private function getSubjectByFamily($client_account) {
        if(empty($client_account)) {
            $client_account = $this->getCookieAccount();
        }
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $client_account_family_relation = array_shift($mFamilyRelation->getFamilyRelationByFamilyUid($client_account));
        //获取孩子帐号
        $child_account_arr = reset($client_account_family_relation);
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_infos = array_shift($mClientClass->getClientClassByUid($child_account_arr['client_account']));
        //获取班级code
        $client_class_arr = reset($client_class_infos);
        $class_code = $client_class_arr['class_code'];
        
        //获取班级科目
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $subejct_infos = $mClassTeacher->getClassTeacherByClassCode($class_code);
        
        $subject_ids = array();
        foreach($subejct_infos[$class_code] as $class_teacher_id=>$val) {
            $subject_ids[] = $val['subject_id'];
        }
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subeject_infos = $mSubjectInfo->getSubjectInfoById($subject_ids);
        
        return !empty($subeject_infos) ? $subeject_infos : false;
    }
    
    
    
    /**
     * 处理作业列表的方法如：日期的转换，追加的权限等
     */
    private function ConvertHomeworkInfos($homework_list) {
        if(empty($homework_list)) {
            return false;
        }
        
        //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user);
        $access_list = $context->getUserAccessList();
        
        $client_account = $this->user['client_account'];
        $subject_infos = $this->getSubjectInfosByClientAccount($client_account);
        
        //获取作业的查看情况
        $homework_ids = array_keys($homework_list);
        $where_appends = "homework_id in('" . implode("','", (array)$homework_ids) . "')";
        
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        $homeworksend_list = $mClassHomeworkSend->getHomeworkSendByAccount($client_account, $where_appends);
        //重组数组，按照homework_id的值组织数据
        $homework_viewed_list = array();
        foreach((array)$homeworksend_list as $homework_send) {
            $homework_viewed_list[$homework_send['homework_id']] = $homework_send['is_view'] ? true : false;
        }
        
        $date = time();
        $mUser = ClsFactory::Create('Model.mUser');
        import("@.Common_wmw.WmwString");
        foreach($homework_list as $homeworkid=>$homeworkval) {
            $append_access_list = array();
            if($access_list['show_know_btn']) {
                $is_show_i_know_btn = isset($homework_viewed_list[$homeworkid]) ? $homework_viewed_list[$homeworkid] : false;
                $append_access_list['is_show_i_know_btn'] = !empty($is_show_i_know_btn) ? 0 : 1;
            }
            
            if($homeworkval['add_account'] == $this->user['client_account']) {
                $append_access_list['can_delete'] = true;
            }
            $homeworkval['subject_name'] = $subject_infos[$homeworkval['subject_id']]['subject_name'];
            $homeworkval['attachment_name'] = '下载'.$subject_infos[$homeworkval['subject_id']]['subject_name'].'作业附件'; 
            $homeworkval['add_time'] = date('Y-m-d H:i:s',$homeworkval['add_time']);
            $homeworkval['content'] = WmwString::unhtmlspecialchars($homeworkval['content']);
            $client_name_arr = array_shift($mUser->getClientAccountById($homeworkval['add_account']));
            $homeworkval['client_name'] = $client_name_arr['client_name'];
            if($homeworkval['end_time'] < $date - 86400) {
                $homeworkval['status'] = '已过期';
            } else{
                 $homeworkval['status'] = '正常';
            }
            $homeworkval['end_time'] = date('Y-m-d',$homeworkval['end_time']); 
            $homeworkval['homework_access_list'] = array_merge($access_list, (array)$append_access_list);
            
            $homeworkval['is_sms'] = intval($homeworkval['is_sms']);
            
            $homework_list[$homeworkid] = $homeworkval;
        }
        
        return !empty($homework_list) ? $homework_list : false;
    }
    
}