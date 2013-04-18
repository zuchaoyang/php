<?php
class CreateFeed {
    
    /**
     * 创建用户动态信息
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     枚举值   1:说说  2：日志  3：相册
     */   
    
    public function createPersonFeed($uid, $from_id, $feed_type, $action) {
        if(empty($uid) || empty($from_id) || empty($feed_type) || empty($action)) {
            return false;
        }
        
        //提取feed信息
        $feed_datas = $this->extractFeed($from_id, $feed_type);
        if(empty($feed_datas)) return false;
        
        //mysql入库操作
        $feed_datas['action'] = $action;
        $feed_id = $this->saveFeed($feed_datas);
        
        if(empty($feed_id)) return false;
        
        //保存到 wmw_feed_person_relation wmw_feed_timeline
        $this->saveFeedToPerson($uid, $feed_id, $feed_type);
        $this->saveFeedToTimeLine($uid, $feed_id, $feed_type);
        
        return $feed_id;
    }
    
    /**
     * 添加用户在班级空间中产生的动态
     * @param $class_code     int    用户当前所在的班级      
     * @param $uid       	  帐号
     * @param $from_id       产生动态的实体id
     * @param $feed_type     int    枚举值   1:说说  2：日志  3：相册

     */
    public function createClassFeed($class_code, $uid, $from_id, $feed_type, $action) {
        if(empty($class_code) || empty($uid) || empty($from_id) || empty($feed_type) || empty($action)) {
            return false;
        }
        
        //提取feed信息
        $feed_datas = $this->extractFeed($from_id, $feed_type);
        if(empty($feed_datas)) return false;
        
        //mysql入库操作
        $feed_datas['action'] = $action;
        $feed_datas['from_class_code'] = $class_code;
        $feed_id = $this->saveFeed($feed_datas);
        
        if(empty($feed_id)) return false;
        
        //保存到 wmw_feed_class_relation wmw_feed_timeline
        $this->saveFeedToClass($class_code, $feed_id, $feed_type);
        $this->saveFeedToTimeLine($uid, $feed_id, $feed_type);
                
        return $feed_id;
    }
    
    /**
     * 提取实体中的动态信息
     * @param  $entity_datas
     * @param  $feed_type
     */
    private function extractFeed($from_id, $feed_type) {
        if(empty($from_id) || empty($feed_type)) {
            return false;
        }
        
        import('@.Control.Api.FeedImpl.PackFeed');
        
        return PackFeed::getFeedDatas($from_id, $feed_type);
    }
    
    /**
     * 将feed信息添加到数据库
     * @param $feed_datas
     */
    private function saveFeed($feed_datas) {
        if(empty($feed_datas) || !is_array($feed_datas)) {
            return false;
        }
        
        $mFeed = ClsFactory::Create('Model.Feed.mFeed');
        $feed_id = $mFeed->addFeed($feed_datas, true);
        return !empty($feed_id) ? $feed_id : false;
    }
    
    /**
     * 添加个人feed关系数据
     * @param $uid			用户id
     * @param $feed_id		动态id
     * @param $feed_type	动态类型
     * @param $timeline	          时间
     * @return $id 	        成功：个人feed关系主键，失败：false
     */
    private function saveFeedToPerson($uid, $feed_id, $feed_type) {
        if(empty($uid) || empty($feed_id) || empty($feed_type)) {
            return false;
        }
        
        $person_relation_datas = array(
            'feed_id'        => $feed_id,
            'client_account' => $uid,
            'feed_type'      => $feed_type,
            'timeline'       => time(),
        );
        $mFeedPersonRelation = ClsFactory::Create('Model.Feed.mFeedPersonRelation');
        $id = $mFeedPersonRelation->addFeedPersonRelation($person_relation_datas, true);
        
        return $id ? $id : false;
    }

    /**
     * 保存班级产生的动态关系
     * @param $class_code	班级class_code
     * @param $feed_id	          动态id
     * @param $feed_type    动态类型
     * @param $timeline     时间戳
     * @return $id 成功：班级关系主键id，失败：false
     */
    private function saveFeedToClass($class_code, $feed_id, $feed_type) {
       if(empty($class_code) || empty($feed_id) || empty($feed_type)) {
           return false;
       }
       
       $class_relation_datas = array(
       	   'feed_id'    => $feed_id,
           'class_code' => $class_code,
           'feed_type'  => $feed_type,
           'timeline'   => time(),
       );
       $mFeedClassRelation = ClsFactory::Create('Model.Feed.mFeedClassRelation');
       $id = $mFeedClassRelation->addFeedClassRelation($class_relation_datas, true);
       
       return $id ? $id : false;
    }

    /**
     * 保存动态关系到timeline
     * @param  $uid         动态添加人uid
     * @param  $feed_id     动态id
     * @param  $feed_type   动态类型
     * @param  $timeline    时间戳
     * @return $id   成功：timeline表对应的主键，失败：false
     */
    private function saveFeedToTimeLine($uid, $feed_id, $feed_type) {
       if(empty($uid) || empty($feed_id) || empty($feed_type)) {
           return false;
       }
       
       $timeline_datas = array(
           'feed_id' => $feed_id,
           'feed_type' => $feed_type,
           'client_account' => $uid,
           'timeline' => time(),
       );
       
       $mFeedTimeLine = ClsFactory::Create('Model.Feed.mFeedTimeLine');
       $id = $mFeedTimeLine->addTimeLine($timeline_datas, true);
       
       return $id ? $id : false;
    }    
}