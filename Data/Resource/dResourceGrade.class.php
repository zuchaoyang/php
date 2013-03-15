<?php
class dResourceGrade extends dBase{
    protected $_pk = 'grade_id';
    protected $_tablename = 'resource_grade';
    protected $_fields = array(
        'grade_id',
        'grade_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'grade_id',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceGradeById($grade_ids) {
        return $this->getInfoByPk($grade_ids);
    }
    
    public function addResourceGrade($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceGrade($dataarr, $grade_id) {
        return $this->modify($dataarr, $grade_id);
    }
    
    public function delResourceGrade($grade_id) {
        return $this->delete($grade_id);
    }
}