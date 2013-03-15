<?php

/**
 * 用户对应家庭成员集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */


class mSetClientParent {
    protected $_dSetClientParent = null;
    
    public function __construct() {
        import('RData.Common.dSetClientParent');
        $this->_dSetClientParent = new dSetClientParent();
    }
    
    /**
     * 获取用户对应的家长集合
     * @param $id = client_account
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClientParentByUid($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClientParent->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);
             
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientParentByUid($id);
                 }
                 
                 $this->setClientParentByUid($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClientParent->sGet($id);
    }
    
    /**
     * 设置用户对应的家长集合
     * @param $id = client_account
     * @param $datas = array()  client_account array
     */
    public function setClientParentByUid($id, $datas) {
        if(empty($id) || empty($datas)) {
            return false;
        }
        
        return $this->_dSetClientParent->sSet($id, $datas);
    }    
    
    
    /**
     * 删除用户对应的家长集合
     * @param $class_code
     * @param $id = client_account
     */
    public function delClientParentByUid($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClientParent->keyDel($id);
    }     
    
    /**
     * 移除用户对应的家长集合集合key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassParentByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClientParent->sDels($id, $members);
    }    

    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mFamilyRelation');
        $datas =  $m->getFamilyRelationByUid($id);
        
    	/**
    	 * datas 结构
    	 * Array
                (
                    [27898122] => Array
                        (
                            [956] => Array
                                (
                                    [relation_id] => 956
                                    [client_account] => 27898122
                                    [family_account] => 92334270
                                    [family_type] => 1
                                    [add_account] => 92334270
                                    [add_time] => 1340680157
                                )
                
                            [957] => Array
                                (
                                    [relation_id] => 957
                                    [client_account] => 27898122
                                    [family_account] => 61031785
                                    [family_type] => 1
                                    [add_account] => 61031785
                                    [add_time] => 1335161215
                                )
                
                        )
                
                )
    	 */         
        
        $datas = $datas[$id];
        //需要转换为真正的client_accounts 数组:

        $result = array();
        foreach ($datas as $key => $val) {
             if (isset($val['family_account'])) {
                 $result[] = $val['family_account'];
             } 
        }
        return $result;   
        
    }     

}
