<?php
class mBlogClassType extends mBase {
    protected $_dBlogClassType =  null;
    
    public function __construct() {
        $this->_dBlogClassType = ClsFactory::Create('Data.Blog.dBlogClassType');
    }
    
    //根据班级关系ID获取信息列表          
    public function getBlogClassTypeById($ids) {
        return $this->_dBlogClassType->getBlogClassTypeById($ids);
    }
    
    //根据class_code获取信息列表
    public function getBlogClassTypeByClassCode($class_code) {
        //三维
        return $this->_dBlogClassType->getBlogClassTypeByClassCode($class_code);
    }
    
    //根据type_id获取信息列表
    public function getBlogClassTypeByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->_dBlogClassType->getBlogClassTypeByTypeId($type_ids);
    }
    
    /**
     * 根据班级class_code 获取日志类型列表和每个分类下的日志数量
     */
    public function getBlogNumsByClassCode($class_code) {
        if (empty($class_code)) {
            return false;
        }

        $nums_list = $this->_dBlogClassType->getBlogNumsByClassCode($class_code);
        
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
    
    
    //添加班级关系
    public function addBlogClassType($data, $is_return_id = false) {
        return $this->_dBlogClassType->addBlogClassType($data, $is_return_id);
    }
    
    //根据关系ID修改信息
    public function modifyById($data, $id) {
        return $this->_dBlogClassType->modifyById($data, $id);
    }
    
    //根据关系ID删除信息
    public function delById($id) {
        return $this->_dBlogClassType->delById($id);   
    }
    
}