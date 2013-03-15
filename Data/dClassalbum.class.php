<?php

class dClassalbum extends dBase {
		protected $_tablename = 'wmw_class_album';
		protected $_fields = array(
			'class_album_id', 
			'album_id', 
			'class_code', 
			'add_time'
		);
		protected $_pk = 'class_album_id';
		protected $_index_list = array(
		    'class_album_id',
		    'class_code',
		    'album_id',
		);
	
	/**
	 * 按班级编号获取相册内容列表
	 * @param $albumid
	*/
	public function getAlbumInfoByClassCode($class_code) {
	    return $this->getInfoByFk($class_code, 'class_code');
	}
	
	/**
	 * 查找相册是否分享到班级
	 * @param $albumid
	*/
	public function findAlbumexistsByAlbumid($albumid) {
	    return $this->getInfoByFk($albumid, 'album_id');
	}

	//删除班级相册
	public function delClassAlbum($class_album_id){
        if (empty($class_album_id)) {
            return false; 
        }
		return $this->delete($class_album_id);
	}	
	
	/**
	 * 日志分享保存映射关系
	 * @param $arrInfoData
	*/
	public function addClassAlbumInfo($arrInfoData, $is_return_id = false) {
        if (empty($arrInfoData) || !is_array($arrInfoData)){
        	return false;	
        }
        
		return $this->add($arrInfoData, $is_return_id);
    }
}
