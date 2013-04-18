<?php

Class PhotocommentsAction extends SnsController {
    
    /**
     * 获取照片的评论信息列表
     */
    public function getPhotoCommentsForFeedAjax() {
        $photo_id = $this->objInput->getInt('photo_id');
        
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '照片信息不存在!', -1, 'json');
        }
        
        import('@.Control.Api.AlbumApi');
        $AlbumApi = new AlbumApi();
        
        $comment_list = $AlbumApi->getPhotoCommentsByPhotoId($photo_id, 0, 10);
        $comment_list = $this->appendCommentsAccess($comment_list);
        
        $this->ajaxReturn($comment_list, '获取成功!', 1, 'json');
    }
    
    /**
     * 发表照片的评论信息
     */
    public function publishPhotoCommentsAjax() {
        
        $content  = $this->objInput->postStr('content');
        $up_id    = $this->objInput->postInt('up_id');
        $photo_id = $this->objInput->postInt('photo_id');
        
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '照片信息不存在!', -1, 'json');
        }
        
        if(empty($content)) {
            $this->ajaxReturn(null, '评论内容不能为空!', -1, 'json');
        }
        
        import('@.Common_wmw.WmwString');
        if(WmwString::mbstrlen($content, 1) > 140) {
            $this->ajaxReturn(null, '评论内容不能超过140字!', -1, 'json');
        }
        
        //检测照片的实体是否存在
        $mAlbumPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        $photo_list = $mAlbumPhotos->getPhotosByPhotoId($photo_id);
        $photo_info = & $photo_list[$photo_id];
        if(empty($photo_info)) {
            $this->ajaxReturn(null, '照片信息不存在或已删除!', -1, 'json');
        }
        
        $comment_datas = array(
            'up_id' => $up_id,
            'photo_id' => $photo_id,
            'content' => $content,
            'client_account' => $this->user['client_account'],
            'add_time' => time(),
            'level' => !empty($up_id) ? 2 : 1
        );        
        
        import('@.Control.Api.AlbumApi');
        $AlbumApi = new AlbumApi();
        
        $comment_id = $AlbumApi->addPhotoComments($comment_datas);
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '评论信息添加失败!', -1, 'json');
        }
        
        $comment_list = $AlbumApi->getPhotoCommentsById($comment_id);
        
        $comment_list = $this->appendCommentsAccess($comment_list);
        
        $comment_info = & $comment_list[$comment_id];
        
        $this->ajaxReturn($comment_info, '评论添加成功!', 1, 'json');
    }
    
    /**
     * 删除照片评论信息
     */
    public function deletePhotoCommentsAjax() {
        $comment_id = $this->objInput->getInt('comment_id');
        
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '评论信息不存在!', -1, 'json');
        }
        
        //判断用户是否有权限删除评论信息
        $mAlbumPhotoComments = ClsFactory::Create('Model.Album.mAlbumPhotoComments');
        $comment_list = $mAlbumPhotoComments->getCommentByCommentId($comment_id);
        $comment_info = & $comment_list[$comment_id];
        if(empty($comment_info)) {
            $this->ajaxReturn(null, '评论信息不存在!', -1, 'json');
        }
        
        if($comment_info['client_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该评论信息!', -1, 'json');
        }
        
        import('@.Control.Api.AlbumApi');
        $AlbumApi = new AlbumApi();
        if(!$AlbumApi->delPhotoComments($comment_id)) {
            $this->ajaxReturn(null, '评论删除失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '评论删除成功!', 1, 'json');
    }
    
    /**
     * 追加评论的删除权限
     * @param $comment_list
     */
    private function appendCommentsAccess($comment_list) {
        if(empty($comment_list)) {
            return false;
        } else if(!is_array($comment_list)) {
            return $comment_list;
        }
        
        foreach($comment_list as $comment_id=>$comment) {
            if($comment['client_account'] == $this->user['client_account']) {
                $comment['can_del'] = true;
            }
            if(!empty($comment['child_items'])) {
                foreach($comment['child_items'] as $child_comment_id=>$child_comment) {
                    if($child_comment['client_account'] == $this->user['client_account']) {
                        $child_comment['can_del'] = true;
                    }
                    $comment['child_items'][$child_comment_id] = $child_comment;
                }
            }
            $comment_list[$comment_id] = $comment;
        }
        
        return $comment_list;
    }
    
    
    
    
    /**
     * author sailong
     * 获取相片评论
     */
    public function getCommentsList() {
        $photo_id = $this->objInput->getInt('photo_id');
        $page = $this->objInput->getInt('page');
        if(empty($photo_id)) {
            $this->ajaxReturn(null, '获取评论失败!', -1, 'json');
        }
        $limit = 10;
        $offset = null;
        $page = max(1,$page);
        $offset = ($page-1)*$limit;
        
        import('@.Control.Api.AlbumApi');
        $AlbumApi = new AlbumApi();
        
        $comment_list = $AlbumApi->getPhotoCommentsByPhotoId($photo_id, $offset, $limit);
        $comment_list = $this->appendCommentsAccess($comment_list);
        if(empty($comment_list)) {
            $this->ajaxReturn(null, '获取评论失败!', -1, 'json');
        }
        $this->ajaxReturn($comment_list, '获取成功!', 1, 'json');
    }
    
}