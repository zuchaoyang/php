<?php
import('@.RModel.Feed.mFeedBase');
class mZsetUserMy extends mFeedBase {
    
    
    public function __construct() {
        import('RData.Feed.dZsetUserMy');
        $this->_rdata = new dZsetUserMy();
    }
        
    /**
     * 加载与我相关动态信息
     * 1. 我产生的动态
     * 2. 我评论的动态.
     * @param $id
     */
    protected function loader($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)) {
            return false;
        }
        	    
        $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');

        $datas_from_db = $mFeedTimeLine->getFeedByUids($id, $lastFeedId, $limit);
        
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