<?php

class CourseAction extends SnsController {
    
    public function __construct() {
        parent::__construct ();
    }
    
    /*
     * 课程表首页 （根据不同的账号类型调用不同的模板（学生，老师，家长））
     * 
     */
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);  //tudo 没有班级跳转到那
        }
        
        $isEditCourse = $this->isEditCourse($class_code); //是否具有修改课程表的权限
        //查询当前班级所有课程
        $mClassCourse = ClsFactory::Create('Model.mClassCourse');
        $class_course_list = $mClassCourse->getClassCourseByClassCode($class_code);
        $class_course_list = & $class_course_list[$class_code];
        
        //重新整理数组为2维数组
        $new_class_course_list = array();
        foreach($class_course_list as $course) {
            $new_class_course_list[$course['num_th']][$course['weekday']] = $course;
        }
        
        //填充课程表数组
        $am_course_list = $pm_course_list = array();
        //import("@.Common_wmw.WmwString");
        for($i = 1; $i <= 8 ; $i++) {
            //$which_course = WmwString::getNumsUppercase($i);
            for($j = 1; $j <= 5 ; $j++) {
                if ($i <= 4) {
                    $am_course_list[$i][$j] = isset($new_class_course_list[$i][$j]) ? $new_class_course_list[$i][$j] : array();
                } else {
                    $pm_course_list[$i][$j] = isset($new_class_course_list[$i][$j]) ? $new_class_course_list[$i][$j] : array();
                }
                
            }
        }

        //取出所有课程皮肤（以后多了可能会分页展示）
        $mClassCourseSkin = ClsFactory::Create('Model.mClassCourseSkin');
        $skin_list = $mClassCourseSkin->getClassCourseSkinList(null, 'skin_id', 0, 20);

        //取出当前账号对应课程皮肤
        $user_course_skin = array();
        $mClassCourseConfig = ClsFactory::Create('Model.mClassCourseConfig');
        $user_skin_list = $mClassCourseConfig->getClassCourseConfigById($this->user['client_account']); 
        $user_skin_id = $user_skin_list[$this->user['client_account']]['skin_id'];
        $user_course_skin = $skin_list[$user_skin_id];
        
        $this->assign('class_code', $class_code);
        $this->assign('am_course_list', $am_course_list);
        $this->assign('pm_course_list', $pm_course_list);
        $this->assign('skin_list', $skin_list);
        $this->assign('user_course_skin', $user_course_skin);
        
        $tpl = 'class_course';
        if($isEditCourse){
            $tpl = 'class_course_admin';
            //取出当前班级的所有科目
            $subject_list = $this->getClassSubject($class_code);

            $this->assign('subject_list', $subject_list);
        }
        
        $this->display($tpl);
    }
    
    //ajax 修改个人课程表皮肤配置
    public function saveSkinAjax() {
        $skin_id = $this->objInput->postInt('skin');
        if(empty($skin_id)) {
            $this->ajaxReturn(null, '非法操作！', -1, 'JSON');
        }
        $data = array('skin_id' => $skin_id);
        $account = $this->user['client_account'];
        $mClassCourseConfig = ClsFactory::Create('Model.mClassCourseConfig');
        $user_skin_list = $mClassCourseConfig->getClassCourseConfigById($this->user['client_account']); 
        $old_skin = $user_skin_list[$this->user['client_account']];
        
        if (!empty($old_skin) && $old_skin['skin_id'] == $skin_id) {
            $this->ajaxReturn(null, '没有更改哦', -1, 'JSON');
        }
        
        if (empty($old_skin)) {
            $data['client_account'] = $this->user['client_account'];
            $is_success = $mClassCourseConfig->addClassCourseConfig($data); 
        } else {
            $is_success = $mClassCourseConfig->modifyClassCourseConfig($data, $account);
        }
        
        if(empty($is_success)) {
             $this->ajaxReturn(null, '系统繁忙稍后重试', -1, 'JSON');
        }
        
        $this->ajaxReturn(null, '修改成功', 1, 'JSON');
    }
    
    //ajax 修改课程表
    public function saveCourseAjax() {
        $course_id   = $this->objInput->postInt('course_id');
        $class_code  = $this->objInput->postInt('class_code');
        $weekday     = $this->objInput->postInt('weekday');
        $num_th      = $this->objInput->postInt('num_th');
        $course_name = $this->objInput->postStr('course_name');

        //数据验证正确性
        if($class_code <= 0 || $weekday < 1 || $weekday > 5 || $num_th < 1 || $num_th > 8 || empty($course_name)) {
             $this->ajaxReturn(null, '操作有误!', -1, 'JSON');
        }
        
        //验证用户是否具有修改权限
        if(!$this->isEditCourse($class_code)) {
           $this->ajaxReturn(null, '您没有权限修改课程表', -1, 'JSON');
        }
        
        $mClassCourse = ClsFactory::Create('Model.mClassCourse');
        //根据班级class_code,weekday,num_th 检查课程是否已经存在
        $where_arr = array(
            "class_code='$class_code'",
            "weekday='$weekday'",
            "num_th='$num_th'"
        );
        $course_info = $mClassCourse->getClassCourse($where_arr, 'course_id desc', 0, 1);
        $course_info = & reset($course_info);

        if(!empty($course_info)) {
            $course_id = $course_info['course_id'];

            $class_course_list = $mClassCourse->getClassCourseById($course_id);
            $class_course = & $class_course_list[$course_id];
            if(empty($class_course) || $class_course['class_code'] != $class_code) {
                $this->ajaxReturn(null, '操作有误!', -1, 'json');
            }
            $class_course_datas = array(
            	'name'     => $course_name,
                'upd_time' => time(),
                'upd_account' => $this->user['client_account']
            );
            if(!$mClassCourse->modifyClassCourse($class_course_datas, $course_id)) {
                unset($course_id);
                unset($course_info);
            }
        } else {
             $class_course_datas = array(
                'class_code'  => $class_code,
                'weekday'     => $weekday,
                'num_th'      => $num_th,
                'name'        => $course_name,
                'upd_account' => $this->user['client_account'],
                'upd_time'    => time()
            );
            $course_id = $mClassCourse->addClassCourse($class_course_datas, true);
        }
        
        if(empty($course_id)) {
            $this->ajaxReturn(null, '系统繁忙请稍后重试', -1, 'JSON');
        }
        $this->ajaxReturn(array('course_id' => $course_id), '修改成功!', 1, 'json');
    }
    
    //ajax 删除课程表
    public function delCourseAjax() {
        $class_code  = $this->objInput->postInt('class_code');
        $course_id   = $this->objInput->postInt('course_id');

        //验证用户是否具有修改权限
        if(!$this->isEditCourse($class_code)) {
           $this->ajaxReturn(null, '您没有权限修改课程表', -1, 'JSON');
        }
        
        $mClassCourse = ClsFactory::Create('Model.mClassCourse');
        $class_course_list = $mClassCourse->getClassCourseById($course_id);
        $class_course = & $class_course_list[$course_id];
        
        if(empty($class_course) || $class_course['class_code'] != $class_code) {
            $this->ajaxReturn(null, '没有权限删除!', -1, 'json');
        }

        if(!$mClassCourse->delClassCourse($course_id)) {
            $this->ajaxReturn(null, '系统繁忙请稍后重试', -1, 'JSON');
        }
        
        $this->ajaxReturn(null, '删除成功', 1, 'JSON');
    }
    /*
     * 判断当前用户在当前班级是否有修改课程表权限
     */
    private function isEditCourse($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //老师, 班级管理员具有修改课程表权限
        $client_class = $this->getUserClientClass($class_code);  //获取当前用户的当前班级关系
        return ($client_class['class_admin'] == IS_CLASS_ADMIN || $client_class['client_type']==CLIENT_TYPE_TEACHER) ? true : false;
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
    
    /*
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
        
        echo('旧数据都处理完成了哦！');
    }
    

    
    
}
