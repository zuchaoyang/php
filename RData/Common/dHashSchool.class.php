<?php

/**
 * 学校基本信息,
 * 1. 基本的get set del 方法封装在rBase HASH 封装基本方法
 * 2. 特殊业务要在本类中扩展编写
 * 
 * @author lnczx
 */
import('RData.RedisCommonKey');

class dHashSchool extends rBaseHash {
    
    /**
     * 获取相应的Key
     * @param $id
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }
        
        return RedisCommonKey::getSchoolHashKey($id);
    }
    
}
