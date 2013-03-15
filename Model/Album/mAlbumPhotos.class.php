<?php
class mAlbumPhotos extends mBase {
    protected $_dAlbumPhotos = null;

    public function __construct() {
        $this->_dAlbumPhotos = ClsFactory::Create('Data.Album.dAlbumPhotos');
    }
    
	/**
     * 通过相册获取图片信息
     * @param $album_ids
     * @param $offset
     * @param $limit
     */
    public function getPhotosByAlbumId($album_ids, $offset = 0, $limit = 10){
        if(empty($album_ids)) {
            return false;
        }
        
        return $this->_dAlbumPhotos->getPhotosByAlbumId($album_ids, $offset, $limit);
    }
    
    //添加照片
    public function addPhoto($data, $is_return_id){
        return $this->_dAlbumPhotos->addPhoto($data, $is_return_id);
    }
    
    //修改照片
    public function modifyPhotoByPhotoId($data, $photo_id){
        return $this->_dAlbumPhotos->modifyPhotoByPhotoId($data, $photo_id);
    }
    
    //删除照片
    public function delPhotosByPhotoId($photo_id){
        return $this->_dAlbumPhotos->delPhotosByPhotoId($photo_id);
    }
    
    //获得照片信息
    public function getPhotosByPhotoId($photo_ids){
        return $this->_dAlbumPhotos->getPhotosByPhotoId($photo_ids);
    }
    
    public function getByAlbumId($album_id, $offset=null, $limit=null){
        return $this->_dAlbumPhotos->getByAlbumId($album_id, $offset, $limit);
    }
    
    //根据相册ID获取相片的数量
    public function getCountByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        /*$this->_dAlbumPhotos->getCountByAlbumId($album_id);
        echo $this->_dAlbumPhotos->getLastSql();die;*/
        return $this->_dAlbumPhotos->getCountByAlbumId($album_id);
        
    }
    
     //根据相册ID获取修改相片的评论数
    public function updCommentCountByPhotoId($count, $photo_id) {
        if(empty($photo_id) || empty($count)) {
            return false;
        }
        
        return $this->_dAlbumPhotos->updCommentCountByPhotoId($count, $photo_id);
        
    }
    
    //通过相册album_id删除相关相册信息
    public function delByAlbumId($album_id) {
        if(empty($album_id)){
            return false;
        }
        
        return $this->_dAlbumPhotos->delByAlbumId($album_id);
    }
    
    
}