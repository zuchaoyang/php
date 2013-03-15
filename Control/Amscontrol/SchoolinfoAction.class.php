<?php
class SchoolinfoAction extends AmsController {
    protected $is_school = true;
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		
	    $this->assign('username', $this->user['ams_name']);
	}  
	
	//显示学校信息
	public function showSchoolInfo(){
		$schoolid = $this->user['schoolinfo']['school_id'];//获得该学校的ID
		
		$schoolid = $this->checkLoginerInSchool($this->user['ams_account'], $schoolid);
		if(empty($schoolid)) {
		    exit('您没有权限查看该学校信息!');
		}
		
		//获取学校基本信息
		$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolinfo_list = $mSchoolInfo->getSchoolInfoById($schoolid);
		$schoolInfo = & $schoolinfo_list[$schoolid];
		

		//将学校类型、学制类型及资源优势的代号替换成具体的类型
        $schoolInfo['resource_advantage'] = Constancearr::school_resource_advantage($schoolInfo['resource_advantage']);
        $schoolInfo['school_type'] = Constancearr::school_type($schoolInfo['school_type']);
        $schoolInfo['grade_type'] = Constancearr::grade_type($schoolInfo['grade_type']);
        
        $addressList = getAreaNameList($schoolInfo['area_id']);
        $schoolInfo['school_address'] = $addressList['province'].$addressList['city'].$addressList['county'].$schoolInfo['school_address'];
        
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
		$classinfo_arr = $mClassInfo->getClassInfoBySchoolId($schoolid);
		$classList = & $classinfo_arr[$schoolid];//将数组降维，获得二维班级列表
	    $classCodes = array_keys($classList);
	    
        $mClientClass = ClsFactory::Create('Model.mClientClass'); 
        $stuInfo = $mClientClass->getSchoolUserTypeTotal($classCodes, CLIENT_TYPE_STUDENT);  //统计学校学生数目
        foreach($stuInfo as $uids){
             $stuuids[] = $uids['client_account'];
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $stu_userlist = $mUser->getUserBaseByUid($stuuids);
	    if(!empty($stu_userlist)) {
		    foreach($stu_userlist as $key=>$val) {
		        if(isset($val['status']) && $val['status'] == 3) {
		            unset($stu_userlist[$key]);
		        }
		    }
		}
        $studentNum = count($stu_userlist);
        
        $parentInfo  = $mClientClass->getSchoolUserTypeTotal($classCodes,CLIENT_TYPE_FAMILY);//统计统计家长数目 
	    foreach($parentInfo as $uids){
             $parentuids[]=$uids['client_account'];
        }
        
        $parent_userlist = $mUser->getUserBaseByUid($parentuids);
        if(!empty($parent_userlist)) {
            foreach($parent_userlist as $key=>$val) {
                if(isset($val['status']) && $val['status'] == 3) {
		            unset($parent_userlist[$key]);
		        }
            }
        }
        $parentNum = count($parent_userlist);
        
        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $teacherNum = $mSchoolTeacher->getSchoolTeacherTotal($schoolid); //统计学校老师数目
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classNum = $mClassInfo->getSchoolClassTotal($schoolid);//统计学校班级数目
        //处理学校的建校日期的显示问题
        if(!empty($schoolInfo) && isset($schoolInfo['school_create_date'])) {
            $school_create_date = $schoolInfo['school_create_date'];
            list($year, $month, $day) = explode("-" , $school_create_date);
            $schoolInfo['school_create_date'] = "$year-$month";
        }
        $loginId = $this->user['ams_account'];
        $mUser = ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getUserBaseByUid($loginId);
        $this->assign('username', $userInfo[$loginId]['client_name']);
        $this->assign('studentNum', intval($studentNum));
        $this->assign('teacherNum', intval($teacherNum));
        $this->assign('parentNum', intval($parentNum));
        $this->assign('classNum', intval($classNum));
		$this->assign('schoolInfo', $schoolInfo); 
		
		$this->display('schoolInfo');
	}
}
