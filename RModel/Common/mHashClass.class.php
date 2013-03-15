<?php

/**
 * 班级基本信息管理封装
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */


class mHashClass {
    
    protected $_dHashClass = null;
    
    public function __construct() {
        import('RData.Common.dHashClass');
        $this->_dHashClass = new dHashClass();
    }
    
    /**
     * 获取班级对象,基本信息
     * @param $id
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClassById($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dHashClass->isExist($id);
        
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $result = $this->loader($id);
             $datas = $result[$id];
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClassById($id);
                 }                 
                 
                 $this->setClassById($id, $datas);
             } else {
                 return false;
             }   
        }
        
        return $this->_dHashClass->hashGet($id);
    }
    
    /**
     * 设置班级对象
     * @param $id
     * @param $datas
     */
    public function setClassById($id, $datas) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClass->hashSet($id, $datas);
    }
    
    
    /**
     * 删除班级对象
     * @param $id
     */
    public function delClassById($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClass->keyDel($id);
    } 

    /**
     * 删除班级 哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassByField($id, $fields) {
        if(empty($id) || empty($fields)) {
            return false;
        }
        
        return $this->_dHashClass->hashDel($id, $fields);
    }    
    
    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mClassInfo');
        return $m->getClassInfoBaseById($id);
    }        

}
