<?php
//todolist 该表的索引信息不全
class dDepartmentMembers extends dBase{
	
	protected $_tablename = 'oa_department_members';
    protected $_fields = array(
        'dptmb_id',
        'dpt_id',//部门id
        'client_account',
        'role_ids',
        'duty_name',//职务名称
        'sort_id',//排序id
        'add_time',
    );
    protected $_pk = 'dptmb_id';
    protected $_index_list = array(
        'dptmb_id',
        'dpt_id',
        'client_account',
    );

    public function _initialize() {
       $this->connectDb('oa' , true);
    }

    //通过uid得到部门成员信息
    function getDepartmentMembersByUid($uids) {
        return $this->getInfoByFk($uids, 'client_account');
    }

    //通过部门id得到部门的成员
    function getDepartmentMembersByDptId($dpt_ids) {
        return $this->getInfoByFk($dpt_ids, 'dpt_id');
    }
    
    //添加部门成员信息
    function addDepartmentMembers($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }

    //修改部门信息
    function modifyDepartmentMembers($datas, $dptmb_id) {
        return $this->modify($datas, $dptmb_id);
    }
    
    //删除部门成员
     function delDepartmentMembers($dptmb_id) {
         return $this->delete($dptmb_id);
    }
}