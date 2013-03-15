<?php
class dScheduleType extends dBase{
    protected $_tablename = null;
    protected $_index_list = array();
    protected $_pk = null;
	protected $_fields = array();
	
	public function _initialize() {
       $this->connectDb('oa' , true);
    }
    
    public function switchToScheduleType() {
        $this->_tablename = 'oa_schedule_type';
        $this->_index_list = array(
            'type_id',
            'client_account',
        );
        $this->_pk = 'type_id';
        $this->_fields = array(
            'type_id',
			'type_name',
			'client_account',
			'add_time',
        );
    }
    
    public function switchToScheduleTypeSysTem() {
        $this->_tablename = 'oa_schedule_type_system';
        $this->_index_list = array(
            'type_id'
        );
        $this->_pk = 'type_id';
        $this->_fields = array(
            'type_id',
			'type_name',
			'add_time',
        );
    }

    //通过主键查看日程分类
	public function getScheduleTypeById($typeid) {
	    $this->switchToScheduleType();
	    
		return $this->getInfoByPk($typeid);
	}

	//查看当前用户自定义的日程类型
	public function getScheduleTypeByUid($client_account) {

	    return $this->getInfoByFk($client_account, 'client_account');  
	}

	//添加自定义日程类型
    public function addScheduleType($datarr, $is_return_id=false) {
		$this->switchToScheduleType();
		
	    return $this->add($datarr, $is_return_id);
	}
	//修改日程分类
	public function modifyScheduleType($datarr,$id) {
		$this->switchToScheduleType();
		return $this->modify($datarr, $id);
	}
	//删除日程分类
	public function delScheduleType($id) {
		$this->switchToScheduleType();
		
		return $this->delete($id);
	}

}
