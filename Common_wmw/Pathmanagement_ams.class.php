<?php
import("@.Common_wmw.Pathmanagement");
class Pathmanagement_ams extends Pathmanagement {
    
    private static $school_logo = 'school_logo/';
    private static $squadron = 'squadron/';
    
    /**
     * 得到school_logo 的上传路径
     * @return String
     */
    public static function uploadSchoolLogo() {
        return self::getWebroot() . self::getAttachment() . self::$school_logo;
    }
    
	/**
     * 得到school_logo 的显示路径
     * @return String
     */
    public static function getSchoolLogo() {
        return '/' . self::getAttachment() . self::$school_logo;
    }
    
    /**
     * 中队上传Logo路径
     * @return String
     */
    public static function uploadSquadronLogo($classcode) {
        return self::getWebroot() . self::getAttachment() . self::$squadron . $classcode . '/';
    }
    
	/**
     * 中队上传Logo显示
     * @return String
     */
    public static function getSquadronLogo($classCode) {
        return '/' . self::getAttachment() . self::$squadron . $classCode . '/';
    }
}
