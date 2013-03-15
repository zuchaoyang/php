<?php
class mAlbumClassGrants extends mBase {
    protected $_dAlbumClassGrants = null;
    
    public function __construct() {
        $this->_dAlbumClassGrants = ClsFactory::Create('Data.Album.dAlbumClassGrants');
    }
    
    public function addAlbumClassGrant($data, $is_return_id) {
        return $this->_dAlbumClassGrants->addAlbumClassGrant($data, $is_return_id);
    }
    
    public function modifyAlbumClassGrantById($data, $id) {
        return $this->_dAlbumClassGrants->modifyAlbumClassGrantById($data, $id);
    }
    
    public function delAlbumClassGrantById($id) {
        return $this->_dAlbumClassGrants->delAlbumClassGrantById($id);
    }
    
    public function getAlbumClassGrantByClassCode($class_code) {
        return $this->_dAlbumClassGrants->getAlbumClassGrantByClassCode($class_code);
    }
    
    public function getAlbumClassGrantByAlbumId($album_id) {
        return $this->_dAlbumClassGrants->getAlbumClassGrantByAlbumId($album_id);
    }
    
    public function delByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        return $this->_dAlbumClassGrants->delByAlbumId($album_id);
    }
}