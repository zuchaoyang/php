<?php
class dResourceVersion extends dBase{
    protected $_pk = 'version_id';
    protected $_tablename = 'resource_version';
    protected $_fields = array(
        'version_id',
        'version_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'versiont_id'
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceVersionById($version_ids) {
        return $this->getInfoByPk($version_ids);
    }
    
    public function addResourceVersion($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delResourceVersion($version_id) {
        return $this->delete($version_id);
    }
    
}