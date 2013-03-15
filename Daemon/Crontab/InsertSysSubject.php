<?php
header("Content-Type:text/html; charset=utf-8");
include_once(dirname(dirname(__FILE__)) . '/Daemon.inc.php');

class InsertSysSubjectAction {
	public function insertSysSubjectToSubject(){
		
		//1.查出所有学校
		$mSchoolInfo = ClsFactory::Create ('Model.mSchoolInfo');
		$school_infos = $mSchoolInfo->getSchoolInfo();
		
		if(!empty($school_infos)){
			echo 'update school subject start';
			//2.查出所有系统科目，并拆分系统科目为小学、初中、高中三个数组
			$sysSubject = $this->getSysSubject();
			
			
			$mSubjectInfo = ClsFactory::Create ('Model.mSubjectInfo');
			foreach($school_infos as $school_info){
				$school_id = intval($school_info['school_id']);
				$school_type = intval($school_info['school_type']);

				//3.查出每个学校所有科目
				$subject_infos_arr = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
				$subject_infos_list = $subject_infos_arr[$school_id];
				$subjec_first = reset($subject_infos_list);
				$add_account = $subjec_first['add_account'];
				
				//4.根据学校类型将学校科目和对应类型系统科目比较，找出差值
				$diff_arr = $this->diffSubject($sysSubject[$school_type], $subject_infos_list);

				//5.将差值插入科目表对应学校中
				foreach($diff_arr as $sys_subject){
					
					$subject_datas = array(
	            		'subject_name' => $sys_subject['subject_name'],
		                'school_id' => $school_id,
		                'sys_subject_id' => $sys_subject['subject_id'],
		                'add_account' => $add_account,
		                'add_date' => date("Y-m-d H:i:s"),
		                'add_time' => time(),
           			 );
           			 
           			 $mSubjectInfo->addSubjectInfo($subject_datas);

				}

			}
			echo 'update school subject end';
			
		}

	}
	
	private function diffSubject($system_list, $school_list) {
		if(empty($system_list)) {
			return false;
		} elseif(empty($school_list)) {
			return $system_list;
		}

		//建立映射关系
		$arr = array();
		foreach($school_list as $key=>$subject) {
			$arr[] = $subject['sys_subject_id'];
		}
		//排除已有数据
		if(!empty($arr)) {
			foreach($system_list as $subject_id=>$subject) {
				if(in_array($subject_id, $arr)) {
					unset($system_list[$subject_id]);
				}
			}
		}

		return !empty($system_list) ? $system_list : false;
	}
	
	//查出所有系统科目，并拆分系统科目为小学、初中、高中三个数组
	private function getSysSubject(){
		$mSysSubject = ClsFactory::Create ('Model.mSysSubject');
		$list = $mSysSubject->getSysSubjectBySubjectType(array(1,2,3));

		$new_list = array();
		foreach($list as $key=>$val) {
			$new_list[$val['subject_type']][$key] = $val;
		}

		return $new_list;
	}
}

$subjectObj = new InsertSysSubjectAction();
$subjectObj->insertSysSubjectToSubject();