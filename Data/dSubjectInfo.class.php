<?php
//author : luanhongmin
class dSubjectInfo extends dBase{
    protected $_tablename = 'wmw_subject_info';
    protected $_fields = array(
        'subject_id',
        'subject_name',
        'school_id',
        'sys_subject_id',
        'add_account',
        'add_date',
        'add_time'
    );
    protected $_pk = 'subject_id';
    protected $_index_list = array(
        'subject_id',
        'school_id',
    );

    //通过主键获取科目信息
    public function getSubjectInfoById($subject_ids) {
        return $this->getInfoByPk($subject_ids);
    }
    
    //获得该学校对应的课程列表
    public function getSubjectInfoBySchoolid($schoolid) { 
        return $this->getInfoByFk($schoolid, 'school_id');
    }
    
    //添加课程
    public function addSubjectInfo($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    //修改课程名称
    public function modifySubjectInfo($dataarr, $subjectId) {
        return $this->modify($dataarr, $subjectId);
    }

}
