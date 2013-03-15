<?php
class dRoleFuncRelation extends dBase {
	
	protected $_tablename = 'wmw_role_func_relation';
	protected $_pk = 'relation_id'; //todo 联合主键
	protected $_fields = array(
        'relation_id',
		'role_code',
        'func_code',
        'add_account',
        'add_date',
	);
	
	protected $_index_list = array(
	    'relation_id',
	    'role_code',
	    'func_code',
	);
	
    public function getCompositeKeys() {
	    return array(
	    	'role_code',
	    	'func_code',
	    );
	}
	
    //通过外键role_code查询 
	public function getRoleFuncRelationbyRoleCode($role_codes) {

	    return $this->getInfoByFk($role_codes, 'role_code');
	}
    
	//通过主键删除
	public function delRoleFuncRelation($relation_id) {
	    
	    return $this->delete($relation_id);
	}
}