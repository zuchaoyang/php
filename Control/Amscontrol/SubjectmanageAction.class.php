<?php
class SubjectmanageAction extends AmsController {
	//Author：luanhongmin
	protected $is_school = true;
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		
	    $this->assign('username', $this->user['ams_name']);
	}
	
    //跳转验证
    private function checkUser($uid,$schoolid) {
        $isThis = $this->checkLoginerInSchool($uid,$schoolid);
        
        return $isThis ? true : false;
    }
    
    //显示课程列表	
	public function showSubjectInfo() { 
		$schoolid = $this->user['schoolinfo']['school_id'];//获得该学校的ID 
		
	    if(!(self::$this->checkUser($this->user['ams_account'], $schoolid))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        return ;
	    }
	    
		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo'); 
	    $existSubjects = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);//获得该学校的课程
	    $existSubjects =  & $existSubjects[$schoolid];
	    
	    //该学校数据为空,则先将系统默认的科目添加进来
        if(empty($existSubjects)) {
            //初始化wmw_subject_info 课程数组
            $subject = array(
				'subject_name' => '',
                'school_id' => $schoolid,
                'sys_subject_id' => 0,
                'add_account' => $this->user['ams_account'],
                'add_date' => date("Y-m-d H:i:s"),
                'add_time' => time()
            );
            
            //通过学校类型，从系统科目表中获取科目
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		    $schoolInfoList = $mSchoolInfo->getSchoolInfoById($schoolid);
		    $schoolInfo = & $schoolInfoList[$schoolid];
		    $schoolType = $schoolInfo['school_type'];
		    
		    $mSysSubject = ClsFactory::Create('Model.mSysSubject');
	        $SysSubject = $mSysSubject->getSysSubjectBySubjectType($schoolType);
            if(!empty($SysSubject)) {//学校对应的系统科目不为空时
                foreach ($SysSubject as $key=>$val) {
                    $subject['subject_name'] = $val['subject_name'];
                    $subject['sys_subject_id'] = $val['subject_id'];
                    $mSubjectInfo->addSubjectInfo($subject, true);
                }
            }
            
            $existSubjects = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);//赋值进来后应重新再查询一下科目表
            $existSubjects = & $existSubjects[$schoolid];
        }
        
        $this->assign('schoolId', $schoolid);
        $this->assign('subjects', $existSubjects);
		$this->display('subjectInfo');
	}
	
	//添加课程
	public function addSubjectInfo(){
		$schoolid = $this->user['schoolinfo']['school_id'];//获得该学校的ID
        $subject_name = $this->objInput->postStr('subject_name');//获取用户输入的课程名称
        
        
        $subject_name = cutstr($subject_name, 20);
        
        $err_msg = array();
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        if(!empty($subject_name)) {
            $existSubject = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);//获取该学校已存在的科目
            $existSubject = & $existSubject[$schoolid];
            
            $is_exist = false;//初始化排重标志位
            foreach($existSubject as $key=>$subjectInfo){
                if($subject_name == $subjectInfo['subject_name']) {
                    $is_exist = true;
                    break;
                }
            }
            $is_exist && $err_msg[] = "添加课程失败，请检查课程是否已存在!";
        } else {
            $err_msg[] = "课程名字不能为空!";
        }
        
        if(empty($err_msg)) {
            $subject_datas = array(
            	'subject_name' => $subject_name,
                'school_id' => $schoolid,
                'sys_subject_id' => 0,    //0表示由用户添加，不属于系统科目表
                'add_account' => $this->user['ams_account'],
                'add_date' => date("Y-m-d H:i:s"),
                'add_time' => time(),
            );
            
            $insert_id = $mSubjectInfo->addSubjectInfo($subject_datas, true);
            if($insert_id) {
                $json = array(
					'result'=>array(
						'code'=>1,
						'message'=>'添加课程成功!',
                    ),
                	'data'=>array(
                		'subject_id'=>$insert_id,
                    )
                );
            } else {
                $json = array(
					'result'=>array(
						'code'=> -1,
						'message'=>'添加课程失败!',
                    ),
                	'data' => array(),
                );
            }
        } else {
            $json = array(
				'result' => array(
					'code' => -1,
					'message' => array_shift($err_msg),
                ),
                'data' => array(),
	        );
        }
        
        echo json_encode($json); 
	}
	
	//修改课程
	public function modifySubjectInfo(){
		$schoolid = $this->user['schoolinfo']['school_id'];//课程的学校ID 
        $subject_name = $this->objInput->postStr('subject_name');
        $subject_id = $this->objInput->postInt('subject_id');
        
		if(empty($schoolid) || empty($this->user['ams_account'])) {
		    echo "请先登录，<a href='/Homeuser/Login/logout/flag/out'>点击这里</a> 返回登录页面"; die;
		}
		
		//最多允许10个汉字
		$subject_name = cutstr($subject_name , 20);
        
		//统计错误信息
        $err_msg = array();
        
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        if(!empty($subject_name) && !empty($subject_id)) {
            //获取该学校已存在的科目
            $existSubject = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);
            $existSubject = & $existSubject[$schoolid];
            
            if(!isset($existSubject[$subject_id])) {
               $err_msg[] = "您无权修改该科目信息!"; 
            }
            
            $is_exist = false;//初始化排重标志位
            foreach($existSubject as $key=>$subjectInfo){
                if($subject_name == $subjectInfo['subject_name']) {
                    $is_exist = true;
                    break;
                }
            }
            $is_exist && $err_msg[] = "修改课程失败，请检查课程是否已存在!";
        } else {
            $err_msg[] = "科目名称不能为空!";
        }
            
        if(empty($err_msg)) {
            $subject_datas = array(
        		'subject_name' => $subject_name,
                'school_id' => $schoolid,
                'sys_subject_id' => '0',//将系统科目Id置0
            	'add_time' => time(),
            );
            $mSubjectInfo->modifySubjectInfo($subject_datas, $subject_id);
            
            $json = array(
				'result'=>array(
					'code'=>1,
					'message' => '修改课程成功',
                ),
            	'data'=>array(
            		'subject_id' => $subject_id,
                )
            );
        } else {
            $json = array(
                'result' => array(
                    'code' => -1,
                    'message' => array_shift($err_msg),
                ),
                'data' => array(),
            );
        }
        
		echo json_encode($json);  
	}
	
    //ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }
}
