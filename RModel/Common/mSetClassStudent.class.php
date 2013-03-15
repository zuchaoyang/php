<?php

/**
 * 班级对应学生集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mSetClassStudent {
    protected $_dSetClassStudent = null;
    
    public function __construct() {
        import('RData.Common.dSetClassStudent');
        $this->_dSetClassStudent = new dSetClassStudent();
    }
    
    /**
     * 获取班级对应的学生集合
     * @param $id = class_code
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClassStudentById($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClassStudent->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);
             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientStudentById($id);
                 }                     
                 
                 $this->setClientStudentById($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClassStudent->sGet($id);
    }
    
    /**
     * 设置班级对应的学生集合
     * @param $id = class_code
     * @param $parent_accounts = array()  client_account array
     */
    public function setClientStudentById($id, $parent_accounts) {
        if(empty($id) || empty($parent_accounts)) {
            return false;
        }
        
        return $this->_dSetClassStudent->sSet($id, $parent_accounts);
    }    
    
    
    /**
     * 删除班级对应的学生集合
     * @param class_code
     * @param $id = client_account
     */
    public function delClientStudentById($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClassStudent->keyDel($id);
    }     
    
    /**
     * 移除班级对应学生集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassStudentByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClassStudent->sDels($id, $members);
    }      
    

    /**
     * 加载数据
     * @param $id
     * $return client_account  array
     * e.g   array(56067742, 48117718);
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mClientClass');
        $datas =  $m->getClientClassByClassCode($id, array('client_type'=>CLIENT_TYPE_STUDENT));
           
        /**
	     * datas格式如下
	     * 
	     *  [56067742] = array(
	     *      [26354339] => Array
                (
                    [client_class_id] => 2400661
                    [client_account] => 26354339
                    [class_code] => 146
                    [client_class_role] => 
                    [teacher_class_role] => 
                    [class_admin] => 0
                    [add_time] => 1352450079
                    [add_account] => 85154685
                    [upd_account] => 
                    [upd_time] => 1352450079
                    [client_type] => 0
                )
        
            	[38762459] => Array
                (
                    [client_class_id] => 2412199
                    [client_account] => 38762459
                    [class_code] => 146
                    [client_class_role] => 
                    [teacher_class_role] => 
                    [class_admin] => 0
                    [add_time] => 1352683089
                    [add_account] => 85154685
                    [upd_account] => 
                    [upd_time] => 1352683089
                    [client_type] => 0
                )
	     *
	     *
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
