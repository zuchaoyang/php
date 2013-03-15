<?php

/**
 * 班级动态信息
 * 1. 基本的方法封装在mFeedBase
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */

import('@.RModel.Feed.mFeedBase');
class mZsetClassAll extends mFeedBase {
    
    
    public function __construct() {
        import('RData.Feed.dZsetClassAll');
        $this->_rdata = new dZsetClassAll();
    }
        
    /**
     * 加载班级全部动态信息
     * @param $id			  班级id
     * @param $timeline      最后查询结果的时间点
     * @param $lastFeedId        最后查询结果的feed_id
     * @param $limit   
     */
    protected function loader($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)) {
            return false;
        }
        	    
        $mFeed = ClsFactory::Create('Model.Feed.mFeedClassRelation');
         
        $datas_from_db = $mFeed->getFeedByClassCode($id, $lastFeedId, $limit);
        
        $result = array();
        foreach ($datas_from_db as $key => $val) {
            $result[] = array(
            	'value' => $val['feed_id'],
                'score' => $val['feed_id']
            );
        }
        
        return $result;
    }    
    
    
}