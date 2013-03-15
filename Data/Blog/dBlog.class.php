<?php
class dBlog extends dBase {
    protected $_pk = 'blog_id';
    protected $_tablename = 'wmw_blog';
    protected $_fields = array(
                    'blog_id',
                    'title',
                    'type_id',
                    'views',
                    'is_published',
                    'add_account',
                    'add_time',
                    'upd_account',
                    'upd_time',
                    'contentbg',
                    'summary',
                    'comments',
              );
    protected $_index_list = array(
    				'blog_id',
                    'add_account',
    				'type_id'
              );
    //根据日志ID获取信息列表          
    public function getBlogById($blog_ids) {
        return $this->getInfoByPk($blog_ids);
    }
    
    //根据日志分类type_id 获取日志列表
    public function getBlogByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->getInfoByFk($type_ids, 'type_id');
    }
    
    //添加日志
    public function addBlog($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据日志ID修改日志信息
    public function modifyByBlogId($data, $blog_id) {
        return $this->modify($data, $blog_id);
    }
    
    //根据日志ID删除日志信息
    public function delBlog($blog_id) {
        return $this->delete($blog_id);   
    }
    
 
}