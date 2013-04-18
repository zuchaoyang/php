<?php

/**
 * 用户与班级对应关系封装
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mHashClientClass {
    
    protected $_dHashClientClass = null;
    
    public function __construct() {
        import('RData.Common.dHashClientClass');
        $this->_dHashClientClass = new dHashClientClass();
    }
    
    /**
     * 获取用户班级关系,基本信息
     * @param $id
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClientClassbyUid($id, $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dHashClientClass->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);
             if ($refresh) {
                 $this->delClientClassbyUid($id);
             }                     
             if (!empty($datas)) {
                 
             
                 
                 $this->setClientClassbyUid($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dHashClientClass->hashGet($id);
    }
    
    /**
     * 设置用户班级关系
     * @param $id
     * @param $datas
     */
    public function setClientClassbyUid($id, $datas) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClientClass->hashSet($id, $datas);
    }
    
    
    /**
     * 删除用户班级关系
     * @param $id
     */
    public function delClientClassbyUid($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dHashClientClass->keyDel($id);
    }  

    /**
     * 删除用户班级关系哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClientClassByField($id, $fields) {
        if(empty($id) || empty($fields)) {
            return false;
        }
        
        return $this->_dHashClientClass->hashDel($id, $fields);
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
        $datas =  $m->getClientClassByUid($id);
        
        /** datas 格式如下:
         * Array
        (
            [56067742] => Array
                (
                [12823] => Array
                    (
                        [client_class_id] => 12823
                        [client_account] => 56067742
                        [class_code] => 146
                        [client_class_role] => 
                        [teacher_class_role] => 2
                        [class_admin] => 0
                        [add_time] => 1316514718
                        [add_account] => 85154685
                        [upd_account] => 
                        [upd_time] => 2012
                        [client_type] => 1
                    )
    
                [118416] => Array
                    (
                        [client_class_id] => 118416
                        [client_account] => 56067742
                        [class_code] => 975
                        [client_class_role] => 
                        [teacher_class_role] => 1
                        [class_admin] => 1
                        [add_time] => 1329100360
                        [add_account] => 85154685
                        [upd_account] => 85154685
                        [upd_time] => 1329100360
                        [client_type] => 1
                    )
                
         }       
         */
        
        //需要转换为真正的class_code array();
        
        $datas = $datas[$id];
        //需要转换为真正的class_code 数组:  即class_code作为下标
        
        $result = array();
        foreach ($datas as $key => $val) {
            $class_code = $val['class_code'];
            $result[$class_code] = $val;
              
        }

        return $result;        
    }     

}
