<?php
/**
 * aothor guoxuewen
 * 班级管理首页
 */
class IndexAction extends SnsController{
    
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index() {
        
        $class_code = $this->objInput->getInt('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        
        list($headteacher, $class_admin_list, $teacher_list, $student_list) = $this->getClassMemberList($class_code);
        //追加教师的科目信息
        $teacher_list = $this->appendTeacherSubjectInfo($teacher_list, $class_code);
        //解析学生的班级角色
        $student_list = $this->appendStudentClassRole($student_list);
        //判断班主任是否在老师的列表中,存在则从老师列表中删除
        if(!empty($headteacher)) {
            $headteacher_account = $headteacher['client_account'];
            if(isset($teacher_list[$headteacher_account])) {
                $headteacher = array_merge((array)$headteacher, (array)$teacher_list[$headteacher_account]);
                unset($teacher_list[$headteacher_account]);
            }
        }
        
        $this->assign('headteacher', $headteacher);
        $this->assign('class_admin_nums', count($class_admin_list));
        $this->assign('class_admin_list', $class_admin_list);
        $this->assign('teacher_nums', count($teacher_list));
        $this->assign('teacher_list', $teacher_list);
        $this->assign('student_nums', count($student_list));
        $this->assign('student_list', $student_list);
        
        $this->assign('class_info', $this->user['class_info'][$class_code]);
        $this->assign('class_code', $class_code);
        
        $this->display('class_member_list');
    }
    
    /**
     * 设置班级管理员
     */
    public function setClassAdminAjax(){
        $class_code     = $this->objInput->getInt('class_code');
        $client_account = $this->objInput->postInt('client_account');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdmin($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限设置管理员!', -1, 'json');
        }
        
        $dataarr = array(
        	'class_admin' => IS_CLASS_ADMIN
        );
        
        $mClinetClass = ClsFactory::Create("Model.mClientClass");
        $client_class_id = $mClinetClass->getClientClassId($class_code, $client_account);
        $mClientClassVm = ClsFactory::Create("RModel.Common.mHashClientClass");
        $mClientClassVm->setClientClassbyUid($client_account, $dataarr);
        if(!$mClinetClass->modifyClientClass($dataarr, $client_class_id)) {
            $this->ajaxReturn(null, "管理员设置失败！", -1, 'json');
        }
        
        $this->ajaxReturn(null, "管理员设置成功！", 1, 'json');
    }
    
    /**
     * 取消班级管理员
     */
    public function cancelClassAdminAjax(){
        $class_code     = $this->objInput->getInt('class_code');
        $client_account = $this->objInput->postStr('client_account');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdmin($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限设置管理员!', -1, 'json');
        }
        
        $dataarr = array(
        	'class_admin' => 0
        );
        $mClinetClass = ClsFactory::Create("Model.mClientClass");
        $client_class_id = $mClinetClass->getClientClassId($class_code, $client_account);
        
        if(!$mClinetClass->modifyClientClass($dataarr, $client_class_id)) {
            $this->ajaxReturn(null, "管理员取消失败！", -1, 'json');
        }
        
        $this->ajaxReturn(null, "管理员取消成功！", 1, 'json');
    }
    
    /**
     * 修改班级的基本信息
     */
    public function modifyClassInfoAjax(){
        $class_code = $this->objInput->getInt('class_code');
        $class_name = $this->objInput->postStr('class_name');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdmin($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限管理班级信息!', -1, 'json');
        }
        
        $class_info_datas = array(
            'class_name' => $class_name
        );
        
        $mClassInfo = ClsFactory::Create("Model.mClassInfo");
        if(!$mClassInfo->modifyClassInfo($class_info_datas, $class_code)) {
            $this->ajaxReturn(null, '修改失败！', -1, 'json');
        }
        
        $mHashClass = ClsFactory::Create("RModel.Common.mHashClass");
        $mHashClass->setClassById($class_code, $class_info_datas);
        
        $this->ajaxReturn("", '修改成功！', 1, 'json');
    }
    
    /**
     * 获取班级成员列表信息
     * @param $class_code
     */
    private function getClassMemberList($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $filters = array(
            CLIENT_TYPE_STUDENT,
            CLIENT_TYPE_TEACHER
        );
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code, $filters);
        $client_class_list = & $client_class_arr[$class_code];
        if(empty($client_class_list)) {
            return false;
        }
        
        //获取班级管理的账号信息
        $class_info_list = $this->user['class_info'];
        if(isset($class_info_list[$class_code])) {
            $class_info = $class_info_list[$class_code];
        } else {
            $mClassInfo = ClsFactory::Create('Model.mClassInfo');
            $class_list = $mClassInfo->getClassInfoById($class_code);
            $class_info = $class_list[$class_code];
        }
        $headteacher_account = $class_info['headteacher_account'];
        
        //获取用户的基本信息
        $uids = array_keys($client_class_list);
        $uids[] = $headteacher_account;
        $mUser = ClsFactory::Create('Model.mUser');
        $member_list = $mUser->getUserBaseByUid($uids);
        
        //追加用户的班级关系信息
        foreach($member_list as $uid=>$user) {
            if(isset($client_class_list[$uid])) {
                $user = array_merge($user, (array)$client_class_list[$uid]);
            }
            $member_list[$uid] = $user;
        }
        
        //用户数据分组
        $class_admin_list = $student_list = $teacher_list = $headteacher = array();
        foreach($member_list as $uid=>$user) {
            $is_admin = (intval($user['class_admin']) == IS_CLASS_ADMIN) ? true : false;
            $user['is_admin'] = $is_admin;
            //判断用户是否是班主任
            if($uid == $headteacher_account) {
                $headteacher = $user;
            }
             //判断用户是否是管理员
            if($is_admin) {
                $class_admin_list[$uid] = $user;
            }
            $client_type = intval($user['client_type']);
            if($client_type == CLIENT_TYPE_TEACHER) {
                $teacher_list[$uid] = $user;
            } else if($client_type == CLIENT_TYPE_STUDENT) {
                $student_list[$uid] = $user;
            }
        }
        
        return array(
            $headteacher,
            $class_admin_list,
            $teacher_list,
            $student_list,
        );
    }
    
    /**
     * 追加教师的科目信息
     * @param $teacher_list
     * @param $class_code
     */
    private function appendTeacherSubjectInfo($teacher_list, $class_code) {
        if(empty($class_code) || empty($teacher_list)) {
            return !empty($teacher_list) ? $teacher_list : array();
        }
        
        //追加老师的科目信息
        $mClassTeacher = ClsFactory::Create("Model.mClassTeacher");
        $class_teacher_arr = $mClassTeacher->getClassTeacherByClassCode($class_code);
        $class_teacher_list = & $class_teacher_arr[$class_code];
        
        //获取科目信息，并以client_account重组数组
        $new_class_teacher_list = $subject_ids = array();
        foreach($class_teacher_list as $key=>$class_teacher) {
            $subject_ids[] = $class_teacher['subject_id'];
            $new_class_teacher_list[$class_teacher['client_account']] = $class_teacher;
        }
        unset($class_teacher_list, $class_teacher_arr);
        
        //追加班级任课老师的科目信息
        $mSubjectInfo = ClsFactory::Create("Model.mSubjectInfo");
        $subject_list = $mSubjectInfo->getSubjectInfoById($subject_ids);
        //合并科目信息和班级任课老师关系
        foreach($new_class_teacher_list as $uid=>$class_teacher) {
            $subject_id = intval($class_teacher['subject_id']);
            if(isset($subject_list[$subject_id])) {
                $class_teacher = array_merge($class_teacher, (array)$subject_list[$subject_id]);
            }
            $new_class_teacher_list[$uid] = $class_teacher;
        }
        //将老师的科目信息合并到教师列表信息中
        foreach($teacher_list as $uid=>$user) {
            if(isset($new_class_teacher_list[$uid])) {
                $user = array_merge($user, (array)$new_class_teacher_list[$uid]);
            }
            $teacher_list[$uid] = $user;
        }
        
        return $teacher_list;
    }
    
    /**
     * 追加学生的班级角色信息
     * @param $student_list
     */
    private function appendStudentClassRole($student_list) {
        if(empty($student_list)) {
            return array();
        }
        
        foreach($student_list as $uid=>$student) {
            $client_class_role = intval($student['client_class_role']);
            $student['client_class_role_name'] = $client_class_role ? Constancearr::classleader($client_class_role) : '学生';
            $student_list[$uid] = $student;
        }

        return $student_list;
    }
    
}