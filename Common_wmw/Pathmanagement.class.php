<?php
abstract class Pathmanagement {

    protected static $head_pic = 'head_pic/';
    protected static $attachment_path = 'attachment/';
    protected static $SERVER_PATH = '';
    private static $excel = 'excel/';
    
    /**
     * 得到项目的根目录
     * @return String
     */
    protected static function getWebroot() {
        return WEB_ROOT_DIR . "/";
    }
    
    /**
     * 得到SERVER_PATH
     * @return String
     */
    protected static function getServerPath() {
        return self::$SERVER_PATH;
    }
    
    /**
     * 得到Attachment路径
     * @return String
     */
    protected static function getAttachment() {
        return self::$attachment_path;
    }
    
    /**
     * 返回excel读取路径
     * @return String
     */
    protected static function getExcel() {
        return '/' . self::getAttachment() . self::$excel;
    }
    
	/**
     * 返回excel读取路径
     * @return String
     */
    public static function uploadExcel() {
        return self::getWebroot() . self::getAttachment() . self::$excel;
    }
}