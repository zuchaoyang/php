<?php
class mBlogTypes extends mBase {
    protected $_dBlogTypes = null;
    
    public function __construct(){
        $this->_dBlogTypes = ClsFactory::Create('Data.Blog.dBlogTypes');
    }

    //根据分类ID获取信息列表          
    public function getByTypeId($type_ids) {
        return $this->_dBlogTypes->getByTypeId($type_ids);
    }
    
    //添加分类
    public function addType($data, $is_return_id) {
        if (empty($data)) {
            return false;
        }
        
        return $this->_dBlogTypes->addType($data, $is_return_id);
    }
    
    //根据分类ID修改日志信息
    public function modifyByTypeId($data, $type_id) {
        return $this->_dBlogTypes->modifyByTypeId($data, $type_id);
    }
    
    public function modifyBlogTypes($type_datas, $type_id) {
        if(empty($type_datas) || !is_array($type_datas) || empty($type_id)) {
            return false;
        }
        
        return $this->_dBlogTypes->modifyBlogTypes($type_datas, $type_id);
    }
    
    
    //根据分类ID删除日志分类信息
    public function delBlogTypes($type_id) {
        return $this->_dBlogTypes->delBlogTypes($type_id);   
    }
    
}