<?php
class mMypyCollect extends mBase {
	
    protected $_dPyCollect = null;
    
    public function __construct() {
        $this->_dPyCollect = ClsFactory::Create('Data.dMyPyCollect');
    }
    
    /**
     * 根据主键获取个人收藏的评语内容
     */
    public function getMyPycollectById($collect_id) {
        if (empty($collect_id)) {
            return false;
        }
        
        return $this->_dPyCollect->getMyPycollectById($collect_id);
    }
    
	/*按评语添加人读取评语
     * @param $account
     * return $py_collect_arr
     */
	public function getMyPycollectByaccount($account) {
	    if(empty($account)) {
	        return false;
	    }
	    
		return  $this->_dPyCollect->getMyPycollectByaccount($account);
	}
	
	/*按where条件读取评语
     * @param $account
     * return $py_collect_arr
     */
	public function getMyPycollectInfo($where, $orderby, $offset = 0, $limit = 10) {
	    $py_list = $this->_dPyCollect->getInfo($where, $orderby, $offset, $limit);

		return  !empty($py_list)? $py_list : false;
	}

	/*删除评语
	 * @param $py_id
	 * return $effect_rows
	 */
	public function delMyCollect($collect_id) {
	    if(empty($collect_id)) {
	        return false;
	    }
	    
	    return $this->_dPyCollect->delMyCollect($collect_id);
	}

	/*收藏到我的评语库
	 * @param $arrPyCommentData
	 * @param $is_return_insert_id
	 * return $effect_rows OR $insert_id
	 */
    public function addMyPyCollect($datas, $is_return_id) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
		return  $this->_dPyCollect->addMyPyCollect($datas, $is_return_id);
    }
}
