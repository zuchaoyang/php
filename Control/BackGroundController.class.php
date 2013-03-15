<?php
//import('@.Common_wmw.ThinkOAuth2');

define('WORKER_DIR', WEB_ROOT_DIR . '/Daemon/asynchronous/Workers');//异步队列工作目录

class BackGroundController extends Controller {
    
    protected $oauth2 = NULL;
    
    //注意大小写
    protected $_tasks = array();
    
    public static $_taskClassPool;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function _initialize(){
        parent::_initialize();
    }
    
    //获取一个任务对象 注意大小写
    public function getTaskClass($_taskName) {
        
        if (empty($_taskName)) {
            return NULL;
        }
        
        $className = $_taskName;
        //资源对象已创建 直接返回使用
        if( isset(self::$_taskClassPool[$className]) && is_object(self::$_taskClassPool[$className]) ){
            return self::$_taskClassPool[$className];
        }
        
        //加载文件资源类文件

        $file = WORKER_DIR."/task/$className.class.php";
        if( file_exists($file) ){
            require_once $file;
            if( class_exists($className) ){
                return self::_createTaskClass($className);
            } else {
                if(C('LOG_RECORD')) Log::write('task load class failed:',  "no class {$className} in file {$file}", Log::ERR);
            }
        } else {
            if(C('LOG_RECORD')) Log::write('task load class file failed:',  "no class file {$file}", Log::ERR);
        }
        
        return NULL;
    }
    
    //创建资源对象
    public function _createTaskClass($_className){
        if( isset( self::$_taskClassPool[$_className] ) && 
            is_object( self::$_taskClassPool[$_className] ) ) {
            return self::$_taskClassPool[$_className];
        } else {
            self::$_taskClassPool[$_className] = new $_className();
            return self::$_taskClassPool[$_className];
        }
    }    

}