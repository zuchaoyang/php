<?php
class mFeedPersonRelation extends mBase{
    
    protected $_dFeedPersonRelation = null;
	
	public function __construct() {
		$this->_dFeedPersonRelation = ClsFactory::Create('Data.Feed.dFeedPersonRelation');
	}
        
    /**
     * 通过我们网帐号信息获取动态信息
     * @param $uids   		  帐号id集合
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit         
     */
    public function getFeedByUids($uids, $lastFeedId = 0, $limit = 10) {
        if(empty($uids)) {
            return false;
        }
        
       return $this->getFeedByUidsAndType($uids, 0, $lastFeedId, $limit);
    } 

    /**
     * 通过我们网帐号和动态类型信息获取动态信息
     * @param $uids   		  帐号id集合
     * @param $feed_type     动态类型: 1：说说 2：日志  3：相册 
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit         
     */
    public function getFeedByUidsAndType($uids, $feed_type = 0, $lastFeedId = 0, $limit = 10) {
        if(empty($uids)) {
            return false;
        }
        
        $where_arr = array();
        $where_arr[] = "client_account in('" . implode("','", (array)$uids) . "')";
        
        if ($feed_type > 0) {
            $where_arr[] = " feed_type = $feed_type";
        }
        
        if($lastFeedId > 0) {
            $where_arr[] = "feed_id < $lastFeedId";
        }
        
        return $this->_dFeedPersonRelation->getInfo($where_arr, 'feed_id desc', 0, $limit);
    }    
    
    public function addFeedPersonRelation($datas, $is_return_id) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dFeedPersonRelation->addFeedPersonRelation($datas, $is_return_id);
    }
    
    public function delFeedPersonRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dFeedPersonRelation->delFeedPersonRelation($id);
    }
    
    public function modifyFeedPersonRelation($datas, $id) {
        if(empty($datas) || !is_array($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dFeedPersonRelation->modifyFeedPersonRelation($datas, $id);
    }
    
    public function modifyFeedPersonRelationByFeedId($feed_id, $datas) {
        if(empty($feed_id) || empty($datas)) {
            return false;
        }
        
        $result = $this->getFeedRelationByFeedId($feed_id);
        
        if (empty($result)) return false;
        $id = key($result);
        
        return $this->modifyFeedPersonRelation($datas, $id);
    }   

    public function delFeedPersonRelationByFeed($feed_id) {
        if(empty($feed_id)) {
            return false;
        }
        
        $result = $this->getFeedRelationByFeedId($feed_id);
        
        if (empty($result)) return false;
        $id = key($result);        
        
        return $this->delFeedPersonRelation($id);
    }    
    
    /**
     * 通过feedId 信息获取动态信息
     * @param $feed_id       动态id
     * @param $limit         
     */
    public function getFeedRelationByFeedId($feed_ids) {
        if(empty($feed_ids)) {
            return false;
        }
                
        return $this->_dFeedPersonRelation->getFeedRelationByFeedId($feed_ids);
    }      
}