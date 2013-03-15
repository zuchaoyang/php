<?php
class UploadAction extends SnsController {
    //不检测登录，因为flash在与服务器交互时得不到cookie值
    public $_isLoginCheck = false;
    
    private $upload_name = 'Filedata';
    
    //允许上传的附件的大小,单位:M(兆)
    private $max_size = 200;
    
    private $allow_types = array();
    
    public function __construct() {
        parent::__construct();
        $this->initSettings();
    }
    
    /**
     * 文件上传代码
     */
    public function index() {
        $uid         = $this->objInput->postStr('uid');
        $secret_key  = $this->objInput->postStr('secret_key');
        
        //密钥的校验
        import('@.Control.Sns.Resource.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        if(! $UploadSecretKeyObject->checkSecretkey($uid, $secret_key)) {
            $this->handError('Without permission!');
        }
        
        //文件检测
        $this->handleUploadErrors();
        $this->check_size();
       
        //文件上传
        $file_attrs = $this->uploadResourceFile();
        $file_attrs['md5_key'] = $this->getFileMd5key($file_attrs['filename']);
        
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
        C(include WEB_ROOT_DIR . '/Config/Resource/config.php');
        
        $allow_upload_types = (array)C('allow_upload_types');
        
        $allow_types = array();
        foreach($allow_upload_types as $key => $types) {
            if(empty($types)) {
                continue;
            }
            $allow_types = array_merge($allow_types, (array)explode(',', $types));
        }
        $allow_types = array_unique($allow_types);
        
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
    private function uploadResourceFile() {
        if(!isset($_FILES[$this->upload_name])) {
            return false;
        }
        
        import('@.Common_wmw.Pathmanagement_sns');
        
        $uploadObj = ClsFactory::Create("@.Common_wmw.WmwUpload");
        $up_init = array (
        	'attachmentspath' => Pathmanagement_sns::uploadResource(),
            'renamed' => true,
            'ifresize' => true,
            'allow_type' => $this->allow_types,
            //文件上传类的大小使用的单位是:kb，在这里需要转换
            'max_size' => 1024 * $this->max_size,
        );
        $uploadObj->_set_options($up_init);
        $uploadObj->ignore_mine();
        
        return $uploadObj->upfile($this->upload_name);
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