<?php
class dBlogClassGrants extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_blog_class_grants';
    protected $_fields = array(
                    'id',
                    'class_code',
                    'blog_id',
                    'grant',
              );
    protected $_index_list = array(
                    'id',
                    'class_code',
                    'blog_id',
              );
    
    /**
     * 根据条件获取班级日志权限表信息
     * @param array $wherearr
     * ef:
     *    $wherearr = array(
     *    		'blog_id'=>"日志ID",
     *    		...
     *    )
     */
    public function getGrantInfo($wherearr) {
        if(empty($wherearr)) {
            return false;
        }
        //二维
        return $this->getInfo($wherearr);
    }
    
    //添加班级日志权限
    public function addBlogClassGrants($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据权限ID修改信息
    public function modifyBlogClassGrants($data, $id) {
        return $this->modify($data, $id);
    }
    
    //根据权限ID删除信息
    public function delBlogClassGrants($id) {
        return $this->delete($id);   
    }
    
    //根据blog_id批量删除信息
    public function delGrantByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $blog_id_str = implode(',', (array)$blog_id);
        $sql = "delete from {$this->_tablename} where blog_id in({$blog_id_str})";
        
        return $this->execute($sql);
    }
    

    //根据班级ID和日志ID修改信息
    public function modifyBlogClassGrantByWhere($data, $where_arr) {
        if(empty($data) || empty($where_arr)) {
            return false;
        }
        $set_str = $this->joinFields($data);
        $where_str = !empty($where_arr) ? "where " . implode(' and ', (array)$where_arr) : "";
        $sql = "update {$this->_tablename} set $set_str $where_str";

        return $this->execute($sql);
    }
    
    /**
     * 
     */
    
    
}