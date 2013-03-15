<?php
class ExamAction extends SnsController{
    public $student_perpage = 2;
    public $teacher_perpage = 5;
    
    public function __construct() {
        parent::__construct();
       
    }
    
    public function checkFunc() {
      //dump($this->getChildAccount($this->user['client_account']));
    }
    
    public function index() {
        if($this->user['client_type'] == CLIENT_TYPE_TEACHER) { //老师
            $this->showTeacherExam();
        } else {                              //学生或家长
            $this->showStudentExam();
        }
    }

    /**
     * 显示学生的成绩管理信息
     * 搜索也调用这个方法
     */
    public function showStudentExam() {
        //接收参数
        $class_code = $this->objInput->getInt('class_code');
        $page       = $this->objInput->getInt('page');
        $subject_id = $this->objInput->postInt('subject_id');
        $exam_name  = $this->objInput->postStr('exam_name');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        
        //参数处理校验
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']); 
        }
        
        $page = max(1, $page);
        $perpage = $this->student_perpage;
        $offset =($page-1) * $perpage;
        
        //如果是老师跳转到老师页面
        if($this->user['client_type'] == CLIENT_TYPE_TEACHER) { //验证权限
            $this->showTeacherExam();
        }
        
        //所有科目
        $subject_list = $this->getClassSubject($class_code);
        
        //获取考试信息列表
        $wherearr = array(
            "class_code='$class_code'", 
            'is_published=1'
        ); 
        //拼装where 条件
        if(!empty($subject_id) ) {
            $wherearr[] = "subject_id='$subject_id'";
        }
        if(!empty($exam_name)) {
            $exam_name = str_replace(array('%', '_'), "", $exam_name);
            $wherearr[] = "exam_name like '%$exam_name%'";
        }
        if(!empty($start_time) && ($start_time = strtotime($start_time)) !== false) {
            $wherearr[] = "exam_time>='$start_time'";
        }
        if(!empty($end_time) && ($end_time = strtotime($end_time)) !== false) {
            $wherearr[] = "exam_time<'" . ($end_time + 86400) . "'";
        }
       
        //根据where 条件获取考试信息列表
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExam($wherearr, 'exam_time desc, exam_id desc', $offset, $perpage+1);    
        if(count($exam_list) > $perpage) {
            $is_next_page = 1;
            array_pop($exam_list);
        }
        
        //获取孩子账号
        $child_account = $this->getChildAccount($this->user['client_account']);
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        if (!empty($exam_list)) {
            foreach ($exam_list as $exam_id=>$exam) {
                $exam['exam_time'] = !empty($exam['exam_time']) ? date('Y-m-d', $exam['exam_time']) : '';
                $exam['subject_name'] = $subject_list[$exam['subject_id']]['subject_name'];
                
                $score_list = $mClassExamScore->getClassExamScoreByExamId($exam_id);
                $exam['stat']  = $this->statClassExamScore($score_list[$exam_id], $exam['exam_good'], $exam['exam_bad']);
                
                //获取孩子的成绩
                $account_score_arr = $mClassExamScore->getClassExamScoreByExamIdAndAccount($exam_id, $child_account);
                $exam['score'] = reset($account_score_arr);
                
                $exam_list[$exam_id] = $exam;
            }
        }
        
        $exam_list  = !empty($exam_list) ? $exam_list : array();
        $class_info = $this->user['class_info'][$class_code];
        $class_name = !empty($class_info) ? ($class_info['grade_id_name'].$class_info['class_name']) : '暂无';

        $this->assign('class_name', $class_name);
        $this->assign('class_code', $class_code);
        $this->assign('exam_name',    $exam_name);
        $this->assign('start_time',   $start_time);
        $this->assign('end_time',     $end_time);
        $this->assign('subject_id',   $subject_id);
        
        $this->assign('subject_list', $subject_list);
        $this->assign('exam_list',    $exam_list);
        $this->assign('is_next_page', $is_next_page);
        
        $this->display('exam_list_student');
    }
    
    /**
     * 显示老师的成绩管理信息
     * 
     */ 
    public function showTeacherExam() {
        //接收参数
        $class_code = $this->objInput->getInt('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('您没有权限查看!', "");
        }
        //如果不是老师跳转到学生页面
        if($this->user['client_type'] != CLIENT_TYPE_TEACHER) {
            $this->showError("您没有权限查看!", "/Sns/ClassExam/Exam/index/class_code/$class_code");
        }
       
        //所有科目
        $subject_list = $this->getClassSubject($class_code);
        $class_info = $this->user['class_info'][$class_code];
        $class_name = !empty($class_info) ? ($class_info['grade_id_name'].$class_info['class_name']) : '暂无';

        $this->assign('subject_list', $subject_list);
        $this->assign('class_name', $class_name);
        $this->assign('class_code',   $class_code);
        $this->display('exam_list_teacher');
    }
    
    /**
     * 获取老师的考试列表
     */
    public function getTeacherExamListAjax() {
        //接收参数
        $class_code = $this->objInput->getInt('class_code');
        $page       = $this->objInput->getInt('page');
        $subject_id = $this->objInput->postInt('subject_id');
        $exam_name  = $this->objInput->postStr('exam_name');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        
        //参数处理校验
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');  
        }
       
        $page = max(1, $page);
        $perpage = $this->teacher_perpage;
        $offset =($page-1) * $perpage;
        
        //如果不是老师跳转到学生页面
        if($this->user['client_type'] != CLIENT_TYPE_TEACHER) {
            $this->ajaxReturn(null, '您暂时没有权限查看!', -1, 'json');
        }
        
        //获取考试信息列表
        $wherearr = array(
            "class_code='$class_code'", 
            'is_published=1'
        );
        if(!empty($subject_id) ) {
            $wherearr[] = "subject_id='$subject_id'";
        }
        if(!empty($exam_name)) {
            $exam_name = str_replace(array('%', '_'), "", $exam_name);
            $wherearr[] = "exam_name like '%$exam_name%'";
        }
        if(!empty($start_time) && ($start_time = strtotime($start_time)) !== false) {
            $wherearr[] = "exam_time>='$start_time'";
        }
        if(!empty($end_time) && ($end_time = strtotime($end_time)) !== false) {
            $wherearr[] = "exam_time<'" . ($end_time + 86400) . "'";
        }
        
        //根据where 条件获取考试信息列表
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        //$perpage+1用于判断是否有下一页
        $exam_list = $mClassExam->getClassExam($wherearr, 'exam_time desc, exam_id desc', $offset, $perpage + 1);
        
        //上下页的判断
        $page_list = array(
            'has_prev_page' => $page > 1 ? true : false,
            'has_next_page' => count($exam_list) > $perpage ? true : false
        );
        if(empty($exam_list)) {
            $return_list = array(
                'page_list' => $page_list,
                'exam_list' => array(),
            );
            $this->ajaxReturn($return_list, '没有更多考试信息!', -1, 'json');
        }
        
        $exam_list = array_slice($exam_list, 0, $perpage, true);
        //所有科目
        $subject_list = $this->getClassSubject($class_code);
        foreach ($exam_list as $exam_id=>$exam) {
            $exam['exam_time']    = !empty($exam['exam_time']) ? date('Y-m-d', $exam['exam_time']) : '';
            $exam['subject_name'] = $subject_list[$exam['subject_id']]['subject_name'];
            //判断是否有删除权限
            $exam['can_del'] = $this->canDelExam($exam['exam_id'], $exam);
            //判断是否有发送短信的权限
            $exam['can_send_sms'] = $this->canSendSms($exam['exam_id'], $exam);
            $exam_list[$exam_id] = $exam;
        }
        
        $return_list = array(
            'page_list' => $page_list,
            'exam_list' => $exam_list,
        );
        $this->ajaxReturn($return_list, '获取成功!', 1, 'json');
    }
    
    /**
     * Ajax 方式获取更多的成绩信息
     */
    public function moreStudentExamAjax() {
        //接收参数
        $class_code = $this->objInput->postStr('class_code');
        $page       = $this->objInput->postInt('page');
        $subject_id = $this->objInput->postInt('subject_id');
        $exam_name  = $this->objInput->postStr('exam_name');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        
        //参数处理校验
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }
        $page = max(1, $page);
        $perpage = $this->student_perpage;
        $offset =($page-1) * $perpage;
        
        //如果是老师，不在继续执行  返回 -1
        if($this->user['client_type'] == CLIENT_TYPE_TEACHER) { //验证权限
            $this->ajaxReturn(null, '您没有权限加载更多!', -1, 'json');
        }

        //获取考试信息列表
        $where = array("class_code=$class_code", 'is_published=1');  //必要条件
        $this->getPushWhere(& $where, $subject_id, $exam_name, $start_time, $end_time); //拼装where 条件
        //根据where 条件获取考试信息列表
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExam($where, 'exam_time desc, exam_id desc', $offset, $perpage+1);   
        if(empty($exam_list)) {
            $this->ajaxReturn(null, '没有更多了!', -1, 'json');
        }
        
        //判断是否有下一页
        if(count($exam_list) > $perpage) {
            $is_nextpage = 1;
            array_pop($exam_list);
        }
        
        //获取所有科目
        $subject_list = $this->getClassSubject($class_code);
        $child_account = $this->getChildAccount($this->user['client_account']);
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        foreach ($exam_list as $exam_id=>$exam) {
            $exam['exam_time'] = !empty($exam['exam_time']) ? date('Y-m-d', $exam['exam_time']) : '';
            $exam['subject_name'] = $subject_list[$exam['subject_id']]['subject_name'];
            
            $score_list = $mClassExamScore->getClassExamScoreByExamId($exam_id);
            $exam['stat']  = $this->statClassExamScore($score_list[$exam_id], $exam['exam_good'], $exam['exam_bad']);
            
            $account_score_arr = $mClassExamScore->getClassExamScoreByExamIdAndAccount($exam_id, $child_account);
            $exam['score'] = reset($account_score_arr);
            
            $exam_list[$exam_id] = $exam;
        }

        $datas = array(
            'page'         => $page,
            'is_nextpage'  => $is_nextpage,
        	'exam_list'    => $exam_list,
        );
        
        $this->ajaxReturn($datas, '加载成功!', 1, 'json');
    }
    
    /*
     * 删除考试信息（包括删除对应成绩）
     */
    public function delExamAjax() {
        $exam_id = $this->objInput->getStr('exam_id');

        //获取考试信息并验证
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExamById($exam_id);
        $exam_info = & $exam_list[$exam_id];
        $class_code = $this->checkoutClassCode($exam_info['class_code']);  //检查获取班级 
        if(empty($exam_info)) {
            $this->ajaxReturn(null, '您要删除的数据不存在或已被删除！', -1, 'json');
        } 
        
        //验证是否具有删除权限
        $is_del = $this->canDelExam($exam_id);
        if(empty($is_del)) {
            $this->ajaxReturn(null, '您没有删除权限！', -1, 'json');
        }
        
        //先删除成绩(成绩为空删除函数返回true)
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $del_score_success = $mClassExamScore->delBatClassExamScoreByExamId($exam_id);
        if(empty($del_score_success)) {
            $this->ajaxReturn(null, '系统繁忙，请稍后重试！', -1, 'json');
        }
        
        //再删除考试信息
        $del_exam_success = $mClassExam->delClassExam($exam_id);  
        if (empty($del_exam_success)) {
            $this->ajaxReturn(null, '系统繁忙，请稍后重试！', -1, 'json');
        }
        
        $this->ajaxReturn(null, '删除成功！', 1, 'json');
    }
    
    /*
     * 判断老师是否是班主任
     */
    private function isClassTeacher($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $client_class = $this->getUserClientClass($class_code);
        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN,
            TEACHER_CLASS_ROLE_CLASSBOTH    
        );
        
        //$teacher_class_role 1 班主任 3班级主任兼老师
        return in_array($client_class['teacher_class_role'], $class_admin_list) ? true : false;
    }
    
    /**
     * 判断当前用户是否具有删除班级成绩的权限
     *  1 班主任  2 谁添加的谁就有删除权限
     * @param $exam_id
     * @return boolean true 可以删除 false 不可以删除
     */
    private function canDelExam($exam_id, $exam_info = false) {
        if(empty($exam_id) && empty($exam_info)) {
            return false;
        }
        //跟据考试id 获取考试信息
        if (empty($exam_info)) {
            $mClassExam = ClsFactory::Create('Model.mClassExam');
            $exam_list = $mClassExam->getClassExamById($exam_id); 
            $exam_info = & $exam_list[$exam_id];
        }
        
        if(empty($exam_info)) {
            return false;
        }
        //是否是班主任
        $is_class_teacher = $this->isClassTeacher($exam_info['class_code']);
        
        return !empty($is_class_teacher) || ($this->user['client_account'] == $exam_info['add_account']) ? true : false;
    }
    
    /**
     * 判断当前用户是否具成绩短信的操作权限（发布，补发）
     *  1 班主任  2 谁添加的谁就有权限
     * @param $exam_id
     * @return boolean true 可以发送 false 不可以发送
     */
    private function canSendSms($exam_id, $exam_info = false) {
        if(empty($exam_id) && empty($exam_info)) {
            return false;
        }
        //跟据考试id 获取考试信息
        if (empty($exam_info)) {
            $mClassExam = ClsFactory::Create('Model.mClassExam');
            $exam_list = $mClassExam->getClassExamById($exam_id); 
            $exam_info = & $exam_list[$exam_id];
        }

        if(empty($exam_info)) {
            return false;
        }
        //是否是班主任
        $is_class_teacher = $this->isClassTeacher($exam_info['class_code']);
        
        return !empty($is_class_teacher) || ($this->user['client_account'] == $exam_info['add_account']) ? true : false;
    }
	/**
	 * 获取用户在对应班级担任的班级角色
	 * @param $class_code
	 */
	private function getUserClientClass($class_code) {
	    if(empty($class_code)) {
	        return false;
	    }
	    
	    $current_client_class = array();
	    
	    $client_class_list = $this->user['client_class'];
	    foreach($client_class_list as $client_class) {
	        if($client_class['class_code'] == $class_code) {
	            $current_client_class = $client_class;
	            break;
	        }
	    }
	    
	    return !empty($current_client_class) ? $current_client_class : false;
	}
    
    /*
     * 获取当前班级的所有科目
     * 并格式好数据添加上任课教师名称
     */
    private function getClassSubject($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取当前班级的所有科目
        list($subject_list, $class_teacher_list) = $this->getSubjectAll($class_code);
        //建立科目到老师的对应关系
        foreach($class_teacher_list as $key=>$class_teacher) {
            $teacher_uids[$class_teacher['subject_id']] = $class_teacher['client_account'];
        }

        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid(array_unique($teacher_uids));
        
        //数据拼装 老师名称  并且过滤掉没有任课老师的科目
        foreach ($subject_list as $subject_id=>$subject) {
            if(!isset($teacher_uids[$subject_id])) {
                continue;
            }
            
            $subject['teacher_name'] = $user_list[$teacher_uids[$subject_id]]['client_name'];
            $new_subject_list[$subject_id] = $subject;
        }
  
        return empty($new_subject_list) ? false : $new_subject_list;
    }
    
    /*
     * 获取本班所有科目  getClassSubject 的辅助函数
     * 并格式好数据添加上教师名称
     */
    private function getSubjectAll($class_code) {
        if(empty($class_code)) {
            return false;
        }

        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $class_teacher_arr = $mClassTeacher->getClassTeacherByClassCode($class_code);
        $class_teacher_list = & $class_teacher_arr[$class_code];

        $school_id = key($this->user['school_info']);
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $school_subject_arr = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
        $subject_list = & $school_subject_arr[$school_id];
        
        return array($subject_list, $class_teacher_list);
    }
    
   /**
     * 统计分析班级的成员列表信息
     * @param $exam_score_list 成绩列表
     * @param $exam_good 优秀分
     * @param $exam_bad  及格分
     * 
     * @return array  统计结果
     */
    private function statClassExamScore($exam_score_list, $exam_good, $exam_bad) {
        if(empty($exam_score_list)) {
            return false;
        }
        
        $join_nums = $unjoin_nums = $excellent_nums = $pass_nums = $total_score = 0;
        $score_list = array();
        foreach($exam_score_list as $exam_score) {
            if(!$exam_score['is_join']) {
                $unjoin_nums += 1;    //未参加人数
                continue;
            }
            
            $join_nums += 1;  //参加人数
            
            $score = max(0, intval($exam_score['exam_score'])); 
            
            $score_list[] = $score;    
            $total_score += $score;
            
            //优秀人数
            if($score >= $exam_good) {
               $excellent_nums += 1; 
            }
            //及格人数
            if($score >= $exam_bad) {
                $pass_nums += 1;
            }
        }

        return array(
            'join_nums' => $join_nums,
            'unjoin_nums' => $unjoin_nums,
            'avg_score' => number_format(floatval(1.0 * $total_score / $join_nums), 2),
            'top_score' => max($score_list),
            'lower_score' => min($score_list),
            'excellent_percent' => number_format(floatval(100.0 * $excellent_nums / $join_nums), 2) . '%',
            'pass_percent' => number_format(floatval(100.0 * $pass_nums / $join_nums), 2) . '%',
        );
    }
    
    /**
     * 根据参数判断where 条件是否成立 成立就像where 数组里面追加上过滤条件
     */
    private function getPushWhere($where, $subject_id, $exam_name, $start_time, $end_time) {
        //拼装where 条件
        if(!empty($subject_id) ) {
            $where[] = "subject_id=$subject_id";
        }
        if(!empty($exam_name)) {
            $exam_name = str_replace(array('%', '_'), "", $exam_name);
            $where[] = "exam_name like '%$exam_name%'";
        }
        if(!empty($start_time) && ($start_time = strtotime($start_time)) !== false) {
            $where[] = "exam_time>='$start_time'";
        }
        if(!empty($end_time) && ($end_time = strtotime($end_time)) !== false) {
            $where[] = "exam_time<'" . ($end_time + 86400) . "'";
        }
    }
    
    /**
     * 获取账号的孩子账号
     * 成功返回 孩子账号信息失败返回 当前账号
     */
    private function getChildAccount($client_account) {
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $family_arr = $mFamilyRelation->getFamilyRelationByFamilyUid($client_account);
        $child_arr = reset($family_arr[$client_account]);
        $child_account = $child_arr['client_account'];
        
        return !empty($child_account) ? $child_account : $client_account;
    }
    /**
     * 旧数据处理 只处理一次
     * 
     */
    public function OldData() {
        
        //旧数据处理
        import('@.Control.Sns.OldData');
        $old_data_obj = new OldData();
        //旧课程表数据处理
        //$old_data_obj->oldCourse();
        //旧课程皮肤表数据处理
        //$old_data_obj->oldcourseSkin();
        //旧个人课程皮肤配置表
        //$old_data_obj->oldcourseConfig();
        
        //旧考试信息表处理
        //$old_data_obj->oldExam();  //有意义的数据共 795 条
        //考试成绩信息表
        $old_data_obj->oldExamScore();  //有意义的数据共 30279 条
        echo('旧数据都处理完成了哦！');
    }
    
}