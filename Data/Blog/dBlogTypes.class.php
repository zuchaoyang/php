<?php
class dBlogTypes extends dBase {
    protected $_pk = 'type_id';
    protected $_tablename = 'wmw_blog_types';
    protected $_fields = array(
                    'type_id',
                    'name',
                    'add_account',
                    'add_time',
              );
    protected $_index_list = array(
    				'type_id',
              );

    //根据分类ID获取信息列表          
    public function getByTypeId($type_ids) {

        return $this->getInfoByPk($type_ids);
    }
    
    //添加分类
    public function addType($data, $is_return_id) {
        return $this->add($data, $is_return_id);
    }
    
    //根据分类ID修改日志信息
    public function modifyByTyprId($data, $type_id) {
        return $this->modify($data, $type_id);
    }
    
    public function modifyBlogTypes($type_datas, $type_id) {

        return $this->modify($type_datas, $type_id);
    }
    
    
    //根据分类ID删除日志分类信息
    public function delBlogTypes($type_id) {
        return $this->delete($type_id);   
    }
}