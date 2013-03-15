<?php
/**
 * UCenter 统一注销类
 * @author    lnczx <lnczx0915@gmail.com>
 */

class LogoutAction extends UcController {
    
    public function index(){
          //todo 根据不同系统展现不同登录页面风格
        $callback = $this->objInput->getStr('callback');
        if (empty($callback)) {
            $uc_domain = C('UC_DOMAIN');
            $callback =  urlencode('http://'.$uc_domain . '/Uc/index');
        }              
        
        $callback_decode = urldecode($callback);
  

        $client_id = $this->objInput->getStr('client_id');
        $client_secret = $this->objInput->getStr('client_secret');
        //  如果域名是主站或者UC域名，则获取配置client_id client_secret
        if (empty($client_id)) {
            $domain = get_domain($_SERVER['HTTP_HOST']);
            if (C('COOKIEDOMAIN') == ".$domain") {
                $client_id = C('CLIENT_ID');
                $client_secret = C('CLIENT_SECRET');
            }
        }        

        $app = $this->objInput->getStr('app');
        if (empty($app)) {
            $app = 'uc';
        }
        
        $uc_login_url = $this->uc_client->get_uc_login_url($app, $callback_decode);
        
        //todo 变成一个提示模板
        $img_server = IMG_SERVER;
        $public = WEB_PUBLIC_PATH;
        $app_domains = json_encode(C('APP_DOMAINS'));   // 将分别发生logout指令到所有的应用中，包括主域名
echo <<<EOF
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> 

<script type="text/javascript" src="{$img_server}{$public}/js/jquery-1.5.2.min.js"></script>
<script>

    var domains={$app_domains};
    var ajaxes = [];
    for ( var i = 0; i < domains.length ; i++ ) {
    	var url =  "http://" + domains[i] + "/api/Uclient/logout";
    	ajaxes.push(url);
    }
	var current = 0;
	function do_ajax_logout() {
        //check to make sure there are more requests to make
        if (current < ajaxes.length) {
    		
            $.ajax({
                url 			: ajaxes[current],
    	        dataType: 'jsonp',
    	        jsonp: 'callback',
    	        jsonpCallback: 'jsonpCallback',  
    	        timeout : 5000, 
    	        success: function(data, status){
  	           
    	        },
    	        error: function(XHR, textStatus, errorThrown){
                    current++;
                    do_ajax_logout();
        		}
            });
        } else {
        	window.location.href = "{$uc_login_url}";
        }
    }

	function jsonpCallback(data){	
		current++;
		do_ajax_logout();
	}
	
	do_ajax_logout();
	 
</script>
</head>
<body>
正在注销...
</body>
</html>
EOF;
        exit;

    }    
    
    
}
