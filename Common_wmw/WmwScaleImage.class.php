<?php
/**
 * 根据原图生成中图和小图。
 * @author lnczx
 * Requires : Requires PHP5, GD library.
 * Usage Example:
 * 
 *    import('@.Common_wmw.WmwImage');
 *    $scaleImage = new WmwScaleImage();
      $scaleImage->scaleSmall('images/cars/large/input.jpg');
      $scaleImage->scaleMiddle('images/cars/large/input.jpg');
 */
class WmwScaleImage  {
    // 小图最大宽
    protected $s_max_width = 198;
    
    // 小图最大高
    protected $s_max_height = 140;    
    
    // 中图最大宽
    protected $m_max_width = 250;
        
    // 中图最大高
    protected $m_max_height = 'auto';    
    
    //允许缩放成的文件后缀名
    protected $allow_types = array(
        'jpg',
        'gif',
        'png',
    );
    
    public function __construct() {
        if(!extension_loaded('gd')) {
            throw new Exception('图片缩放功能需要GD库的支持!');
        }
    }

    /**
     * 
     * 切割图方法
     * @param string $img_path 原图路径
     * @param int $max_width 图宽，默认可以不传递，用类默认值
     * @param int $max_height 图高，默认可以不传递，用类默认值
     * @return 返回 小图和中图的文件路径
     */    
    public function scaleAll($img_path, $max_width, $max_height) {
        if(empty($img_path) || !file_exists($img_path)) {
            return false;
        }

        $result = array();
        $result['file'] = $img_path;
        $result['file_s'] = $this->scaleSmall($img_path, $max_width, $max_height);
        $result['file_m'] = $this->scaleMiddle($img_path, $max_width, $max_height);
        return $result;
    }    
    
    /**
     * 
     * 切割小图方法
     * @param string $img_path 原图路径
     * @param int $max_width 小图宽，默认可以不传递，用类默认值
     * @param int $max_height 小图高，默认可以不传递，用类默认值
     * @return $small_path  返回小图路径
     */    
    public function scaleSmall($img_path, $max_width, $max_height) {
        if(empty($img_path) || !file_exists($img_path)) {
            return false;
        }

        if (empty($max_width)) $max_width = $this->s_max_width;
        if (empty($max_height)) $max_height = $this->s_max_height;
        
        $path_parts = pathinfo($img_path);

        //源文件路径
        $src_path = $path_parts['dirname'];

        //源文件扩展名
        $extension = $path_parts['extension'];
       
        //源文件名称
        $src_name = basename($img_path, '.'. $extension);


        //目的文件名称
        $file_name = $src_name . '_s.' . $extension;

        //目的文件完整路径
        $save_path = $src_path . '/' . $file_name;

        return $this->scale($img_path, $save_path, $max_width, $max_height);
    }
    
    
    /**
     * 
     * 切割小图方法
     * @param string $img_path 原图路径
     * @param int $max_width 小图宽，默认可以不传递，用类默认值
     * @param int $max_height 小图高，默认可以不传递，用类默认值
     * @return $small_path  返回小图路径
     */    
    public function scaleMiddle($img_path, $max_width, $max_height) {
        if(empty($img_path) || !file_exists($img_path)) {
            return false;
        }

        if (empty($max_width)) $max_width = $this->m_max_width;
        if (empty($max_height)) $max_height = $this->m_max_height;
        
        $path_parts = pathinfo($img_path);

        //源文件路径
        $src_path = $path_parts['dirname'];

        //源文件扩展名
        $extension = $path_parts['extension'];
       
        //源文件名称
        $src_name = basename($img_path, '.'. $extension);


        //目的文件名称
        $file_name = $src_name . '_m.' . $extension;

        //目的文件完整路径
        $save_path = $src_path . '/' . $file_name;

        return $this->scale($img_path, $save_path, $max_width, $max_height);
    }    
    
    /**
     * 
     * 切割图方法
     * @param string $img_path 原图路径
     * @param int $max_width 图宽
     * @param int $max_height 图高
     * @return $small_path  返回小图路径
     */    
    protected function scale($img_path, $save_path, $max_width, $max_height) {

        if(empty($img_path) || !file_exists($img_path) || !is_readable($img_path)) {
             throw new Exception('源文件不存在或不可读!');
        }
        
        if (empty($max_width) || empty($max_height)) {
            return false;
        }
        
        $extension = $this->getImgExt($img_path);
        if(empty($extension) || !in_array($extension, $this->allow_types)) {
            throw new Exception("系统暂时不支持缩放成{$ext}格式的图片!");
            break;
        }
        
        $im = $this->loadImg($img_path);
        
        if(!is_resource($im)) {
            throw new Exception('源文件加载失败!');
        }
        
        $width = $this->getImgWidth($im);
        $height = $this->getImgHeight($im);
        
        if ($max_height == 'auto') $max_height = $height;
        //获取缩放比例
        $w_scale = $max_width * 100 / $width;
        $h_scale = $max_height * 100 / $height;
        $scale = $w_scale > $h_scale ? $h_scale : $w_scale;
        $scale = $scale > 0 ? $scale : 75;
        //缩放宽高
        $newWidth = round($width * $scale / 100);
        $newHeight = round($height * $scale / 100);        
        
        $newIm = imagecreatetruecolor($newWidth, $newHeight);
        $do  = imagecopyresampled($newIm, $im, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $success = $this->saveImgFile($newIm, $save_path);
        return $success ? $save_path : false;
    }

    /**
     * 将图像流输出到文件
     * @param $im
     * @param $dst_file
     */
    protected function saveImgFile($im, $save_path, $imageQuality="100") {
        if(!is_resource($im) || empty($save_path)) {
            return false;
        }
        
        $success = false;
        switch($this->getImgExt($save_path)) {
			case 'jpg':
			case 'jpeg':
                $success = imagejpeg($im, $save_path, $imageQuality);
                break;
            case 'gif':
                $success = imagegif($im, $save_path, $imageQuality);
                break;
            case 'png':
                $success = imagepng($im, $save_path, $imageQuality);
                break;
        }
        imagedestroy($im);
        
        return $success;
    }
    
    /**
     * 加载图片文件信息
     * @param $src_img
     */
    protected function loadImg($file) {
        if(empty($file)) {
            return false;
        }
        switch($this->getImgExt($file)) {
			case 'jpg':
			case 'jpeg':
                $im = imagecreatefromjpeg($file);
                break;
            case 'gif':
                $im = imagecreatefromgif($file);
                break;
            case 'png':
                $im = imagecreatefrompng($file);
                break;
        }
        
        return $im;
    }
    
    /**
     * 获取图片文件的后缀名
     * @param $filename
     */
    protected function getImgExt($filename) {
        if(empty($filename)) {
            return false;
        }
        
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }    

    /**
     * 获取图片的高
     * @param $im from imagecreatefromtype 
     */
    protected function getImgHeight($im) {
        return is_resource($im) ? imagesy($im) : 0;
    }    
    
    /**
     * 获取图片的宽
     * @param $im from imagecreatefromtype 
     */
    protected function getImgWidth($im) {
        return is_resource($im) ? imagesx($im) : 0;
    }       
}