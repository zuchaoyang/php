<?php
class dResourceSubject extends dBase{
    protected $_pk = 'subject_id';
    protected $_tablename = 'resource_subject';
    protected $_fields = array(
        'subject_id',
        'subject_name',
        'upd_time',
        'add_time'
    );
    protected $_index_list = array(
        'subject_id',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceSubjectById($subject_id) {
        return $this->getInfoByPk($subject_id);
    }
    
    public function addResourceSubject($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceSubject($dataarr, $subject_id) {
        return $this->modify($dataarr, $subject_id);
    }
    
    public function delResourceSubject($subject_id) {
        return $this->delete($subject_id);
    }
}