<?php

class dClassInfo extends dBase{
	protected $_tablename = 'wmw_class_info';
    protected $_fields = array(
        'class_code',
        'school_id',
        'class_name',
        'grade_id',
        'add_account',
        'add_date',
        //'class_style',
        'add_time',
        'headteacher_account',
    	'upgrade_year'
    );
    protected $_pk = 'class_code';
    protected $_index_list = array(
        'class_code',
        'school_id',
        'headteacher_account',
    );
    
    /**
     * 通过班级id获取班级的信息，映射关系:一对一，以class_code组织数据
     * @param $class_code
     */
    public function getClassInfoById($class_code) {
        return $this->getInfoByPk($class_code);
    }
    
    public function getClassInfoByUid($uids) {
        return $this->getInfoByFk($uids, 'headteacher_account');
    }
    
    public function getClassInfoBySchoolId($school_ids) {
        return $this->getInfoByFk($school_ids, 'school_id');
    }
    
	/**
    * 更新数据
    * todolist 检测应用是否存在批量修改的调用情况
    */
    public function modifyClassInfo($datas , $class_code) {
        return $this->modify($datas, $class_code);
    }
    
    /**
     * 添加课程
     * @param $dataarr
     */
    public function addClassInfo($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 删除对应的数据
     * @param $uids
     */
    public function delClassInfo($class_code) {
        return $this->delete($class_code);
    }
    
    public function delClassInfoBat($class_codes) {
        return $this->delete($class_codes, true);
    }
}
