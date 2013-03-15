<?php
class mFeedClassRelation extends mBase{
    
    protected $_dFeedClassRelation = null;
	
	public function __construct() {
		$this->_dFeedClassRelation = ClsFactory::Create('Data.Feed.dFeedClassRelation');
	}
	
    /**
     * 通过班级id信息获取动态信息
     * @param $class_codes   班级id集合
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit         
     */
    public function getFeedByClassCode($class_codes, $lastFeedId = 0, $limit = 10) {
        if(empty($class_codes)) {
            return false;
        }
        
       return $this->getFeedByClassCodeAndType($class_codes, 0, $lastFeedId, $limit);
    } 

    /**
     * 通过班级id和动态类型信息获取动态信息
     * @param $class_codes   班级id集合
     * @param $feed_type     动态类型: 1：说说 2：日志  3：相册 
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit         
     */
    public function getFeedByClassCodeAndType($class_codes, $feed_type = 0, $lastFeedId = 0, $limit = 10) {
        if(empty($class_codes)) {
            return false;
        }
        
        $where_arr = array();
        $where_arr[] = "class_code in('" . implode("','", (array)$class_codes) . "')";
        
        if ($feed_type > 0) {
            $where_arr[] = " feed_type = $feed_type";
        }
        
        if($lastFeedId > 0) {
            $where_arr[] = "feed_id < $lastFeedId";
        }
        
        return $this->_dFeedClassRelation->getInfo($where_arr, 'feed_id desc', 0, $limit);
    }    
    
    public function addFeedClassRelation($datas, $is_return_id) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dFeedClassRelation->addFeedClassRelation($datas, $is_return_id);
    }
    
    public function modifyFeedClassRelationByFeedId($feed_id, $datas) {
        if(empty($feed_id) || empty($datas)) {
            return false;
        }
        
        $result = $this->getFeedRelationByFeedId($feed_id);
        
        if (empty($result)) return false;
        $id = key($result);
        
        return $this->modifyFeedClassRelation($datas, $id);
    }   

    public function delFeedClassRelationByFeed($feed_id) {
        if(empty($feed_id)) {
            return false;
        }
        
        $result = $this->getFeedRelationByFeedId($feed_id);
        
        if (empty($result)) return false;
        $id = key($result);        
        
        return $this->delFeedClassRelation($id);
    }    
    
    public function delFeedClassRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dFeedClassRelation->delFeedClassRelation($id);
    }
    
    public function modifyFeedClassRelation($datas, $id) {
        if(empty($datas) || !is_array($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dFeedClassRelation->modifyFeedClassRelation($datas, $id);
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
                
        return $this->_dFeedClassRelation->getFeedRelationByFeedId($feed_ids);
    }    
}