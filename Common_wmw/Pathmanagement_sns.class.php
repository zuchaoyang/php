<?php
import("@.Common_wmw.Pathmanagement");
class Pathmanagement_sns extends Pathmanagement {
                    
    private static $photo_pic = 'photo_pic/';
    private static $talk = 'talk/';                     //得到用户说说图片路径
    private static $work = 'work/';                     //作业上传路径
    private static $jsupload = 'jsupload/';             //js上传路径
    private static $tmp = 'tmp/';                       //说说临时目录
    private static $resouce = 'uploads/';               //用户上传资源的目录
    private static $homework = 'homework/';            //班级作业上传附件路径
    private static $xheditorimgpath = 'xheditor/';     //xheditor编辑器图片上传路径
    private static $exam_tpl_path = 'exam/';     //成绩导入模板的保存路径
    private static $private_msg_img = 'private_msg_img/'; //私信图片存储路径    
    
    private static $mood_img_path = 'mood_pic/';      //说说的存放目录
    
    
    /** 获取用户头像显示路径
	 * $photoname 数据库存储的头像名称
	 * $account 用户账号
	 * @return 头像显示路径
	*/
     public static function getHeadImg($account) {
         return '/' . self::getAttachment() . self::$head_pic . $account.'/';
     }
     
	/** 获取用户头像显上传路径
	 * $photoname 数据库存储的头像名称
	 * $account 用户账号
	 * @return 头像显示路径
	*/
     public static function uploadHeadImg() {
         return self::getWebroot() . self::getAttachment() . self::$head_pic;
     }

	/** 获取用户相册图片路径
	 * $account 用户账号
	 * @return 相册封面显示路径
	*/
	public static function getAlbum($account){
		return '/' . self::getAttachment() . self::$photo_pic . $account.'/';
	}
	
	/** 获取用户相册图片上传路径
	 * @return 用户图片路径
	*/
	public static function uploadAlbum($account){
		return self::getWebroot() . self::getAttachment() . self::$photo_pic . $account;
	}
	
	/**
	 * 得到用户说说图片路径
	 * @return String
	 */
	public static function getTalkIco() {
	    return '/' . self::getAttachment() . self::$talk;
	}
	/**
	 * 得到用户说说图片上传路径
	 * @return String
	 */
	public static function uploadTalkIco() {
	    return self::getWebroot() . self::getAttachment() . self::$talk;
	}

	
	/**
	 * 得到用户说说图片临时路径
	 * @return String
	 */
	public static function getTalktmp() {
	    return '/' . self::getAttachment() . self::$talk . self::$tmp;
	}
	
	/**
	 * 得到用户说说图片上传临时路径
	 * @return String
	 */
	public static function uploadTalktmp() {
	    return self::getWebroot() . self::getAttachment() . self::$talk . self::$tmp;
	}
	
	/**
	 * 获取显示相册图片路径
	 */
	public static function getjsupload() {
	    return '/' . self::getAttachment() . self::$jsupload;
	}
	
	/**
	 * 获取上传相册图片路径
	 */
	public static function uploadjsupload() {
	    return self::getWebroot() . self::getAttachment() . self::$jsupload;
	}
	
	 /**
     * 获取用户上传文件路径
     */
    public static function uploadResource() {
        return self::getWebroot() . self::getAttachment() . self::$resouce;
    }
    
    public function getResource() {
        return "/" . self::getAttachment() . self::$resouce;
    }
    
    /**
     * 获取说说的附件的存放路径
     */
    public static function uploadMood() {
        return self::getWebroot() . self::getAttachment() . self::$mood_img_path;
    }
    
    /**
     * 获取说说附件的显示路径
     */
    public static function getMood() {
        return "/" . self::getAttachment() . self::$mood_img_path;
    }
    
    /*
     * 上传班级作业附件的路径
     */
    public function uploadHomework() {
        return  self::getAttachment() . self::$homework;
    }
    
    public function getHomework() {
        return self::getAttachment() . self::$homework;
    }
    
    /*
     * xheditor编辑器图片上传路径
     */
    public function uploadXheditor() {
        return self::getWebroot() . self::getAttachment() . self::$xheditorimgpath;
    }
    
    /**
     * 私信图片上传路径
     */
    public function upload_private_msg_img() {
        return self::getWebroot() . self::getAttachment() . self::$private_msg_img;
    }
    
	/**
     * 私信图片读取路径
     */
    public function get_private_msg_img() {
        return "/" . self::getAttachment() . self::$private_msg_img;
    }
    
    /*
     * 获取xheditor编辑图片的路径
     */
    public function getXheditorimgPath() {
        return "/" .  self::getAttachment() . self::$xheditorimgpath;
    }
    
	/**
	 * 获取成绩导入模板的保存路径
	 * @return String
	 */
	public static function getExamTplPath() {
	    return self::getWebroot() . self::getExcel() . self::$exam_tpl_path;
	}
    
	
	/*
     * 获取xheditor编辑图片上传的路径 (绝对路径)
     */
    public function uploadXheditorimgPathTmp() {
        return self::getWebroot() . self::getAttachment() . self::$tmp . self::$xheditorimgpath;
    }
    
    /*
     * 获取xheditor编辑图片的路径 (相对路径)
     */
    public function getXheditorimgPathTmp() {
        return "/" . self::getAttachment() . self::$tmp . self::$xheditorimgpath;
    }
    
    /**
     * 获取临时文件路径（绝对路径）
     * 
     */
    public function tmpPath() {
        return self::getWebRoot() . self::$attachment_path . self::$tmp;
    }
    
    /**
     * 获取临时文件路径 （相对路径 ）
     */
    public function getTmpPath() {
         return '/' . self::$attachment_path . self::$tmp;
    }
    
}