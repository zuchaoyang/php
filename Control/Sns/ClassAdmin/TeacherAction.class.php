<?php
class TeacherAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index() {
        $school_id = key($this->user['school_info']);
        $class_code = $this->objInput->getInt("class_code");
        
        $mSubjectInfo = ClsFactory::Create("Model.mSubjectInfo");
        $subject_list = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
        
        $class_teacher_list = $this->getTeacherListByClassCode($class_code, $school_id);
        
        $this->assign('subject_list', $subject_list[$school_id]);
        $this->assign('class_teacher_list', $class_teacher_list);
        
        $this->assign('class_code', $class_code);
        $this->display('teacher_admin');
    }
    
    /**
     * 得到班级的所有老师list
     */
    public function getTeacherListByClassCode($class_code, $school_id){
        if(empty($class_code)) {
            return false;
        }
        
        $mClassTeacher = ClsFactory::Create("Model.mClassTeacher");
        $class_teacher_list = $mClassTeacher->getClassTeacherByClassCode($class_code);
        
        $class_teacher_list  = reset($class_teacher_list);
        
        $client_account = array();
        if(!empty($class_teacher_list)) {
            foreach($class_teacher_list as $val) {
                $client_account[$val['client_account']] = $val['client_account'];
            }
            
            $mSubjectInfo = ClsFactory::Create("Model.mSubjectInfo");
            $subject_list = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
            
            $subject_list = reset($subject_list);
            $mUser = ClsFactory::Create("Model.mUser");
            $user_list = $mUser->getUserBaseByUid($client_account);
            
            foreach($class_teacher_list as $id => $val) {
                $val['client_name'] = $user_list[$val['client_account']]['client_name'];
                $val['subject_name'] = $subject_list[$val['subject_id']]['subject_name'];
                $class_teacher_list[$id] = $val;
            }
        }
        
        return !empty($class_teacher_list) ? $class_teacher_list : false;
    }
    
    /**
     * 得到学校的所有老师
     * @param unknown_type $school_id
     */
    public function getTeacherlist_json() {
        $subject_id = $this->objInput->postInt('subject_id');
        $class_code = $this->objInput->postInt('class_code');
        $school_id = key($this->user['school_info']);
        $mSchoolTeacher = ClsFactory::Create("Model.mSchoolTeacher");
        $filter = array(
            'subject_id' => $subject_id
        );
        $teacher_list = $mSchoolTeacher->getSchoolTeacherInfoBySchoolId($school_id,$filter);
        $teacher_uid = array();
        
        $teacher_list = reset($teacher_list);
        
        if(!empty($teacher_list)){
            foreach($teacher_list as $class_teacher_id => $teacher_class_info){
                $teacher_uid[$teacher_class_info['client_account']] = $teacher_class_info['client_account'];
            }
        }
        $mUser = ClsFactory::Create("Model.mUser");
        $user_list = $mUser->getUserBaseByUid($teacher_uid);
        $checked_list = $this->getTeacherByCode($subject_id, $class_code);
       
        foreach($teacher_list as $class_teacher_id => $teacher_class_info){
            if(in_array($teacher_class_info['client_account'], $checked_list)){
                $teacher_class_info['is_checked'] = true;
            }else{
                $teacher_class_info['is_checked'] = false;
            }
            
            $teacher_class_info['client_name'] = $user_list[$teacher_class_info['client_account']]['client_name'];
            $teacher_class_info['client_headimg_url'] = $user_list[$teacher_class_info['client_account']]['client_headimg_url'];
            $teacher_list[$class_teacher_id] = $teacher_class_info;
        }
        
        !empty($teacher_list) ? $this->ajaxReturn($teacher_list, '获取对象成功', 1, 'json') : $this->ajaxReturn($teacher_list, '获取对象失败', -1, 'json');
    }
    
    //得到当前班级的任课教师list
    private function getTeacherByCode($subject_id, $class_code){
        
        $mClassTeacher = ClsFactory::Create("Model.mClassTeacher");
        $datarr = $mClassTeacher->getClassTeacherByClassCodeAndSubjectId($subject_id, $class_code);
        $check_uid = array();
        if(!empty($datarr)) {
            foreach($datarr as $val){
                $check_uid[$val['client_account']] = $val['client_account'];
            }
        }
        
        return !empty($check_uid) ? $check_uid : false;
    }
    
    public function setTeacher(){
        $class_code = $this->objInput->postInt('class_code');
        $data = $this->objInput->postArr('data');
        
        $uid = $this->usr['client_account'];
        
        $mClassTeacher = ClsFactory::Create("Model.mClassTeacher");
        $add_dataarr = array();
        $del_dataarr = array();
        
        $add_result = true;
        $del_result = true;
        
        $mClassInfo = ClsFactory::Create("Model.mClassInfo");
        $class_info = $mClassInfo->getClassInfoBaseById($class_code);
        $header_account = $class_info[$class_code]['headteacher_account'];
                
                
        $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
	    $mClientClass = ClsFactory::Create("Model.mClientClass");
        if(!empty($data['del'])) {
            foreach($data['del'] as $val){
                if(!empty($val)) {
                    $class_teacher = $mClassTeacher->getClassTeacherByClassCodeAndSubjectId($val['subject_id'], $class_code);
                    $class_teacher_id = key($class_teacher);
                    $del_result = $mClassTeacher->delClassTeacher($class_teacher_id);
                    $uid = $class_teacher[$class_teacher_id]['client_account'];
                    $class_id = $mClientClass->getClientClassId($class_code, $uid);
                    if($header_account == $val['uid']) {
                        $dataarr = array(
                            'teacher_class_role' => 2,
                        );
                        $mClientClass->modifyClientClass($dataarr, $class_id);
                    }else{
                        $mClientClass->delClientClass($class_id);
                    }
                    //更新个人与班级关系Redis
                    $client_class = $mHashClientClass->getClientClassbyUid($uid, true);            
                }
            }
        }
        
        $dataarr_client_class = array();
        if(!empty($data['add'])) {
            foreach($data['add'] as $val){
                $add_dataarr = array(
        		    'client_account' => $val['uid'],
        		    'class_code' => $class_code,
        		    'subject_id' => $val['subject_id'],
        		    'add_time' => time(),
        		    'add_account' => $uid,
        		    'upd_time' => time(),
        		    'upd_account' => $uid,
                );
                
                $add_result = $mClassTeacher->addClassTeacher($add_dataarr);
                
                if($header_account == $val['uid']) {
                    $class_id = $mClientClass->getClientClassId($class_code, $val['uid']);
                    $dataarr = array(
                        'teacher_class_role' => 3,
                    );
                    $mClientClass->modifyClientClass($dataarr, $class_id);
                }else{
                    $dataarr_client_class = array(
                        'client_account' => $val['uid'],
                        'class_code' => $class_code,
                        'teacher_class_role' => 2,
                        'class_admin' => 0,
                        'add_time' => time(),
                        'add_account' => $this->user['client_account'],
                        'upd_account' => $this->user['client_account'],
                        'upd_time' => time(),
                        'client_type' => CLIENT_TYPE_TEACHER,
                    );
                    
                    $mClientClass->addClientClass($dataarr_client_class);
                }
                //更新个人与班级关系Redis
                $uid = $val['uid'];
                $client_class = $mHashClientClass->getClientClassbyUid($uid, true); 
            }
        }       

        //更新班级老师Redis
        $mSetClassTeacher = ClsFactory::Create('RModel.Common.mSetClassTeacher');
        $class_teacher = $mSetClassTeacher->getClassTeacherById($class_code, true);
        
        !empty($add_result) && !empty($del_result) ? $this->ajaxReturn(array(), '修改成功！', 1, 'json') : $this->ajaxReturn(array(), '修改失败！', -1, 'json');
    }
}