<?php
class PhotouploadAction extends SnsController {
    //不检测登录，因为flash在与服务器交互时得不到cookie值
    public $_isLoginCheck = false;
    
    private $upload_name = 'Filedata';
    
    //允许上传的附件的大小,单位:M(兆)
    private $max_size = 8;
    
    private $allow_types = array('jpg','gif','png');
    
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 文件上传代码
     */
    public function index() {
        $class_code = $this->objInput->postInt('class_code');
        $album_id = $this->objInput->postInt('album_id');
        $account = $this->objInput->postInt('client_account');
        $secret_key = $this->objInput->postStr('secret_key');
        $is_class = false;
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
    		import("@.Control/Api/AlbumImpl/PhotoInfo");
            $PhotoInfo = new PhotoInfo();
            $photo_id = $PhotoInfo->addPhoto($data);
        }
        
//        $str = '';

//        foreach($data as $key=>$val) {
//            $str.=$key.'-----'.$val.'/n/r';
//        }
//        file_put_contents(WEB_ROOT_DIR.'/debug.txt',$str);
        if(empty($photo_id)) {
            $this->ajaxReturn('上传失败','',-1,'json');
        }
        //添加动态
        import("@.Control.Api.FeedApi");
        $feed_api = new FeedApi();
        if(!empty($class_code)) {
            $feed_api->class_create($class_code,$account,$photo_id,FEED_ALBUM, FEED_ACTION_PUBLISH);
        }else{
            $feed_api->user_create($account, $photo_id, FEED_ALBUM,FEED_ACTION_PUBLISH);
        }
        
        echo base64_encode(json_encode($file_attrs));
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
     * @param  
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
        $img_name = basename($src_img);

        import('@.Common_wmw.WmwScaleImage');
        $wmwScaleImage = new WmwScaleImage();
        $_s_path = $wmwScaleImage->scaleSmall($src_img);
        $_m_path = $wmwScaleImage->scaleMiddle($src_img);
        $img_name_arr = array(
            'img_name'=> $img_name,
            '_s'	  => basename($_s_path),
            '_m'	  => basename($_m_path)
        ); 
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
     * 获取文件的加密key
     * @param $file_attrs
     */
    private function getFileMd5key($file_name) {
        $prime_num = 5381;
        $rand_str = "bnmdeixkedsbxide__%%_**";
        
        return md5($file_name . $prime_num . $rand_str);
    }
    
}