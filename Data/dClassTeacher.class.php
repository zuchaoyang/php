<?php

class dClassTeacher extends dBase {
	
	protected $_tablename = 'wmw_class_teacher';
    protected $_fields = array(
		  'class_teacher_id',
		  'client_account',
		  'class_code',
		  'subject_id',
		  'add_time',
		  'add_account',
		  'upd_time',
		  'upd_account',
    );
    protected $_pk = 'class_teacher_id';
    protected $_index_list = array(
        'class_teacher_id',
        'client_account',
        'class_code',
    );
    
    /**
     * 通过教师账号获取信息
     * @param $uids
     */
    public function getClassTeacherByUid($uids) {
        return $this->getInfoByFk($uids, 'client_account');
    }
    
    public function getClassTeacherByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code');
    }
    
    public function modifyClassTeacher($dataarr, $class_teacher_id) {
        return $this->modify($dataarr, $class_teacher_id);
    }
    
    public function addClassTeacher($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delClassTeacher($class_teacher_id) {
        return $this->delete($class_teacher_id);
    }
}

