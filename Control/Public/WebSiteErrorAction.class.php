<?php
class WebSiteErrorAction extends Controller{
    
    public function _initialize(){
        header("Content-Type:text/html;charset=utf-8");
    }
    
    public function index() {
        
        $protocol = 'http://';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https://';
        }
        $home_url = $protocol . $_SERVER['HTTP_HOST'];
        $this->assign('home_url', $home_url);
        $this->display('website_error');
    }
}