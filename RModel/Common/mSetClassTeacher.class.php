<?php

/**
 * 班级对应老师集合
 * 注意: loader作为如果redis没有对应的key,则调用对应的Model方法，从数据库获取
 * @author lnczx
 */

class mSetClassTeacher {
    protected $_dSetClassTeacher = null;
    
    public function __construct() {
        import('RData.Common.dSetClassTeacher');
        $this->_dSetClassTeacher = new dSetClassTeacher();
    }
    
    /**
     * 获取班级对应的教师集合
     * @param $id = class_code
     * $param $refresh  true = 强制从数据库重新读取 ,默认为false
     */
    public function getClassTeacherById($id,  $refresh = false) {
        if(empty($id)) {
            return false;
        }
        
        $is_exist = $this->_dSetClassTeacher->isExist($id);
        //即使reids有对应的key,但是refresh为true也要从数据库读取
        if(!$is_exist || $refresh) {
             $datas = $this->loader($id);

             if (!empty($datas)) {
                 
                 if ($refresh) {
                     $this->delClientTeacherById($id);
                 }
                 
                 $this->setClientTeacherById($id, $datas);
             } else {
                 return false;
             }   
        }        
        
        return $this->_dSetClassTeacher->sGet($id);
    }
    
    /**
     * 设置班级对应的教师集合
     * @param $id = class_code
     * @param $parent_accounts = array()  client_account array
     */
    public function setClientTeacherById($id, $parent_accounts) {
        if(empty($id) || empty($parent_accounts)) {
            return false;
        }
        
        return $this->_dSetClassTeacher->sSet($id, $parent_accounts);
    }    
    
    
    /**
     * 删除班级对应的教师集合
     * @param class_code
     * @param $id = client_account
     */
    public function delClientTeacherById($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dSetClassTeacher->keyDel($id);
    }    

    /**
     * 移除班级对应教师集合 key 中的一个或多个 member 元素，不存在的 member 元素会被忽略。
     * @param $id
     * $param $fvalue  hash  field value
     */
    public function delClassTeacherByMember($id, $members) {
        if(empty($id) || empty($members)) {
            return false;
        }
        
        return $this->_dSetClassTeacher->sDels($id, $members);
    }      

    /**
     * 加载数据
     * @param $id
     */
    public function loader($id) {
        if(empty($id)) {
            return array();
        }
        
        $m = ClsFactory::Create('Model.mClassTeacher');
        
        $datas = $m->getClassTeacherByClassCode($id);
        /**
         * datas 结构如下:
         * Array
            (
                [1075] => Array
                    (
                        [11166] => Array
                            (
                                [class_teacher_id] => 11166
                                [client_account] => 56067742
                                [class_code] => 1075
                                [subject_id] => 904
                                [add_time] => 1329205069
                                [add_account] => 85154685
                                [upd_time] => 1329205069
                                [upd_account] => 85154685
                            )
            
                    )
            
            )
            Array
            (
                [1041] => Array
                    (
                        [11102] => Array
                            (
                                [class_teacher_id] => 11102
                                [client_account] => 41707047
                                [class_code] => 1041
                                [subject_id] => 897
                                [add_time] => 1329190400
                                [add_account] => 85154685
                                [upd_time] => 1329190400
                                [upd_account] => 85154685
                            )
            
                    )
            
            )
         */
    	        
        $datas = $datas[$id];
        //需要转换为真正的client_accounts 数组:
        
        $result = array();
        foreach ($datas as $key => $val) {
            $result[] = $val['client_account'];
        }

        return $result;    	        
    	        
    }
}
