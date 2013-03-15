<?php
class dAlbumPersonRelation extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_album_person_relation';
    protected $_fields = array(
                    'id',
                    'client_account',
                    'album_id',
    
                );
    protected $_index_list = array(
                    'client_account',
                    'album_id',
                );
                
    public function addAlbumPersonRel($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    public function modifyAlbumPersonRelById($data, $id) {
        return $this->modify($data, $id);
    }
    
    public function delAlbumPersonRelById($id) {
        return $this->delete($id);
    }
    
    public function getAlbumPersonRelByUid($uid, $offset = null, $limit = null) {
        $orderby = $this->_pk.' asc';
        return $this->getInfoByFk($uid, 'client_account', $orderby, $offset, $limit);
    }
    
    public function getAlbumPersonRelByAlbumId($album_id) {
        return $this->getInfoByFk($album_id, 'album_id');
    }
    
    public function delByAlbum_id($album_id) {
        $sql = "delete from {$this->_tablename} where album_id={$album_id}";
        
        return $this->execute($sql);
    }
    
}