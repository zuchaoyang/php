<?php

/**
 * 用户基本信息管理封装
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mHashClient {
    
    protected $_dHashClient = null;
    
    public function __construct() {
        import('RData.Common.dHashClient');
        $this->_dHashClient = new dHashClient();
    }
    
    /**
     * 获取用户对象,基本信息
     * @param $id
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClientbyUid($id, $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dHashClient->isExist($id);
        
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $result = $this->loader($id);
             $datas = $result[$id];
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->setClientByUid($id);
                 }                  
                 
                 $this->setClientByUid($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dHashClient->hashGet($id);
    }
    
    /**
     * 设置用户对象
     * @param $id
     * @param $datas
     */
    public function setClientByUid($id, $datas) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClient->hashSet($id, $datas);
    }
    
    /**
     * 删除用户对象
     * @param $id
     */
    public function delClientByUid($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClient->keyDel($id);
    }   
    
    /**
     * 删除用户哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClientByField($id, $fields) {
        if(empty($id) || empty($fileds)) {
            return false;
        }
        
        return $this->_dHashClient->hashDel($id, $fields);
    }       
    
    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mUser');
        return  $m->getUserBaseByUid($id);
    }      

}
