<?php

/**
 * 班级对应家长集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mSetClassFamily {
    protected $_dSetClassFamily = null;
    
    public function __construct() {
        import('RData.Common.dSetClassFamily');
        $this->_dSetClassFamily = new dSetClassFamily();
    }
    
    /**
     * 获取班级对应的家长集合
     * @param $id = class_code
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClassFamilyById($id, $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClassFamily->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);

             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientFamilyById($id);
                 } 
                 
                 $this->setClientFamilyById($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClassFamily->sGet($id);
    }
    
    /**
     * 设置班级对应的家长集合
     * @param $id = class_code
     * @param $parent_accounts = array()  client_account array
     */
    public function setClientFamilyById($id, $parent_accounts) {
        if(empty($id) || empty($parent_accounts)) {
            return false;
        }
        
        return $this->_dSetClassFamily->sSet($id, $parent_accounts);
    }    
    
    
    /**
     * 删除班级对应的家长集合
     * @param class_code
     * @param $id = client_account
     */
    public function delClientFamilyById($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClassFamily->keyDel($id);
    }     
    
    /**
     * 移除班级对应家长集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassFamilyByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClassFamily->sDels($id, $members);
    }          
    

    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mClientClass');
        $datas = $m->getClientClassByClassCode($id, array('client_type'=>CLIENT_TYPE_FAMILY));
        
    	/**
    	 * datas 结构如下:
    	 * Array
            (
                [1041] => Array
                    (
                        [31486597] => Array
                            (
                                [client_class_id] => 126472
                                [client_account] => 31486597
                                [class_code] => 1041
                                [client_class_role] => 
                                [teacher_class_role] => 
                                [class_admin] => 0
                                [add_time] => 1329190434
                                [add_account] => 85154685
                                [upd_account] => 
                                [upd_time] => 2012
                                [client_type] => 2
                            )
            
                        [90485134] => Array
                            (
                                [client_class_id] => 126473
                                [client_account] => 90485134
                                [class_code] => 1041
                                [client_class_role] => 
                                [teacher_class_role] => 
                                [class_admin] => 0
                                [add_time] => 1329190434
                                [add_account] => 85154685
                                [upd_account] => 
                                [upd_time] => 2012
                                [client_type] => 2
                            )
            )
    	 */  
        
        $datas = $datas[$id];
        //需要转换为真正的client_accounts 数组:
        
        $result = array();
        foreach ($datas as $key => $val) {
            $result[] = $key;
        }

        return $result;        
        
    }         
}
