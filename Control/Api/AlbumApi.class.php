<?php
/**
 * author:sailong<shailong123@126.com>
 * 功能：Album manage
 * 说明：作为相册或照片操作的统一接口
 * 
 * @return json
 */


class AlbumApi extends ApiController {
    
    protected $_ClassAlbum = null;
    protected $_PersonAlbum = null;
    /**
     * 
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
        $this->_ClassAlbum = ClsFactory::Create('Control.Api.AlbumImpl.ClassAlbum');
        $this->_PersonAlbum = ClsFactory::Create('Control.Api.AlbumImpl.PersonAlbum');
    }    
   
    /**
     * 
     * 固定函数
     */    
    public function _initialize(){
		parent::_initialize();        
    }
    
    //班级相册接口start
    /**
     * 添加班级相册 
     * 
     * @return album_id
     */
    public function addClassAlbum($album_data) {
        if(empty($album_data)) {
            return false;
        }
        
        return $this->_ClassAlbum->addClassAlbum($album_data);
    }
    
    /**
     * 根据相册album_id获取相册信息
     * 
     * @return album_info
     */
    public function getClassAlbumByAlbumId($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
       
        return $this->_ClassAlbum->getClassAlbumByAlbumId($album_id, $class_code);
    }
    
    /**
     * 根据班级class_code获取班级相册列表
     * 
     * @return album_list
     */
    public function getClassAlbumListByClassCode($class_code, $offset = null, $limit = null) {
        if(empty($class_code)) {
            return false;
        }
        
        return $this->_ClassAlbum->getClassAlbumListByClassCode($class_code, $offset, $limit);
    }
    
    /**
     * 根据相册album_id修改相册信息
     * 
     * @return boolean
     */
    public function updClassAlbum($album_data, $album_id, $class_code) {
        if(empty($album_data) || empty($album_id) || empty($class_code)) {
            return false;
        }
        
        return $this->_ClassAlbum->updClassAlbum($album_data, $album_id, $class_code);
    }
    
    /**
     * 删除相册
     * 
     * @return boolean
     */
    public function delClassAlbum($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
        
        return $this->_ClassAlbum->delClassAlbum($album_id, $class_code);
    }
    
    /**
     * 班级相册权限列表
     */
    public function getClassGrantList() {
        return $this->_ClassAlbum->grant_arr();
    }
    //个人相册接口start
    /**
     * 添加个人相册 
     * 
     * @return album_id
     */
    public function addPersonAlbum($album_data) {
        if(empty($album_data)) {
            return false;
        }
        
        return $this->_PersonAlbum->addPersonAlbum($album_data);
    }
    
    /**
     * 根据相册album_id获取相册信息
     * 
     * @return album_info
     */
    public function getPersonAlbumByAlbumId($album_id, $client_account) {
        if(empty($album_id) || empty($client_account)) {
            return false;
        }
        
        
        return $this->_PersonAlbum->getPersonAlbumByAlbumId($album_id, $client_account);
    }
    
    /**
     * 根据班级class_code获取班级相册列表
     * 
     * @return album_list
     */
    public function getPersonAlbumListByUid($client_account, $offset = null, $limit = null) {
        if(empty($client_account)) {
            return false;
        }
        
        
        return $this->_PersonAlbum->getPersonAlbumListByUid($client_account, $offset, $limit);
    }
    
    /**
     * 根据相册album_id修改相册信息
     * 
     * @return boolean
     */
    public function updPersonAlbum($album_data, $album_id, $client_account) {
        if(empty($album_data) || empty($album_id) || empty($client_account)) {
            return false;
        }
        
        return $this->_PersonAlbum->updPersonAlbum($album_data, $album_id, $client_account);
    }
    
    /**
     * 删除相册
     * 
     * @return boolean
     */
    public function delPersonAlbum($album_id, $client_account) {
        if(empty($album_id) || empty($client_account)) {
            return false;
        }
        
        return $this->_PersonAlbum->delPersonAlbum($album_id, $client_account);
    }
    /**
     * 个人相册权限列表
     */
    public function getPersonGrantList() {
        return $this->_PersonAlbum->grant_arr();
    }
    
 
    /***************************************************************************
     * 相片评论部分的api函数的实现
     ***************************************************************************/
    /**
     * 通过主键获取评论的相关信息
     * @param $comment_ids
     */
    public function getPhotoCommentsById($comment_ids) {
        if(empty($comment_ids)) {
            return false;
        }
        
        import('@.Control.Api.AlbumImpl.PhotoComments');
        $PhotoComments = new PhotoComments();
        
        return $PhotoComments->getPhotoCommentsById($comment_ids);        
    }
    /**
     * 通过日志id获取日志的评论信息
     * @param $blog_id
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getPhotoCommentsByPhotoId($photo_id, $offset = 0, $limit = 10) {
        if(empty($photo_id)) {
            return false;
        }
        
        import('@.Control.Api.AlbumImpl.PhotoComments');
        $PhotoComments = new PhotoComments();
        
        return $PhotoComments->getPhotoCommentsByPhotoId($photo_id, $offset, $limit);
    }
    
    /**
     * 添加日志的评论信息
     * @param $comment_datas
     */
    public function addPhotoComments($comment_datas) {
        if(empty($comment_datas) || !is_array($comment_datas)) {
            return false;
        }
        
        import('@.Control.Api.AlbumImpl.PhotoComments');
        $PhotoComments = new PhotoComments();
        
        return $PhotoComments->addPhotoComments($comment_datas);
    }
    
    /**
     * 删除日志的评论信息
     * @param $comment_id
     */
    public function delPhotoComments($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        
        import('@.Control.Api.AlbumImpl.PhotoComments');
        $PhotoComments = new PhotoComments();
        
        return $PhotoComments->delPhotoComments($comment_id);
    }
   
}
