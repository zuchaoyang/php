<?php
/**
 * 文件下载类接口
 * @author Administrator
 *
 */
interface DownloadInterface {
    //文件下载方法
    public function downfile($pFileName, $downname = null);
}