<?php

class dAccountrelation extends dBase{
    
	protected $_tablename = 'wmw_account_relation';
	protected $_fields = array(
	    'relation_id',
		'client_account',
		'friend_account', 
		//'relation_type', 
		'friend_group', 
		'add_account',
		'add_date',
	);
	protected $_pk = 'relation_id';
	protected $_index_list = array(
	    'relation_id',
	    'client_account',
	    'friend_account',
	    'friend_group',
	);
    
	/**
	 * 获取数据库表的联合主键信息，M层调用
	 */
    public function getCompositeKeys() {
	    return array(
	    	'client_account',
	        'friend_account',
	    );
	}
	
	/**
	 * 获取用户的好友关系
	 * @param $client_accounts
	 */
	public function getAccountRelationByClientAccout($client_accounts,$orderby,$offset,$limit) {
	    return $this->getInfoByFk($client_accounts, 'client_account',$orderby,$offset,$limit);
	}
	
	//查找我的好友
	//todolist 数据维度不对
	public function getAccountRelationByAddAccount($account) {
		return $this->getInfoByFk($account,'client_account');
    }
  
    function addAccountRelation ($datas, $is_return_id=false) {
        return $this->add($datas, $is_return_id);
    }
    
    //todolist 特殊业务  命名不规范
    function modifyAccountRelation($dataarr, $relation_id) {
        return $this->modify($dataarr, $relation_id);
    }
    
    // todolist命名不规范
    
    function delAccountRelation($relation_id) {
       return $this->delete($relation_id);
    }
    
}