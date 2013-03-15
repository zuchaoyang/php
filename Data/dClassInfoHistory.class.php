<?php
class dClassInfoHistory extends dBase {
    protected $_tablename = 'wmw_class_info_history';
    protected $_fields = array(
        'history_id',
        'class_code',
        'school_id',
        'class_name',
        'grade_id',
        'add_account',
        'add_time',
        'headteacher_account',
    	'upgrade_year',
        'graduation_time'
    );
    protected $_pk = 'history_id';
    protected $_index_list = array(
        'history_id',
    	'class_code',
        'school_id',
        'headteacher_account',
    );
    
    public function getClassInfoHistoryById($history_id) {
        return $this->getInfoByPk($history_id);
    }
    
    public function getClassInfoHistoryByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code');
    }
    
    public function getClassInfoHistoryByUid($uids) {
        return $this->getInfoByFk($uids, 'headteacher_account');
    }
    
    public function getClassInfoHistoryBySchoolId($school_ids) {
        return $this->getInfoByFk($school_ids, 'school_id');
    }
    
	/**
    * 更新数据
    * todolist 检测应用是否存在批量修改的调用情况
    */
    public function modifyClassInfoHistory($datas , $class_code) {
        return $this->modify($datas, $class_code);
    }
    
    /**
     * 添加课程
     * @param $dataarr
     */
    public function addClassInfoHistory($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 删除对应的数据
     * @param $uids
     */
    public function delClassInfoHistory($class_code) {
        return $this->delete($class_code);
    }
    
    public function delClassInfoHistoryBat($class_codes) {
        return $this->delete($class_codes, true);
    }
}