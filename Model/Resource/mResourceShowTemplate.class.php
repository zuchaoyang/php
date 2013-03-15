<?php
class mResourceShowTemplate extends mBase{
    protected $_dResourceShowTemplate = null;
    
    public function __construct() {
        $this->_dResourceShowTemplate = ClsFactory::Create('Data.Resource.dResourceShowTemplate');
    }
    
    public function getAllResourceShowTemplate() {
        return $this->_dResourceShowTemplate->getInfo();
    }
    
    public function getResrouceShowTemplateById($showtemplate_ids) {
        if(empty($showtemplate_ids)) {
            return false;
        }
        
        return $this->_dResourceShowTemplate->getResrouceShowTemplateById($showtemplate_ids);
    }
    
    public function addResourceShowTemplate($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceShowTemplate->addResourceShowTemplate($dataarr, $is_return_id);
    }
    
    public function modifyResourceShowTemplate($dataarr, $showtemplate_id) {
        if(empty($showtemplate_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceShowTemplate->modifyResourceShowTemplate($dataarr, $showtemplate_id);    
    }
    
    public function delResourceShowTemplate($showtemplate_id) {
        if(empty($showtemplate_id)) {
            return false;
        }
        
        return $this->_dResourceShowTemplate->delResourceShowTemplate($showtemplate_id);
    }
}