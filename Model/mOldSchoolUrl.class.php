<?php
class mOldSchoolUrl extends mBase {
    
    protected $_dOldSchoolUrl = null;
    
    public function __construct() {
        $this->_dOldSchoolUrl = ClsFactory::Create('Data.dOldSchoolUrl');
    }
    
	/**
	 * 查看该学校申请的新网址是否已被注册了
	 * @param 	string 	$newUrl		新申请的网址
	 * @return 	Boolean true|false	true表示已经存在，false表示不存在
	 */
	public function checkOldSchoolUrlForUrlIsExist($newUrl) {
	    if(empty($newUrl)) {
	        return false;
	    }
	    $wheresql = array(
	        "school_url='$newUrl'"
	    );
	    $list = $this->_dOldSchoolUrl->getInfo($wheresql, null, 0, 1);
	    
	    return !empty($list) ? $list : false;
	}
	
}