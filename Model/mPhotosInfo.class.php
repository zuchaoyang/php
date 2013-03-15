<?php
class mPhotosInfo extends mBase {
	
    protected $_dPhotosinfo = null;
    
    public function __construct() {
        $this->_dPhotosinfo = ClsFactory::Create('Data.dPhotosinfo');
    }
    
	/*通过id得到相册id
	 * @param $ids
	 * return $new_photos_info_list 二维数组
	 */
	public function getPhotoInfoById($photo_ids) {
        if(empty($photo_ids)) {
           return false; 
        }
        
        return $this->_dPhotosinfo->getPhotoInfoById($photo_ids);
	}
	
	/*通过Album_id 得到photo_list
	 * @param $AlbumId
	 * return $new_photos_info_list 三维数组
	 */
	public function getPhotoInfoByAlbumId($album_ids) {
	    if(empty($album_ids)) {
	        return false;
	    }
	    
		return  $this->_dPhotosinfo->getPhotoInfoByAlbumId($album_ids);
	}
	
	/**
	 * 通过添加人获取图片信息
	 * @param $add_accounts
	 */
	public function getPhotoInfoByAddaccount($add_accounts) {
	    if(empty($add_accounts)) {
	        return false;
	    }
	    
	    return $this->_dPhotosinfo->getPhotoInfoByAddaccount($add_accounts);
	}
	
	/**
	 * 增加图片信息
	 * @param  $datas
	 * @param  $is_return_id
	 */
	public function addPhotoInfo($datas, $is_return_id = false) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dPhotosinfo->addPhotoInfo($datas, $is_return_id);
	}
	
	/**
	 * 修改图片信息
	 * @param  $datas
	 * @param  $photo_id
	 */
	public function modifyPhotoInfo($datas, $photo_id) {
	    if(empty($datas) || !is_array($datas) || empty($photo_id)) {
	        return false;
	    }
	    
	    return $this->_dPhotosinfo->modifyPhotoInfo($datas, $photo_id);
	}
	
	/**
	 * 删除图片信息
	 * @param $photo_id
	 */
	public function delPhotoInfo($photo_id) {
	    if(empty($photo_id)) {
	        return false;
	    }
	    return $this->_dPhotosinfo->delPhotoInfo($photo_id);
	}
	
	/*更改默认相册ID
	 * @param $dataatt
	 * @param $uids
	 * return $effect_rows
	 */
	//todolist C层和M层的代码都需要调整，部分业务逻辑如album_id=0应该属于C层的
	public function modifyphotos($dataarr, $uids) {
	    if(empty($dataarr) || !is_array($dataarr) || empty($uids)) {
	        return false;
	    }
	    
	    $photo_arr = $this->getPhotoInfoByAddaccount($uids);
	    $effect_rows = 0;
	    if(!empty($photo_arr)) {
	        foreach($photo_arr as $add_account=>$photo_list) {
	            foreach($photo_list as $photo_id=>$photo) {
	                if(intval($photo['album_id']) != 0) {
	                    continue;
	                }
	                $this->modifyPhotoInfo($dataarr, $photo_id) && $effect_rows++;
	            }
	        }
	    }
	    
	    return $effect_rows;
    }

    /*修改照片内容
	 * @param $data
	 * @param $photoId
	 * return $effect_rows
	 */
    //todolist M层不合理的方法
	public function modifyphotosbyId($data,$photoId) {
	    return $this->modifyPhotoInfo($data, $photoId);
    }

    /*添加相片信息
	 * @param $photoData
	 * @param $is_return_id
	 * return $effect_rows OR $insert_id
	 */
    //todolist M层命名不规范的函数
	public function addphotos($photosData, $is_return_id = false) {
	    if(empty($photosData) || !is_array($photosData)) {
	        return false;
	    }
	    
	    return $this->addPhotoInfo($photosData, $is_return_id);
    }


	/*相册内照片移动
	 * @param $moveToAlbumId
	 * @param $oldPhotoId
	 * return $effect_rows
	 */
	public function movePhotoInfo($moveToAlbumId, $oldPhotoId) {
	    
	    $moveToAlbumId = intval($moveToAlbumId);
	    return $this->modifyPhotoInfo(array('album_id' => $moveToAlbumId), $oldPhotoId);
		//return  $this->_dPhotosinfo->movePhotoInfo($moveToAlbumId,$oldPhotoId);
	}

	/*相册整体移动
	 * @param $moveToId
	 * @param $oldId
	 * return $effect_rows
	 */
	//todolist 特殊业务
	public function movePhotoToNewAlbum($moveToAlbumId, $oldAlbumId) {
	    if(empty($moveToAlbumId) || empty($oldAlbumId)) {
	        return false;
	    }
	    
	    $moveToAlbumId = is_array($moveToAlbumId) ? array_shift($moveToAlbumId) : $moveToAlbumId;
	    $oldAlbumId = is_array($oldAlbumId) ? array_shift($oldAlbumId) : $oldAlbumId;
	    
	    $photo_arr = $this->getPhotoInfoByAlbumId($oldAlbumId);
	    $photo_list = & $photo_arr[$oldAlbumId];
	    
	    $effect_rows = 0;
	    $datas = array(
	        'album_id' => $moveToAlbumId,
	    );
	    if(!empty($photo_list)) {
	        foreach($photo_list as $photo_id=>$photo) {
	            $this->modifyPhotoInfo($datas, $photo_id) && $effect_rows++;
	        }
	    }
	    
	    return $effect_rows;
	}
}
