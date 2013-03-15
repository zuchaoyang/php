<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:测试异步队列接口
 * 
*/

class TqueueApi extends ApiController {

    public function __construct() {
        parent::__construct();
    }    
    
	public function index() {

	}
	
    public function _initialize(){
		parent::_initialize();        
    }	    

    public function dosync() {
    	$result = Gearman::send('feed_dispatch', 5);
    	dump($result);
    }
}