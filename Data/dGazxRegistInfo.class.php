<?php
//todolist 数据的维度问题
class dGazxRegistInfo extends dBase{
    
    protected $_tablename = 'wmw_gazx_regist_info';
	protected $_fields = array(
	    'regist_id',
		'parent_account', 
		'parent_phone', 
		'parent_id', 
		'child_account', 
		'child_phone',
		'child_id', 
		'add_date',  
	);
	
	protected $_pk = 'regist_id';
	protected $_index_list = array(
	    'parent_account',
	);

	
	//查找关爱之星办理用户BY 家长账号
	public function getRegistInfoByParentAccount($parentAccount) {
	    return $this->getInfoByFk($parentAccount, 'parent_account');
    }


	//查找关爱之星办理用户BY 孩子账号
	public function getRegistInfoByChildAccount($childAccount) {
	    return $this->getInfoByFk($childAccount, 'child_account');
    }     
    
	//查找关爱之星办理用户BY 孩子手机
	public function getRegistInfoByChildPhone($childPhone) {
	    return $this->getInfoByFk($childPhone, 'child_phone');
    }
    
    /**
     * 注册关爱之心信息
     * @param $datas
     * @return last insert id from auto_increment
     */
    public function addRegistInfo($dataarr , $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
}