<?php
class MapAction extends SnsController{
		
	public $_isLoginCheck = false;
    
	public function _initialize(){
		parent::_initialize();
	}
	
	//WMW成长地图
	public function school_map(){
		$this->display('school_map');
	}
}

