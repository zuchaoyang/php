<?php
//todolist 代码涉及的业务可能有问题
class mClientClassHistory extends mBase {

	protected $_dClientClassHistory = null;
	
	public function __construct() {
		$this->_dClientClassHistory = ClsFactory::Create('Data.dClientClassHistory');
	}
	
	public function addClientClassHistoryBat($dataarr) {
	    if (empty($dataarr ) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dClientClassHistory->addBat($dataarr);
	}
}

