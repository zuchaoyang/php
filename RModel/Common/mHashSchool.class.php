<?php

/**
 * 学校基本信息管理封装
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mHashSchool {
    
    protected $_dHashSchool = null;
    
    public function __construct() {
        import('RData.Common.dHashSchool');
        $this->_dHashSchool = new dHashSchool();
    }
    
    /**
     * 获取学校对象,基本信息
     * @param $id
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getSchoolbyId($id, $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dHashSchool->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $result = $this->loader($id);
             $datas = $result[$id];
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delSchoolById($id);
                 }                       
                 
                 $this->setSchoolById($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dHashSchool->hashGet($id);
    }
    
    /**
     * 设置学校对象
     * @param $id
     * @param $datas
     */
    public function setSchoolById($id, $datas) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashSchool->hashSet($id, $datas);
    }
    
    
    /**
     * 删除学校对象
     * @param $id
     */
    public function delSchoolById($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashSchool->keyDel($id);
    }   
    
    /**
     * 删除学校哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delSchoolByField($id, $fields) {
        if(empty($id) || empty($fileds)) {
            return false;
        }
        
        return $this->_dHashSchool->hashDel($id, $fields);
    }     
    
    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mSchoolInfo');
        return $m->getSchoolInfoById($id);
    }     

}
