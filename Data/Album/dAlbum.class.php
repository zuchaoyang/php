<?php
class dAlbum extends dBase {
    protected $_pk = 'album_id';
    protected $_tablename = 'wmw_album';
    protected $_fields = array(
                    'album_id' ,
                    'album_name',
                    'album_explain',
                    'album_img',
                    'add_account',
                    'add_time',
                    'upd_account',
                    'upd_time',
                    'album_auto_img',
                    'photo_num'
              );
    protected $_index_list = array(
                    'album_id'
              );
    
    //创建相册
    public function addAlbum($data,$is_return_id) {
        return $this->add($data,$is_return_id);
    }
    //修改相册
    public function modifyAlbumByAlbumId($data,$album_id) {
        return $this->modify($data,$album_id);
    }
    //修改相册todo list
    public function modifyByAlbumId($data,$album_id) {
        return $this->modify($data,$album_id);
    }
    
    //删除相册
    public function delAlbumByAlbumId($album_id) {
        return $this->delete($album_id);
    }
    //删除相册todo list
    public function delByAlbumId($album_id) {
        return $this->delete($album_id);
    }
    
    //获取相册信息
    public function getAlbumByAlbumId($album_ids) {
        return $this->getInfoByPk($album_ids);
    }
    //获取相册信息todo list
    public function getByAlbumId($album_ids) {
        return $this->getInfoByPk($album_ids);
    }
    //修改相片数todo delete
    public function updPhotoCountByAlbumId($count, $album_id) {
        if(empty($album_id) || empty($count)) {
            return false;
        }
        
        $sql = "update {$this->_tablename} set photo_num=photo_num{$count} where album_id={$album_id}";
       
        return $this->execute($sql);
    }
}