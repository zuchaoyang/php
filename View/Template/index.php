<?php

include_once('./global.inc.php');

//检测需要调整处理的页面请求
$redirect_url_list = array(
    //'ams' => '/Amscontrol/Amslogin/index',
    'ams' => '/Amscontrol/Index/index',
    'bms' => '/Basecontrol/Index/index',
    'sns' => '/Homeuser/index',
    'wms' => '/Adminbase/Body/index',
);

$request_uri = trim(urldecode($_SERVER['REQUEST_URI']));

if($request_uri{0} == '/') {
    $request_uri = substr($request_uri, 1);
}

$redirect_url = '';
if(!empty($request_uri)) {
    $path_arr = explode('/', $request_uri);
    if(count($path_arr) == 1) {
        $script_name = strtolower(array_shift($path_arr));
        if(strpos($script_name, '.html') !== false) {
            $module_name = trim(array_shift(explode('.', $script_name)));
            $redirect_url = isset($redirect_url_list[$module_name]) ? $redirect_url_list[$module_name] : false;
        }
    }
} else {
    $host = $_SERVER['HTTP_HOST'];
    $module_name = strtolower(array_shift(explode('.', $host)));
    $redirect_url = isset($redirect_url_list[$module_name]) ? $redirect_url_list[$module_name] : false;
}

//判断页面是否需要跳转
if(!empty($redirect_url)) {
    header("Location:" . $redirect_url);
    exit;
}

//实例化一个网站应用实例 
App::run();