<?php
class dClientRoleRelation extends dBase {
	
	
	protected $_tablename = 'wmw_client_role_relation';
	//todo 有两个主键联合主键
	protected $_pk = 'relation_id';
	protected $_fields = array(
	    'relation_id',
		'client_account',
        'role_code',
        'add_account',
        'add_date',
	);
	
	protected $_index_list = array(
	    'relation_id',
	    'client_account',
	    'role_code',
	);
	
	public function getCompositeKeys() {
	    return array(
	    	'client_account',
	        'role_code',
	    );
	}
	
	//通过uid查询关系
	public function getClientRoleRelationByClientAccount($client_account) { 

	    return $this->getInfoByFk($client_account, 'client_account');
	}
	
	//删除
    public function delClientRoleRelation($relation_id) {
       return $this->delete($relation_id);
    }
 
}