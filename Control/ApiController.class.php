<?php
/**
 * UCenter 控制类
 * @author lnczx
 */

//import('@.Common_wmw.ThinkOAuth2');
abstract class ApiController extends Controller {

    protected $oauth2 = NULL;
        
    public function __construct() {
        parent::__construct();
    }
    
    public function _initialize(){
        parent::_initialize();
//        $this->oauth2 = new ThinkOAuth2();   
    }
    
}
