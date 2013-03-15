<?php
/**
 * 文件的基本属性获取类
 * @author Administrator
 *
 */
abstract class AbstractFile {
    //允许下载的文件类型
    protected $allow_mine_types = array();
    
    /**
     * 设置系统允许的文件下载类型
     * @param $allow_mine_types
     */
    public function setAllowMineTypes($allow_mine_types) {
        if(!empty($allow_mine_types) && is_array($allow_mine_types)) {
            $this->allow_mine_types = (array)$allow_mine_types;
        }
    }
    
    /**
     * 检测文件的mine类型是否允许
     * @param $pFileName
     */
    public function check_mine($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $ext = $this->getFileExt($pFileName);
        
        return !empty($ext) && isset($this->allow_mine_types[$ext]) ? true : false;
    }
    
    /**
     * 获取文件的content_type的值
     * @param unknown_type $pFileName
     */
    public function getFileContentType($pFileName) {
        $ext = $this->getFileExt($pFileName);
        
        return !empty($ext) && isset($this->mine_types[$ext]) ? $this->mine_types[$ext] : "application/octet-stream";
    }
    
    /**
     * 获取文件的扩展名
     * @param $pFileName
     */
    public function getFileExt($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return pathinfo($pFileName, PATHINFO_EXTENSION);
    }
    
    abstract public function canRead($pFileName);
    abstract public function getFileContentLength($pFileName);
}

class LocalFile extends AbstractFile {
    
    /**
     * 判断文件是否可读
     * @param $pFileName
     */
    public function canRead($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return file_exists($pFileName) && is_readable($pFileName) ? true : false;
    }
    
    /**
     * 获取文件内容的大小
     * @param $pFileName
     */
    public function getFileContentLength($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return filesize($pFileName);
    }
}

/**
 * 远程文件的信息获取类
 * @author Administrator
 *
 */
class RemoteFile extends AbstractFile {
    /**
     * 判断远程文件是否可读
     * @param $pFileName
     */
    public function canRead($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $pFileName = trim($pFileName);
        
        $url_arr = parse_url($pFileName);
        if(empty($url_arr) || !is_array($url_arr)) {
            return false;
        }
        
        $host = $url_arr['host'];
        $path = $url_arr['path'] . (!empty($url_arr['query']) ? "?" . $url_arr['query'] : "");
        $port = isset($url_arr['port']) ? $url_arr['port'] : "80";
        
        //连接服务器
        $fp = fsockopen($host, $port, $err_no, $err_str, 5);
        if(!$fp) {
            return false;
        }
        
        //构造请求协议
        $request_str = "GET " . $path . " HTTP/1.1\r\n";
        $request_str .= "Host:" . $host . "\r\n";
        $request_str .= "Connection:Close\r\n\r\n";
        
        //发送请求
        fwrite($fp, $request_str);
        $first_header = fgets($fp, 1024);
        fclose($fp);
        
        return !empty($first_header) && preg_match("/200/", $first_header) ? true : false;
    }
    
    /**
     * 获取远程文件的大小
     * @param $pFileName
     */
    public function getFileContentLength($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        $response_headers = get_headers($pFileName, true);
        
        return $response_headers['Content-Length'];
    }
}