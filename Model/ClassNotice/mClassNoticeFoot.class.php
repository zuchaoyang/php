<?php
class mClassNoticeFoot extends mBase {
    protected $_dClassNoticeFoot = null;
	
	public function __construct() {
		$this->_dClassNoticeFoot = ClsFactory::Create('Data.ClassNotice.dClassNoticeFoot');
	}
	
	/**
	 * 添加浏览记录
	 */
	public function addClassNoticeFoot($dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNoticeFoot->addClassNoticeFoot($dataarr);
	    
	    return !empty($resault) ? $resault : false;
	}

	/**
	 * 根据主键删除公告浏览记录
	 */
	public function delClassNoticeFoot($notice_id) {
	    if(empty($notice_id)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNoticeFoot->delClassNoticeFoot($notice_id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 根据主键查询公告浏览表
	 */
	public function getClassNoticeFoot($ids) {
	    if(empty($ids)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassNoticeFoot->getClassNoticeFoot($ids);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	
	/**
	 * 根据帐号和外键notice_id查询数据
	 */
	public function getClassNoticeFootByNoticeIdAndAccount($wherearr) {
	    if(empty($wherearr)) {
	        return false;
	    }
	    
	    return $this->_dClassNoticeFoot->getInfo($wherearr);
	}
	
	/**
	 * 获取单个用户的公告的回执信息
	 * @param $client_account
	 * @param $where_appends
	 */
	public function getNoticeFootByClientAccount($client_account, $where_appends = array()) {
	    if(empty($client_account)) {
	        return false;
	    }
	    
	    $client_account = is_array($client_account) ? array_shift($client_account) : $client_account;
	    $wherearr = array(
	    	"client_account='$client_account'"
	    );
	    if(!empty($where_appends)) {
	        $wherearr = array_merge($wherearr, (array)$where_appends);
	    }
	    
	    return $this->_dClassNoticeFoot->getInfo($wherearr);
	}
	
	/**
	 * 根据外键查询公告浏览记录
	 */
    public function getClassNoticeFootByNoticeId($notice_ids) {
        if(empty($notice_ids)) {
            return false;
        }
        
        return $this->_dClassNoticeFoot->getClassNoticeFootByNoticeId($notice_ids);
    }
}