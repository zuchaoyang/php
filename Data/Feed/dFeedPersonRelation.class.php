<?php
Class dFeedPersonRelation extends dBase{
    protected $_tablename = 'wmw_feed_person_relation'; //主表
    protected $_fields = array(
        'id',
        'feed_id',
        'client_account',
        'feed_type',
        'timeline'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'feed_id',
        'client_account'
    );
        
    public function getFeedRelationByFeedId($feed_ids) {
        return $this->getInfoByFk($feed_ids, 'feed_id', 'timeline desc');
    }  
        
    public function getFeedByUid($uids) {
        return $this->getInfoByFk($uids, 'client_account', 'timeline desc');
    }
    
    public function addFeedPersonRelation($datas, $is_return_id) {
        return $this->add($datas, $is_return_id);
    }
    
    public function delFeedPersonRelation($id) {
        return $this->delete($id);
    }
    
    public function modifyFeedPersonRelation($datas, $id) {
        return $this->modify($datas, $id);
    }
}