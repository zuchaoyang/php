<?php
class dAlbumClassGrants extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_album_class_grants';
    protected $_fields = array(
                    'id',
                    'class_code',
                    'album_id',
                    'grant',
                );
    protected $_index_list = array(
                    'class_code',
                    'album_id',
                );
    /**
     * 添加关系信息
     */            
    public function addAlbumClassGrant($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    public function modifyAlbumClassGrantById($data, $id) {
        return $this->modify($data, $id);
    }
    
    public function delAlbumClassGrantById($id) {
        return $this->delete($id);
    }
    
    public function getAlbumClassGrantByClassCode($class_code) {
        return $this->getInfoByFk($class_code, 'class_code');
    }
    
    public function getAlbumClassGrantByAlbumId($album_id) {
        return $this->getInfoByFk($album_id, 'album_id');
    }
    
    //todo delete
    public function delByAlbumId($album_id) {
        $sql = "delete from {$this->_tablename} where album_id={$album_id}";
        return $this->execute($sql);
    }
}