<?php
/**
 * 前端控制层基类
 * @author Administrator
 * 1. 功能说明：
 *    a. 通过$_isLoginCheck参数配置自动实现用户的登录拦截,并加载用户信息；
 *    b. 执行命令检测用户是否激活，是否冻结等；
 *    c. 统一了前台的登录和退出的接口；
 */
abstract class FrontController extends Controller {
    protected $user;
    
    protected $uc_client;
    
    public $_isLoginCheck = true;
    
    protected $appName = 'uc';
    
    public function __construct() {
        parent::__construct();
    }
    
	public function _initialize() {
		parent::_initialize();
	    if(method_exists($this,'controllerAop')) {
            $this->controllerAop();        
        }
	}    
    
	/**
	 * 登录拦截器
	 */
    public function controllerAop(){
    	header("Content-Type:text/html; charset=utf-8");
    	$this->uc_client = ClsFactory::Create('@.Control.Api.UclientApi');
    	
    	//检查用户是否登录;
    	if ($this->_isLoginCheck) {
    	    $this->login_aop();
    	}
 	    // 执行命令模式 去掉每个C都检测的情况
//        $this->run_command();
    }
    
    //判断用户是否登录.
    protected function login_aop() {
        $access_token = $this->uc_client->get_access_token();
        $username = $this->getCookieAccount();

        if (empty($access_token) || empty($username)) {
            // 跨域调用统一获取cookie,
            $domain = get_domain($_SERVER['HTTP_HOST']);
            //是否跨域
            if (C('COOKIEDOMAIN') != ".$domain") {
                $this->crossDomainLogin($domain);
            } else {
                // 当前域没有登录，跳转到登录页面
                $this->toLogin();
            }
        }
        
        //设定当前登录用户
        $this->initCurrentUser();
    }
    
    /**
     * 初始化当前用户信息
     */
    abstract protected function initCurrentUser();
    
    //跳转到登录页面
    public  function toLogin($callback = '') {
        $sso_url = $this->uc_client->get_uc_login_url($this->appName, $callback);
        header("Location:$sso_url");
        exit;
    }
    
    /**
     * 跨域登录
     * @param $domain
     */
    public function crossDomainLogin($domain = 'zscy.cn') {
        
        $uc_get_cookie_url = $this->uc_client->get_uc_cookie_url();
        $uc_get_cookie_url = $uc_get_cookie_url . '?' . rand();
        
        $uc_login_url = $this->uc_client->get_uc_login_url();
        $img_server = IMG_SERVER;
        $public = WEB_PUBLIC_PATH;
        $cookie_name = SNS_SESSION_TOKEN;
        $expires = time() + COOKIE_TIME_OUT;
echo <<<EOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> 

<script type="text/javascript" src="{$img_server}{$public}/js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="{$img_server}{$public}/js/jquery_plugins/jquery.cookie.js"></script>
<script>

	$(document).ready(function(){    
	    $.ajax({
	        url: "{$uc_get_cookie_url}",
	        dataType: 'jsonp',
	        jsonp: 'callback',
	        jsonpCallback: 'jsonpCallback',
	        success: function(){
	           
	        }
	    });
	});
	function jsonpCallback(data){
		var logined = data.logined;
		var token = data.token;
		if (logined) {
	    	$.cookie("{$cookie_name}", token, {domain:"{$domain}", expire:{$expires}, path:"/"});
	    	window.location.reload();
	    } else {
	    	window.location.href = "{$uc_login_url}";
	    }		
	}
</script>
</head>
<body>
验证登录...
</body>
</html>
EOF;
        exit;
    }

    //获取用户账号
    //其他的应用比较wms ams bms 的登录获取cookie覆盖此方法
	protected  function getCookieAccount(){
		$token = $this->uc_client->get_uc_cookie_token_info();
		$username = '';
		if (isset($token['username'])) {
		    $username = $token['username'];
		}
		return $username;
	}

    // 执行命令
    protected function run_command() {       
        //检查用户是否激活,去掉每个C都检测的情况
//        $this->add_command( ClsFactory::Create('@.Control.Command.CheckActived') );
        
        $this->_before_run_command();
        
        //执行系列命令
        $chainobj = ClsFactory::Create('@.Control.Command.CommandChain');
        $chainobj->addCommand($this->commandobjarr);
        if(Db::getDbConf('main')) {
            $chainobj->runCommand();
        } 
        
        $this->_after_run_command();
        
    }
    
	protected function add_command($command = null) {
	    if (empty($command)) {
	        return false;
	    }
	    
	    if (!($command instanceof Command)) {
	        return false;
	    }
	    
	    $this->commandobjarr[] = $command;
	}    
    
    private function _before_run_command() {
        //extend me
    }
    
    private function _after_run_command() {
        //extend me
    }    

}
