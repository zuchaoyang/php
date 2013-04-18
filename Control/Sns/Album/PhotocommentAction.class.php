<?php
class PhotocommentAction extends SnsController {
    protected $PhotoComments;
    public function _initialize() {
        parent::_initialize();
        import('@.Control.Api.AlbumImpl.PhotoComments');
        $this->PhotoComments = new PhotoComments();
    }
	/**
     * 添加评论
     *
     */
    public function addPhotoComment() {
        $client_account = $this->objInput->postInt('add_uid');
        $content = $this->objInput->postStr("content");
        $photo_id = $this->objInput->postInt("photo_id");
        $up_id = $this->objInput->postInt("up_id");
        $album_id = $this->objInput->postInt("album_id");
        $class_code = $this->objInput->postInt("class_code");
        if(empty($client_account) || empty($content) || empty($photo_id) || empty($album_id)) {
            $this->ajaxReturn(null, '评论失败', -1, 'json');
        }
        import('@.Control.Api.AlbumImpl.PhotoInfo');
        $PhotoInfo = new PhotoInfo();
        $photo_info = $PhotoInfo->getPhotoByPhotoId($photo_id);
        if(empty($photo_info) || $photo_info[$photo_id]['album_id'] != $album_id) {
            $this->ajaxReturn(null, '评论失败', -1, 'json');
        }
        $level = 2;
        if($photo_id == $up_id){
            $level = 1;
        }
        $add_time = time();
        $data_arr = array(
            "up_id"=>$up_id,
            "photo_id"=>$photo_id,
            "content"=>$content,
            "client_account"=>$this->user['client_account'],
            "add_time"=>$add_time,
            "level"=>$level
        );
        
        $comment_id = $this->PhotoComments->addPhotoComments($data_arr);
        
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '评论失败', -1, 'json');
        }
        import("@.Control.Api.FeedApi");
        $feed_api = new FeedApi();
         if(!empty($class_code)) {
            $feed_api->class_create($class_code, $this->user['client_account'], $photo_id, FEED_ALBUM, FEED_ACTION_COMMENT);
        }else{
            $feed_api->user_create($this->user['client_account'], $photo_id, FEED_ALBUM, FEED_ACTION_COMMENT);
        }
        import('@.Common_wmw.WmwFace');
        $data_arr['content'] = WmwFace::parseFace($data_arr['content']);
        $data_arr['comment_id']=$comment_id;
        $data_arr['add_date']=date('y-m-d H:i:s', $add_time);
        $this->ajaxReturn($data_arr, '评论成功', 1, 'json');
    }
    
    /**
     * 删除评论
     */
    public function delPhotoComment() {
        $comment_id = $this->objInput->getInt('comment_id');
        if(empty($comment_id)) {
            $this->ajaxReturn($comment_id, '操作失败', -1, 'json');
        }
        $rs = $this->PhotoComments->delPhotoComments($comment_id);
        if(empty($rs)) {
            $this->ajaxReturn($rs, '操作失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
    }
    
}
