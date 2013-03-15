<?php
define("WMW_AUTO_LOADER_DIR", dirname(__FILE__));
WmwAutoLoader::register();

class WmwAutoLoader {
    public static function register() {
        //注册自动加载类
        spl_autoload_register('__autoload');
        return spl_autoload_register(array('WmwAutoLoader', 'AutoLoad'));
    }
    
    public static function AutoLoad($objectName) {
        if(class_exists($objectName) || empty($objectName)) {
            return false;
        }
        
        $dir = dir(WMW_AUTO_LOADER_DIR);
        while(($file = $dir->read()) !== false) {
            if(in_array($file, array('.', '..')) || !is_dir(WMW_AUTO_LOADER_DIR . "/" . $file)) {
                continue;
            }
            
            $objectFilePath = WMW_AUTO_LOADER_DIR . "/$file/$objectName" . ".class.php";
            if (file_exists($objectFilePath) && is_readable($objectFilePath)) {
    			require($objectFilePath);
    			break;
    		}
        }
    }
}