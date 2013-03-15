<?php
class mBlogPersonRelation extends mBase {
    protected $_dBlogPersonRelation =  null;
    
    public function __construct() {
        $this->_dBlogPersonRelation = ClsFactory::Create('Data.Blog.dBlogPersonRelation');
    }
    
	/**
     * 通用的获取班级日志的函数
     * @param $class_codes
     * @param $where_appends
     * 注明：$where_appends只能是数组，并且一个元素只能包含一个过滤条件
     *       ef:
     *       $where_appends = array(
     *       	"add_time>='1000'",
     *       	"add_time<='2000'"
     *       );
     * @param $offset
     * @param $limit
     */
    public function getPersonBlogByUid($client_account, $where_appends, $orderby = null, $offset = 0, $limit = 10) {
        if(empty($client_account)) {
            return false;
        }
        
        return $this->_dBlogPersonRelation->getPersonBlogByUid($client_account, $where_appends, $orderby, $offset, $limit);
    }
    
    public function getBlogPersonRelationInfo($wheresql, $orderby=null, $offset=null, $limit=null){
        if(empty($wheresql)) {
            return false;
        }
        
        return $this->_dBlogPersonRelation->getInfo($wheresql, $orderby, $offset, $limit);
    }
    
    /**
     * 添加
     * @param $datas
     * @param $return_insert_id
     */
    public function addBlogPersonRelation($datas, $return_insert_id = false) {
        if(empty($datas)) {
            return false;
        }
        
        return $this->_dBlogPersonRelation->addBlogPersonRelation($datas, $return_insert_id);
    }
    
    /**
     * 修改
     * @param $datas
     * @param $id
     */
    public function modifyBlogPersonRelation($datas, $id) {
        if(empty($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dBlogPersonRelation->delBlogPersonRelation($datas, $id);
    }

    /**
     * 删除
     * @param  $id
     */
    public function delBlogPersonRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dBlogPersonRelation->delBlogPersonRelation($id);
    }
    
    //根据blog_id批量删除信息
    public function delBlogPersonRelationByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }

        return $this->_dBlogPersonRelation->delBlogPersonRelationByBlogID($blog_id);
    }

}