<?php
class dAlbuminfo extends dBase {
	//wmw_album_info
	protected $_tablename = 'wmw_album_info';
	protected $_fields = array(
		'album_id', 
		'album_name', 
		'album_explain', 
		'album_img', 
		'add_account', 
		'add_date', 
		'upd_account',
		'upd_date', 
		'album_create_type'
	);
	protected $_pk = 'album_id';
	protected $_index_list = array(
	    'album_id',
	    'add_account',
	);

	//我的相册列表
	public function getAlbumInfoByAddaccount($account, $orderby = null, $offset = 0, $limit = 10) {
        if (empty($account)) {
           return false; 
        }
        
        return $this->getInfoByFk($account, 'add_account', $orderby, $offset, $limit);
	}

	//按相册ID 读取
	public function getAlbumListByAlbumid($albumids) {
		return $this->getInfoByPk($albumids);
	}
	
	//添加分类
    public function addAlbuminfo($AlbumInfoData, $return_insert_id = false) {
        return $this->add($AlbumInfoData, $return_insert_id);
    }

    public function modifyAlbuminfo($datas, $albumid){
        return $this->modify($datas, $albumid);
    }
	
	// 删除相册
	public function deleteAlbuminfoById($albumid) {
		return $this->delete($albumid);
	}

}
