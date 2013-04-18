<?php
class dFeed extends dBase{
    protected $_tablename = 'wmw_feed'; //主表
    protected $_fields = array(
        'feed_id',
        'feed_type',
        'title',
    	'feed_content',
    	'from_id',
    	'add_account',
        'img_url',
    	'timeline',
        'action',
        'from_class_code'
    );
    protected $_pk = 'feed_id';
    protected $_index_list = array(
        'feed_id',
        'add_account'
    );
    
    public function getFeedById($feed_ids) {
        if(empty($feed_ids)) {
            return false;
        }
        
        //按照给定的feed_id的值的顺序排序,使用了mysql的find_in_set函数作为排序条件
        $orderby = "find_in_set({$this->_pk}, '" . implode(',', (array)$feed_ids) . "')";
        
        return $this->getInfoByPk($feed_ids, $orderby);
    }

    public function addFeed($datas, $is_return_id) {
        return $this->add($datas, $is_return_id);
    }
    
    public function delFeed($feed_id) {
        return $this->delete($feed_id);
    }
    
    public function modifyFeed($datas, $feed_id) {
        return $this->modify($datas, $feed_id);
    }
}