<?php

//是否追加动态对应实体的基本信息
define('APPEND_ENTITY_INFO', false);

class FeedParser {
    /**
     * 解析动态的基本信息
     * @param $feed_list
     */
    public function parseFeed($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        $feed_list = $this->formatFeed($feed_list);
        $feed_list = $this->appendFeedUserInfo($feed_list);
        $feed_list = $this->appendFeedEntity($feed_list);
        $feed_list = $this->appendFeedEntityUrl($feed_list);
        
        return $feed_list;
    }
    
   /**
     * 解析动态信息包括表情的转换以及时间的格式化
     * @param $feed_list
     */
    private function formatFeed($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        import('@.Common_wmw.Date');
        
        foreach($feed_list as $feed_id=>$feed) {
            $feed['add_time_format'] = Date::timestamp($feed['timeline']);
            
            $content = $feed['feed_content'];
            if (!empty($content)) {
                $content = str_replace('#分享照片#', ' ', $content);
            }
            $feed['feed_content'] = $content;
            $feed['feed_content'] = WmwFace::parseFace($feed['feed_content']);
            
            $img_url = $feed['img_url'];
            if (!empty($img_url)) {
                $path_parts = pathinfo($img_url);
                $src_path = $path_parts['dirname'];
                $filename = $path_parts['basename'];
                $big_filename = str_replace('_m', '', $filename);
                $feed['big_img_url'] = $src_path . '/' . $big_filename;
            }
            
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    /**
     * 添加动态的用户信息
     * @param $feed_list
     */
    private function appendFeedUserInfo($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        $uids = array();
        foreach($feed_list as $feed) {
            $uids[$feed['add_account']] = $feed['add_account'];
        }
        
        //获取用户的基本信息
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getClientAccountById($uids);
        foreach((array)$user_list as $uid=>$user) {
            $user_list[$uid] = array(
                'client_name'        => $user['client_name'],
                'client_headimg_url' => $user['client_headimg_url'],
            );
        }
        
        foreach($feed_list as $feed_id=>$feed) {
            $add_account = $feed['add_account'];
            if(isset($user_list[$add_account])) {
                $feed['user_info'] = $user_list[$add_account];
                $feed = array_merge($feed, (array)$user_list[$add_account]);
            }
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    /**
     * 获取动态对应实体的相关信息
     * @param $feed_list
     */
    private function appendFeedEntity($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        //获取实体对应的id信息
        $feed_entity_ids = array();
        foreach($feed_list as $feed_id => $feed) {
            $feed_entity_ids[$feed['feed_type']][] = $feed['from_id'];
        }
        
        $entity_arr = array();
        //获取说说相关的信息
        if(!empty($feed_entity_ids[FEED_MOOD])) {
            $mMood = ClsFactory::Create('Model.Mood.mMood');
            $mood_list = $mMood->getMoodById($feed_entity_ids[FEED_MOOD]);
            
            $entity_arr[FEED_MOOD] = & $mood_list;
        }
        
        //获取日志的实体信息
        if(!empty($feed_entity_ids[FEED_BLOG])) {
            $mBlog = ClsFactory::Create('Model.Blog.mBlog');
            $blog_list = $mBlog->getBlogById($feed_entity_ids[FEED_BLOG]);
            
            $entity_arr[FEED_BLOG] = & $blog_list;
        }
        
        //获取照片的实体信息
        if(!empty($feed_entity_ids[FEED_ALBUM])) {
            $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
            $photo_list = $mAlbumPhotos->getPhotosByPhotoId($feed_entity_ids[FEED_ALBUM]);
            
            $entity_arr[FEED_ALBUM] = & $photo_list;
        }
        
        //将实体的信息追加到动态中去
        foreach($feed_list as $feed_id => $feed) {
            $feed_type = $feed['feed_type'];
            $from_id = $feed['from_id'];
            
            if(APPEND_ENTITY_INFO) {            
                if(isset($entity_arr[$feed_type][$from_id])) {
                    $feed['entity_datas'] = $entity_arr[$feed_type][$from_id];
                } else {
                    $feed['entity_datas'] = array();
                }
            }
            
            //获取对应实体的评论次数
            $feed['comments'] = intval($entity_arr[$feed_type][$from_id]['comments']);
            
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    /**
     * 追加实体对应的url
     * @param $feed_list
     */
    private function appendFeedEntityUrl($feed_list) {
        if(empty($feed_list)) {
            return false;
        }
        
        foreach($feed_list as $feed_id=>$feed) {
            if(!empty($feed['from_class_code'])) {
                $src_url = $this->getClassFeedEntityUrl($feed['from_class_code'], $feed['feed_type'], $feed['from_id']);
            } else {
                $src_url = $this->getPersonFeedEntityUrl($feed['add_account'], $feed['feed_type'], $feed['from_id']);
            }
            $feed['src_url'] = $src_url;
            $feed_list[$feed_id] = $feed;
        }
        
        return $feed_list;
    }
    
    /**
     * 获取班级的动态实体的url
     * @param $from_class_code
     * @param $feed_type
     * @param $from_id
     */
    private function getClassFeedEntityUrl($from_class_code, $feed_type, $from_id) {
        if(empty($from_class_code) || empty($feed_type) || empty($from_id)) {
            return "";
        }
        
        if($feed_type == FEED_MOOD) {
            $url = "/Sns/Mood/ClassMood/show/class_code/$from_class_code/mood_id/$from_id";
        } else if($feed_type == FEED_BLOG) {
            $url = "/Sns/Blog/Content/index/class_code/$from_class_code/blog_id/$from_id";
        } else if($feed_type == FEED_ALBUM) {
            $url = "/Sns/Album/Classphoto/photo/class_code/$from_class_code/photo_id/$from_id";
        }
        
        return $url;
    }
    
    /**
     * 获取个人动态实体对应的url
     * @param  $uid
     * @param  $feed_type
     * @param  $from_id
     */
    private function getPersonFeedEntityUrl($uid, $feed_type, $from_id) {
        if(empty($uid) || empty($feed_type) || empty($from_id)) {
            return false;
        }
        
        if($feed_type == FEED_MOOD) {
            $url = "/Sns/Mood/PersonMood/show/client_account/$uid/mood_id/$from_id";
        } else if($feed_type == FEED_BLOG) {
            $url = "/Sns/Blog/PersonContent/index/client_account/$uid/blog_id/$from_id";
        } else if($feed_type == FEED_ALBUM) {
            $url = "/Sns/Album/Personphoto/photo/client_account/$uid/photo_id/$from_id";
        }
        
        return $url;
    }
    
}