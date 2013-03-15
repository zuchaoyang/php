<?php
/**
 * UCenter 统一登录类
 * @author    lnczx <lnczx0915@gmail.com>
 */

class Oauth2Action extends UcController {

    public $_isLoginCheck = false;
    
    public $oauth2_client = NULL;
    
    public $callback = '';
    
    /**
     *  初始化社会化 Object Client 对象，参考Common_wmw/Vendor/Oauth2/Client
     *  必须参数:
     *      $connect  目前支持的社会化登录参数  qzone = qq  
     *     
     */    
    
    public function _initOauth2($connect){
        
        $supported_oauth2_type = C('SUPPORTED_OAUTH2_TYPE');
        if (empty($connect) || !isset($supported_oauth2_type[$connect])) {
            //todo error
            exit;
        }

        switch ($connect) {
            case 'qzone' : 
                import('@.Common_wmw.Vendor.OAuth2.Client.QzoneTOAuth2', null, '.php');
                
                $client_id = $supported_oauth2_type[$connect]['client_id'];
                $client_secret = $supported_oauth2_type[$connect]['client_secret'];
                $callback = $supported_oauth2_type[$connect]['callback'];
                $this->oauth2_client = new QzoneTOAuth2($client_id, $client_secret);
                $callback = $callback . '/Uc/Oauth2/qzone_callback';
                break;
            case 'sina'	 :
                //todo
                break;
                
        }
        
        $url_params = array();
        $url_params['connect'] = $connect;
        $redirect_url = $this->objInput->getStr('redirect_url');
        if (!empty($redirect_url)) {
            $url_params['redirect_url'] = $redirect_url; 
        }
        $this->callback = $callback . '?'. http_build_query($url_params);
        
    }    
    
    /**
     *  社会化登录 跳转,并初始化SESSION['oauth2_state'] 作为client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
     */        
    public function login() {
        session_start();
        $connect = $this->objInput->getStr('connect');
        $this->_initOauth2($connect);

        if (empty($this->oauth2_client)) return;
        (string)$state = md5(date('YmdHis'));
        $_SESSION['oauth2_state'] = $state;

        $this->oauth2_client->login($this->callback, $state);
    }
    
    /**
     *  社会化登录后call_back 处理接口
     *  必须参数:
     *      state:  作为client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
     *     
     */        
    public function qzone_callback() {

        $state = isset($_GET['state'])?(string)$_GET['state']:false;
        $redirect_url = $this->objInput->getStr('redirect_url');
        
        if($_SESSION['oauth2_state'] !== $state ) {
            return false;
        }
        
        $connect = $social_type = 'qzone';
        $this->_initOauth2($connect);
        $qzone_info = $this->oauth2_client->callback($this->callback, $state);

        $openid = $qzone_info['openid'];
        $access_token = $qzone_info['access_token'];

        $this->_do_callback($openid, $social_type, $access_token, $redirect_url);
    }
    
    private function _do_callback($social_account, $social_type, $access_token, $redirect_url) {

        if (empty($social_account)) return false;
        if (empty($social_type)) return false;
        if (empty($access_token)) return false;
        
        $callback = C('WMW_SERVER');
        
        //此地方逻辑分为三个步骤
        //1. 如果未登录，未绑定,则走display_social_login_to_wmw方法
        //2. 如果未登录，已绑定,则走social_auto_login_to_wmw方法
        //3. 如果已登录，则走set_social_bind方法
        
        $username = $this->getCookieAccount();
        $redirect_url = urldecode($redirect_url);
        
        $m = ClsFactory::Create('Model.mOauthBind');
        if (!empty($username)) {

            // 做判断是否该QQ号被其他帐号绑定过
            $params = array();
            $params[] = "social_account = '$social_account'";
            $params[] = "social_type = '$social_type'";        
            $checkResult = $m->getOauthBindBySocialAccountAndType($params);            
            if (!empty($checkResult)) {
                $info = reset($checkResult);
                $exist_client_account = $info['client_account'];
                if ($username != $exist_client_account) {
                    
                    $this->showError('QQ帐号已被其他我们网帐号设置,操作中断!', $redirect_url);
                    exit;
                }
            }
            
            $params = array();
            $params[] = "client_account = '$username'";
            $params[] = "social_type = '$social_type'";        
            $result = $m->getOauthBindBySocialAccountAndType($params);
            if (!empty($result)) {
                $oauth_bind_info = reset($result);
            }            
            
            $datas = array();            

            if ($oauth_bind_info == false) {
                $id = 0;
            } else {
                $id = $oauth_bind_info['id'];
            }
            $datas = array( 'id'             => $id,
                          'client_account'   => $username,
                          'social_account'	 => $social_account,
                          'social_type'		 => $social_type,
                          'access_token'	 => $access_token,
                          'add_time'		     => time()
                     );
            $this->uc_client->set_oauth_bind($datas);
            

            header("Location:$redirect_url");
            exit;

        } else {
//             dump('111');
             $oauth_bind_info = $this->check_oauth2_bind($social_account, $social_type);

             if ($oauth_bind_info == false) {
                $this->display_social_login_to_wmw($social_account, $social_type, $access_token);
             } else {         
                $client_account = $oauth_bind_info['client_account'];
                $social_account = $oauth_bind_info['social_account'];             
                
                $oauth_bind_info['access_token'] = $access_token;
                $this->uc_client->set_oauth_bind($datas);
                $this->social_auto_login_to_wmw($client_account, $social_account, $social_type, $callback, $access_token);
            }
        }        
        
    }

    /**
     *  社会化登录后,检测如果未与我们网和帐号绑定，则需要展现登录窗口，登录成功后自动绑定
     *  必须参数:
     *      $social_account:  登录成功后分配给网站的appid
     *      $social_type   :  社会化登录的方式 qzone = qq 
     */    
    
    private function display_social_login_to_wmw($social_account, $social_type, $access_token) {

        if (empty($social_account) || empty($social_type)) {
            return false;
        }
        
        $client_id = C('CLIENT_ID');
        $client_secret = C('CLIENT_SECRET');
        
        $wmw_server = C('WMW_SERVER');
        $callback = urlencode($wmw_server );
        $this->assign('callback', $callback);
        $this->assign('client_id', $client_id);
        $this->assign('client_secret', $client_secret);
        $this->assign('social_account', $social_account);
        $this->assign('social_type', $social_type);
        $this->assign('access_token', $access_token);
        $this->display('./login/social_login_to_wmw');
    }
    
    /**
     *  社会化登录后,检测已经与我们网和帐号绑定，则自动填充参数后登录，参考/uc/LoginApiAction/social_login
     *  必须参数:
     *      $client_account:  我们网帐号
     *      $social_account:  登录成功后分配给网站的appid
     *      $callback   :     登录成功后跳转页面
     *      $access_token :   登录成功后分配给网站的Access Token
     */
    
    private function social_auto_login_to_wmw($client_account, $social_account, $social_type,  $callback, $access_token) {
        
        if (empty($client_account) || empty($social_account)) {
            return false;
        }

        $oauth_bind_info = $this->check_oauth2_bind($social_account, $social_type);

        if ($oauth_bind_info == false) {
            return false;
        } else {
            if ($client_account != $oauth_bind_info['client_account']) {
                return false;
            }
        }

        $client_id = C('CLIENT_ID');
        $client_secret = C('CLIENT_SECRET');        
        $img_server = IMG_SERVER;
        $public = WEB_PUBLIC_PATH;        
        
echo <<<EOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> 

<script type="text/javascript" src="{$img_server}{$public}/js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="{$img_server}{$public}/uc/js/login/social_login.js"></script>

</head>
<body>
	   <div id="captcha" style="display:none"></div>
	   <input type="hidden" id="username" name="username" value="{$client_account}"></input>
	   
       <input type="hidden" id="client_id" name="client_id" value="{$client_id}"></input>
       <input type="hidden" id="client_secret" name="client_secret" value="{$client_secret}"></input>
       <input type="hidden" id="callback" name="callback" value="{$callback}"></input>

       <input type="hidden" id="social_account" name="social_account" value="{$social_account}"></input>
       <input type="hidden" id="social_type" name="social_type" value="{$social_type}"></input>
       <input type="hidden" id="access_token" name="access_token" value="{$access_token}"></input>
       
登录中...
</body>
</html>
EOF;
        exit;
    }
    
    /**
     *  检查与Oauth2登录绑定的接口，用于例如QQ登录，新浪微博登录等
     *  必须参数:
     *      $appid
     *  可选参数:
     *      无
     *  返回          :
     *      直接返回Comon_wmw.captecha 类对应的验证码图片
     *     
     */
    
    public function check_oauth2_bind($social_account, $social_type) {

        if(empty($social_account)) {
            return false;
        }
        
        $m = ClsFactory::Create('Model.mOauthBind');
        $params = array();
        $params[] = "social_account = '$social_account'";
        $params[] = "social_type = '$social_type'";        
        $oauth_bind_info = $m->getOauthBindBySocialAccountAndType($params);
        if (!empty($oauth_bind_info)) {
            $result = reset($oauth_bind_info);
            return !empty($result) ? $result : false;
        }
        
        return false;
    }
}
?>
