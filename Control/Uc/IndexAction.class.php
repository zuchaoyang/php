<?php

class IndexAction extends UcController {
    public function _initialize() {
           parent::_initialize();
    }
        
    public function index() {
        $user_list = $this->user;
        $client_account = $user_list['client_account'];
        $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
        $PhoneInfos = $mBusinessphone->getbusinessphonebyalias_id($client_account);
        $phone_id = $PhoneInfos[$client_account]['account_phone_id2'];
        if(!empty($this->user['client_email'])) {
            $texta = substr($user_list['client_email'],0,strpos($user_list['client_email'],"@"));
    		$textb = substr($user_list['client_email'],strpos($user_list['client_email'],"@"));
    		
    		if(strlen($texta)>2){
    			$user_list['client_email'] = substr($texta,0,2)."******".$textb;
    		}else{
    			$user_list['client_email'] = $texta."******".$textb;
    		}
        }
        
		if(!empty($phone_id)) {
		    $phone_replace = "/(1\d{1,2})\d\d(\d{0,2})/";
    		$replacphone = "\$1****\$3";
    		
    		$phone_id = preg_replace($phone_replace,$replacphone,$phone_id);
    		
    		$user_list['phone_id'] = $phone_id;
		}
		
        $this->assign('user_list' , $user_list);
        
        //qzone 设置
        $uc_domain = C('UC_DOMAIN');
        $redirect_url = 'http://'.$uc_domain . '/Uc/index';
        $qzone_user_info = $this->uc_client->get_oauth_bind_qq($client_account, $redirect_url);
                
        $this->assign('qzone_user_info', $qzone_user_info);

        
        $this->display('uc_index');
            
    }
    
    
    //保存注册账号到本地
	public function saveaccount(){
		$account = $this->objInput->getStr('client_account');
		$downname = '我们网账号-'.$account.'.txt';

		$user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(stripos($user_agent, 'MSIE') !== false) {
            $downname = 'filename="' . str_replace('+', '%20', urlencode($downname)) . '"';
        } elseif(stripos($user_agent, 'Firefox') !== false) {
            $downname = 'filename*="utf8\'\'' . $downname . '"';
        } else {
            $downname = 'filename="' . $downname . '"';
        }
        
        ob_end_clean();
        header("Content-Type:text/html;charset=utf-8");
		header("Content-Disposition:attachment;$downname");
		echo "您的我们网账号为（".$account.")，请您妥善保管。\r\n文档生成时间：".date('Y-m-d H:i:s',time());
	}
    

}
?>
