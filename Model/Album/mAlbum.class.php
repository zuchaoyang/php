<?php
class mAlbum extends mBase {
    protected $_dAlbum = null;
    
    public function __construct() {
        $this->_dAlbum = ClsFactory::Create('Data.Album.dAlbum');
    }
    //创建相册
    public function addAlbum($data,$is_return_id) {
        return $this->_dAlbum->addAlbum($data,$is_return_id);
    }
    //修改相册
    public function modifyAlbumByAlbumId($data,$album_id) {
        return $this->_dAlbum->modifyAlbumByAlbumId($data,$album_id);
    }
    
    //删除相册
    public function delAlbumByAlbumId($album_id) {
        return $this->_dAlbum->delAlbumByAlbumId($album_id);
    }
    
    //获取相册信息
    public function getAlbumByAlbumId($album_ids) {
        return $this->_dAlbum->getAlbumByAlbumId($album_ids);
    }
    //根据相册ID获取修改相片的评论数
    public function updPhotoCountByAlbumId($count, $album_id) {
        if(empty($album_id) || empty($count)) {
            return false;
        }
        
        return $this->_dAlbum->updPhotoCountByAlbumId($count, $album_id);
        
    }
}