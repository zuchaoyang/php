<?php

/**
 * 班级对应的教师集合,
 * 1. 基本的get set del 方法封装在rBase Set 封装基本方法
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */

import('RData.RedisCommonKey');

class dSetClassTeacher extends rBaseSet {
    
    /**
     * 获取相应的Key
     * @param $id = class_code
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisCommonKey::getClassTeacherSetKey($id);
    }    
}
