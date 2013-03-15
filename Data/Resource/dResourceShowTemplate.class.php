<?php
class dResourceShowTemplate extends dBase{
    protected $_pk = 'showtemplate_id';
    protected $_tablename = 'resource_showtemplate';
    protected $_fields = array(
        'showtemplate_id',
        'showtemplate_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'showtemplate_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResrouceShowTemplateById($showtemplate_ids) {
        return $this->getInfoPk($showtemplate_ids);
    }
    
    public function addResourceShowTemplate($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceShowTemplate($dataarr, $showtemplate_id) {
        return $this->modify($dataarr, $showtemplate_id);    
    }
    
    public function delResourceShowTemplate($showtemplate_id) {
        return $this->delete($showtemplate_id);
    }
}