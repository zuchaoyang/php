<?php
class mSchedule extends mBase{
    protected $_dSchedule = null;

    public function __construct() {
        $this->_dSchedule = ClsFactory::Create('Data.dSchedule');
    }
    
    //通过id查看日程
	public function getScheduleById($schedule_ids){
		if(empty($schedule_ids)){
			return false;
		}
		
		return $this->_dSchedule->getScheduleById($schedule_ids);
	}
 
    function getScheduleByUid($uid, $offset='', $length=''){
        if(empty($uid)){
            return false;
        }
        
        $uid = is_array($uid) ? array_shift($uid) : $uid ;
        $wheresql = array(
            "client_account = '$uid'",
            "is_draft = ".SCHEDULE_IS_DRAFT_FALSE,
        );
        $orderby = " upd_time desc ";
        
        return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $length);
	}
	
	function getScheduleByUidandType($uid, $type_id, $offset, $length){
        if(empty($uid)  || empty($length) || empty($type_id)){
            return false;
        }
        $uid = is_array($uid) ? array_shift($uid) : $uid;
        $type_id = implode("','", (array)$type_id);
        
        $wheresql = array(
            "client_account = '$uid'",
            "type_id in('$type_id')", 
            "is_draft = ".SCHEDULE_IS_DRAFT_FALSE 
        );
        $orderby = "upd_time desc ";
        $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $length);
        return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $length);
    }

    function getScheduleByName($schedule_name, $client_account, $offset, $length){
        $wheresql = array(
            "client_account = $client_account ",
            "schedule_title like '$schedule_name%'",
            "is_draft = " . SCHEDULE_IS_DRAFT_FALSE,
        );        
        $orderby = " upd_time desc";
        return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $length);
	}
    
	//添加日程类型方法
	public function addSchedule($datarr, $is_return_id=false){
		 if(empty($datarr) || !is_array($datarr)) {
	            return false;
	     }
	     
	     return  $this->_dSchedule->addSchedule($datarr, $is_return_id);
	}
	
	
	//修改日程类型管理方法
	public function modifySchedule($datarr, $id){
		if(empty($id) && empty($datarr)){
			return false;
		}
		
		return $this->_dSchedule->modifySchedule($datarr,$id);
	}
	
    //删除分类后，将其下日程修改分类状态为默认分类
	public function modifyScheduleByUidAndTypeId($data, $uid, $type_id){
	    if(empty($data) || empty($uid) || empty($type_id)){
	        return false;
	    }
	    
	    $wheresql = array(
	        "client_account='$uid'",    
	        "type_id='$type_id'"
	    );
	    $list = $this->_dSchedule->getInfo($wheresql);
	    
	    $effect_rows = 0;
	    if(!empty($list)) {
	        foreach ($list as $id=>$schedule) {
	           $this->_dSchedule->modifySchedule($data, $id) && $effect_rows++;
	        }    
	    } 
	    
	    return $effect_rows;
	}
	 
	//删除日程类型管理方法
	public function delSchedule($id){
		if(empty($id)){
			return false;
		}
		
		return $this->_dSchedule->delSchedule($id);
	}

	//通过uid和is_draft分页得到日程信息草稿信息
	function getScheduleByUidAndIs_draft($uid, $type_id, $offset, $limit){
	    if(empty($uid) || empty($type_id)){
            return false;
        }
        
        
        $uid = is_array($uid) ? array_shift($uid) : $uid ;
        $type_id = is_array($type_id) ? array_shift($type_id) : $type_id;
        $wheresql = array(
            "client_account = '$uid'",
            "type_id = '$type_id'",
            "is_draft = ".SCHEDULE_IS_DRAFT_TRUE
        );
        $orderby = ' upd_time desc ';
        
        return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $limit);
	}
	
	
	//通过日程时间得到日程信息
    function getScheduleByDate($client_account,  $upd_start_time , $upd_end_time, $offset, $limit){
        if(empty($client_account) || empty($upd_start_time)){
            return false;
        }
        $wheresql = array(
            "client_account = '$client_account'",
        	"is_draft = " . SCHEDULE_IS_DRAFT_FALSE,
            "add_time >'$upd_start_time'",
            "add_time<'$upd_end_time' "
        );
        $orderby = " schedule_id desc ";
        
        return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $limit);
	}
	
	//通过到期时间查询日程
	public function getScheduleByRemindTime($uid,$offset,$limit){
	    if(empty($uid)) {
	        return false;
	    }
	    
		list($day, $month, $year) = explode('-', date('d-m-Y'));
		$start_time = mktime(0,0,0,$month,$day,$year);
		$end_time = mktime(0,0,0,$month,$day+3,$year);
		$wheresql = array(
		    "client_account='$uid'",
		    "expiration_time >= '$start_time'",
		    "expiration_time < '$end_time' "
		);
		$orderby = " expiration_time asc ";
		
		return $this->_dSchedule->getInfo($wheresql, $orderby, $offset, $limit);
	}
}