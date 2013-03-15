<?php
class PersonphotouploadAction extends SnsController {
    //不检测登录，因为flash在与服务器交互时得不到cookie值
    public $_isLoginCheck = false;
    
    private $upload_name = 'Filedata';
    
    //允许上传的附件的大小,单位:M(兆)
    private $max_size = 8;
    
    private $allow_types = array();
    
    //自定义图片的宽高
    private $custom_img_width_height = array(
                            '_s'=>array(
                                'width'=>198,
                                'height'=>140
                            ),
                            '_m'=>array(
                            	'width'=>178
                            )
                        );
    
    public function __construct() {
        parent::__construct();
        $this->initSettings();
    }
    
    
    /**
     * 文件上传代码
     */
    public function index() {
        $album_id = $this->objInput->postInt('album_id');
        $account = $this->objInput->postInt('client_account');
        $secret_key = $this->objInput->postStr('secret_key');
        //密钥的校验
        import('@.Control.Sns.Album.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        if(! $UploadSecretKeyObject->checkSecretkey($account, $secret_key)) {
            $this->handError('Without permission!');
        }
        
        //文件检测
        $this->handleUploadErrors();
        $this->check_size();
       
        //文件上传
        $file_attrs = $this->uploadPhotoFile($account);
        if(empty($file_attrs)) {
            echo '';
            exit;
        }
        $file_attrs['md5_key'] = $this->getFileMd5key($file_attrs['filename']);
        
        //缩略图die;
        $img_arr = $this->scalePhoto($file_attrs['getfilename']);
        $img_name_arr = explode('.',$file_attrs['name']);
        $img_name = $img_name_arr[0];
        if(!empty($img_arr)) {
            $data=array(
    			'album_id'      => $album_id,  
    			'name'          => $img_name, // 类中返回的$up_r['filename']有误，同$up_r['getfilename']值相同
    			'file_big'      => $img_arr['img_name'],  //该字段可删除
                'file_middle'   => $img_arr['_m'],
    			'file_small'    => $img_arr['_s'],
    			'description'   => "",
                'comments'      => 0,
    			'upd_time'      => time(),
    			'upd_account'   => $account,
    		);
    		import("@.Control/Api/AlbumImpl/ByPerson");
            $ByPerson = new ByPerson();
            $add_rs = $ByPerson->addPersonPhoto($data,true);
        }
        
//        $str = '';

//        foreach($data as $key=>$val) {
//            $str.=$key.'-----'.$val.'/n/r';
//        }
//        file_put_contents(WEB_ROOT_DIR.'/debug.txt',$str);
        if(empty($add_rs)) {
            $this->ajaxReturn('上传失败','',-1,'json');
        }
        
        echo base64_encode(json_encode($file_attrs));
    }
    
    /**
     * 上传文件的清理
     */
    public function clearUploadFile() {
        $file_attrs_encode = $this->objInput->postStr('file_attrs');
        
        $file_attrs = json_decode(base64_decode($file_attrs_encode), true);
        $file_name = $file_attrs['filename'];
        
        if($file_attrs['md5_key'] != $this->getFileMd5key($file_name)) {
            $this->ajaxReturn(null, '非法操作!', -1, 'json');
        }
        
        //删除附件信息
        if(is_file($file_name)) {
            @ unlink($file_name);
            $this->ajaxReturn(null, '清理成功!', 1, 'json');
        }
        
        $this->ajaxReturn(null, '清理失败!', -1, 'json');
    }
    
    /**
     * 初始化系统配置
     */
    private function initSettings() {
        //C(include WEB_ROOT_DIR . '/Config/Resource/config.php');
        
        //$allow_upload_types = (array)C('allow_upload_types');
        
        $allow_types = array('jpg','gif','png');
//        foreach($allow_upload_types as $key => $types) {
//            if(empty($types)) {
//                continue;
//            }
//            $allow_types = array_merge($allow_types, (array)explode(',', $types));
//        }
        //$allow_types = array_unique($allow_types);
        
        //去掉空格和空数据
        foreach($allow_types as $key => $type) {
            $type = trim($type);
            if(empty($type)) {
                unset($allow_types[$key]);
            }
            $allow_types[$key] = $type;
        }
        
        $this->allow_types = $allow_types;
    }
    
    /**
     * 上传附加
     */
    private function uploadPhotoFile($account) {
        if(!isset($_FILES[$this->upload_name])) {
            return false;
        }
        
        import('@.Common_wmw.Pathmanagement_sns');
        
        $uploadObj = ClsFactory::Create("@.Common_wmw.WmwUpload");
        $up_init = array (
        	'attachmentspath' => Pathmanagement_sns::uploadAlbum($account),
            'renamed' => true,
            'allow_type' => $this->allow_types,
            //文件上传类的大小使用的单位是:kb，在这里需要转换
            'max_size' => 1024 * $this->max_size,
        );
        $uploadObj->_set_options($up_init);
        $uploadObj->ignore_mine();
        
        return $uploadObj->upfile($this->upload_name);
    }
    //相片缩略图（中：瀑布流，小：照片普通列表）
    /**
     * $scale=array(
     * 		'_s'=>'百分比'
     * 		'_m'=>'百分比'
     * );
     * 
     * @return array
     * $img_path_arr = array(
     * 		'_'
     * );
     */
    private function scalePhoto($src_img) {
        if(empty($src_img) || !file_exists($src_img)) {
            return false;
        }
        //相册,照片列表width:198 height:162------small
        //瀑布流列表width:178--------------------middle
        list($width, $height) = $this->getImageWeightAndHeight($src_img);
        $scale_arr = array();
        foreach($this->custom_img_width_height as $key=>$val){
            if(empty($val['width'])) {
                $val['width'] = $width;
            }elseif(empty($val['height'])) {
                $val['height'] = $height;
            }
            $w_scale = $val['width']*100/$width;
            $h_scale = $val['height']*100/$height;
            if($w_scale>$h_scale) {
                $scale_arr[$key]=$h_scale;
            }else{
                $scale_arr[$key]=$w_scale;
            }
        }
        $img_url_arr = explode('/', $src_img);
        $img_name = array_pop($img_url_arr);
        $img_name_arr = array(
            'img_name'=>$img_name
        );
        
        foreach($scale_arr as $key=>$val) {
            $dst_files[$key]=array(
                'path' => str_replace('.',$key.'.',$src_img),
				'scale' => $val
            );
            $img_name_arr[$key]=str_replace('.',$key.'.',$img_name);
        }
        
        import('@.Common_wmw.WmwImage');
        $WmwImage = new WmwImage();
        $WmwImage->scale($src_img, $dst_files);
        
        return $img_name_arr;
    }
    
    /**
     * 检测文件的大小设置
     */
    private function check_size() {
     	$POST_MAX_SIZE = ini_get('post_max_size');
    	$unit = strtoupper(substr($POST_MAX_SIZE, -1));
    	$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
    
    	if((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
    		header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
    		$this->handleError("POST exceeded maximum allowed size.");
    	}
    	
    	$file_size = @filesize($_FILES[$this->upload_name]["tmp_name"]);
    	if(!$file_size || $file_size > $this->max_size * 1024 * 1024) {
    		$this->handleError("File exceeds the maximum allowed size");
    	}
    	
    	if($file_size <= 0) {
    		$this->handleError("File size outside allowed lower bound");
    	}
    	
    	return true;
    }
    
    /**
     * 处理上传错误
     */
    private function handleUploadErrors() {
         //表单上传错误类型
    	$uploadErrors = array(
            0 => "There is no error, the file uploaded with success",
            1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
            3 => "The uploaded file was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder"
    	);
    	
        // Validate the upload
    	if (!isset($_FILES[$this->upload_name])) {
    		$this->handleError("No upload found in \$_FILES for " . $this->upload_name);
    	}
    	
    	if (isset($_FILES[$this->upload_name]["error"]) && $_FILES[$this->upload_name]["error"] != 0) {
    		$this->handleError($uploadErrors[$_FILES[$this->upload_name]["error"]]);
    	}
    	
    	if (!isset($_FILES[$this->upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$this->upload_name]["tmp_name"])) {
    		$this->handleError("Upload failed is_uploaded_file test.");
    	}
    	
    	if (!isset($_FILES[$this->upload_name]['name'])) {
    		$this->handleError("File has no name.");
    	}
    	
    	return true;
    }

    /**
     * 异常处理函数
     * @param $message
     */
    private function handleError($message) {
    	echo $message;
    	exit(0);
    }
    
    /**
     * 获取图片资源的宽和高
     * @param $src_img
     */
    private function getImageWeightAndHeight($src_img) {
        if(empty($src_img)) {
            return false;
        }
        
        switch(strtolower(pathinfo($src_img, PATHINFO_EXTENSION))) {
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
        if(!is_resource($im)) {
            return false;
        }
        
        $width = imagesx($im);
        $height = imagesy($im);
        imagedestroy($im);
        
        return array(
            $width,
            $height
        );
    }
    
    /**
     * 获取文件的加密key
     * @param $file_attrs
     */
    private function getFileMd5key($file_name) {
        $prime_num = 5381;
        $rand_str = "bnmdeixkedsbxide__%%_**";
        
        return md5($file_name . $prime_num . $rand_str);
    }
    
}