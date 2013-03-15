<?php
class dResourceTerm extends dBase{
    protected $_pk = 'term_id';
    protected $_tablename = 'resource_term';
    protected $_fields = array(
        'term_id',
        'term_name',
        'upd_time',
        'add_time',
    );
    protected $_index_list = array(
        'term_id',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceTermById($term_ids) {
        return $this->getInfoByPk($term_ids);
    }
    
    public function addResourceTerm($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyResourceTerm($dataarr, $term_id) {
        return $this->modify($dataarr, $term_id);
    }
    
    public function delResourceTerm($term_id) {
        return $this->delete($term_id);
    }
}