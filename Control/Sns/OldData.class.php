<?php

class OldData extends SnsController{
    
    public function __construct() {
        parent::__construct ();
        
    }
    
    private function parseAmContent($class_code, $am_content, $upd_account, $upd_time) {
        $am_course = @ unserialize($am_content);
        if(empty($am_course)) {
            return false;
        }
        
        $sum = count($am_course);
        for($i = 1; $i <= $sum; $i++) {
            $course_name = $this->getCourseName($am_course[$i - 1]);  //获取真确的课程名称 去掉旧数据的图片url
            if(empty($course_name)) {
                continue;
            }
            
            $weekday = $i % 5;
            if($weekday == 0) {
                $weekday = 5;
            }
            $num_th = intval(ceil($i / 5));
            $new_course[] = array(
                'class_code'=> $class_code,
            	'weekday' => $weekday,          
                'num_th'  => $num_th,
            	'name'    => $course_name,
                'upd_account' => $upd_account,
                'upd_time'    => $upd_time
            );
        }
        
        return $new_course;
    }
    
    private function parsePmContent($class_code, $pm_content, $upd_account, $upd_time) {
        $pm_course = @ unserialize($pm_content);
        if(empty($pm_course)) {
            return false;
        }
        
        $sum = count($pm_course);
        for($i = 1; $i <= $sum; $i++) {
            $course_name = $this->getCourseName($pm_course[$i - 1]);  //获取真确的课程名称 去掉旧数据的图片url
            if(empty($course_name)) {
                continue;
            }
            
            $weekday = $i % 5;
            if($weekday == 0) {
                $weekday = 5;
            }
            
            $num_th = intval(ceil($i / 5) + 4);
            $new_course[] = array(
                'class_code'  => $class_code,
            	'weekday'     => $weekday,          
                'num_th'      => $num_th,
            	'name'        => $course_name,
                'upd_account' => $upd_account,
                'upd_time'    => $upd_time
            );
        }
        
        return $new_course;
    }
    
    private function getCourseName($tmp_name) {
        if (empty($tmp_name)) {
            return false;
        }
        
        $course_name = false;
        $tmp_name = explode('%', $tmp_name);

        if(count($tmp_name) > 1 && !in_array($tmp_name[2], array('空','无','--'))) {
            
            $course_name = $tmp_name[2];
            
        } else if(count($tmp_name) < 1 && !in_array($tmp_name[0], array('空','无','--')) ) {
            
            $course_name = $tmp_name[0];
        }

        unset($tmp_name);
        return !empty($course_name) ? $course_name : false;
        
    }  
    
    /*
     * 旧课程表数据处理 原来一个班级一条现在一个班级对多 40 条（一节课一条）
     */
    public function oldCourse() {
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
       
        $start_time =  microtime(TRUE);
        
        $perpage = 50;
        $offset = ($page - 1) * $perpage;
        
        $mCurriculum = ClsFactory::Create('Model.mCurriculum');
        $where = array("(am_content !='' or pm_content != '')", 'class_code > 0');
        $oldList = $mCurriculum->getCurriculumInfo($where, 'class_code', $offset, $perpage);
        if(empty($oldList)) {
            echo '旧数据处理完成了！';exit;
        }
        
        $course_list = array();
        foreach ($oldList as $curriculum_id => $curriculum_info) {
            $am_course_list = $this->parseAmContent($curriculum_info['class_code'], $curriculum_info['am_content'], $curriculum_info['upd_account'], $curriculum_info['upd_time']);
            $pm_course_list = $this->parsePmContent($curriculum_info['class_code'], $curriculum_info['pm_content'], $curriculum_info['upd_account'], $curriculum_info['upd_time']);
            
            $tmp_course_list = array_merge((array)$am_course_list, (array)$pm_course_list);
            if(!empty($tmp_course_list)) {
                $course_list = array_merge($tmp_course_list, $course_list); 
            }
        }
        
        if (!empty($course_list)) {
            $class_code_sorts = $weekday_sorts = $num_th_sorts = array();
            foreach($course_list as $key => $course) {
                $class_code_sorts[$key] = $course['class_code'];
                $weekday_sorts[$key] = $course['weekday'];
                $num_th_sorts[$key] = $course['num_th'];
            }
            array_multisort($class_code_sorts, SORT_ASC, $weekday_sorts, SORT_ASC, $num_th_sorts, SORT_ASC, $course_list);
            
            $mClassCourse = ClsFactory::Create('Model.mClassCourse');
            //dump($mClassCourse);
            $success = $mClassCourse->addBatClassCourse($course_list);
            if(empty($success)) {
                --$page;
            }
        }

        $this->showError(null, "/Sns/ClassCourse/Course/OldData?page=" . ($page + 1));
    }
    
    /*
     * 课程皮肤表旧数据处理
     */
    public function oldcourseSkin() {
        
        $mCurriculumskin = ClsFactory::Create('Model.mCurriculumskin');
        $old_skin_list = $mCurriculumskin->getCurriculumSkinList();
        
        if(empty($old_skin_list)){
            echo '没有数据需要处理';exit;    
        }
        
        $skin_list = array();
        foreach ($old_skin_list as $skin_id=>$skin_info) {
            $skin_list[] = array(
                'skin_id'=> $skin_info['skin_id'],
            	'name'   => $skin_info['skin_name'],
                'url'    => $skin_info['skin_value']
            );
        }
        
        $mClassCourseSkin = ClsFactory::Create('Model.mClassCourseSkin');
        
        $success = $mClassCourseSkin->addBatClassCourseSkin($skin_list);
        if(empty($success)) {
            
            $this->showError(null, "/Sns/ClassCourse/OldData/index");
        }
        
        echo '数据处理完成共处理'.count($skin_list).'条数据';exit;    
    }
    
    /*
     * 个人课程皮肤配置表旧数据处理
     * 
     */
    
    public function oldcourseConfig(){
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
        
        $perpage = 100;
        $offset = ($page - 1) * $perpage;
        $mPersonconfig = ClsFactory::Create('Model.mPersonconfig');

        $where = array('curriculum_bg_id > 0', 'client_account > 0');
        $old_course_config_list = $mPersonconfig->getPsersonConfigInfo($where, 'client_account', $offset, $perpage);
        if(empty($old_course_config_list)){
            echo '数据处理完成了';exit;    
        }
        
        foreach($old_course_config_list as $client_account=>$course_config_info) {
            $course_config_list[] = array(
                'client_account' => $course_config_info['client_account'],
                'skin_id' => $course_config_info['curriculum_bg_id']
            );
        }
       // dump($course_config_list);exit;
        $mClassCourseConfig = ClsFactory::Create('Model.mClassCourseConfig');

        $success = $mClassCourseConfig->addBatClassCourseConfig($course_config_list);
        
        if(empty($success)) {
            --$page;
        }
        
        $this->showError(null, "/Sns/ClassCourse/Course/OldData?page=" . ($page + 1));
        
    }
    
    
    /*
     * 考试信息表旧数据处理 
     * 
     */
    public function oldExam() {
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
        
        $perpage = 100;
        $offset = ($page - 1) * $perpage;
        
        $where = array('class_code>0', 'subject_id>0');
        $mExam = ClsFactory::Create('Model.mExamInfo');
        $exam_list = $mExam->getExamInfo($where, 'exam_id', $offset, $perpage);
        if(empty($exam_list)){
            echo '数据处理完成了';exit;  //共814 条  
        }
        //格式化数据为存入新表做准备
        $new_data = array();
        foreach ($exam_list as $key=>$exam) {

            $new_data[] = array(
                'class_code' => $exam['class_code'],
                'subject_id' => $exam['subject_id'],
                'exam_name'  => $exam['exam_name'],
                'exam_time'  => strtotime($exam['exam_date']),
                'add_account'=> $exam['add_account'],
                'add_time'   => strtotime($exam['add_date']),
                'upd_account'=> $exam['upd_account'],
                'upd_time'   => strtotime($exam['upd_date']),
                'exam_good'  => (float)$exam['exam_good'],
                'exam_bad'   => (float)$exam['exam_bad'],
                'exam_well'  => (float)$exam['exam_well'],
                'is_published' => empty($exam['subtype']) ? 1 : 0,    //0:未发布 1:已发布 默认1 原来 ：默认 0 发布 1 草稿
                'is_sms'       => 1,      //0:全部未发送 1:全部发送  2:部分发送 默认1 原来没有这个字段旧数据默认全部发送
            );
        }
         
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        
        $success = $mClassExam->addBatClassExam($new_data);

        if(empty($success)) {
            --$page;
        }
        
        $this->showError(null, "/Sns/ClassExam/Exam/OldData?page=" . ($page + 1));
    }
    
    
   /*
     * 考试成绩信息表旧数据处理 
     * 
     */
    public function oldExamScore() {
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
        
        $perpage = 100;
        $offset = ($page - 1) * $perpage;
        
        
        $mExam = ClsFactory::Create('Model.mStudentScore');
        $exam_score_list = $mExam->getStudentScoreInfo(null, 'exam_id, score_id', $offset, $perpage);
        if(empty($exam_score_list)){
            echo '数据处理完成了';exit;  //共814 条  
        }
        //格式化数据为存入新表做准备
        $new_data = array();
        foreach ($exam_score_list as $key=>$exam_score) {

            $new_data[] = array(
                'client_account'=> $exam_score['client_account'],
                'exam_id'=> $exam_score['exam_id'],
                'exam_score'=>$exam_score['exam_score'],
                'score_py'=>$exam_score['score_py'],
                'add_time'=>strtotime($exam_score['add_date']),
                'add_account'=>$exam_score['add_account'],
                'upd_time'=>strtotime($exam_score['upd_date']),
                'upd_account'=>$exam_score['upd_account'],
                'is_join'=> $exam_score['exam_score'] >0 ? 1 : 0,  //0未参加 1 参加 原数据没有这个字段 分数为 -1 为未参加 
            	'is_sms'=> 1, // 0:未发送 1:发送  默认0 原来没有该字段 默认全部发送了
            

            );
        }

        $mClassExam = ClsFactory::Create('Model.mClassExamScore');
        
        $success = $mClassExam->addBatClassExamScore($new_data);

        if(empty($success)) {
            --$page;
        }
        
        $this->showError(null, "/Sns/ClassExam/Exam/OldData?page=" . ($page + 1));
    }
}

?>