<?php
class mCheckin extends mBase{
	protected $_dCheckin = null;
    
    public function __construct() {
		$this->_dCheckin = ClsFactory::Create('Data.Checkin.dCheckin');
	}
	
	public function add_checkin($dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dCheckin->add_checkin($dataarr);
	}
	
	public function getCheckinByInfo($wheresql,$orderby=null,$offset=null,$limit=null) {
	    if(empty($wheresql)) {
	        return false;
	    }
	    
	    return $this->_dCheckin->getInfo($wheresql,$orderby,$offset,$limit);
	}
}