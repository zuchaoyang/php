<?php
if(!defined("VENDOR_DOWN_LOAD_DIR")) {
    define("VENDOR_DOWN_LOAD_DIR", dirname(__FILE__));
}

include_once VENDOR_DOWN_LOAD_DIR . "/DownloadInterface.php";
include_once VENDOR_DOWN_LOAD_DIR . "/ext/DownFile.class.php";

/**
 * 
 * @author anlicheng $2012-07-12
 * 文件下载类，支持多种文件类型的下载
 *
 */
class Download implements DownloadInterface {
    
     protected $mine_types = array(
    	'ez' => 'application/andrew-inset', 
		'hqx' => 'application/mac-binhex40', 
		'cpt' => 'application/mac-compactpro',
		'doc' => 'application/msword', 
		'bin' => 'application/octet-stream', 
		'dms' => 'application/octet-stream', 
		'lha' => 'application/octet-stream', 
		'lzh' => 'application/octet-stream', 
		'exe' => 'application/octet-stream', 
		'class' => 'application/octet-stream', 
		'so' => 'application/octet-stream', 
		'dll' => 'application/octet-stream', 
		'oda' => 'application/oda', 
		'pdf' => 'application/pdf', 
		'ai' => 'application/postscrīpt', 
		'eps' => 'application/postscrīpt', 
		'ps' => 'application/postscrīpt', 
		'smi' => 'application/smil', 
		'smil' => 'application/smil', 
		'mif' => 'application/vnd.mif', 
		'xls' => 'application/vnd.ms-excel', 
		'ppt' => 'application/vnd.ms-powerpoint', 
		'wbxml' => 'application/vnd.wap.wbxml', 
		'wmlc' => 'application/vnd.wap.wmlc', 
		'wmlsc' => 'application/vnd.wap.wmlscrīptc', 
		'bcpio' => 'application/x-bcpio', 
		'vcd' => 'application/x-cdlink', 
		'pgn' => 'application/x-chess-pgn', 
		'cpio' => 'application/x-cpio', 
		'csh' => 'application/x-csh', 
		'dcr' => 'application/x-director', 
		'dir' => 'application/x-director', 
		'dxr' => 'application/x-director', 
		'dvi' => 'application/x-dvi', 
		'spl' => 'application/x-futuresplash', 
		'gtar' => 'application/x-gtar', 
		'hdf' => 'application/x-hdf', 
		'js' => 'application/x-javascrīpt', 
		'skp' => 'application/x-koan', 
		'skd' => 'application/x-koan', 
		'skt' => 'application/x-koan', 
		'skm' => 'application/x-koan', 
		'latex' => 'application/x-latex', 
		'nc' => 'application/x-netcdf', 
		'cdf' => 'application/x-netcdf', 
		'sh' => 'application/x-sh', 
		'shar' => 'application/x-shar', 
		'swf' => 'application/x-shockwave-flash', 
		'sit' => 'application/x-stuffit', 
		'sv4cpio' => 'application/x-sv4cpio', 
		'sv4crc' => 'application/x-sv4crc', 
		'tar' => 'application/x-tar', 
		'tcl' => 'application/x-tcl', 
		'tex' => 'application/x-tex', 
		'texinfo' => 'application/x-texinfo', 
		'texi' => 'application/x-texinfo', 
		't' => 'application/x-troff', 
		'tr' => 'application/x-troff', 
		'roff' => 'application/x-troff', 
		'man' => 'application/x-troff-man', 
		'me' => 'application/x-troff-me', 
		'ms' => 'application/x-troff-ms', 
		'ustar' => 'application/x-ustar', 
		'src' => 'application/x-wais-source', 
		'xhtml' => 'application/xhtml+xml', 
		'xht' => 'application/xhtml+xml', 
		'zip' => 'application/zip', 
		'au' => 'audio/basic', 
		'snd' => 'audio/basic', 
		'mid' => 'audio/midi', 
		'midi' => 'audio/midi', 
		'kar' => 'audio/midi', 
		'mpga' => 'audio/mpeg', 
		'mp2' => 'audio/mpeg', 
		'mp3' => 'audio/mpeg',
		'wma' => 'audio/mpeg', 
		'aif' => 'audio/x-aiff', 
		'aiff' => 'audio/x-aiff', 
		'aifc' => 'audio/x-aiff', 
		'm3u' => 'audio/x-mpegurl', 
		'ram' => 'audio/x-pn-realaudio', 
		'rm' => 'audio/x-pn-realaudio', 
		'rpm' => 'audio/x-pn-realaudio-plugin', 
		'ra' => 'audio/x-realaudio', 
		'wav' => 'audio/x-wav', 
		'pdb' => 'chemical/x-pdb', 
		'xyz' => 'chemical/x-xyz', 
		'bmp' => 'image/bmp', 
		'gif' => 'image/gif', 
		'ief' => 'image/ief', 
		'jpeg' => 'image/jpeg', 
		'jpg' => 'image/jpeg', 
		'jpe' => 'image/jpeg', 
		'png' => 'image/png', 
		'tiff' => 'image/tiff', 
		'tif' => 'image/tiff', 
		'djvu' => 'image/vnd.djvu', 
		'djv' => 'image/vnd.djvu', 
		'wbmp' => 'image/vnd.wap.wbmp', 
		'ras' => 'image/x-cmu-raster', 
		'pnm' => 'image/x-portable-anymap', 
		'pbm' => 'image/x-portable-bitmap', 
		'pgm' => 'image/x-portable-graymap', 
		'ppm' => 'image/x-portable-pixmap', 
		'rgb' => 'image/x-rgb', 
     	'rar' => "application/octet-stream",
		'xbm' => 'image/x-xbitmap', 
		'xpm' => 'image/x-xpixmap', 
		'xwd' => 'image/x-xwindowdump', 
		'igs' => 'model/iges', 
		'iges' => 'model/iges', 
		'msh' => 'model/mesh', 
		'mesh' => 'model/mesh', 
		'silo' => 'model/mesh', 
		'wrl' => 'model/vrml', 
		'vrml' => 'model/vrml', 
		'css' => 'text/css', 
		'html' => 'text/html', 
		'htm' => 'text/html', 
		'asc' => 'text/plain', 
		'txt' => 'text/plain', 
		'rtx' => 'text/richtext', 
		'rtf' => 'text/rtf', 
		'sgml' => 'text/sgml', 
		'sgm' => 'text/sgml', 
		'tsv' => 'text/tab-separated-values', 
		'wml' => 'text/vnd.wap.wml', 
		'wmls' => 'text/vnd.wap.wmlscrīpt', 
		'etx' => 'text/x-setext', 
		'xsl' => 'text/xml', 
		'xml' => 'text/xml', 
		'mpeg' => 'video/mpeg', 
		'mpg' => 'video/mpeg', 
		'mpe' => 'video/mpeg', 
		'qt' => 'video/quicktime', 
		'mov' => 'video/quicktime', 
		'mxu' => 'video/vnd.mpegurl', 
		'avi' => 'video/x-msvideo', 
		'movie' => 'video/x-sgi-movie', 
		'wmv' => 'application/x-mplayer2',
		'ice' => 'x-conference/x-cooltalk', 
     	'voc' => 'application/octet-stream',
        'vob' => 'application/octet-stream',
        'mpa' => 'video/mpeg',
        'mic' => 'application/octet-stream',
        'mka' => 'application/octet-stream',
        'rmvb' => 'application/octet-stream',
        '3gp' => 'video/3gpp',
        'amv' => 'application/octet-stream',
        'mp4' => 'video/mp4',
        'mkv' => 'application/octet-stream',
        'asf' => 'video/x-ms-asf',
        'dat' => 'application/octet-stream',
        'tga' => 'application/octet-stream',
        'itf' => 'application/x-idealist-topscript',
        'pgx' => 'application/octet-stream',
        'chm' => 'application/octet-stream',
        'wps' => 'application/kswps',
    );
    
    /**
     * 文件下载类
     * @param $pFileName 下载文件的完整路径，支持本地下载和远程文件下载；远程文件必须以:http(s):\\开头
     * @param $downname  下载文件在客服端保存的文件名,不包括文件的后缀名
     */
    public function downfile($pFileName, $downname = null) {
        if(empty($pFileName)) {
            return false;
        }
        
        //动态生成文件操作对象
        $fileObj = $this->isRemoteFile($pFileName) ? new RemoteFile() : new LocalFile();
        $fileObj->setAllowMineTypes($this->mine_types);
  
         //检测文件是否可下载
        if(!$fileObj->check_mine($pFileName)) {
            throw new Exception("不支持该类文件下载!", -1);
        } else if($this->isRemoteFile($pFileName) && !$this->allow_url_fopen()) {
            throw new Exception("系统限制了远程访问!", -2);
        } else if(!$fileObj->canRead($pFileName)) {
            throw new Exception('文件不可读!', -3);
        }
        
        //文件下载显示的名称信息
        if(empty($downname)) {
            $downname = pathinfo($pFileName, PATHINFO_BASENAME);
        } else {
            $downname .= "." . pathinfo($pFileName, PATHINFO_EXTENSION);
        }
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(stripos($user_agent, 'MSIE') !== false) {
            $downname = 'filename="' . str_replace('+', '%20', urlencode($downname)) . '"';
        } elseif(stripos($user_agent, 'Firefox') !== false) {
            $downname = 'filename*="utf8\'\'' . $downname . '"';
        } else {
            $downname = 'filename="' . $downname . '"';
        }
        
        $content_type = $fileObj->getFileContentType($pFileName);
        $content_length = $fileObj->getFileContentLength($pFileName);
        
        //输出文件内容信息
        ob_end_clean();
        ob_start();
        
        header('Cache-control: max-age=31536000'); 
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT'); 
		header('Content-Type: ' . $content_type);
		header('Content-Disposition:attachment;' . $downname); 
		header('Content-Length:' . $content_length);
		
		echo file_get_contents($pFileName);
		
        ob_end_flush();
        
    }
    
    /**
     * 检测系统是否开启了远程访问权限
     */
    private function allow_url_fopen() {
        return !!ini_get('allow_url_fopen') ? true : false;
    }
    
    /**
     * 判断是否是远程文件
     */
    private function isRemoteFile($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return preg_match("/^http(s)?:\/\/(.+)$/", trim($pFileName)) ? true : false;
    }
}