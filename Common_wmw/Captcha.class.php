<?php
if (!defined('WMW_COMMON')) {
    define('WMW_COMMON', dirname(__FILE__));
}
include WMW_COMMON . "/Vendor/Captcha/simple-php-captcha.php";

class Captcha {

    public function __construct() {
    
    }
    
    public function showCaptcha() {
        session_start();     
        
        $_SESSION['captcha'] = captcha();
//        return '<img src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA" />';
        return $_SESSION['captcha'];
    }
    
}