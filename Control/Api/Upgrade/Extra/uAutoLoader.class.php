<?php
define('U_AUTO_LOAD_DIR', dirname(__FILE__));
uAutoLoader::register();

class uAutoLoader {
    public static function register() {
        //注册自动加载类
        spl_autoload_register('__autoload');
        return spl_autoload_register(array('uAutoLoader', 'AutoLoad'));
    }
    
    public static function AutoLoad($className) {
        if(class_exists($className, false) || empty($className)) {
            return false;
        }
        
        $dir_path = realpath(U_AUTO_LOAD_DIR . "/../");
        if(strtolower(substr($className, 0, 4)) == 'pack') {
            $dir_path = $dir_path . "/Pack/";
        }
        
        $dir = dir($dir_path);
        while(($file = $dir->read()) !== false) {
            if(in_array($file, array('.', '..'))) {
                continue;
            }
            $classFile = $dir_path . "/$className.class.php";
            if(file_exists($classFile) && is_readable($classFile)) {
                require($classFile);
                break;
            }
        }
    }
}