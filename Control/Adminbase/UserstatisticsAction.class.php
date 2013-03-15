<?php
class UserstatisticsAction extends WmsController{
    public function _initialize(){
        parent::_initialize();
        header('Content-Type:text/html; charset=utf-8');
        import("@.Control.Adminbase.WmsadminloginAction");
    }

	/*
	 * 显示页面
	 */
	function getschoolusernum(){
	    
	    $user_count       = $this->objInput->postArr('user_count');
	    $phone_count      = $this->objInput->postArr('phone_count');
	    $area_user_count  = $this->objInput->postArr('area_user_count');
	    $area_phone_count = $this->objInput->postArr('area_phone_count');
	    $area_id          = $this->objInput->postInt('area_id');
	    $school_name      = $this->objInput->postStr('school_name');
	    $page             = $this->objInput->postInt('page');
	    
	    $page = max(1, $page);
	    $length = 10;
	    $offset = ($page-1)*$length;

	    if(empty($area_id)){
	        $area_id = 11008000;
	    }
	    
	    if(empty($user_count) || empty($phone_count)) {
	        $user_count = $this->userCount();
	        $phone_count = $this->bdingPhoneCount();
	    }
	    $no_next = true;
        if(empty($area_user_count) || empty($area_phone_count)) {
            $a = $this->getClientCountByArea($area_id,$school_name);
            $area_user_count = $a['people'];
            $area_phone_count = $a['phone'];//$this->phoneCountByArea($area_id);
        }
        $mCount = ClsFactory::Create('Model.mCount');
	    $area_schoolInfos = $mCount->getCountByAreaSchooName($school_name, $area_id, $offset, $length + 1);

	    if(count($area_schoolInfos)>$length) {
	        $no_next = false;
	        $area_schoolInfos = array_slice($area_schoolInfos, 0, $length, true);
	    }
        $area_list = array();
    	foreach ($area_schoolInfos as $key=>$val){
    	    $areainfos = getAreaNameList($val['area_id']);
    		$area_schoolInfos[$key]['province'] = $areainfos['province'];
    		$area_schoolInfos[$key]['city'] = $areainfos['city'];
    		$area_schoolInfos[$key]['county'] = $areainfos['county'];
    	}
	    
	    $this->assign('phone_count',$phone_count);
	    $this->assign('user_count',$user_count);
	    $this->assign('area_user_count',$area_user_count);
	    $this->assign('area_phone_count',$area_phone_count);
	    $this->assign('area_schoolInfos',$area_schoolInfos);
	    
	    
	    $this->assign('page', $page);
	    $this->assign('school_name', $school_name);
	    $this->assign('length', $length);
	    $this->assign('no_next',$no_next);
	    $this->assign('area_id', $area_id);
	    
	    
	    
	    $this->display('client_count');
	}
	///////////////////////////////////////////////////////////////////////////////////
	/**
	 * 用户总计
	 * t_count 老师
	 * f_count 家长
	 * s_count 学生
	 * z_count=t_count+f_count+s_count 老师+家长+学生
	 */
	protected function userCount() {
	    $mCount = ClsFactory::Create('Model.mCount');
	    $count_by_type=$mCount->getClientAccountOrderbyClientType();
	    
	    if(empty($count_by_type)) {
	        return false;
	    }
	    $new_count = array(
	        'total_people_count' => 0,
	        'student_count'      => 0,
	        'teacher_count'      => 0,
	        'parents_count'      => 0
	    );
	    $total_count = 0;
	    foreach($count_by_type as $key=>$val) {
	        if($val['client_type'] == 0) {
	            $new_count['student_count'] += $val['count'];
	        }
	        if($val['client_type'] == 1) {
	            $new_count['teacher_count'] += $val['count'];
	        }
	        if($val['client_type'] == 2) {
	            $new_count['parents_count'] += $val['count'];
	        }
	        unset($count_by_type['$key']);
	    }
	    $new_count['total_people_count'] = $new_count['teacher_count']
	                                       +$new_count['student_count']
	                                       +$new_count['parents_count'];
	    
	    return $new_count;
	}
	
	/**
	 * 绑定手机号总计
	 * old_count 老用户
	 * new_count 新用户
	 * z_phones_count=old_count+new_count 绑定总计
	 */
	protected function bdingPhoneCount() {
	    $mCount = ClsFactory::Create('Model.mCount');
	    $phoneCount = $mCount->phoneCountOrderbyPhoneType();
	    if(empty($phoneCount)) {
	        return false;
	    }
	    $new_phoneCount = array(
	        'total_phone_count'=>0,
	        'phone_old_count'  =>0,
	        'phone_new_count'  =>0  
	    );
	    foreach($phoneCount as $key=>$val) {
	        if($val['phone_type'] == 1) {
	            $new_phoneCount['phone_old_count'] += $val['count'];
	        }
	        if($val['phone_type'] == 2) {
	            $new_phoneCount['phone_new_count'] += $val['count'];
	        }
	        unset($phoneCount[$key]);
	    }
	    $new_phoneCount['total_phone_count'] = $new_phoneCount['phone_old_count']+$new_phoneCount['phone_new_count'];
	    
	    
	    return $new_phoneCount;
	    
	}
	/////////////////////////////////////////////////////////////////////////////////////
	/**
	 * 区域用户统计
	 * 
	 */
	protected function getClientCountByArea($area_id,$school_name) {
	    if(empty($area_id)) {
            return false;
        }
	    $mCount = ClsFactory::Create('Model.mCount');
	    $peopleCount = $mCount->getClientCountByArea($area_id,$school_name);
	    $peoples_count = array(
            'total_people_count'=>0,
            'student_count'     =>0,
            'teacher_count'     =>0,
            'parents_count'     =>0
        );
        foreach($peopleCount['people'] as $key=>$val) {
            if($val['client_type'] == 0) {
                $peoples_count['student_count'] += $val['count'];
            }
            if($val['client_type'] == 1) {
                $peoples_count['teacher_count'] += $val['count'];
            }
            if($val['client_type'] == 2) {
                $peoples_count['parents_count'] += $val['count'];
            }
            unset($peopleCount['people'][$key]);
        }
        $peoples_count['total_people_count'] =  $peoples_count['student_count']
                                               +$peoples_count['teacher_count']
                                               +$peoples_count['parents_count'];
	    //区域绑定手机号统计
        $phones_count = array(
            'total_phone_count'=>0,
            'old_phone_count'  =>0,
        	'new_phone_count'  =>0
        );
	    foreach($peopleCount['phone'] as $key1=>$val1) {
            if($val1['phone_type'] == 1) {
                $phones_count['old_phone_count'] += $val1['count'];
            }
            if($val1['phone_type'] == 2) {
                $phones_count['new_phone_count'] += $val1['count'];
            }
            unset($peopleCount['phone'][$key1]);
        }
        $phones_count['total_phone_count'] =  $phones_count['old_phone_count']
                                             +$phones_count['new_phone_count'];
        $list_count['people'] = $peoples_count;
        $list_count['phone'] = $phones_count;
        return $list_count;
	}
	
}