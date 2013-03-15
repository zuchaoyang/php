<?php
if(!defined('WMW_IMAGE_DIR')) {
    define('WMW_IMAGE_DIR', dirname(__FILE__));
}
include_once WMW_IMAGE_DIR . "/Vendor/Image/ImageInterface.php";
include_once WMW_IMAGE_DIR . "/Vendor/WmwAutoLoader.class.php";

class WmwImage implements ImageInterface {
    protected $objImage = null;
    
    public function __construct() {
        $this->objImage = new Image();
    }
    
    public function scale($src_img, $dst_files = array()) {
        return $this->objImage->scale($src_img, $dst_files);
    }
    
}