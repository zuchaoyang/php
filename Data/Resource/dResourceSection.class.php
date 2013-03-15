<?php
class dResourceSection extends dBase{
    
    protected $_pk = 'section_id';
    protected $_tablename = 'resource_section';
    protected $_fields = array(
        'section_id',
        'section_name',
        'display_order',
        'md5_key',
        'add_time'
    );
    protected $_index_list = array(
        'section_id',
        'md5_key',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceSectionById($section_ids) {
        return $this->getInfoByPk($section_ids);
    }
    
    public function getResourceSectionByMd5key($md5_keys) {
        return $this->getInfoByFk($md5_keys, 'md5_key');
    }
    
    public function addResourceSection($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delResourceSection($section_id) {
        return $this->delete($section_id);
    }
}