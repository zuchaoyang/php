<?php
class mAlbumClassRelation extends mBase {
    protected $_dAlbumClassRelation = null;
   
    public function __construct() {
        $this->_dAlbumClassRelation = ClsFactory::Create('Data.Album.dAlbumClassRelation');
    }
                
    public function addAlbumClassRel($data, $is_return_id) {
        return $this->_dAlbumClassRelation->addAlbumClassRel($data, $is_return_id);
    }
    
    public function modifyAlbumClassRelById($data, $id) {
        return $this->_dAlbumClassRelation->modifyAlbumClassRelById($data, $id);
    }
    
    public function delAlbumClassRelById($id) {
        return $this->_dAlbumClassRelation->delAlbumClassRelById($id);
    }
    
    public function getAlbumClassRelByClassCode($class_code, $offset = null, $limit = null) {
        return $this->_dAlbumClassRelation->getAlbumClassRelByClassCode($class_code, $offset, $limit);
    }
    public function getAlbumClassRelByClassAlbumId($album_id, $class_code) {
        if(empty($album_id) || empty($class_code)) {
            return false;
        }
        $wherearr[] = "class_code={$class_code}";
        $wherearr[] = "album_id={$album_id}";
        $orderby = ' class_code asc';
 
        return $this->_dAlbumClassRelation->getInfo($wherearr, $orderby);
    }
    //通过相册album_id删除班级相册关系
    public function delByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        return $this->_dAlbumClassRelation->delByAlbumId($album_id);
    }
}