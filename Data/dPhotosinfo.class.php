<?php

class dPhotosinfo extends dBase {
    
    protected $_tablename = 'wmw_photos_info';
	protected $_fields = array(
        'photo_id', 
        'album_id', 
        'photo_name', 
        'photo_url', 
        'photo_min_url',
		'photo_explain', 
		'add_account', 
		'add_date', 
		'upd_account', 
		'upd_date',
		'upd_temp_code',
	);
	protected $_pk = 'photo_id';
    protected $_index_list = array(
        'photo_id',
        'album_id',
    	'add_account'
    );

	/*通过id得到相册id
	 * @param $ids
	 * return $new_photos_info_list 二维数组
	 */
	public function getPhotoInfoById($photo_ids) {
		return $this->getInfoByPk($photo_ids);
	}

	/*通过Album_id 得到photo_list
	 * @param $AlbumId
	 * return $new_photos_info_list 三维数组
	 */
	public function getPhotoInfoByAlbumId($album_ids) {
		return $this->getInfoByFk($album_ids, 'album_id');
	}
	
	/**
	 * 通过添加人获取图片信息
	 * @param $add_accounts
	 */
	public function getPhotoInfoByAddaccount($add_accounts) {
	    return $this->getInfoByFk($add_accounts, 'add_account');
	}
	
	/**
	 * 增加图片信息
	 * @param  $datas
	 * @param  $is_return_id
	 */
	public function addPhotoInfo($datas, $is_return_id = false) {
	    return $this->add($datas, $is_return_id);
	}
	
	/**
	 * 修改图片信息
	 * @param  $datas
	 * @param  $photo_id
	 */
	public function modifyPhotoInfo($datas, $photo_id) {
	    return $this->modify($datas, $photo_id);
	}
	
	/**
	 * 删除图片信息
	 * @param $photo_id
	 */
	public function delPhotoInfo($photo_id) {
	    return $this->delete($photo_id);
	}
}
