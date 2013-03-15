<?php
class dSchedule extends dBase{
    protected $_tablename = 'oa_schedule';
	protected  $_fields = array(
		'schedule_id',
		'client_account',
		'schedule_title',
		'schedule_message',
		'type_id',
		'schedule_start_time',
		'is_draft',
		'add_time',
        'upd_time',
		'expiration_time',
        'deadline_hours',
	);
	protected $_pk = 'schedule_id';
    protected $_index_list = array(
        'schedule_id',
        'client_account',
    );
    
	public function _initialize() {
       $this->connectDb('oa' , true);
    }

	//查询日程管理方法
	public function getScheduleById($schedule_ids) {
		
		return $this->getInfoByPk($schedule_ids);
	}

	//添加日程管理方法
	public function addSchedule($datas, $is_return_id=false) {
        
        return $this->add($datas, $is_return_id);
    }
	    
	//修改日程管理方法
	public function modifySchedule($datas, $id) {
	   
		return $this->modify($datas, $id);
	}
	
	//删除日程管理方法
	public function delSchedule($id) {
	    
		return $this->delete($id);
	}
}