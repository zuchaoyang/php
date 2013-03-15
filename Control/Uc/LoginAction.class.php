<?php
/**
 * UCenter 统一登录类
 * @author    lnczx <lnczx0915@gmail.com>
 */

class LoginAction extends UcController {

    public $_isLoginCheck = false;
    
    public function index(){
        
        //todo 根据不同系统展现不同登录页面风格
        $callback = $this->objInput->getStr('callback');
        $app = $this->objInput->getStr('app');
        $client_id = $this->objInput->getStr('client_id');
        $client_secret = $this->objInput->getStr('client_secret');
        
        //合法的应用组
        $app_groups = array(
            'wmw',
            'wm616',
            'zscy',
        );
        $app = !empty($app) && in_array($app, $app_groups) ? $app : 'wm616';
        
        if (empty($callback)) {
            $uc_domain = C('UC_DOMAIN');
            $callback = urlencode('http://'.$uc_domain . '/Uc/index');
        }
        $callback_decode = urldecode($callback);
        
        // 如果域名是主站或者UC域名，则获取配置client_id client_secret
        if (empty($client_id)) {
            $domain = get_domain($_SERVER['HTTP_HOST']);
            if (C('COOKIEDOMAIN') == ".$domain") {
                $client_id = C('CLIENT_ID');
                $client_secret = C('CLIENT_SECRET');
            }
        }
        
        // 注意 : View/Template/uc/login/皮肤与应用名称相等
        // 比如 默认主站用的是 View/Template/uc/login/uc_login.html
        //     雏鹰则用的是   View/Template/uc/zscy        
        $skin = 'default';
        if (empty($app) || $app == 'wmw' || $app == 'wm616') {
            $skin = 'uc';
        } else {
            $skin = $app;
        }
        
        if ($app == "zscy") {
            //todo 忘记密码的路径,雏鹰不走我们网的用户中心，仅在统一登录入口进入
//            $url_datas = parse_url($callback_decode);
//            $findpwd_url = $url_datas['scheme'] . '://' . $url_datas['host'] . '/User/Findpwd/findpwdAccount';
//            echo $findpwd_url;
//            $this->assign('findpwd_url', $findpwd_url);
              $zscy_server_url = C('ZSCY_SERVER');
              $this->assign('zscy_server_url', $zscy_server_url);
        }
        $this->assign('callback', $callback);
        $this->assign('client_id', $client_id);
        $this->assign('client_secret', $client_secret);
        
//        $captcha = $this->show_captcha();
//        $this->assign('captcha', $captcha);
        
        $this->display("./login/". $skin . "_login");  
    }

    
//    Public function show_captcha(){
//        // 导入Image类库
//        $captcha = ClsFactory::Create('@.Common_wmw.Captcha');
//        return $captcha->showCaptcha();
//    }    
}
?>
