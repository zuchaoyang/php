<?php
class mAlbumPersonRelation extends mBase {
    protected $_dAlbumPersonRelation = null;

    public function __construct() {
        $this->_dAlbumPersonRelation = ClsFactory::Create('Data.Album.dAlbumPersonRelation');
    }
    
    public function addAlbumPersonRel($data, $is_return_id) {
        return $this->_dAlbumPersonRelation->addAlbumPersonRel($data, $is_return_id);
    }
    
    public function modifyAlbumPersonRelById($data, $id) {
        return $this->_dAlbumPersonRelation->modifyAlbumPersonRelById($data, $id);
    }
    
    public function delAlbumPersonRelById($id) {
        return $this->_dAlbumPersonRelation->delAlbumPersonRelById($id);
    }
    
    public function getAlbumPersonRelByUid($uid, $offset = null, $limit = null) {
        return $this->_dAlbumPersonRelation->getAlbumPersonRelByUid($uid, $offset, $limit);
    }
    public function getAlbumPersonRelByUidAlbumId($album_id, $uid) {
        if(empty($album_id) || empty($uid)) {
            return false;
        }
        $wherearr['client_account'] = $uid;
        $wherearr['album_id'] = $album_id;
        $orderby = ' client_account asc';
        return $this->_dAlbumPersonRelation->getInfo($wherearr, $orderby);
    }
   /* public function getAlbumPersonRelByAlbumId($album_id) {
        return $this->_dAlbumPersonRelation->getAlbumPersonRelByAlbumId($album_id);
    }*/
    
    public function delByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        
        return $this->_dAlbumPersonRelation->delByAlbum_id($album_id);
    }
}