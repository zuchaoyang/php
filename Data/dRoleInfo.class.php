<?php 
/**
 * wms 角色设置
 */
class dRoleInfo extends dBase{
	protected $_tablename = 'wmw_role_info';
	protected $_fields = array(
        'role_code',
        'role_name',
        'add_account',
        'add_date',
	);
	protected $_pk = 'role_code';
	protected $_index_list = array(
	    'role_code',
	);

    //通过主键查询
	public function getRoleInfoById($role_code) {
		return $this->getInfoByPk($role_code);
	}

	//添加角色
	public function addRoleInfo($datas) {
	    return $this->add($datas);
	}
	
}
