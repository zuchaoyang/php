<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:UCenter Client
 * 说明:	作为与UCenter通信的接口类，并提供通用的与用户信息有关的方法
 * 
*/

class UclientApi extends ApiController {

    public function __construct() {
        parent::__construct();
    }    
    
	public function index() {

	}
	
    public function _initialize(){
		parent::_initialize();        
    }	    

    /* 
     * 统一注销方法
     */  
	public function logout() {
        
	    $callback = $this->objInput->getStr('callback');
	    $token_name = $this->objInput->getStr('token_name');
		if (empty($token_name)) {
			$token_name = SNS_SESSION_TOKEN;
		}
		$domain = get_domain($_SERVER['HTTP_HOST']);
        header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");
        setcookie($token_name, '', time() - 3600, "/", $domain);
        
        
        //提供jsonp回调的方式
        if (!empty($callback)) {
        
            $data = array('status' => 1);
            echo $callback. '(' . json_encode($data) . ')';
        }
	}
    
    /* 
     * 获取用户中心统一登录地址
     */    
    
    public function get_uc_login_url($app = 'uc', $callback = '') {
        $uc_login_part = '/Uc/Login';
        return $this->generate_url($uc_login_part, $app, $callback);
        
    }
    
    /* 
     * 获取用户中心统一注销地址
     * @param callback
     */    
    
    public function get_uc_logout_url($app = 'uc', $callback = '') {
        $uc_logout_part = '/Uc/Logout';
        $callback = 'http://'.$_SERVER['HTTP_HOST'];
        return $this->generate_url($uc_logout_part, $app, $callback);        
    }  

    /* 
     * 获取用户中心统一激活地址
     * @param callback
     */    
    
    public function get_uc_activate_url($app= 'uc', $callback = '') {
        $uc_active_part = '/Uc/Activate';
        return $this->generate_url($uc_active_part, $app, $callback);       
    }     
    
    /* 
     * 获取用户中心首页地址
     */    
    
    public function get_uc_index_url() {
        $url_part = '/Uc/Index';
        $uc_domain = C('UC_DOMAIN');
        $uc_index_url = "http://$uc_domain$url_part";  
        if (empty($uc_domain)) {
            $uc_index_url = 'http://'.$_SERVER['SERVER_NAME'] . $url_part;
        }
        return $uc_index_url;
    }
    
    /* 
     * 获取用户中心个人资料地址
     * @param callback
     */    
    
    public function get_uc_userinfo_url($app= 'uc', $callback = '') {
        $uc_active_part = '/Uc/Userinfos/head_photo';
        return $this->generate_url($uc_active_part, $app, $callback);       
    }    
        
    
    
    /* 
     * 获取用户中心统一获取cookie地址
     * $param 无
     */    
    
    public function get_uc_cookie_url() {
        
        $uc_domain = C('UC_DOMAIN');
        if (empty($uc_domain)) {
            $uc_domain = $_SERVER['SERVER_NAME'];
        }
        $uc_get_cookie_url = '/Uc/LoginApi/getcookie';        
        $getcookie_url = "http://$uc_domain$uc_get_cookie_url";  

        return $getcookie_url;
    }     
    
    /* 
     * 获取cookie oauth2 access_token 令牌信息
     * $param 无
     */ 
	function get_access_token(){
		$token = $this->get_uc_cookie_token_info();
		$access_token = '';
		if (isset($token['access_token'])) {
		    $access_token = $token['access_token'];
		}
		return $access_token;
	}    
	
    /* 
     * 获取cookie 登录信息
     * $param $token_name = cookie名称
     */ 
	public function get_uc_cookie_token_info($token_name = SNS_SESSION_TOKEN) {

		if (empty($token_name)) {
			$token_name = SNS_SESSION_TOKEN;
		}
		
		$token = $_COOKIE[$token_name];
		$token_arr = token_decode($token);
		
		$result = array();
		if (!empty($token_arr) && count($token_arr) == 5) {
		    list($client_id, $username, $access_token, $expires_in, $scope) = $token_arr;
            $result = array(
              "client_id"    => $client_id,
              "username"	 => $username,
              "access_token" => $access_token,
              "expires_in"	 => $expires_in,
              "scope"		 => $scope
            );			    
		    
		}
		
		return $result;
	}    

    /* 
     * 生成地址
     * @param callback
     */    
    
    private function generate_url($url_part = '', $app= 'uc', $callback = '') {
        
        if (empty($url_part)) {
            return '';
            // todo handle exception
        }
        
        if (empty($app)) {
            $app = 'uc';
        }
        
        if(empty($callback)) {
            $callback = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        }
        
        $uc_domain = C('UC_DOMAIN');
        $url = "http://$uc_domain$url_part";  
        if (empty($uc_domain)) {
            $url = 'http://'.$_SERVER['SERVER_NAME'] . $url_part;
        }
        
        $params = array(
            'callback'      => urlencode($callback),
            'app'			=> $app,
            'client_id'     => C('CLIENT_ID'),
            'client_secret' => C('CLIENT_SECRET')
        );        
        
        $url = $url . '?'. http_build_query($params);     
        
        return $url;
    }  	
    
    /**
     *  设置第三方登录绑定信息
     */
    
    public function set_oauth_bind($datas = array()) {
        //检查数组的正确性
        if (empty($datas)) return false;
        if ( !isset($datas['client_account']) ) return false;
        if ( !isset($datas['social_account']) ) return false;
        if ( !isset($datas['social_type']) ) return false;
        if ( !isset($datas['access_token']) ) return false;
        
        if ( !isset($datas['id']) ) {
            $datas['id'] = 0;
        }
        $datas['add_time'] = time();
        $id = $datas['id'];
        $m = ClsFactory::Create('Model.mOauthBind');
        if ($id == 0) {
           $m->addOauthBind($datas);
        } else {
           $m->modifyOauthBindByClientAccount($datas, $id);
        }
        
        return true;
    }
    
    /**
     * 
     * 获取QQ登录绑定信息
     * @param String $client_account   我们网帐号
     * @param String $redirect_url     返回地址
     * 
     * return array
     * 
     * $set_qq_bind_url   设定绑定的地址
     * $nickname          绑定QQ号昵称
     * $display_action_name  显示操作名称   
     */
    
    public function get_oauth_bind_qq($client_account, $redirect_url) {
        
        if (empty($client_account)) {
            return array();
        }
        $result = array();
        import('@.Control.Api.Uc.QzoneTClientApi');
        $qzoneClient = new QzoneTClientApi($client_account);        
        $qzone_user_info = $qzoneClient->get_user_info();
                
        $params = array();
        $params['connect'] = 'qzone';
        $params['redirect_url'] = urlencode($redirect_url);
        
        
        $set_qq_bind_url =  'http://' . C('UC_DOMAIN') .'/Uc/Oauth2/login?' . http_build_query($params);
        
        $result['set_qq_bind_url'] = $set_qq_bind_url;
        if (!empty($qzone_user_info)) {
            $nickname = $qzone_user_info['nickname'];
            $display_action_name = '修改QQ帐号';
        } else {
            $nickname = '未设置QQ帐号';
            $display_action_name = '设置QQ帐号';
        }
        
        $result['nickname'] = $nickname;
        $result['display_action_name'] = $display_action_name;
        
        return $result;
    }    
    
    /**
     * 
     * 用户在线状态加入
     * 将cookies 对于的client_account 刷新在线用户库
     * @param String $client_account   我们网帐号
	 *
     */    
    
    public function ping() {
        
        $cookies = $this->get_uc_cookie_token_info();
        if (!empty($cookies)) {
            $client_account = $cookies['username'];
            
            $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
            return $mLiveUsr->ping($client_account);
        }
    }
    
}