<?php
class PackFeed {
    /**
     * 获取对应的动态内容
     * @param $entity_datas
     * @param $feed_type
     */
    public static function getFeedDatas($from_id, $feed_type) {
        if(empty($from_id) || empty($feed_type)) {
            return false;
        }
        
        if($feed_type == FEED_ALBUM) {
            import('@.Control.Api.FeedImpl.PackFeed.PackAlbum');
            $PackAlbumObject = new PackAlbum();
            
            return $PackAlbumObject->getFeedDatas($from_id);
        } else if($feed_type == FEED_BLOG) {
            import('@.Control.Api.FeedImpl.PackFeed.PackBlog');
            $PackBlogObject = new PackBlog();
            
            return $PackBlogObject->getFeedDatas($from_id);
        } else if($feed_type == FEED_MOOD) {
            import('@.Control.Api.FeedImpl.PackFeed.PackMood');
            $PackMoodObject = new PackMood();
            
            return $PackMoodObject->getFeedDatas($from_id);
        }
        
        return false;
    }
    
}