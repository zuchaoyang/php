<?php
class dClientClassHistory extends dBase{

    /**
     * 通过用户的uid获取班级的关系信息
     * @param $account
     */
	protected $_tablename = 'wmw_client_class_history';
    protected $_fields = array(
        'history_id',
        'client_class_id',
        'client_account',
        'class_code',
        'client_class_role',
        'teacher_class_role',
        'class_admin',
        'add_time',
        'add_account',
        'upd_account',
        'upd_time',
        'client_type',
        'graduation_time',
    );
    protected $_pk = 'history_id';
    protected $_index_list = array(
        'history_id',    
        'client_account',
        'class_code',
    );

    /**
     * 根据用户id获取用户关系数据
     * @param $uids
     */
    public function getClientClassHistoryByUid($uids) {
    	return $this->getInfoByFk($uids, 'client_account');
    }

    /**
     * 通过班级id获取班级成员信息
     * @param $classCodes
     */
    public function getClientClassHistoryByClassCode($classCodes) {
    	  return $this->getInfoByFk($classCodes, 'class_code');
    }
    /**
     * 增加会员关系信息
     * @param $clientClassInfo
     */
    public function addClientClassHistory($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 删除用户和班级的对应关系
     * @param $uids
     */
    public function delClientClassHistory($history_id) {
        return $this->delete($history_id);
    }
}