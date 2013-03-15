<?php
if(!defined('WMW_DOWNLOAD_DIR')) {
    define('WMW_DOWNLOAD_DIR', dirname(__FILE__));
}

include_once WMW_DOWNLOAD_DIR . "/Vendor/Download/DownloadInterface.php";
include_once WMW_DOWNLOAD_DIR . "/Vendor/WmwAutoLoader.class.php";

/**
 * 
 * @author anlicheng $2012-07-12
 * 文件下载类，支持多种文件类型的下载
 *
 */
class WmwDownload implements DownloadInterface {
    private $objDownload = null;
    
    public function __construct() {
        $this->objDownload = new Download();
    }
    
    /**
     * 文件下载类
     * @param $pFileName 下载文件的完整路径，支持本地下载和远程文件下载；远程文件必须以:http(s):\\开头
     * @param $downname  下载文件的文件名
     */
    public function downfile($pFileName, $downname = null) {
        return $this->objDownload->downfile($pFileName, $downname);
    }
}