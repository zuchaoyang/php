<?php
class dAlbumClassRelation extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_album_class_relation';
    protected $_fields = array(
                    'id',
                    'class_code',
                    'album_id',
                );
    protected $_index_list = array(
                    'class_code',
                    'album_id',
                );
    /**
     * 添加班级相册关系
     * @param unknown_type $data
     * @param unknown_type $is_return_id
     */            
    public function addAlbumClassRel($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    public function modifyAlbumClassRelById($data, $id) {
        return $this->modify($data, $id);
    }
    
    public function delAlbumClassRelById($id) {
        return $this->delete($id);
    }
    
    public function getAlbumClassRelByClassCode($class_code, $offset = null, $limit = null) {
        $orderby = $this->_pk.' asc';
        return $this->getInfoByFk($class_code,'class_code', $orderby, $offset, $limit);
    }
    
    
    //通过相册album_id删除班级相册关系
    public function delByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
         
        $sql = "delete from {$this->_tablename} where album_id={$album_id}";
        
        return $this->execute($sql);
    }
}