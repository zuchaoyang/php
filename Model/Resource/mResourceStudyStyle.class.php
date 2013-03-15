<?php
class mResourceStudyStyle extends mBase{
    protected $_dResourceStudyStyle = null;
    public function __construct() {
        $this->_dResourceStudyStyle = ClsFactory::create("Data.Resource.dResourceStudyStyle");
    }
    
    public function getResourceStudyStyleById($study_style_ids) {
        if(empty($study_style_ids)) {
            return false;
        }
        
        return $this->_dResourceStudyStyle->getResourceStudyStyleById($study_style_ids);
    }
    
    public function addResourceStudyStyle($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceStudyStyle->addResourceStudyStyle($dataarr, $is_return_id);
    }
    
    public function modifyResourceStudyStyle($dataarr, $study_style_id) {
        if(empty($dataarr) || !is_array($dataarr) || empty($study_style_id)) {
            return false;
        }
        
        return $this->_dResourceStudyStyle->modifyResourceStudyStyle($dataarr, $study_style_id);
    }
    
    public function delResourceStudyStyle($study_style_id) {
        if(empty($study_style_id)) {
            return false;
        }
        
        return $this->_dResourceStudyStyle->delResourceStudyStyle($study_style_id);
    }
}