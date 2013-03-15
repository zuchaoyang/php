<?php
import('@.RModel.Feed.mFeedBase');
class mZsetUserChildren extends mFeedBase {
    
    
    public function __construct() {
        import('RData.Feed.dZsetUserChildRen');
        $this->_rdata = new dZsetUserChildren();
    }
        
    /**
     * 加载孩子相关的动态信息
     * 1. 我产生的动态
     * 2. 我评论的动态.
     * @param $id
     */
    protected function loader($id, $lastFeedId = 0, $limit = 10) {
        if(empty($id)) {
            return false;
        }
        //获取孩子帐号    
        
        $mSetClientChileren = ClsFactory::Create('RModel.Common.mSetClientChildren');
        $uids = $mSetClientChileren->getClientChildrenByUid($id);     
        
        $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');

        $datas_from_db = $mFeedTimeLine->getFeedByUids($uids, $lastFeedId, $limit);
        
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