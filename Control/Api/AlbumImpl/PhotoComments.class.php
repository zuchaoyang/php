<?php

class PhotoComments {
    
    protected $mAlbumPhotoComments = null;
    
    public function __construct() {
        $this->mAlbumPhotoComments = ClsFactory::Create("Model.Album.mAlbumPhotoComments");
    }
    
    /**
     * 通过主键获取评论的相关信息
     * @param $comment_ids
     */
    public function getPhotoCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        $comment_list = $this->mAlbumPhotoComments->getCommentByCommentId($comment_ids);
        
        return $this->parseCommentList($comment_list);
    }
    
    /**
     * 通过相册id获取相册的评论信息
     * @param $photo_id
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getPhotoCommentsByPhotoId($photo_id, $offset = 0, $limit = 10) {
        if(empty($photo_id)) {
            return false;
        }
        
         //todolist：M层和D层得代码不规范
        $comment_arr = $this->mAlbumPhotoComments->getAlbumPhotoCommentByPhotoId($photo_id, 1, $offset, $limit);
        
        return $this->parseCommentList($comment_arr);
    }
    
    /**
     * 添加日志的评论信息
     * @param $comment_datas
     */
    public function addPhotoComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        $comment_id = $this->mAlbumPhotoComments->addAlbumPhotoComment($comment_datas, true);
        
        if(empty($comment_id)) {
            return false;
        }
        
        //更新日志对应的评论数
        $this->updatePhotoCommentCountByPhotoId($comment_datas['photo_id']);
        
        return $comment_id;
    }
    
    /**
     * 删除日志的评论信息
     * @param $comment_id
     */
    public function delPhotoComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        $comment_list = $this->mAlbumPhotoComments->getCommentByCommentId($comment_id);
        $comment_info = & $comment_list[$comment_id];
        
        //评论信息不存在或者删除评论信息本身失败
        if(empty($comment_info) || !$this->mAlbumPhotoComments->delCommentByCommentId($comment_id)) {
            return false;
        }
        
        //如果是一级评论，删除评论的二级评论信息
        if($comment_info['level'] == 1) {
            $child_comment_list = $this->mAlbumPhotoComments->getAlbumPhotoCommentByUpId($comment_id, 2);
            //由于一级评论被删除，二级评论无法找到，因此尝试删除时都认为二级的删除是成功的
            if(!empty($child_comment_list)) {
                foreach($child_comment_list as $child_comment_id=>$child_comment) {
                    $this->mAlbumPhotoComments->delCommentByCommentId($child_comment_id);
                }
            }
        }
        
        //修改日志的评论数
        $this->updatePhotoCommentCountByPhotoId($comment_info['photo_id']);
        
        return true;
    }
    
    /**
     * 解析照片的评论列表
     * @param $comment_list
     */
    private function parseCommentList($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $comment_list = $this->appendChildComments($comment_list);
        $comment_list = $this->formatComments($comment_list);
        
        return $comment_list;
    }
    
    /**
     * 获取评论的孩子节点
     * 注明：暂时不考虑2级评论过多的情况
     * @param $comment_list
     */
    private function appendChildComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        $each_limit = 5;
        $up_ids = array_keys($comment_list);

        $mAlbumPhotoComments = ClsFactory::Create('Model.Album.mAlbumPhotoComments');

        $stat_list = $mAlbumPhotoComments->getAlbumPhotoCommentsChildrenStatByUpid($up_ids);
        
        $child_comment_arr = $mAlbumPhotoComments->getAlbumPhotoCommentsChildrenByUpid($up_ids, $each_limit);
        
        foreach($comment_list as $comment_id=>$comment) {
            if(isset($child_comment_arr[$comment_id])) {
                $comment['child_items'] = (array)$child_comment_arr[$comment_id];
                $remain_nums = $stat_list[$comment_id] - $each_limit;
                if($remain_nums > 0) {
                    $comment['remain_nums'] = $remain_nums;
                }
            } else {
                $comment['child_items'] = array();
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
    /**
     * 格式化评论信息
     * 1. 添加评论的用户信息
     * 2. 格式化评论的时间
     * 3. 解析评论的表情信息
     * @param  $comment_list
     */
    private function formatComments($comment_list) {
        if(empty($comment_list)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        import('@.Common_wmw.Date');
        
        //获取评论相关的用户信息
        $uids = array();
        foreach($comment_list as $comment_id => $comment) {
            $uids[$comment['client_account']] = $comment['client_account'];
            if(isset($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    $uids[$child_comment['client_account']] = $child_comment['client_account'];
                }
            }
        }
        $mUser = ClsFactory::Create("Model.mUser");
        $user_list = $mUser->getUserBaseByUid($uids);
        if(!empty($user_list)) {
            foreach($user_list as $uid=>$user) {
                $user_list[$uid] = array(
                    'client_name'        => $user['client_name'],
                    'client_headimg_url' => $user['client_headimg_url'],
                );
            }
        }
        
        foreach($comment_list as $comment_id=>$comment) {
            $uid = $comment['client_account'];
            if(isset($user_list[$uid])) {
                $comment['user_info'] = $user_list[$uid];
                $comment = array_merge($comment, (array)$user_list[$uid]);
            }
            
            //转换时间格式和解析表情信息
            $comment['add_time_format'] = Date::timestamp($comment['add_time']);
            $comment['content'] = WmwFace::parseFace($comment['content']);
            
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    $child_uid = $child_comment['client_account'];
                    if(isset($user_list[$child_uid])) {
                        $child_comment['user_info'] = $user_list[$child_uid];
                        $child_comment = array_merge($child_comment, (array)$user_list[$child_uid]);
                    }
                    $child_comment['add_time_format'] = Date::timestamp($child_comment['add_time']);
                    $child_comment['content'] = WmwFace::parseFace($child_comment['content']);
                    
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
   /**
    * 更新照片的评论数
    * @param $photo_id
    */
    private function updatePhotoCommentCountByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        
        //获取相片评论数
        $comment_count = $this->mAlbumPhotoComments->getCountByPhotoId($photo_id);
        //更新相片评论数
        $data_arr = array(
            'comments' => $comment_count
        );
        $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        
        return $mAlbumPhotos->modifyPhotoByPhotoId($data_arr, $photo_id);
    }
    
}