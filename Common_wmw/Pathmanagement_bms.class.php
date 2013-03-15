<?php
import("@.Common_wmw.Pathmanagement");
class Pathmanagement_bms extends Pathmanagement {

    private static $school_scan = 'schoolscan_pic/';
    
    /**
     * 获取学校上传扫描件路径
     * @return string
     */
    public static function uploadSchoolScan() {
        return self::getWebroot() . self::getAttachment() . self::$school_scan;
    }
    
	/**
     * 获取显示学校扫描件路径
     * @return string
     */
    public static function getSchoolScan() {
        return '/' . self::getAttachment() . self::$school_scan;
    }
}
