<?php
class mScheduleType extends mBase{
	//查看日程分类用户自定义表
	protected $_dScheduleType = null;
	
	public function __construct() {
	    $this->_dScheduleType = ClsFactory::Create('Data.dScheduleType');
	}
	
 	public function getScheduleTypeById($typeids){
		return $this->_dScheduleType->getScheduleTypeById($typeids);
	}
	
	//添加日程分类
	public function addScheduleType($datarr, $return_insert_id = false){
		 if(empty($datarr) || !is_array($datarr)) {
	            return false;
	     }
	     
	     return $this->_dScheduleType->addScheduleType($datarr, $return_insert_id);
	}
	
	//修改日程类型分类
	public function modifyScheduleType($datarr,$id){
		if(empty($id) && empty($datarr)){
			return false;
		}

		return $this->_dScheduleType->modifyScheduleType($datarr, $id);
	}
	
	//删除日程类型分类
	public function delScheduleType($id){
		if(empty($id)){
			return false;
		}
		
		return $this->_dScheduleType->delScheduleType($id);
	}
	
	//查询日程分类
	//param: with_sys:是否同时查询系统默认分类表
	public function getScheduleTypeByUid($client_account, $with_sys=false){
	    if(empty($client_account)) {
	        return false;
	    }
	    $this->_dScheduleType->switchToScheduleType();
	    $st_list = $this->_dScheduleType->getScheduleTypeByUid($client_account);
	    $st_list = &$st_list[$client_account];
	    
	    if($with_sys) {
	        $this->_dScheduleType->switchToScheduleTypeSysTem();
		    $st_list_sys = $this->_dScheduleType->getInfo();
	        foreach($st_list_sys as $key=> $info) { //系统标志位
	            $st_list_sys[$key]['is_system'] = 1;
    		}
	    }
	    
	    if(!empty($st_list_sys)) { //合并数组
	        if(!empty($st_list)) {
	            $st_list = array_merge($st_list_sys, $st_list);
    	        foreach($st_list as $key=> $info) {
                    $new_list[$info['type_id']] = $info ;
                }
                $st_list = $new_list;
	        } else {
	            $st_list = $st_list_sys;
	        }
	    }
		
		return !empty($st_list) ? $st_list : false;
	}
	
	
}