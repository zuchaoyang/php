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
    
    
	/**
     * 分组统计
     * @param $up_ids
     */
    public function getAlbumPhotoCommentsChildrenStatByUpid($up_ids) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dAlbumPhotoComments->getTableName();
        
         //统计孩子节点的个数
        $stat_sql = "select up_id, count(*) as nums from $table_name where up_id in('" . implode("','", (array)$up_ids) . "') group by up_id";
        $stat_list = $this->_dAlbumPhotoComments->query($stat_sql);
        
        $new_stat_list = array();
        if(!empty($stat_list)) {
            foreach($stat_list as $stat) {
                $new_stat_list[$stat['up_id']] = $stat['nums'];
            }
        }
        
        return !empty($new_stat_list) ? $new_stat_list : false;
    }
    
    /**
     * 通过上级id获取对应的最新的孩子结点数
     * @param $up_ids
     * @param $each_limit
     */
    public function getAlbumPhotoCommentsChildrenByUpid($up_ids, $each_limit = 5) {
        if(empty($up_ids)) {
            return false;
        }
        
        $table_name = $this->_dAlbumPhotoComments->getTableName();
        
        $select_children_sql = "select * from (select * from $table_name a where a.up_id in('" . implode("','", (array)$up_ids) . "')) as b where " .
        	   				   "$each_limit>(select count(*) from $table_name c where c.up_id=b.up_id and c.comment_id > b.comment_id)";
        $comment_list = $this->_dAlbumPhotoComments->query($select_children_sql);
        
        $new_comment_list = array();
        if(!empty($comment_list)) {
            foreach($comment_list as $comment_id => $comment) {
                $new_comment_list[$comment['up_id']][$comment['comment_id']] = $comment;
                unset($comment_list[$comment_id]);
            }
        }
        
        return !empty($new_comment_list) ? $new_comment_list : false;
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