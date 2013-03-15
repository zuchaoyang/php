<?php
//佣金功能，属于临时文件，当表 chinaunicom的数据为空，此功能将作废
//lnc
class mChinaUnicom extends mBase{
    
    protected $_dChinaUniocom = null;
    
	public function __construct() {
		$this->_dChinaUniocom = ClsFactory::Create('Data.dChinaUnicom');

	}    
    
    function getPhoneInfoAll(){
        return $this->_dChinaUniocom->getInfo();
    }
}