<?php
//郭学文
class dSchoolTeacher extends dBase{
    protected $_tablename = 'wmw_school_teacher';
    protected $_fields = array(
            'teacher_school_id',
            'client_account',
            'school_id',
            'subject_id',
            'add_time',
            'add_account',
            'upd_account',
            'upd_time',
    );
    protected $_pk = 'teacher_school_id';
    protected $_index_list = array(
        'teacher_school_id',
        'client_account',
        'school_id',
    );
    
    //通过学校id得到老师信息
    function getSchoolTeacherInfoBySchoolId($schoolIds) {
        return $this->getInfoByFk($schoolIds, 'school_id');
    }
    
    //通过教师账号获取教师的科目信息
    public function getSchoolTeacherByTeacherUid($client_account) {
        return $this->getInfoByFk($client_account, 'client_account');
    }
    
    //添加信息
    function addSchoolTeacher($dataarr, $is_return_id=false) {
        return $this->add($dataarr, $is_return_id);
    }
    
     //删除老师信息
    public function delSchoolTeacher($school_teacher_id) {
        return $this->delete($school_teacher_id);
    }
   

}
