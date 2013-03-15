<?php
class dSchoolClientStatistics extends dBase{
    protected $_tablename = 'wmw_school_client_statistics';
    protected $_fields = array(
          'school_id',
          'school_name',
          'school_address',
          'parents_num',
          'teacher_num',
          'student_num',
          'phone_old_num',
          'phone_new_num',
          'teacher_phone_num',
		  'family_phone_num', 
          'area_id',
		  'create_date'
    );
    protected $_pk = 'school_id';
    protected $_index_list = array(
        'school_id',
    );

    //添加
    public function addSchoolClientStatistics($dataarr, $is_return_id=false) {
        
        return $this->add($dataarr, $is_return_id);
    }

    //根据学校id 查询学校统计
    public function getSchoolClientStatisticsById($schooleIds) {
    	return $this->getInfoByPk($schooleIds);
    }
    
    //清空表
    function cleartable() {
        $this->execute("delete from $this->_tablename");
    }
  
}