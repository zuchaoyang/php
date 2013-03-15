<?php
class mSubjectInfo extends mBase{
    protected $_dSubjectInfo = null;

    public function __construct() {
        $this->_dSubjectInfo = ClsFactory::Create('Data.dSubjectInfo');
    }
    
    //通过主键获取科目信息
    public function getSubjectInfoById($subject_ids) {
        if(empty($subject_ids)) {
            return false;
        }
        return $this->_dSubjectInfo->getSubjectInfoById($subject_ids);
    }
    
    //获得该学校对应的课程列表
    public function getSubjectInfoBySchoolid($schoolid) {
        if(empty($schoolid)) {
            return false;
        }
        
        return $this->_dSubjectInfo->getSubjectInfoBySchoolid($schoolid);
    }
    
    //添加课程
    public function addSubjectInfo($subject, $return_insertid = false){
        if(empty($subject)) {
            return false;
        }
        
        return $this->_dSubjectInfo->addSubjectInfo($subject, $return_insertid);
    }
    
    //添加课程
    public function modifySubjectInfo($subject,$subject_id){
        if(empty($subject)||empty($subject_id)) {
            return false;
        }
        
        return $this->_dSubjectInfo->modifySubjectInfo($subject,$subject_id);        
    }
    
    /**
     * 获取教师的所有科目信息
     * @param $uids
     */
    public function getSubjectInfoByTeacherUidFromSchoolTeacher($uids) {
        if(empty($uids)) {
            return false;
        }
        
        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $schoolteacher_arr = $mSchoolTeacher->getSchoolTeacherByTeacherUid($uids);
        $subjectids = $teachersubject_list = array();
        foreach((array)$schoolteacher_arr as $uid=>$list) {
            foreach($list as $schoolteacher) {
                $subject_id = intval($schoolteacher['subject_id']);
                if($subject_id <= 0) continue;
                $teachersubject_list[$uid][$subject_id] = $subject_id;
                $subjectids[$subject_id] = $subject_id;
            }
        }
        unset($schoolteacher_arr);
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfolist = $mSubjectInfo->getSubjectInfoById($subjectids);
        $new_subjectinfolist = array();
        if(!empty($subjectinfolist) && !empty($teachersubject_list)) {
            foreach($teachersubject_list as $uid=>$list) {
                foreach($list as $subject_id) {
                    if(isset($subjectinfolist[$subject_id])) {
                        $new_subjectinfolist[$uid][$subject_id] = $subjectinfolist[$subject_id];
                    }
                }
            }
        }
        
        return !empty($new_subjectinfolist) ? $new_subjectinfolist : false;
    }
    
     /**
     * 获取教师的所有科目信息
     * @param $uids
     */
    public function getSubjectInfoByTeacherUidFromClassTeacher($uids) {
        if(empty($uids)) {
            return false;
        }
        
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_arr = $mClassTeacher->getClassTeacherByUid($uids);
        
        $subjectids = $teachersubject_list = array();
        foreach((array)$classteacher_arr as $uid=>$list) {
            foreach($list as $classteacher) {
                $subject_id = intval($classteacher['subject_id']);
                if($subject_id <= 0) continue;
                $teachersubject_list[$uid][$subject_id] = $subject_id;
                $subjectids[$subject_id] = $subject_id;
                $new_classteacher[] = $classteacher;
            }
        }
        unset($classteacher_arr);
       	$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subjectinfolist = $mSubjectInfo->getSubjectInfoById($subjectids);
        
        foreach($subjectinfolist as $keys=>& $vals){
        	foreach($new_classteacher as $val){
        		if($keys == $val['subject_id']){
        			$vals['class_code'] = $val['class_code'];
        			break;
        		}
        	}
        }
        
        $new_subjectinfolist = array();
        if(!empty($subjectinfolist) && !empty($teachersubject_list)) {
            foreach($teachersubject_list as $uid=>$list) {
                foreach($list as $subject_id) {
                    if(isset($subjectinfolist[$subject_id])) {
                        $new_subjectinfolist[$uid][$subject_id] = $subjectinfolist[$subject_id];
                    }
                }
            }
        }
        return !empty($new_subjectinfolist) ? $new_subjectinfolist : false;
    }
    
    /**
     * 通过教师账号获取教师的科目信息
     * @param $uids
     */
    public function getSubjectInfoByTeacherUid($uids, $class_code) {
        if(empty($uids) || empty($class_code)) {
            return false;
        }
        //只支持单个班级的教师科目信息获取
        $class_code = is_array($class_code) ? array_shift($class_code) : $class_code;
        $class_code = max(0, intval($class_code));
        if(!$class_code) {
            return false;
        }
        //建立教师和科目信息之间的关系
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_arr = $mClassTeacher->getClassTeacherByUid($uids, array('class_code' => $class_code));
		
        $classteacher_subject_arr = $subjectids = array();
        foreach((array)$classteacher_arr as $uid=>$classteacher_list) {
            foreach($classteacher_list as $classteacher) {
                $subject_id = intval($classteacher['subject_id']);
                if($subject_id <= 0 ) continue;
                
                $classteacher_subject_arr[$uid][$subject_id] = $classteacher;
                $subjectids[$subject_id] = $subject_id;
            }
        }
        unset($classteacher_arr);
        
        $subjectinfolist = $this->getSubjectInfoById($subjectids);
        foreach((array)$classteacher_subject_arr as $uid=>$list) {
            foreach($list as $subject_id=>$classteacher) {
                if(isset($subjectinfolist[$subject_id])) {
                    $classteacher = array_merge($classteacher, $subjectinfolist[$subject_id]);
                }
                $list[$subject_id] = $classteacher;
            }
            $classteacher_subject_arr[$uid] = $list;
        }
        return !empty($classteacher_subject_arr) ? $classteacher_subject_arr : false;
    }
    
    /**
     * 通过教师账号获取教师的所有科目信息
     * @param $uids
     */
    public function getSubjectInfoByTeacherUids($uids) {
        if(empty($uids)) {
            return false;
        }
        $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
        $schoolteacherlist = $mSchoolTeacher->getSchoolTeacherByTeacherUid($uids);
        $subjectids = $teachersubjectlist = array();
        //不关心对应school_teacher里面的数据
        if(!empty($schoolteacherlist)) {
            foreach($schoolteacherlist as $uid=>$list) {
                foreach($list as $key=>$schoolteacher) {
                    $subject_id = intval($schoolteacher['subject_id']);
                    if($subject_id <= 0) {
                        continue;
                    }
                    $subjectids[] = $subject_id;
                    $teachersubjectlist[$uid][$subject_id] = $subject_id;
                }
            }
            !empty($subjectids) && $subjectids = array_unique($subjectids);
            unset($schoolteacherlist);
        }
        //获取教师的科目信息
        $new_teachersubjectinfolist = array();
        if(!empty($subjectids) && !empty($teachersubjectlist)) {
            $subjectinfolist = $this->getSubjectInfoById($subjectids);
            foreach($teachersubjectlist as $uid=>$list) {
                foreach($list as $subject_id) {
                    if(!empty($subjectinfolist[$subject_id])) {
                        $new_teachersubjectinfolist[$uid][$subject_id] = $subjectinfolist[$subject_id];
                    }
                }
            }
        }
        
        return !empty($new_teachersubjectinfolist) ? $new_teachersubjectinfolist : false;
    }
    
    /**
     * 通过用户id获取用户的科目信息
     * @param $uids 教师用户的账号id
     * @param $repeat_flag 是否去掉相同科目的教师信息
     * @return 教师对应的科目信息按照科目信息组织的数据
     */
    function getSubjectInfoByTeacherUidWithName($uids , $class_code) {
        if(empty($uids) || empty($class_code)) {
            return false;
        }
        //只支持单个班级的教师科目信息获取
        $class_code = is_array($class_code) ? array_shift($class_code) : $class_code;
        $class_code = max(0, intval($class_code));
        if(!$class_code) {
            return false;
        }
        
        //建立教师和科目信息之间的关系
        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $classteacher_arr = $mClassTeacher->getClassTeacherByUid($uids, array('class_code' => $class_code));
        
        $classteacher_subject_arr = $subjectids = array();
        foreach((array)$classteacher_arr as $uid=>$classteacher_list) {
            foreach($classteacher_list as $classteacher) {
                $subject_id = intval($classteacher['subject_id']);
                if($subject_id <= 0 ) continue;
                
                $classteacher_subject_arr[$uid][$subject_id] = $classteacher;
                $subjectids[$subject_id] = $subject_id;
            }
        }
        unset($classteacher_arr);
        
        //获取教师的科目信息
        $subjectinfolist = array();
        $subjectids = array_unique($subjectids);
        $subjectinfolist = $this->getSubjectInfoById($subjectids);
        //获取教师的姓名相关信息,并整合科目信息到对应的教师账号中去
        $new_subjectinfolist = array();
        if(!empty($classteacher_subject_arr) && !empty($subjectinfolist)) {
            $uids = array_keys($classteacher_subject_arr);
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($uids);
            foreach($classteacher_subject_arr as $uid=>$list) {
                $teachername = isset($userlist[$uid]) ? $userlist[$uid]['client_name'] : false;
                foreach($list as $subject_id => $classteacher) {
                    if(isset($subjectinfolist[$subject_id])) {
                        $subject = $subjectinfolist[$subject_id]; 
                        //兼容以前的调用
                        $subject['subject_name_short'] = $subject['subject_name'];
                        $subject['subject_name'] = $subject['subject_name'] . ($teachername ?  "($teachername)" : "");
                        $new_subjectinfolist[$uid][$subject_id] = array_merge($classteacher, $subject);
                    }
                }
            }
            unset($userlist , $classteacher_subject_arr);
        }
        
        //数据降维度处理,多数情况下调用者不关心科目和教师的id映射关系
        if(!empty($new_subjectinfolist)) {
            $tmp_subjectinfolist = array();
            foreach($new_subjectinfolist as $uid=>$list) {
                foreach($list as $subject_id=>$subject) {
                    $tmp_subjectinfolist[] = $subject;
                }
            }
            $new_subjectinfolist = & $tmp_subjectinfolist;
        }
        
        return !empty($new_subjectinfolist) ? $new_subjectinfolist : false;
    }
    
    
}