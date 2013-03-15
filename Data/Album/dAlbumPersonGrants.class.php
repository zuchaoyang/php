<?php
class dAlbumPersonGrants extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_album_person_grants';
    protected $_fields = array(
                    'id',
                    'client_account',
                    'album_id',
                    'grant',
    
                );
    protected $_index_list = array(
                    'client_account',
                    'album_id',
                );
                
    public function addAlbumPersonGrant($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    public function modifyAlbumPersonGrantById($data, $id) {
        return $this->modify($data, $id);
    }
    
    public function delAlbumPersonGrantById($id) {
        return $this->delete($id);
    }
    
    public function getAlbumPersonGrantByUid($uid) {
        return $this->getInfoByFk($uid, 'client_account');
    }
    
    public function getAlbumPersonGrantByAlbumId($album_id) {
        return $this->getInfoByFk($album_id, 'album_id');
    }
    
    public function delByAlbumId($album_id) {
        $sql = "delete from {$this->_tablename} where album_id={$album_id}";
        
        return $this->execute($sql);
    }
    
}