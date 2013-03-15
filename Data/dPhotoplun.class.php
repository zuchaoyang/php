<?php
class dPhotoplun extends dBase {
    
    protected $_tablename = 'wmw_photo_plun';
	protected $_fields = array(
        'plun_id', 
        'photo_id',  
        'plun_content', 
        'add_account', 
        'add_date',
        'photo_account',
	);
	protected $_pk = 'plun_id';
	protected $_index_list = array(
	    'plun_id',
	    'photo_id',
	);

	/*根据$photoid查找评论
	 * @param $photoid
	 * return 
	 */
	public function getPhotoPlunByPhotoId($photoid) {
		return $this->getInfoByFk($photoid, 'photo_id');
	}
	
	/*添加评论
	 * @param $plunData
	 * @is_return_id
	 * return $effect_rows OR $insert_id
	 */
    public function addPhotoPlun($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 通过主键删除评论信息
     * @param $plun_id
     */
    public function delPhotoPlun($plun_id) {
        return $this->delete($plun_id);
    }
}
