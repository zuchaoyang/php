<?php
class LoginAction extends Controller {
    protected $allow_app_names = array(
        'ams', 
        'bms', 
        'wms'
    );
    
    public function _initialize(){
		header("Content-Type:text/html; charset=utf-8");
    }
    
    public function login() {
        $app_name = $this->objInput->getStr('app_name');
        
        $app_name = strtolower($app_name);
        if(empty($app_name) || !in_array($app_name, $this->allow_app_names)) {
            $this->showError("登录出错！");
        }
        
        $class_name = ucfirst($app_name) . "Login";
        $appObject = ClsFactory::Create("Control.Adminlogin.Login." . $class_name);
        
        $appObject->setView($this->view);
        
        $appObject->login();
    }
    
    public function loginin() {
        $uid = $this->objInput->postStr('uid');
        $passwd = $this->objInput->postStr('passwd');
        
        $app_name = $this->objInput->getStr('app_name');
        $app_name = strtolower($app_name);
        
        if(empty($app_name) || !in_array($app_name, $this->allow_app_names)) {
            $this->showError("登录出错！");
        }
        
        $class_name = ucfirst($app_name) . "Login";
        
        $appObject = ClsFactory::Create("Control.Adminlogin.Login." . $class_name);
        
        $appObject->setView($this->view);
        
        $appObject->loginin($uid, $passwd);
    }
    
    public function loginout() {
        
        $app_name = $this->objInput->getStr('app_name');
        
        $app_name = !empty($app_name) && in_array($app_name, $this->allow_app_names) ? $app_name : 'ams';
        
        $class_name =  ucfirst($app_name) . "Login";
        
        $appObject = ClsFactory::Create("Control.Adminlogin.Login." . $class_name);
        
        $appObject->setView($this->view);
        
        $appObject->loginout();
    }
}