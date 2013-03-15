<?php
class dResourceInfo extends dBase{
    protected $_tablename = 'resource_info'; //主表
    protected $_fields = array(
        'resource_id',
        'title',
        'description',
        'product_id',
        'grade_id',
        'subject_id',
        'version_id',
        'chapter_id',
        'section_id',
        'term_id',
        'column_id',
        'file_type',
        'show_type',
        'file_path',
        'file_name',
        'mixed',
        'add_time',
        'is_system',
        'add_account',
        'refuse_reason',
        'click_nums',
        'resource_status',
    );
    protected $_pk = 'resource_id';
    protected $_index_list = array(
        'resource_id',
        'product_id',
        'grade_id',
        'subject_id',
        'version_id',
        'chapter_id',
        'section_id',
        'term_id',
        'column_id',
        'title',
        'resource_status',
        'add_account',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getCombinedKey() { 
        return array(
            'product_id',
            'grade_id',
            'subject_id',
            'version_id',
            'chapter_id',
            'term_id',
            'column_id',
            'is_system',
            'add_account',
            'is_system',
            'resource_status',
        );
    }
    
    public function getResourceInfoById($resource_ids) {
        return $this->getInfoByPk($resource_ids);
    }
    
    public function addResourceInfo($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delResourceInfo($resource_id) {
        return $this->delete($resource_id);
    }
    
    public function delResourceInfoBat($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        $resource_ids = $this->checkIds($resource_ids);
        $wheresql = "where resource_id in('" . implode("','", (array)$resource_ids) . "')";
        $limitsql = "limit " . count($resource_ids);
        
        return $this->execute("delete from {$this->_tablename} $wheresql $limitsql");
    }
    
    
    public function modifyResourceInfo($dataarr, $resource_id) {
        return $this->modify($dataarr, $resource_id);
    }
}