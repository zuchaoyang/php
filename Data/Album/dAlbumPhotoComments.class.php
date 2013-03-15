<?php
class dAlbumPhotoComments extends dBase {
    protected $_pk = 'comment_id';
    protected $_tablename = 'wmw_album_photo_comments';
    protected $_fields = array(
                    'comment_id',
                    'up_id',
                    'photo_id',
                    'content',
                    'client_account',
                    'add_time',
                    'level',
                );
                
    protected $_index_list = array(
    				'comment_id',
                    'photo_id',
    				'up_id',
                );
                
    public function addAlbumPhotoComment($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
   
    public function modifyAlbumPhotoCommentByCommentId($data, $comment_id) {
        return $this->modify($data, $comment_id);
    }
   
    public function delCommentByCommentId($comment_id) {
        return $this->delete($comment_id);
    }
    
    public function getCommentByCommentId($comment_id) {
        return $this->getInfoByPk($comment_id);
    }
    
    //通过相册ID获取评论信息
    public function getAlbumPhotoCommentByPhotoId($photo_id, $level, $offset=0, $limit=10) {
        $orderby = ' comment_id desc';
        $wherearr[] = "photo_id={$photo_id}";
        $wherearr[] = "level={$level}";
        return $this->getInfo($wherearr, $orderby, $offset, $limit);
    }
    
    
    //通过up_id获取评论信息
    public function getAlbumPhotoCommentByUpId($up_id, $level, $offset=0, $limit=10) {
        
        $orderby = ' comment_id desc';
        $wherearr[] = "up_id in ('".implode("','",(array)$up_id)."')";
        $wherearr[] = "level={$level}";
        return $this->getInfo($wherearr, $orderby, $offset, $limit);
    }
    
    //通过相片photo_id删除评论信息
    public function delByPhotoId($photo_ids) {
        if(empty($photo_ids)) {
            return false;
        }
        
        $photo_ids = implode(',', (array)$photo_ids);
        $sql = "delete from {$this->_tablename} where photo_id in({$photo_ids})";
        
        return $this->execute($sql);
    }
    
    
    //通过相片删除二级评论信息
    public function delByUpId($up_id) {
        if(empty($up_id)) {
            return false;
        }
        
        $up_id = implode(',', (array)$up_id);
        $sql = "delete from {$this->_tablename} where up_id in({$up_id})";
        
        return $this->execute($sql);
    }
    
    //根据相片ID获取评论的数量
    public function getCountByPhotoId($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        $wherearr[] = "photo_id={$photo_id}";
        
        return $this->getCount($wherearr);
        
    }
}