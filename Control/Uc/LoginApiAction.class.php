<?php
/**
 * UCenter 统一登录类
 * @author    lnczx <lnczx0915@gmail.com>
 */

class LoginapiAction extends UcController {
    public $_isLoginCheck = false;
    
    public function index() {
//        echo 'say hello!';
    }
    
    /*
     *   统一登录验证接口，仅接受post方法,且通常为json方式调用
     *  必须参数:
     *      username
     *      password : 仅接受md5后的password;
     *      callback
     *      
     *  json格式，参考ThinkOAuth2.php function createAccessTokenWithUserName
     *    $token = array(
          "client_id"    => $client_id,
          "username"	 => $username,
          "access_token" => $this->genAccessToken(),
          "expires_in"	 => $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME),
          "scope"		 => $scope
        );   
     */
    
    public function login() {
        
        
        //=====================================获取参数==============================================
        $callback = $this->objInput->postStr('callback');
        //todo 根据callback 获取域名验证是否来源于信任的域名

        $callback_decode = urldecode($callback);

        //如果$callback 为空，则指定到用户中心首页
        if (empty($callback)) {
            $uc_domain = C('UC_DOMAIN');
            $callback_decode = 'http://'.$uc_domain . '/Uc/index';
            $callback = urlencode($callback_decode);
        }        

        
	    $username = $this->objInput->postStr('username');
	    $password = $this->objInput->postStr('password');
	    
        $client_id = $this->objInput->postStr('client_id');
        $client_secret = $this->objInput->postStr('client_secret');
        
        $captcha = $this->objInput->postStr('captcha');
        
        //第三方登录的参数获取
	    $social_account = $this->objInput->postStr('social_account');
	    $social_type = $this->objInput->postStr('social_type');
	    $access_token = $this->objInput->postStr('access_token');        

        //=====================================登录之前的的验证=========================================
        
        // todo 做client_id 的有效性验证，可调用ThinkOAuth2.class.php checkClientCredentials 方法
        // todo 需要考虑跨域访问，如果不是有效性的客户端，如何操作	    

	    $client_account = $this->validateAccount($username, $password, $callback, $captcha);
                
        //删除用户尝试次数表记录
        $this->_doDelLoginAttemps($client_account);
        
        
//        $data = array();
//        $data['callback'] = $callback_decode;        

        //==================================登录成功后之的操作============================================
        if (!empty($social_account) && !empty($social_type)) {
            $this->_oauthBind($client_account, $social_account, $social_type, $access_token);
        }
        $this->_doLogin($client_account, $callback_decode, $access_token);
    }
    
    /*
     *  社会化登录接口，仅接受post方法,且通常为json方式调用,用于绕过密码登录方式
     *  必须参数:
     *      username
     *      password : 仅接受md5后的password;
     *      callback
     *      social_account
     *      social_type
     *      
     *  json格式，参考ThinkOAuth2.php function createAccessTokenWithUserName
     *    $token = array(
          "client_id"    => $client_id,
          "username"	 => $username,
          "access_token" => $this->genAccessToken(),
          "expires_in"	 => $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME),
          "scope"		 => $scope
        );   
     */
    
    public function social_login() {
        //=====================================获取参数==============================================
        $callback = $this->objInput->postStr('callback');
        //todo 根据callback 获取域名验证是否来源于信任的域名
        
        $callback_decode = urldecode($callback);
	    
        //如果$callback 为空，则指定到用户中心首页
        if (empty($callback)) {
            $uc_domain = C('UC_DOMAIN');
            $callback_decode = 'http://'.$uc_domain . '/Uc/index';
            $callback = urlencode($callback_decode);
        }
        
	    $username = $this->objInput->postStr('username');
	    $password = $this->objInput->postStr('password');
	    
        $client_id = $this->objInput->postStr('client_id');
        $client_secret = $this->objInput->postStr('client_secret');
        
        $captcha = $this->objInput->postStr('captcha');
        
        //第三方登录的参数获取
	    $social_account = $this->objInput->postStr('social_account');
	    $social_type = $this->objInput->postStr('social_type');        
        $access_token = $this->objInput->postStr('access_token');
        //=====================================登录之前的的验证=========================================
        
        // todo 做client_id 的有效性验证，可调用ThinkOAuth2.class.php checkClientCredentials 方法
        // todo 需要考虑跨域访问，如果不是有效性的客户端，如何操作	    
        if (empty($username)) {
            $this->ajaxReturn(null, '用户名或密码不能为空', 0, 'json');
        }
	    
        if (empty($social_account) || empty($social_type)) {
            $this->ajaxReturn(null, '用户名或密码不能为空', 0, 'json');
        }
        
        //todo 检查绑定的有效性，即post 过来的 client_account  social_account , social_type 是否与uc_oauth_bind 一致
        
        $client_account = $username;
                
        //删除用户尝试次数表记录
        $this->_doDelLoginAttemps($client_account);

        //==================================登录成功之后的操作============================================
        $this->_oauthBind($client_account, $social_account, $social_type, $access_token);
        $this->_doLogin($client_account, $callback_decode, $access_token);
    }    
    
    /*
     *  统一获取cookies接口，仅接受get方法,且通常为jsonp方式调用
     *  必须参数:
     *      $client_id : oauth2分配的client_id
     *  可选参数:
     *      $client_secret : oauth2分配的client_secret
     */    
    
    public function getCookie() {
//        $client_id = $this->convertClientAccount($this->objInput->getStr('client_id'));
//        $client_secret = $this->convertClientAccount($this->objInput->getStr('client_secret'));
//        
        // todo 做client_id 的有效性验证，可调用ThinkOAuth2.class.php checkClientCredentials 方法
        // todo 需要考虑跨域访问，如果不是有效性的客户端，如何操作// 返回错误代码
        
        header("content-type: text/javascript"); 
        // 获取cookie
        $callback = $this->objInput->getStr('callback');
        $callback = urldecode($callback);
        $client_id = $this->objInput->postStr('client_id');
        $client_secret = $this->objInput->postStr('client_secret');        
        
        
        $token = $_COOKIE[SNS_SESSION_TOKEN];
        header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");
        $isLogined = false;
        if (!empty($token)) {
            $isLogined = true;
        }    
        
        // todo 规范返回方式
        $data = array(
            'logined'	=> $isLogined,
            'token'		=> $token
        );

        //提供jsonp回调的方式
        if (!empty($callback)) {
            echo $callback. '(' . json_encode($data) . ')';
        } else {
            $this->ajaxReturn($data, 'success', 1, 'json');
        }
    }
    
    /** 
     * 检测是否三次登录失败，显示验证码  0 = 没有三次登录失败   1 = 有三次登录失败
     * todo 加入传参数，比如来源哪个模块，以后放到api可加入
     */
    Public function show_captcha(){
        // 导入Image类库
        
        $username = $this->objInput->postStr('username');
        $data = array();
        if (empty($username)) {
            $this->ajaxReturn($data, 'showcaptcha', 0, 'json');
        }
        
        $client_account = $this->_convertAccount($username);  
        $attempts = 0;
        $m = ClsFactory::Create('Model.mUcLoginAttempts');
        $datas = $m->getLoginAttemptsById($client_account);
        if (!empty($datas)) {
           if (isset($datas[$client_account]['attempts'])) {
               $attempts = $datas[$client_account]['attempts'];
           }  
        }
        
        
        if ($attempts > 3) {
        
            $captcha = ClsFactory::Create('@.Common_wmw.Captcha');
            $data =  $captcha->showCaptcha();
            $this->ajaxReturn($data, 'showcaptcha', 1, 'json');
        }
        
        $this->ajaxReturn($data, 'showcaptcha', 0, 'json');
    }
    
    /*
     *  刷新验证码接口
     *  必须参数:
     *      无
     *  可选参数:
     *      无
     *  返回          :
     *      直接返回Comon_wmw.captecha 类对应的验证码图片
     *     
     */      
    
    Public function refresh_captcha(){
        $captcha = ClsFactory::Create('@.Common_wmw.Captcha');
        $data =  $captcha->showCaptcha();
        $this->ajaxReturn($data, 'success', 1, 'json');
    }    
    
    /**
     * 
     * 登录校验
     * @param String $client_account  用户名
     * @param unknown_type $password  密码
     */
    
    private function validateAccount($username, $password, $callback, $captcha = NULL) {

	    //校验1：参数空校验
	    if (empty($username) || empty($password)) {
	        $this->ajaxReturn(null, '用户名或密码不能为空', 0, 'json');
	    } 
	    //长度校验
	    if (!isLengthLimit($username, 5, 60) ) {
	        $this->ajaxReturn(null, '用户名或密码格式错误', 0, 'json');
	    }
	    
	    //如果有验证码，则需要验证码的校验:
	    if (!empty($captcha)) {
	        $captcha_verify = false;
	        if (isset($_SESSION['captcha']) && isset($_SESSION['captcha']['code'])) {
	            if ($captcha != $_SESSION['captcha']['code']) {
	              $captcha_verify =  false;
	            } else {
	                $captcha_verify = true;
	            }
	        }  
	        
	        if (!$captcha_verify) {
	            $this->ajaxReturn(null, '验证码填写错误', 0, 'json');     
	        }
	    }

        //校验2: 验证用户名及密码是否正确
        $client_account = $this->_convertAccount($username);  
        $mUser=ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getClientAccountById($client_account);
		
        if (!empty($userInfo)) {
            
            if ($userInfo[$client_account]['client_password'] != $password) { 
                $this->_doLoginAttemps($client_account);
                $this->ajaxReturn(null, '用户名或密码错误', 0, 'json');     
            }
        } else {
            $this->ajaxReturn(null, '该用户不存在', 0, 'json');     
        }        
        
        //校验3： 用户冻结状态:
        $stop_flag = intval($userInfo[$client_account]['status']);
        if ($stop_flag == CLIENT_STOP_FLAG_FOREVER) {
            $this->ajaxReturn(null, '该用户已被冻结', 0, 'json');
        }      
        
        return $client_account;
    }
    
    /**
     * 
     * 登录成功之后的操作
     * @param String $client_account  用户名
     * json格式，参考ThinkOAuth2.php function createAccessTokenWithUserName
     *    $token = array(
     *     "client_id"    => $client_id,
     *     "username"	 => $username,
     *     "access_token" => $this->genAccessToken(),
     *     "expires_in"	 => $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME),
     *     "scope"		 => $scope
     *   );   
     */
    
    private function _doLogin($client_account, $callback, $access_token = NULL) {
        
        if (empty($client_account)) {
            return false;
        }
        
        $data = array();
        $data['callback'] = $callback;    
        
        $mUser=ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getClientAccountById($client_account);        
        $stop_flag = intval($userInfo[$client_account]['status']);
        //校验4： 用户激活状态，如果未激活，则增加激活的情况，增加callback参考 callback，方便激活之后的跳转;
        //http://vm.wmw.cn/Uc/activate?callback=
        if ($stop_flag == CLIENT_STOP_FLAG) {
            $data['callback'] = "http://" . C('UC_DOMAIN') . '/Uc/Activate?callback=' . $callback;
        }
        
	    //记录登录登录时间，加入用户在线库，异步操作
        Gearman::send('default_usr_login', $client_account);
	    
        //用户token, 格式看本函数注释
        $json = $this->oauth2->grantAccessToken();
        if (empty($json)) {
            $this->ajaxReturn(null, '登录失败', 0, 'json');     
        }
        $oauth_data = json_decode($json, true);
        //将username 转换为client_account ,适应不同类型账号登录
        $oauth_data['username'] = $client_account;
        if (isset($oauth_data['error'])) {
            $this->ajaxReturn(null, '登录失败', 0, 'json');
        }
        
        $user_token = token_encode($oauth_data);
        
        $expires = time() + COOKIE_TIME_OUT;
        
        // 生成统一认证完毕后的cookies
        $domain = C('COOKIEDOMAIN');
        
        if (empty($domain)) {
            $domain = '.wmw.cn';
        }
        
        // 设置登录cookie
        header("P3P: CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR");
        header(getCookieStr(SNS_SESSION_TOKEN, $user_token, $expires, "/", $domain));
        
        
        $this->ajaxReturn($data, '登录成功', 1, 'json');        
    }
    
    /**
     * 插入uc_login_attempts , 记录用户的失败次数; 仅在登录验证密码失败触发
     */
    private function _doLoginAttemps($client_account) {

        if(empty($client_account)) {
            return false;
        }
        
        $attempts = 1;
        $add_or_upd = 'add';
        
        //1. 检查是否存在
        //如果存在则加1;
        
        $m = ClsFactory::Create('Model.mUcLoginAttempts');
        $datas = $m->getLoginAttemptsById($client_account);
        if (!empty($datas)) {
           if (isset($datas[$client_account]['attempts'])) {
               $attempts = $datas[$client_account]['attempts'] + 1;
               $add_or_upd = 'upd';
           }  
        }
        
        $add_datas = array('client_account' => $client_account,
                             'client_ip'	  => long2ip($_SERVER['REMOTE_ADDR']),
                             'attempts'		  => $attempts,
                             'upd_time'		  => time()
                            );
        if ($add_or_upd == 'add') {
            return $m->addLoginAttempts($add_datas);
        } else if ($add_or_upd == 'upd') {
            return $m->modifyLoginAttempts($add_datas, $client_account);
        }
                                    
        return true;
    }  

    /**
     * 删除uc_login_attempts , 记录用户的失败次数; 仅在登录验证成功后触发
     */
    private function _doDelLoginAttemps($client_account) {
        if(empty($client_account)) {
            return false;
        }
        
        $m = ClsFactory::Create('Model.mUcLoginAttempts');
        $datas = $m->getLoginAttemptsById($client_account);
        if (!empty($datas)) {
           return $m->delLoginAttempts($client_account);  
        }
                        
        return true;
    }     
    
    /**
     * 
     * 处理第三方登录接口绑定业务
     * @param string $client_account  我们网帐号
     * @param string $social_account  登录成功后给网站分配的appid
     * @param string $social_type     社会化登录类型  qzone = qq   
     * @param string $access_token    登录成功后给网孩子分配的access_token
     */
    private function _oauthBind($client_account, $social_account, $social_type, $access_token) {
    	//如果为第三方接口登录，则自动绑定此业务到OAuthBind表:
	    if (empty($social_account)) {
	       return false;   
	    }
	    
    	if (empty($social_type)) {
	       return false;   
    	}
    	
        $m = ClsFactory::Create('Model.mOauthBind');
        $params = array();    	
    	$datas = array();
        $params[] = "client_account = '$client_account'";
        $params[] = "social_type = '$social_type'";        
        $result = $m->getOauthBindBySocialAccountAndType($params);
        
        $datas['id'] = 0;
        if (!empty($result)) {
            $oauth_bind_info = reset($result);
            $datas['id'] = $oauth_bind_info['id'];
        }
    	
        $datas['client_account'] = $client_account;
        $datas['social_account'] = $social_account;
        $datas['social_type']    = $social_type;
        $datas['access_token']   = $access_token;
//        dump($datas);
//        exit;
        $this->uc_client->set_oauth_bind($datas);

	    return true;
    }    
        
    /**
     * 将用户输入的账号信息映射到实际的账号
     */
    private function _convertAccount($input) {
        if(empty($input)) {
            return false;
        }
        
        //先尝试从手机绑定中获取账号信息，如果手机中找到了该账号信息，则返回；
        if(isPhonenum($input)) {
            $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
            $business_phone_list = $mBusinessphone->getbusinessphonebyalias_id($input);
            $business_phone = & $business_phone_list[$input];
            if(!empty($business_phone['uid'])) {
                return $business_phone['uid'];
            }
        }
        
        if(isUid($input)) {
            return $input;
        }
        
        if(isEmail($input)) {
            $time33_key = time33($input);
            //获取email映射到的账号信息
            $mEmailBinding = ClsFactory::Create('Model.mEmailBinding');
            $email_bind_arr = $mEmailBinding->getEmailBindingByTime33Key($time33_key);
            $email_bind_list = $email_bind_arr[$time33_key];
            //查找对应的账号信息
            foreach((array)$email_bind_list as $bind) {
                if($bind['email'] == $input) {
                    return $bind['client_account'];
                }
            }
            return false;
        }        
        
        return false;
    }
    
    public function test($param1, $param2, $param3) {
    	
    	dump(array($param1, $param2, $param3));
    	
    	//$callback = $this->objInput->postStr('callback');
    	echo 'licheng si liumeng';
    }
    
}