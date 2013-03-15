<?php
class mBlogClassGrants extends mBase {
    protected $_dBlogClassGrants = null;
    
    public function __construct() {
        $this->_dBlogClassGrants = ClsFactory::Create('Data.Blog.dBlogClassGrants');
    }
    
    //添加班级日志权限
    public function addBlogClassGrants($data, $is_return_id) {
        return $this->_dBlogClassGrants->addBlogClassGrants($data, $is_return_id);
    }
    
    //根据权限ID修改信息
    public function modifyBlogClassGrants($data, $id) {
        return $this->_dBlogClassGrants->modifyBlogClassGrants($data, $id);
    }
    
    //根据权限ID删除信息
    public function delBlogClassGrants($id) {
        return $this->_dBlogClassGrants->delBlogClassGrants($id);   
    }
    
    //根据blog_id删除信息
    public function delGrantByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        return $this->_dBlogClassGrants->delGrantByBlogId($blog_id);
    }

    /**
     * 根据班级ID和日志ID修改信息
     */
    public function modifyBlogClassGrantByWhere($data, $where_arr) {
        if(empty($data) || empty($where_arr)) {
            return false;
        }
        
        return $this->_dBlogClassGrants->modifyBlogClassGrantByWhere($data, $where_arr);
    }

    /**
     * 根据where 条件获取班级日志权限列表
     * @param $where_arr
     * 注明： where 两个条件 1 班级class_code 只支持一个班级  
     * 2日志ids多个 最多200个
     * @param $orderby
     * @param $offset
     * @param $limit
     */
    public function getGrantInfo($where_arr, $orderby=null, $offset=0, $limit=10) {
        if (empty($where_arr)) {
            return false;
        }
        
        return $this->_dBlogClassGrants->getInfo($where_arr, $orderby, $offset, $limit);
    }
}