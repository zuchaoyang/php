<?php

/**
 * 用户对应孩子集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mSetClientChildren {
    protected $_dSetClientChildren = null;
    
    public function __construct() {
        import('RData.Common.dSetClientChildren');
        $this->_dSetClientChildren = new dSetClientChildren();
    }
    
    /**
     * 获取用户对应的孩子集合
     * @param $id = client_account
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClientChildrenByUid($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClientChildren->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientChildrenByUid($id);
                 }
                 
                 $this->setClientChildrenByUid($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClientChildren->sGet($id);
    }
    
    /**
     * 设置用户对应的孩子集合
     * @param $id = client_account
     * @param $parent_accounts = array()  client_account array
     */
    public function setClientChildrenByUid($id, $parent_accounts) {
        if(empty($id) || empty($parent_accounts)) {
            return false;
        }
        
        return $this->_dSetClientChildren->sSet($id, $parent_accounts);
    }    
    
    
    /**
     * 删除用户对应的孩子集合
     * @param $class_code
     * @param $id = client_account
     */
    public function delClientChildrenByUid($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClientChildren->keyDel($id);
    }     
    
    /**
     * 移除用户对应的孩子集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClientChildrenByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClientChildren->sDels($id, $members);
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
        $datas =  $m->getFamilyRelationByFamilyUid($id);
        
    	/**
    	 * $datas 结构如下
    	 Array
            (
                [61031785] => Array
                    (
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
             if (isset($val['client_account'])) {
                 $result[] = $val['client_account'];
             } 
        }        
        
        return $result;
        
    }     
}
