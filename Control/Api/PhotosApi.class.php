<?php
class PhotosApi extends ApiController{
    public function __construct() {
        parent::__construct();
    }    
    
    public function _initialize() {
        parent::_initialize();
    }
    
    public function getByAlbumId($album_id, $offset=null, $limit=null) {
        if(empty($album_id)) {
            return false;
        }
        $mAlubm = ClsFactory::Create('Model.Album.mAlbum');
        $rs = $mAlubm->getAlbumByAlbumId($album_id);
        if(empty($rs)) {
            return false;
        }
        
        $mAlubmPhotos = ClsFactory::Create('Model.Album.mAlbumPhotos');
        $photo_list = $mAlubmPhotos->getByAlbumId($album_id, $offset, $limit);
        $photo_list = $photo_list[$album_id];
        
        return !empty($photo_list) ? $photo_list : false;
        
    }
    
   
    
    public function addPhoto() {
        
    }
}