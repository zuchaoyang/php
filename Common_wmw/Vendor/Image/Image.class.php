<?php
define("VENDOR_IMAGE_DIR", dirname(__FILE__));
include_once VENDOR_IMAGE_DIR . "/ImageInterface.php";

class Image implements ImageInterface {
    /**
     * 图片缩放功能
     * @param $src_img
     * @param $dst_files
     */
    public function scale($src_img, $dst_files = array()) {
        $this->loadExtFile('ImageScale');
        $objImageScale = new ImageScale();
        
        return $objImageScale->scale($src_img, $dst_files);
    }
    
    /**
     * 加载image操作的扩展库
     * @param $filename
     */
    protected function loadExtFile($filename) {
        if(empty($filename)) {
            return false;
        }
        
        include_once VENDOR_IMAGE_DIR . "/ext/{$filename}.class.php";
    }
}