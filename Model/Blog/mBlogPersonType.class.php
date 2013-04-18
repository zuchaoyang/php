<?php
class mBlogPersonType extends mBase {
    protected $_dBlogPersonType = null;
    
    public function __construct() {
        $this->_dBlogPersonType = ClsFactory::Create('Data.Blog.dBlogPersonType');
    }
              
   //根据班级关系ID获取信息列表          
    public function getBlogPersonTypeById($ids) {
        return $this->_dBlogPersonType->getBlogPersonTypeById($ids);
    }
    
    //根据用户账号获取信息列表
    public function getBlogPersonTypeByUid($client_account) {
        //三维
        return $this->_dBlogPersonType->getBlogPersonTypeByUid($client_account);
    }
    
    //根据type_id获取信息列表
    public function getBlogPersonTypeByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->_dBlogPersonType->getBlogPersonTypeByTypeId($type_ids);
    }
    
    /**
     * 根据用户账号 获取日志类型列表和每个分类下的日志数量
     * @param $grant_where 权限条件
     * @param $client_account 要查询的账号
     */
    public function getBlogNumsByUid($client_account, $grant_where) {
        if (empty($client_account)) {
            return false;
        }

        $nums_list = $this->_dBlogPersonType->getBlogNumsByUid($client_account, $grant_where);
        
        if(empty($nums_list)) {
            return false;
        }
        
        //数据处理 用blog_id 作为键方便后期使用
        $new_nums_list = array();
        foreach($nums_list as $nums_info) {
            $key = $nums_info['type_id'];
            $new_nums_list[$key] = $nums_info;
        }
        
        return $new_nums_list;
    }
    
    
    //添加用户日志分类关系
    public function addBlogPersonType($data, $is_return_id = false) {
        return $this->_dBlogPersonType->addBlogPersonType($data, $is_return_id);
    }
    
    //根据关系ID修改信息
    public function modifyById($data, $id) {
        return $this->_dBlogPersonType->modifyById($data, $id);
    }
    
    //根据关系ID删除信息
    public function delById($id) {
        return $this->_dBlogPersonType->delById($id);   
    }
              
}