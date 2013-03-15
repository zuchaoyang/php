<?php
class dLogtypes extends dBase {
    protected $_tablename = "wmw_log_types";
	protected $_fields = array(
		'logtype_id', 
		'logtype_name', 
		'add_account', 
		'add_date', 
		'log_create_type'
	);
	protected $_pk = 'logtype_id';
	protected $_index_list = array(
	    'logtype_id',
	    'add_account',
	);

	/*通过主键得到信息
	 * @param $logtype_id
	 * return $new_logtype_list 二维数组
	 */
	//todolist 函数命名不规范
	public function getLogTypesById($logtype_id) {
	    return $this->getInfoByPk($logtype_id);
	}

	/**
	 * 通过添加人获取日志分类信息
	 * @param $add_accounts
	 */
	public function getLogTypesByAddaccount($add_accounts) {
	    return $this->getInfoByFk($add_accounts, 'add_account');
	}
	

    /*删除日志分类
     * @param $logtypeid
     * return $effect_rows
     */
	public function delLogTypes($logtype_id) {
	    return $this->delete($logtype_id);
	}

    /*添加分类
     * @param $LogTypesData
     * @param $is_return_insert_id
     * return $effect_rows OR $insertId
     */
    public function addLogTypes($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }

    /**
     * 修改分类信息
     * @param $datas
     * @param $logtype_id
     */
	public function modifyLogTypes($datas, $logtype_id) {
	    return $this->modify($datas, $logtype_id);
	}	
}
