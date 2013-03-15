<?php
class dAlbumPhotos extends dBase {
    protected $_pk = 'photo_id';
    protected $_tablename = 'wmw_album_photos';
    protected $_fileds = array(
        'photo_id',
        'album_id',
        'name',
        'file_big',
        'file_middle',
        'file_small',
        'description',
        'comments',
        'upd_account',
        'upd_time',
    );

    protected $_index_list = array(
        'photo_id',
    	'album_id',
    );
    
    /**
     * 通过相册获取图片信息
     * @param $album_ids
     * @param $offset
     * @param $limit
     */
    //todo delete
    public function getPhotosByAlbumId($album_ids) {
        return $this->getInfoByFk($album_ids, 'album_id', 'photo_id desc');
    }
    
    //添加照片
    public function addPhoto($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //修改照片
    public function modifyPhotoByPhotoId($data, $photo_id){
        return $this->modify($data, $photo_id);
    }
    
    //删除照片
    public function delPhotosByPhotoId($photo_id){
        return $this->delete($photo_id);
    }
    
    //获得照片信息
    public function getPhotosByPhotoId($photo_ids){
        return $this->getInfoByPk($photo_ids);
    }
    //获得照片信息
    public function getByAlbumId($album_id, $offset=0, $limit=10){
        $orderby = ' photo_id asc';
        $wherearr[] = "album_id={$album_id}";
        
        return $this->getInfo($wherearr, $orderby, $offset, $limit);
    }
    
    //通过相册album_id删除相册信息
    public function delByAlbumId($album_id) {
        
        $sql = "delete from {$this->_tablename} where album_id={$album_id}";
        return $this->execute($sql);
    }
    
    //根据相册ID获取相片的数量
    public function getCountByAlbumId($album_id) {
        if(empty($album_id)) {
            return false;
        }
        $wherearr[] = "album_id={$album_id}";
        
        return $this->getCount($wherearr);
        
    }
    
    //修改相片评论数
    //todo delte
    public function updCommentCountByPhotoId($count, $photo_id) {
        if(empty($photo_id) || empty($count)) {
            return false;
        }
        $sql = "update {$this->_tablename} set comments=comments{$count} where photo_id={$photo_id}";
         
        return $this->execute($sql);
    }
}