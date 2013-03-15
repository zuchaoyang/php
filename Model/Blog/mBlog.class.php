<?php
class mBlog extends mBase {
    protected $_dBlog = null;
    
    public function __construct() {
        $this->_dBlog = ClsFactory::Create('Data.Blog.dBlog');
    }
    
    //根据日志ID获取信息列表          
    public function getBlogById($blog_ids) {
        return $this->_dBlog->getBlogById($blog_ids);
    }
    
    //根据日志分类type_id 获取日志列表
    public function getBlogByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->_dBlog->getBlogByTypeId($type_ids);
    }
    
    //添加日志
    public function addBlog($data, $is_return_id) {

        return $this->_dBlog->addBlog($data, $is_return_id);
    }
    
    //根据日志ID修改日志信息
    public function modifyBlog($blog_datas, $blog_id) {
        if(empty($blog_datas) || !is_array($blog_datas) || empty($blog_id)) {
            return false;
        }
        
        return $this->_dBlog->modifyByBlogId($blog_datas, $blog_id);
    }
    
    //根据日志ID删除日志信息
    public function delBlog($blog_id) {
        return $this->_dBlog->delBlog($blog_id);   
    }
    
   
}