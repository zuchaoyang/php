<?php
/**
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:UCenter Client
 * 说明:	作为与UCenter通信的接口类，并提供通用的与用户信息有关的方法
 * 
*/

class DemoApi extends ApiController {

    /**
     * 
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
    }    
   
    /**
     * 
     * 固定函数
     */    
    public function _initialize(){
		parent::_initialize();        
    }	    

	/**
	 * 获取相片列表接口
	 *
	 * 
	 *
	 * @param int $albumId  相册ID  
	 * @param int $offset   分页偏移量
	 * @param int $limit    分页个数
	 * @param string $display 授权页面类型 可选范围: 
	 *  - default		默认授权页面		
	 *  - mobile		支持html5的手机		
	 *  - popup			弹窗授权页		
	 *  - wap1.2		wap1.2页面		
	 *  - wap2.0		wap2.0页面		
	 *  - js			js-sdk 专用 授权页面是弹窗，返回结果为js-sdk回掉函数		
	 *  - apponweibo	站内应用专用,站内应用不传display参数,并且response_type为token时,默认使用改display.授权后不会返回access_token，只是输出js刷新站内应用父框架
	 * @return json
	 */
    
    public function show_class($albumId = 0, $offset = 0, $limit = 10) {
        
    }
    
    public function show_person() {
        
    }
    
    public function create() {
        
    }    
    
    public function update() {
        
    }
    
    public function destroy() {
        
    }
    
    
}