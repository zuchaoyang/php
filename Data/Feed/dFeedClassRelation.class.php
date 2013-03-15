<?php
class dFeedClassRelation extends dBase{
    protected $_tablename = 'wmw_feed_class_relation'; //主表
    protected $_fields = array(
        'id',
        'feed_id',
        'class_code',
        'feed_type',
        'timeline'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'feed_id',
        'class_code'
    );

    public function getFeedRelationByFeedId($feed_ids) {
        return $this->getInfoByFk($feed_ids, 'feed_id', 'timeline desc');
    }    
    
    public function getFeedByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code', 'timeline desc');
    }
    
    public function addFeedClassRelation($datas, $is_return_id) {
        return $this->add($datas, $is_return_id);
    }
    
    public function delFeedClassRelation($id) {
        return $this->delete($id);
    }
    
    public function modifyFeedClassRelation($datas, $id) {
        return $this->modify($datas, $id);
    }
}