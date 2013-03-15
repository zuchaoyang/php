<?php
class dRole extends dBase{
    protected $_tablename = null;
    protected $_index_list = array();
    protected $_pk = null;
    protected $_fields = array();
    
    public function switchToRole() {
        $this->_tablename = 'oa_role';
        $this->_index_list = array(
            'role_id',
            'school_id',
            'add_account'
        );
        $this->_pk = 'role_id';
        $this->_fields = array(
            'role_id',
        	'school_id', 
        	'role_name', 
        	'role_access', 
        	'add_account', 
        	'add_time',
        );
    }
    
    public function switchToRoleSystem() {
        $this->_tablename = 'oa_role_system';
        $this->_index_list = array(
            'role_id'
        );
        $this->_pk = 'role_id';
        $this->_fields = array(
            'role_id', 
        	'role_name', 
        	'role_access', 
        	'add_time',
        );
    }
    
    //连接oa数据库
    public function _initialize() {
         $this->connectDb('oa', true);
    }
    
    //通过id获取系统角色
    public function getRoleSystemById($role_ids) {
        $this->switchToRoleSystem();
        
        return $this->getInfoByPk($role_ids);
    }
    
    //通过id获取自定义角色
    public function getRoleById($role_ids) {
        $this->switchToRole();
        
        return $this->getInfoByPk($role_ids);
    }
    
    //通过school_id获取学校自定义角色
    public function getRoleBySchoolId($school_id) {
        $this->switchToRole();
        
        return $this->getInfoByFk($school_id, 'school_id');
    }
    
    //修改用户自定义角色
    public function modifyRole($datas, $role_id) {
        $this->switchToRole();
        return $this->modify($datas, $role_id);        
    }
    
    //添加用户自定义角色
    public function addRole($datas, $is_return_id=false) {
        $this->switchToRole();
       
        return $this->add($datas, $is_return_id);        
    }
    
    //删除用户自定义角色
    public function delRoleById($role_id) {
        $this->switchToRole();
        return $this->delete($role_id);  
    }      
}