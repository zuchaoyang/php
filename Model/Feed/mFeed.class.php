<?php
class mFeed extends mBase{

	protected $_dFeed = null;
	
	public function __construct() {
		$this->_dFeed = ClsFactory::Create('Data.Feed.dFeed');
	}
    
    public function getFeedByid($feed_ids) {
        if(empty($feed_ids)) {
            return false;
        }
  
        return $this->_dFeed->getFeedById($feed_ids);
    }    

    public function addFeed($datas, $is_return_id) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dFeed->addFeed($datas, $is_return_id);
    }
    
    public function delFeed($feed_id) {
        if(empty($feed_id)) {
            return false;
        }
        
        return $this->_dFeed->delFeed($feed_id);
    }
    
    public function modifyFeed($datas, $feed_id) {
        if(empty($datas) || !is_array($datas) || empty($feed_id)) {
            return false;
        }
        
        return $this->_dFeed->modifyFeed($datas, $feed_id);
    }
}
