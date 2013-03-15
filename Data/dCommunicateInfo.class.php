<?php
class dCommunicateInfo extends dBase {
	
	protected $_tablename = 'wmw_communicate_info';	
	protected $_fields = array(
        'communicate_id',
        'communicate_content',
        'to_account',
        'add_account',
        'add_time',
        'child_account',
	);
	protected $_pk = 'communicate_id';
	protected $_index_list = array(
	    'communicate_id',
	    'child_account'
	);

	//通过孩子信息得到老师与家长的沟通内容
	public function getCommunicateByChildId($child_uids) {
	    return $this->getInfoByFk($child_uids, 'child_account');
	}
	
	//发送沟通信息
	public function addCommunicate($datas, $is_return_id = false) {
	    return $this->add($datas, $is_return_id);
    }
}