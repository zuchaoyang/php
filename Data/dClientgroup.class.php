<?php
class dClientgroup extends dBase {
	
	protected $_tablename = 'wmw_client_group';
	protected $_fields = array(
		'group_id',
		'client_account',
		'group_name',
		'group_type',
		'add_account',
		'add_date',
	);
	
	protected $_pk = 'group_id';
	protected $_index_list = array(
	    'group_id',
	    'client_account',
	);

	/**
	 * 获取用户所有分组内容
	 * @param $account
	*/
	public function getClientGroupByUid($account) {
	    return $this->getInfoByFk($account , 'client_account');
	}
	
	/**
	 * 查找分组名称
	 * @param $group_id
	*/
    //todolist 数据维度问题
	public function getClientGroupById($group_id) {
	    return $this->getInfoByPk($group_id);
	}

	
    public function addClientGroup($datas, $is_return_id = false) {
       return $this->add($datas, $is_return_id);
    }
	
    public function modifyClientGroup($datas, $group_id) {
        return $this->modify($datas, $group_id);
    }
	
	public function delClientGroup($group_id) {
        return $this->delete($group_id);
    }
}
