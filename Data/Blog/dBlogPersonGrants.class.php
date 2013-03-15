<?php
class dBlogPersonGrants extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_blog_person_grants';
    protected $_fields = array(
                    'id',
                    'client_account',
                    'blog_id',
                    'grant',
              );
    protected $_index_list = array(
                    'id',
                    'client_account',
                    'blog_id',
              );
              
    //根据个人日志权限ID获取信息列表          
    public function getById($ids) {
        return $this->getInfoByPk($ids);
    }
    //根据client_account获取信息列表
    public function getListByClientAccount($client_account) {
        //三维
        return $this->getInfoByFk($client_account, 'client_account');
    }
    //添加日志权限
    public function addGrant($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据个人权限ID修改信息
    public function modifyById($data, $id) {
        return $this->modify($data, $id);
    }
    
    //根据权限ID删除信息
    public function delById($id) {
        return $this->delete($id);   
    }
    
    //根据client_account批量删除信息
    public function delAllByClientAccount($client_account) {
        if(empty($client_account)) {
            return false;
        }
        
        $client_account_str = implode(',', (array)$client_account);
        $sql = "delete from {$this->_tablename} where client_account in({$client_account_str})";
        //二维
        return $this->execute($sql);
    }      
    //根据blog_id批量删除信息
    public function delAllByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $blog_id_str = implode(',', (array)$blog_id);
        $sql = "delete from {$this->_tablename} where blog_id in({$blog_id_str})";
        //二维
        return $this->execute($sql);
    }                                           
}