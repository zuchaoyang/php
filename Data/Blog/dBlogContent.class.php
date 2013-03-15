<?php
class dBlogContent extends dBase {
    protected $_pk = 'blog_id';
    protected $_tablename = 'wmw_blog_content';
    protected $_fields = array(
                    'blog_id',
					'content',
              );
    protected $_index_list = array(
    				'blog_id',
              );
    
    //根据日志ID获取信息列表          
    public function getBlogContentById($blog_ids) {
        return $this->getInfoByPk($blog_ids);
    }
    
    //添加日志
    public function addContent($data) {
        return $this->add($data);
    }
    
    //根据日志ID修改日志信息
    public function modifyBlogContent($data, $blog_id) {
        return $this->modify($data, $blog_id);
    }
    
    //根据日志ID删除日志信息
    public function delByBlogId($blog_id) {
      
        return $this->delete($blog_id);   
    }
    
    //根据日志批量删除日志信息
    public function delAllByBlogIds($blog_ids) {
        if(empty($blog_ids)) {
            return false;
        }
        
        $blog_id_str = implode(',', (array)$blog_ids);
        $sql = "delete from {$this->_tablename} where blog_id in({$blog_id_str})";
        //二维
        return $this->execute($sql);
    }
}