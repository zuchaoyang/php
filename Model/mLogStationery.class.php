<?php
class mLogStationery extends mBase {

    protected $_dLogStationery = null;
    
    public function __construct(){
        $this->_dLogStationery = ClsFactory::Create('Data.dLogStationery');
    }
    
	//读取所有信纸
	public function getAllLogStationery(){
        $arrMySqlData_list = $this->_dLogStationery->getInfo(null, 'id desc');
        
		return  $arrMySqlData_list;
	}













}
