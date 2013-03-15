<?php
class mFunc extends mBase {
	protected $_dFunc = null;
	
	public function __construct() {
		$this->_dFunc = ClsFactory::Create('Data.dFunc');
	}
	
	/**
	 * 根据功能类型 和是否显示 ，获取功能权限列表
	 * @param $func_type 类型
	 * @param $is_showflag 是否显示默认显示
	 * @param $order 排序字段 和排序方式 正序倒序例子：func_num asc
	 * @return 返回权限列表
	 *  
	 */
	public function getFuncByFuncTypeAndIsShowflag($func_type, $is_showflag = 1, $order = ''){
		if (empty($func_type) || $is_showflag == '') {
			return false;
		}

		$wheresql = array(
    		"func_type='$func_type'",
    		"is_showflag='$is_showflag'"
		);
		return $this->_dFunc->getInfo($wheresql, $order);
	}
	
	//获取所有功能（默认不包括nolink及is_showflag=0的功能）
	public function getFunc($is_showflag = 1, $with_super_fc=false, $order = '') {
	    if($is_showflag) {
	        $wheresql[] = "is_showflag='$is_showflag'"; 
	    }
	    if(!$with_super_fc) {
	        $wheresql[] = "super_func_code!='NOLINK'"; 
	    }
	    
		return $this->_dFunc->getInfo($wheresql, $order);		
	}
	
}