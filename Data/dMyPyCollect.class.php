<?php
class dMyPyCollect extends dBase {

    protected $_tablename='wmw_py_collect';
    protected $_fields = array(
        'collect_id',
        'py_content',
        'py_type',
        'py_att',
        'client_account',
        'add_time',
    );
    protected $_pk = 'collect_id';
    protected $_index_list = array(
        'collect_id',
        'client_account'
    );
    
    /**
     * 根据主键获取个人收藏的评语内容
     */
    public function getMyPycollectById($collect_id) {
        if (empty($collect_id)) {
            return false;
        }
        
        return $this->getInfoByPk($collect_id);
    }
    
    /*按评语类型读取评语
     * @param $account
     * return $py_collect_arr
     */
	public function getMyPycollectByaccount($clientAccount) {
		return $this->getInfoByFk($clientAccount, 'client_account', 'collect_id desc');
	}
	
	/*删除评语
	 * @param $py_id
	 * return $effect_rows
	 */
	public function delMyCollect($collect_id) {
        return $this->delete($collect_id);
	}
	
	/*收藏到我的评语库
	 * @param $arrPyCommentData
	 * @param $is_return_insert_id
	 * return $effect_rows OR $insert_id
	 */
    public function addMyPyCollect($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
}