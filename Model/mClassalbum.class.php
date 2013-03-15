<?php
class mClassalbum extends mBase {
	
	protected $_dClassalbum = null;
	
	public function __construct() {
		$this->_dClassalbum = ClsFactory::Create('Data.dClassalbum');
	}
	/**
	 * 查找相册是否分享到班级
	 * @param $albumid
	*/
   
   public function findAlbumexistsByAlbumid($albumid) {
   		if (empty($albumid)) {
   			return false;
   		}
   		
		return $this->_dClassalbum->findAlbumexistsByAlbumid($albumid);
    }

	
	/**
	 * 查找相册是否分享到指定班级
	 * @param $albumid $class_code
	*/
	public function findAlbumexistsByAlbumidclasscode($albumid,$class_code) {
        if (empty($albumid) || empty($class_code)) {
           return false; 
        }
        
	    $albumid = array_unique((array)$albumid);
	    $class_code = array_unique((array)$class_code);
	     
		$condition = " album_id in(" . implode(',',$albumid) . ") and class_code in(" . implode(',',$class_code) . ")";
		$class_album_list = $this->_dClassalbum->getInfo($condition);
        
		return !empty($class_album_list) ? $class_album_list : false;
	}

	public function addClassAlbumInfo($arrInfoData, $is_return_id = false) {
        if (empty($arrInfoData) || !is_array($arrInfoData)){
        	return false;	
        }
      
		$effect_rows = $this->_dClassalbum->addClassAlbumInfo($arrInfoData, $is_return_id);
		return !empty($effect_rows) ? $effect_rows : false;
    }
	/**
	 * 查找班级所有相册列表
	 * @param $class_code
	*/
   
   public function getAlbumInfoByClassCode($class_code) {
   		if (empty($class_code)) {
   			return false;
   		}
   		
		return $this->_dClassalbum->getAlbumInfoByClassCode($class_code);
    }

    //   错误的方法已经没人使用了
//	public function cancelAlbumPush($class_album_id){
//		return $this->_dClassalbum->cancelAlbumPush($class_album_id);
//	}	
//    
//   
    
	/**
	 * 取消用户相册分享
	 * @param $albumid
	*/
   public function delClassAlbum($albumid) {
   		if  (empty($albumid)) {
   			return false;
   		}
   		
		return $this->_dClassalbum->delClassAlbum($albumid);
    }

	/*
	 * 通过班级id和相册id得到内容
	 */
	public function getAlbumInfoByalbumIdClassCode($albumid, $class_code) {
		if (empty($albumid) || empty($class_code)) {
           return false; 
        }
        
	    $albumid = array_unique((array)$albumid);
		$condition = " album_id in(" . implode(',',$albumid) . ") and class_code in({$class_code})";
		$class_album_list = $this->_dClassalbum->getInfo($condition);
		
		return $class_album_list;
	}


}
