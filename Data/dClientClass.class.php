<?php

class dClientClass extends dBase{

    /**
     * 通过用户的uid获取班级的关系信息
     * @param $account
     */
	protected $_tablename = 'wmw_client_class';
    protected $_fields = array(
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
        'sort_seq',
    );
    protected $_pk = 'client_class_id';
    protected $_index_list = array(
        'client_class_id',
        'client_account',
        'class_code',
    );

    /**
     * 根据用户id获取用户关系数据
     * @param $uids
     */
    //todolist 维度非规则
    public function getClientClassByUid($uids) {
    	return $this->getInfoByFk($uids, 'client_account');

    }

  

    /**
     * 通过班级id获取班级成员信息
     * @param $classCodes
     */
    //todolist 维度组织非规则
    public function getClientClassByClassCode($classCodes) {
    	  return $this->getInfoByFk($classCodes,'class_code');
    }
    /**
     * 增加会员关系信息
     * @param $clientClassInfo
     */
    //todo C M 
    public function addClientClass($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    public function modifyClientClass($datas , $clientClassId) {
        return $this->modify($datas, $clientClassId);
    }

    /**
     * 删除用户和班级的对应关系
     * @param $uids
     */
    public function delClientClass($clientClassId) {
        return $this->delete($clientClassId);
    }
}
