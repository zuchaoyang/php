<?php
//todolist 该表相应的索引不全
class dDepartment extends dBase {
	protected $_tablename = 'oa_department';
    protected $_fields = array(
        'dpt_id',
        'school_id',
        'sort_id',//排序id
        'dpt_name',//部门名称
        'dpt_phone',//部门电话
        'dpt_description',//部门职能
        'dpt_photo',//部门logo
        'up_id',//上级部门id
    );
    protected $_pk = 'dpt_id';
    protected $_index_list = array(
        'dpt_id',
        'school_id',
        'up_id'
    );

    public function _initialize() {
       $this->connectDb('oa' , true);
    }

    //通过部门id得到部门信息
    function getDepartmentById($dpt_ids) {
        return $this->getInfoByPk($dpt_ids);
    }

    public function getDepartmentBySchoolId($school_ids) {
        return $this->getInfoByFk($school_ids, 'school_id');
    }
    

    //添加部门信息
    function addDepartment($datas , $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }

    //修改部门信息
    function modifyDepartment($datas, $dpt_id) {
        return $this->modify($datas, $dpt_id);
    }

    //删除部门
    //todolist 命名不规范
    function delDepartmentByDptId($dpt_id) {
        return $this->delete($dpt_id);
    }
    
    //根据upid查询信息
    //todolist 数据的维度问题
    public function getDepartmentByUpid($upid) {
    	return $this->getInfoByFk($upid , 'up_id');
    }
    
}