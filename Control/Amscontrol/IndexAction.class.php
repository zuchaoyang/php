<?php
class IndexAction extends AmsController {
    public function _initialize(){
        parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import('@.Common_wmw.Constancearr');
	}
	
	public function index(){
	    $this->display('index');
	}
	
    public function top(){
    	$this->display('top');
    }
    
    public function left(){
        $login_uid = $this->user['ams_account'];
        $mSchoolInfo = ClsFactory::Create("Model.mSchoolInfo");
        $SchoolInfo = $mSchoolInfo->getSchoolInfoByNetManagerAccount($login_uid);
        $schoolInfo = $SchoolInfo[$login_uid];
        $school_id = intval(key($schoolInfo));
        $school_id = max(0, $school_id);
        $schoolInfo = $schoolInfo[$school_id];
        
        $this->assign('gradelist', $this->gradelists($schoolInfo['school_type'], $schoolInfo['grade_type']));
        $this->assign('schoolinfo', $schoolInfo);
        $this->assign('school_id', $school_id);
	    $this->display('left');
	}
    	
    public function main(){
        $this->assign('username', $this->user['ams_name']);
	    $this->display('hyym');
    }
    
	//年级列表
	private function gradelists($schooltype, $grade_type){
		$grade_lists = Constancearr::class_grade_id();
		if($schooltype ==1){
			if($grade_type == 1) {
		        $grade_list = array_slice($grade_lists,0,6,true);
		    }else{
		        $grade_list = array_slice($grade_lists,0,5,true);
		    }
		}elseif($schooltype ==2){
			$grade_list = array_slice($grade_lists,6,3,true);
			if($grade_type == 2) {
			    $grade_list[13] = $grade_lists[13];
			}
		}elseif($schooltype ==3){
			$grade_list = array_slice($grade_lists,9,3,true);
		}
		return !empty($grade_list)?$grade_list:false;
	}
}
