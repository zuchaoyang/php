<?php

/**
 * 班级对应的孩子集合,
 * 1. 基本的get set del 方法封装在rBase Set 封装基本方法
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */

import('RData.RedisCommonKey');

class dSetClassStudent extends rBaseSet {
    
    /**
     * 获取相应的Key
     * @param $id = class_code
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisCommonKey::getClassStudentSetKey($id);
    }
}
