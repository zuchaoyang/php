<?php
class mAlbuminfo extends mBase {
	
	protected $_dAlbuminfo = null;
	
	public function __construct() {
		$this->_dAlbuminfo = ClsFactory::Create('Data.dAlbuminfo');
	}
	
	//todolist 我的相册列表读取 M层出现的非合理的方法需要调整到相应的C层
	public function getAlbumInfoByaccount($account, $offset = 0, $limit = 10){
		if (empty($account)) {
   			return false;
   		}
   		
   		$account = is_array($account) ? array_shift($account) : $account;
   		$orderby = "album_id desc";
   		
   		$album_arr = $this->getAlbumInfoByAddaccount($account, $orderby, $offset, $limit);
   		$album_list = & $album_arr[$account];
   		
   		return !empty($album_list) ? $album_list : false;
	}
	
	public function getAlbumInfoByAddaccount($account, $orderby = null, $offset = 0, $limit = 100) {
	    if (empty($account)) {
   			return false;
   		}
   		
		return  $this->_dAlbuminfo->getAlbumInfoByAddaccount($account, $orderby, $offset, $limit);
	}


	//创建相册
   public function addAlbuminfo($AlbumInfoData, $is_return_id = false) {
   		if (empty($AlbumInfoData)) {
   			return false;
   		}
   		
		return $this->_dAlbuminfo->addAlbuminfo($AlbumInfoData);
    }
	
	//修改
	public function modifyAlbuminfo($data, $albumid){
		if (empty($data) || empty($albumid)) {
   			return false;
   		}
   		
		return  $this->_dAlbuminfo->modifyAlbuminfo($data, $albumid);
	}

	//设置封面
	public function setAlbumCover($data,$albumid){
		if (empty($data) || empty($albumid)) {
   			return false;
   		}
   		
		return  $this->_dAlbuminfo->modifyAlbuminfo($data, $albumid);
	}


	//根据 2个类型 查找 相册
	public function getAlbuminfoByTowType($album_create_type,$add_account){
		if (empty($album_create_type) || empty($add_account)) {
   			return false;
   		}
   		$wheresql = "add_account='$add_account' and album_create_type='$album_create_type'";
   		return $this->_dAlbuminfo->getInfo($wheresql);
		
		//return  $this->_dAlbuminfo->getAlbuminfoByTowType($album_create_type,$add_account);
	}

	//删除相册
	public function deleteAlbuminfoById($albumid){
		if (empty($albumid)) {
   			return false;
   		}
		return  $this->_dAlbuminfo->deleteAlbuminfoById($albumid);
	}

	//删除相册封面
	public function deleteAlbumimgById($albumid){
		if (empty($albumid)) {
   			return false;
   		}
		
		return  $this->_dAlbuminfo->deleteAlbumimgById($albumid);
	}

	//相册分享到班级
	public function plushalbumByAlbumid($albumid){
		if (empty($albumid)) {
   			return false;
   		}
   		
		return  $this->_dAlbuminfo->plushalbumByAlbumid($albumid);
	}


	//相册信息
	public function getAlbumListByAlbumid($albumid){
		if (empty($albumid)) {
   			return false;
   		}
		
		return  $this->_dAlbuminfo->getAlbumListByAlbumid($albumid);
	}

	//相册列表 todo 三维变成二维 
	public function getAlbumListByaccount($account){
		if (empty($account)) {
   			return false;
   		}
		
		$new_albumlist = $this->getAlbumInfoByAddaccount($account);
		
		return  !empty($new_albumlist) ? $new_albumlist : false;
	}

}
