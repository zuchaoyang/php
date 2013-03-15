<?php
class dBlogClassType extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_blog_types_class_relation';
    protected $_fields = array(
                    'id',
                    'class_code',
                    'type_id',
              );
    protected $_index_list = array(
                    'id',
                    'class_code',
                    'type_id',
              );
    
    //根据班级关系ID获取信息列表          
    public function getBlogClassTypeById($ids) {
        return $this->getInfoByPk($ids);
    }
    //根据class_code获取信息列表
    public function getBlogClassTypeByClassCode($class_code) {
        //三维
        return $this->getInfoByFk($class_code, 'class_code');
    }
    
    //根据type_id获取信息列表
    public function getBlogClassTypeByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->getInfoByFk($type_ids, 'type_id');
    }
    
    /**
     * 根据班级class_code 获取日志类型列表和每个分类下的日志数量
     * 不包括草稿
     */
    public function getBlogNumsByClassCode($class_code) {
        if (empty($class_code)) {
            return false;
        }
        
        $sql = "SELECT count(*) nums, a.type_id from wmw_blog_class_relation as b inner join  wmw_blog as a  on
        	       " . " a.blog_id=b.blog_id where b.class_code='$class_code' and a.is_published=1 group by a.type_id";
        
        return $this->query($sql);
    }        
        
    //添加班级关系
    public function addBlogClassType($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据关系ID修改信息
    public function modifyById($data, $id) {
        return $this->modify($data, $id);
    }
    
    //根据关系ID删除信息
    public function delById($id) {
        return $this->delete($id);   
    }
    
	/**
     * 根据条件获取班级日志类型信息
     * @param array $wherearr
     * ef:
     * 	  $wherearr = array(
     * 			'class_code'=>"班级编号",
     * 			...
     *    );
     */
    public function getTypeInfo($wherearr, $orderby, $offset=null, $limit=null) {

        return $this->getInfo($wherearr, $orderby, $offset, $limit);
    }
    
}