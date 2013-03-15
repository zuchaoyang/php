<?php
class mBlogContent extends mBase {
    protected $_dBlogContent = null;
    
    public function __construct() {
        $this->_dBlogContent = ClsFactory::Create('Data.Blog.dBlogContent');        
    }
    
    //根据日志ID 获取日志内容
    public function getBlogContentById($blog_ids) {
        if(empty($blog_ids)) {
            return false;
        }

        $blog_content = $this->_dBlogContent->getBlogContentById($blog_ids);
        
        if(!empty($blog_content)){
            foreach($blog_content as $key => $val){
                $val['content'] = htmlspecialchars_decode($val['content']);
                $blog_content[$key] = $val;
            }
        }
        
        return !empty($blog_content) ? $blog_content : false;
    }
    
    //添加日志
    public function addContent($data, $is_return_id) {
        return $this->_dBlogContent->addContent($data, $is_return_id);
    }
    
    //根据日志ID修改日志信息
    public function modifyBlogContent($data, $blog_id) {
  
        return $this->_dBlogContent->modifyBlogContent($data, $blog_id);
    }
    
    //根据日志ID删除日志信息
    public function delByBlogId($blog_id) {
        return $this->_dBlogContent->delByBlogId($blog_id);   
    }
    
    //根据添加人删除日志信息
    public function delByAddAccount($add_accounts) {
        if(empty($add_accounts)) {
            return false;
        }
        //二维
        return $this->_dBlogContent->delByAddAccount($add_accounts);
    }
    
}