<?php
class mSysSubject extends mBase{
	protected $_dSysSubject = null;
	
	public function __construct() {
		$this->_dSysSubject = ClsFactory::Create('Data.dSysSubject');
	}
	
	//通过学校的类型获取对应的系统默认科目
	public function getSysSubjectBySubjectType($subject_type) {
        if (empty($subject_type)) { 
            return false;
        }
        
        $subject_type = implode(',', (array)$subject_type);
        $wheresql = array(
            "subject_type in($subject_type)"
        );
        $orderby = "subject_id asc ";
        
        return $this->_dSysSubject->getInfo($wheresql, $orderby);
	}
	
}