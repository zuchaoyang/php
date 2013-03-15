<?php
class Dispatch {
    
    public function dispatchFeed() {
        $queue_datas = $this->getTaskFromRedisQueue();
        
        if(empty($queue_datas)) {
             FEED_DEBUG && trigger_error('异步队列中提取数据失败', E_USER_ERROR);
        }
        
        if($queue_datas['context'] == FEED_CONTEXT_PERSON) {
            //初始化用户的信息
            $uid = $queue_datas['uid'];
            $feed_id = $queue_datas['feed_id'];
            $feed_type = $queue_datas['feed_type'];
            $add_time = $queue_datas['add_time'];
            
            import('@.Control.Api.FeedImpl.Dispatch.UserFeedDispatch');
            $userFeedDispatch = new UserFeedDispatch();
            $userFeedDispatch->dispatch($uid, $add_time, $feed_id, $feed_type);
            
        } else if($queue_datas['context'] == FEED_CONTEXT_CLASS) {
            
            $uid = $queue_datas['uid'];
            $class_code = $queue_datas['class_code'];
            $feed_id = $queue_datas['feed_id'];
            $feed_type = $queue_datas['feed_type'];
            
            import('@.Control.Api.FeedImpl.Dispatch.ClassFeedDispatch');
            $classFeedDispatch = new ClassFeedDispatch();
            $classFeedDispatch->dispatch($uid, $class_code, $add_time, $feed_id, $feed_type);
        }
    }
    
    /**
     * 从队列里获取数据
     */
    private function getTaskFromRedisQueue() {
        
        $mFeedAsyncTaskQueue = ClsFactory::Create('RModel.Feed.mFeedAsyncTaskQueue');
        
        return $mFeedAsyncTaskQueue->getAsyncTask();
    }
}