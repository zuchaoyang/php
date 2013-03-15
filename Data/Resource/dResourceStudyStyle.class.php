<?php
class dResourceStudyStyle extends dBase{
    protected $_pk = 'study_style_id';
    protected $_tablename = 'resource_study_style';
    protected $_fields = array(
        'study_style_id',
        'study_style_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'study_style_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceStudyStyleById($study_style_ids) {
        return $this->getInfoByPk($study_style_ids);
    }
    
    public function addResourceStudyStyle($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceStudyStyle($dataarr, $study_style_id) {
        return $this->modify($dataarr, $study_style_id);
    }
    
    public function delResourceStudyStyle($study_style_id) {
        return $this->delete($study_style_id);
    }
}