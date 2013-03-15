<?php

/**
 * 用户对应好友集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */


class mSetClientFriends {
    protected $_dSetClientFriends = null;
    
    public function __construct() {
        import('RData.Common.dSetClientFriends');
        $this->_dSetClientFriends = new dSetClientFriends();
    }
    
    /**
     * 获取用户对应的好友集合
     * @param $id = client_account
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClientFriendsByUid($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClientFriends->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);             
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientFriendsByUid($id);
                 }
                 
                 $this->setClientFriendsByUid($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClientFriends->sGet($id);
    }
    
    /**
     * 设置用户对应的好友集合
     * @param $id = client_account
     * @param $parent_accounts = array()  client_account array
     */
    public function setClientFriendsByUid($id, $datas) {
        if(empty($id) || empty($datas)) {
            return false;
        }
        
        return $this->_dSetClientFriends->sSet($id, $datas);
    }    
    
    /**
     * 删除用户对应的好友集合
     * @param $class_code
     * @param $id = client_account
     */
    public function delClientFriendsByUid($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClientFriends->keyDel($id);
    }     
    
    /**
     * 移除用户对应的好友集合key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassFriendsByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClientFriends->sDels($id, $members);
    }      
    

    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mAccountrelation');
        $datas =  $m->getAccountRelationByClientAccout($id);
        
        $datas = $datas[$id];
        //需要转换为真正的client_accounts 数组:
        
        $result = array();
        foreach ($datas as $key => $val) {
             if (isset($val['friend_account'])) {
                 $result[] = $val['friend_account'];
             } 
        }            

        return $result;
    }  
}
