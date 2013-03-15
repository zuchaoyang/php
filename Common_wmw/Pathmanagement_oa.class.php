<?php

import("@.Common_wmw.Pathmanagement");

class Pathmanagement_oa extends Pathmanagement {
    
    private static $oa_department_path = 'oa/';

    
     /**
      * 获取部门图片上传路径
      * @return String
      */
     public static function uploadDepartmentImg() {
         return self::getWebroot() . self::getAttachment() . self::$oa_department_path;
     }
     
     /**
      * 获取部门图片显示路径
      * @return String
      */
     public static function getDepartmentImg() {
         return '/' . self::getAttachment() . self::$oa_department_path;
     }
}

?>