<?php
class UploadphotoAction extends SnsController {
	//如果雏鹰用远程方法上传头像，请打开不检测登录配置
//	public $_isLoginCheck = false;
	
	public function _initialize(){
		parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");
	}
	
	public function modifyHeadPhoto(){
		$uid = $this->getCookieAccount();
        $jpgData = $_POST['jpgData'];
        
        $rs = $this->uploadphotoForRemote($jpgData);
        $json_rs = json_decode($rs, true);
        $error_code = $json_rs['error'];
        $img_path = $json_rs['img_path'];
        if($error_code == 1){
            $flag = "true";
            $msg = "上传成功";
        }else{
            $flag = "false";
            $msg = "上传失败，请重试";
        }
		$json = "<?xml version='1.0' encoding='utf-8' ?>
        	 <data>
        		<result>".$flag. "</result>
        		<url>".$img_path."</url>
        		<errorInfo>".$msg ."</errorInfo>
        	 </data>";
//		echo json_encode($xml); //
        echo $json;
	}
	
	/**
	 * 远程上传用户头像的处理接口
	 */
	public function uploadphotoForRemote($dataarr1) {
	    $uid = $this->getCookieAccount();
       if(!$dataarr1){
	        $uid = $this->objInput->postInt('uid');  
	    	$jpgData = $this->objInput->postStr('jpgData');
	    }else{
	    	$jpgData = $dataarr1;
	    }
	    $jpgData = base64_decode($jpgData);
	    if(empty($uid) || empty($jpgData)) {
	        $dataarr = array(
	            'error' => -1,
	            'message' => '用户名或用户头像不能为空!',
	        );
	        
	        echo json_encode($dataarr);
	        return false;
	    }
	    

	    $upload_dir = Pathmanagement_sns::uploadHeadImg(); 

	    $upload_path = $upload_dir . $uid;
	    //产生head_pic文件夹
		if(!is_dir($upload_dir)) {
			mkdir($upload_dir, 0777);
		}else{
		    chmod($upload_dir, 0777);
		}
		//每个用户产生一个图片文件夹
		if(!is_dir($upload_path)){
			mkdir($upload_path, 0777);
		}else{
            chmod($upload_path, 0777);		    
		}
		//图片文件名
	    $pic_name = "thumbnail_" . time() . "_" . $uid . ".jpg";
	    $upload_file = $upload_path . "/" . $pic_name;
	   
	    $fw = $fc = false;
	    if(is_dir($upload_path)) {
	        chmod($upload_path, 0777);
    	    //写入文件
    	    $fp = fopen($upload_file, 'w+');
    	    $fw = fwrite($fp, $jpgData);
    	    $fc = fclose($fp);
	        if($fw && $fc) {
    	        //修改用户的数据
    	        $mUser = ClsFactory::Create('Model.mUser');
    	        //删除以前的头像信息
    	        $userlist = $mUser->getUserBaseByUid($uid);
    	        $user = & $userlist[$uid];
    	        if(!empty($user['client_headimg'])) {
        	        $old_file = $upload_path . "/" . $user['client_headimg'];
        	        if(is_file($old_file)) {
        	            @unlink($old_file);
        	        }
    	        }
    	        //更新用户头像
    	        
    	        $user_data = array(
    	            'client_headimg' => $pic_name,
    	        	'upd_time' => time(),
    	        );
    	        $mUser->modifyUserClientAccount($user_data, $uid);
    	        
    	        $dataarr = array(
    	            'error' => 1,
    	            'message' => '上传成功!',
    	        	'img_path' => Pathmanagement_sns::getHeadImg($uid) . $pic_name,
    	        );
    	        
    	        $fp = fopen(WEB_ROOT_DIR.'/er.txt', 'a+');
    	        fwrite($fp, $dataarr['img_path']);
    	        fclose($fp);
    	        
                $mHashClient = ClsFactory::Create('RModel.Common.mHashClient');
        	    $client_base = $mHashClient->getClientbyUid($uid, true);    	        
    	        
    	    } else {
    	        $dataarr = array(
    	            'error' => -1,
    	            'message' => '上传失败!',
    	        );
    	    }
	    } else {
	         $dataarr = array(
	            'error' => -1,
	            'message' => '系统繁忙!',
	        );
	    }
	    if(!$dataarr1){
	    	 echo json_encode($dataarr);
	    }else{
	    	return json_encode($dataarr);
	    }
	}

}