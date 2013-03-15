<?php
define('WEB_ROOT_DIR', dirname(__FILE__));//网站根目录
define('CONFIGE_DIR', WEB_ROOT_DIR . '/Config');
define('WMW_COMMON', WEB_ROOT_DIR . '/Common_wmw');//网站根目录
include_once(CONFIGE_DIR . '/define.php');
include_once(CONFIGE_DIR . '/vocation.php');
include_once(LIBRARIES_DIR . '/common.php');
include_once(LIBRARIES_DIR . '/ClsFactory.class.php');
include_once(LIBRARIES_DIR . '/Gearman.class.php');
// 定义项目名称和路径 
define('APP_NAME',  'wmw');
//清除核心缓存
//define('NO_CACHE_RUNTIME' ,True);
//define('APP_PATH' ,  WEB_ROOT_DIR);
// 定义 ThinkPHP 框架路径 ( 相对于入口文件 )
//define('THINK_PATH', WEB_ROOT_DIR . '/ThinkPHP/');

//判断是否是ajax请求
define('IS_AJAX_REQUESTED', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false);
	

require('WCF.php');