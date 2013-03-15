<?php
class mAlbumPhotoComments extends mBase {
    protected $_dAlbumPhotoComments = null;

    public function __construct() {
        $this->_dAlbumPhotoComments = ClsFactory::Create('Data.Album.dAlbumPhotoComments');
    }
    
    
    public function getCommentByCommentId($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        return $this->_dAlbumPhotoComments->getCommentByCommentId($comment_id);
    }
    
    public function addAlbumPhotoComment($data, $is_return_id) {
        return $this->_dAlbumPhotoComments->addAlbumPhotoComment($data, $is_return_id);
    }
    
    public function modifyAlbumPhotoCommentByCommentId($data, $comment_id) {
        return $this->_dAlbumPhotoComments->modifyAlbumPhotoCommentByCommentId($data, $comment_id);
    }
    
    public function delCommentByCommentId($comment_id) {
        return $this->_dAlbumPhotoComments->delCommentByCommentId($comment_id);
    }
    
    /*public function getAlbumPhotoCommentByPhotoId($photo_id,$offset=null,$limit=null) {
        return $this->_dAlbumPhotoComments->getAlbumPhotoCommentByPhotoId($photo_id,$offset,$limit);
    }*/
    //通过相片删除二级评论信息
    public function delByUpId($up_id) {
        if(empty($up_id)) {
            return false;
        }
        
        return $this->_dAlbumPhotoComments->delByUpId($up_id);
    }
    //根据相片ID和评论级数获取评论信息
    public function getAlbumPhotoCommentByPhotoId($photo_id,$level,$offset=null,$limit=null) {
        if(empty($photo_id)) {
            return false;
        }
        if(empty($level)) {
            $level = 1;
        }
        return $this->_dAlbumPhotoComments->getAlbumPhotoCommentByPhotoId($photo_id,$level,$offset,$limit);
    }
    //根据up_id和评论级数获取评论信息
    public function getAlbumPhotoCommentByUpId($up_id,$level,$offset=null,$limit=null) {
        if(empty($up_id)) {
            return false;
        }
        if(empty($level)) {
            $level = 1;
        }
        return $this->_dAlbumPhotoComments->getAlbumPhotoCommentByUpId($up_id,$level,$offset,$limit);
    }
    public function delByPhotoId($photo_ids) {
        return $this->_dAlbumPhotoComments->delByPhotoId($photo_ids);
    }
    
    //根据相片ID获取评论的数量
    public function getCountByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        /*$this->_dAlbumPhotos->getCountByAlbumId($album_id);
        echo $this->_dAlbumPhotos->getLastSql();die;*/
        return $this->_dAlbumPhotoComments->getCountByPhotoId($photo_id);
        
    }
}