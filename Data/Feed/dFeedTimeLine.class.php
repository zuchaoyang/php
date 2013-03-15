<?php
class dFeedTimeLine extends dBase{
    protected $_tablename = 'wmw_feed_timeline'; //主表
    protected $_fields = array(
        'id',
        'feed_id',
        'feed_type',
        'client_account',
        'timeline'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'feed_id',
        'feed_type',
        'client_account',
    );
    
    public function getTimeLineByUids($uids) {
        return $this->getInfoByFk($uids, 'client_account', 'timeline desc');
    }
    
    public function addTimeLine($datas, $is_return_id) {
        return $this->add($datas, $is_return_id);
    }
    
    public function delTimeLine($id) {
        return $this->delete($id);
    }
    
    public function modifyTimeline($datas, $id) {
        return $this->modify($datas, $id);
    }
}