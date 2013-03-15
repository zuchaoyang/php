<?php
class dBlogPersonType extends dBase {
    protected $_pk = 'id';
    protected $_tablename = 'wmw_blog_types_person_relation';
    protected $_fields = array(
                    'id',
                    'client_account',
                    'type_id',
              );
    protected $_index_list = array(
                    'id',
                    'client_account',
                    'type_id',
              );
              
    //根据班级关系ID获取信息列表          
    public function getBlogPersonTypeById($ids) {
        return $this->getInfoByPk($ids);
    }
    //根据client_account获取信息列表
    public function getBlogPersonTypeByUid($client_account) {
        //三维
        return $this->getInfoByFk($client_account, 'client_account');
    }
    
    //根据type_id获取信息列表
    public function getBlogPersonTypeByTypeId($type_ids) {
        if (empty($type_ids)) {
            return false;
        }
        
        return $this->getInfoByFk($type_ids, 'type_id');
    }
    
    /**
     * 根据班级client_account 获取日志类型列表和每个分类下的日志数量
     * 不包括草稿
     */
    public function getBlogNumsByUid($client_account) {
        if (empty($client_account)) {
            return false;
        }
        
        $sql = "SELECT count(*) nums, a.type_id from wmw_blog_person_relation as b inner join  wmw_blog as a  on
        	       " . " a.blog_id=b.blog_id where b.client_account='$client_account' and a.is_published=1 group by a.type_id";
        
        return $this->query($sql);
    }        
        
    //添加班级关系
    public function addBlogPersonType($data, $is_return_id) {
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
     * 			'client_account'=>"学生账号",
     * 			...
     *    );
     */
    public function getTypeInfo($wherearr, $orderby, $offset=null, $limit=null) {

        return $this->getInfo($wherearr, $orderby, $offset, $limit);
    }           
}