<?php
/**
 * 图片缩放功能
 * @author anlicheng $2012-7-17 
 * @param $allow_type 允许缩放的图片格式限制
 * 
 * 对外接口函数:scale($src_img, $dst_files);
 * 参数格式说明: $src_img: 源图片文件完整路径，如:/home/src.jpg
 * 				$dst_files:
 *                         array(
 *                           	array(
 *                        			'path' => '目标图片完整路径,如:/home/test.jpg'
 *                        			'scale' => '图片缩放比列,大于0的正整数'
 *                         		),
 *                         )
 */
class ImageScale {
    //允许缩放成的文件后缀名
    protected $allow_types = array(
        'jpg',
        'gif',
        'png',
    );
    
    /**
     * 图片缩放函数
     * @param $src_img 源图片文件完整路径，如:/home/src.jpg
     * @param $dst_files,数据格式
     * array(
     * 		array(
     * 			'path' => '目标图片完整路径,如:/home/test.jpg'
     * 			'scale' => '图片缩放比列,大于0的正整数'
     * 		),
     * )
     */
    public function scale($src_img, $dst_files) {
        if(empty($src_img) || empty($dst_files)) {
            return false;
        }
        
        //异常处理
        $this->_initEnv();
        $this->_checkSrcFile($src_img);
        $this->_checkDstFiles($dst_files);
        
        //加载源图片信息
        $im = $this->loadImg($src_img);
        //判断图片资源是否加载成功
        if(!is_resource($im)) {
            throw new Exception('源文件加载失败!');
        }
        
        foreach($dst_files as $file_attrs) {
            $this->createImg($im, $file_attrs['path'], $file_attrs['scale']);
        }
        imagedestroy($im);
        
        return true;
    }
    
    /**
     * 环境监测
     */
    protected function _initEnv() {
        if(!extension_loaded('gd')) {
            throw new Exception('图片缩放功能需要GD库的支持!');
        }
    }
    
    /**
     * 检测源文件的相关信息
     * @param $src_img
     */
    protected function _checkSrcFile($src_img) {
        if(empty($src_img) || !file_exists($src_img) || !is_readable($src_img)) {
            throw new Exception('源文件不存在或不可读!');
        }
        
        $ext = $this->getImgExt($src_img);
        if(empty($ext) || !in_array($ext, $this->allow_types)) {
            throw new Exception('图片类型不符合,系统暂不支持:' . $ext . "文件的缩放!");
        }
        
        return true;
    }
    
    /**
     * 检测目标文件设置的相关信息
     * @param $dst_files
     */
    protected function _checkDstFiles($dst_files) {
        if(empty($dst_files) || !is_array($dst_files)) {
            throw new Exception('目标文件不能为空或数据格式错误!');
        }
        
        foreach($dst_files as $file_attrs) {
            if(!isset($file_attrs['path'], $file_attrs['scale'])) {
                throw new Exception("目标文件信息不全!");
                break;
            }
            
            $ext = $this->getImgExt($file_attrs['path']);
            if(empty($ext) || !in_array($ext, $this->allow_types)) {
                throw new Exception("系统暂时不支持缩放成{$ext}格式的图片!");
                break;
            }
            
            $dir = dirname($file_attrs['path']);
            //如果目录不存在，尝试递归创建目录
            !is_dir($dir) && self::mkDirRecursion($dir);
            
            if(!is_dir($dir)) {
                throw new Exception("系统无法创建目录{$dir},请手动创建 !");
                exit;
            }
            
            if(intval($file_attrs['scale']) <= 0) {
                throw new Exception("缩放比例必须大于0!");
                break;
            }
        }
        
        return true;
    }
    
    /**
     * 加载图片文件信息
     * @param $src_img
     */
    protected function loadImg($src_img) {
        if(empty($src_img)) {
            return false;
        }
        
        switch($this->getImgExt($src_img)) {
            case 'jpg':
                $im = imagecreatefromjpeg($src_img);
                break;
            case 'gif':
                $im = imagecreatefromgif($src_img);
                break;
            case 'png':
                $im = imagecreatefrompng($src_img);
                break;
        }
        
        return $im;
    }
    
    /**
     * 获取图片的高
     * @param $im
     */
    protected function getImgHeight($im) {
        return is_resource($im) ? imagesy($im) : 0;
    }
    
    /**
     * 获取图片的宽
     * @param $im
     */
    protected function getImgWidth($im) {
        return is_resource($im) ? imagesx($im) : 0;
    }
    
    /**
     * 获取图片文件的后缀名
     * @param $pFileName
     */
    protected function getImgExt($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return strtolower(pathinfo($pFileName, PATHINFO_EXTENSION));
    }
    
    /**
     * 图片缩放功能
     * @param $im
     * @param $dst_file
     * @param $scale
     */
    protected function createImg($im, $dst_file, $scale) {
        if(!is_resource($im) || empty($dst_file)) {
            return false;
        }
        
        $scale = $scale > 0 ? $scale : 75;
        
        $src_w = $this->getImgWidth($im);
        $src_h = $this->getImgHeight($im);
        
        $dst_w = round($src_w * $scale / 100);
        $dst_h = round($src_h * $scale / 100);
        
        $dst_im = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($dst_im, $im, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        
        return $this->saveImgFile($dst_im, $dst_file);
    }
    
    /**
     * 将图像流输出到文件
     * @param $im
     * @param $dst_file
     */
    protected function saveImgFile($om, $dst_file) {
        if(!is_resource($om) || empty($dst_file)) {
            return false;
        }
        
        $success = false;
        switch($this->getImgExt($dst_file)) {
            case 'jpg':
                $success = imagejpeg($om, $dst_file);
                break;
            case 'gif':
                $success = imagegif($om, $dst_file);
                break;
            case 'png':
                $success = imagepng($om, $dst_file);
                break;
        }
        imagedestroy($om);
        
        return $success;
    }
    
    /**
     * 递归创建目录
     * @param $dir
     */
    protected static function mkDirRecursion($dir) {
        if(empty($dir)) {
            return false;
        }
        
        if(!is_dir($dir)) {
            self::mkDirRecursion(dirname($dir));
            mkdir($dir);
            @ chmod($dir, 0777);
        }
        
        return true;
    }
    
}