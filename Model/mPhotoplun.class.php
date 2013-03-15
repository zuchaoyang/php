<?php
class mPhotoplun extends mBase {
	
	protected $_dPhotoplun = null;
	
	public function __construct() {
	    $this->_dPhotoplun = ClsFactory::Create('Data.dPhotoplun');
	}
	
	/*根据$photoid查找评论
	 * @param $photoid
	 * return $photo_plun_list
	 */
	public function getPhotoPlunByPhotoId($photo_ids) {
	    if(empty($photo_ids)) {
	        return false;
	    }
	    
		return  $this->_dPhotoplun->getPhotoPlunByPhotoId($photo_ids);
	}
	
	/*添加评论
	 * @param $plunData
	 * @param $is_return_id
	 * return $effect_rows OR $insert_id
	 */
	public function addPhotoPlun($datas, $is_return_id = false) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
		return  $this->_dPhotoplun->addPhotoPlun($datas, $is_return_id);
	}
	
	/**
	 * 通过主键删除评论信息
	 * @param $plun_id
	 */
	public function delPhotoPlun($plun_id) {
	    if(empty($plun_id)) {
	        return false;
	    }
	    
	    return $this->_dPhotoplun->delPhotoPlun($plun_id);
	}
	
	/*照片评论数量
	 * @param $photoid
	 * return $photo_plun_list
	 */
	public function getPhotoPlunCountByPhotoId($photoid) {
	    if(empty($photoid)) {
	        return false;
	    }
	    
	    $wheresql = "photo_id='$photoid'";
	    return $this->_dPhotoplun->getCount($wheresql);
	}

}

