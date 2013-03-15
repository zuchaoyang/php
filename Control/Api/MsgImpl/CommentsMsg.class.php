<?php
class CommentsMsg{
    public function getToAccount($comments_id){
    
        $Comments_M = ClsFactory::Create('Model.Feed.mFeedComments');
        $Comments_list = $Comments_M->getFeedCommentsById($comments_id);
        $to_account = array();
        $feed_ids = array();
        if(!empty($comments_id)) {
            $Feed_M = ClsFactory::Create('Model.Feed.mFeed');
            foreach($Comments_list as $comments_id => $comments_info){
                if($comments_info['level'] == 1){
                    $feed_info = $Feed_M->getFeedById($comments_info['']);
                    $to_account[$feed_info['add_account']] = $feed_info['add_account'];
                }else{
                    $Comments_list = $Comments_M->getFeedCommentsById($comments_info['up_id']);
                    $to_account[$Comments_list['client_account']] = $Comments_list['client_account'];
                }
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}