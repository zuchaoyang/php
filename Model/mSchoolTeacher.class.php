<?php
//郭学文
class mSchoolTeacher extends mBase{
    protected $_dSchoolTeacher = null;
    
    public function __construct() {
        $this->_dSchoolTeacher = ClsFactory::Create('Data.dSchoolTeacher');
    }
    
    //通过学校id得到老师学校信息表里的信息
    function getSchoolTeacherInfoBySchoolId($schoolIds , $filters = array()){
        if(empty($schoolIds)) {
            return false;
        }
        
        $SchoolTeacherInfos = $this->_dSchoolTeacher->getSchoolTeacherInfoBySchoolId($schoolIds);
        
         if(!empty($SchoolTeacherInfos) && !empty($filters)) {
          foreach($filters as $field=>$values) {
              $values = is_array($values) ? $values : array($values);
              foreach($SchoolTeacherInfos as $schoolid=>$schoolteacherInfoList) {
                  foreach ($schoolteacherInfoList as $teacherSchoolId=>$schoolteacherInfo){
                      if(isset($schoolteacherInfo[$field]) && !in_array($schoolteacherInfo[$field] , $values)) {
                          unset($schoolteacherInfoList[$teacherSchoolId]);
                      }
                  }
                  $SchoolTeacherInfos[$schoolid] = $schoolteacherInfoList;
              }
          }
        }
        return !empty($SchoolTeacherInfos) ? $SchoolTeacherInfos : false;
    }
    /**
     * 通过教师账号获取科目的关系
     * @param $uids
     */
    public function getSchoolTeacherByTeacherUid($uids) {
        if(empty($uids)) {
            return false;
        }
        
        return $this->_dSchoolTeacher->getSchoolTeacherByTeacherUid($uids);
    }

    //添加信息
    function addSchoolTeacher ($schoolTeacherInfo, $is_return_id=false){
        if(empty($schoolTeacherInfo)) {
            return false;    
        }
        
        return $this->_dSchoolTeacher->addSchoolTeacher($schoolTeacherInfo, $is_return_id);
    }

    // 批量插入教师科目信息
    public function addSchoolTeacherBat($schoolTeacher_arr) {
        if(empty($schoolTeacher_arr)) {
            return false;
        }
        
        return $this->_dSchoolTeacher->addBat($schoolTeacher_arr);
    }
    
    //删除老师信息
    public function delSchoolTeacher($school_teacher_id) {
        if(empty($school_teacher_id)) {
            return false;
        }
        
        return $this->_dSchoolTeacher->delSchoolTeacher($school_teacher_id);
    }

    /**
     * 批量修改教师信息
     * @param $dataarr
     * @param $uid
     */
    function modifySchoolTeacherBat($dataarr , $uid) {
        if(empty($uid) || empty($dataarr)) {
            return false;
        }
        
        $uid = is_array($uid) ? array_shift($uid) : $uid;
        $uid = intval($uid);
        //搜索当前用户下对应的科目信息
        $schoolTeacher_arr = $this->getSchoolTeacherByTeacherUid($uid);
        $schoolTeacher_list = & $schoolTeacher_arr[$uid];
        $exists_subjectids = array();
        if(!empty($schoolTeacher_list)) {
            foreach($schoolTeacher_list as $subject) {
                $teacher_school_id = intval($subject['teacher_school_id']);
                $subject_id = intval($subject['subject_id']);
                $exists_subjectids[$subject_id] = $teacher_school_id;
            }
        }
        //将数据重新按照subject_id进行组织
        $tmp_dataarr = array();
        foreach($dataarr as $arr) {
            $subject_id = intval($arr['subject_id']);
            if($subject_id < 0) {
                continue;
            }
            $tmp_dataarr[$subject_id] = $arr;
            $subjectids[] = $subject_id;
        }
        $dataarr = & $tmp_dataarr;

        $add_arr = $delete_schoolteacherids = array();
        //增加和删除的数据分类
        if(empty($exists_subjectids)) {
            $add_arr = $dataarr;
            $delete_schoolteacherids = false;
        } else {
            //获取要添加的数据,即现有数据库中不存在的部分
            foreach($dataarr as $subject_id=>$arr) {
                if(!isset($exists_subjectids[$subject_id])) {
                    $add_arr[$subject_id] = $arr;
                }
            }
            //获取要删除的school_teacher_id数据
            $add_keys = array_keys($dataarr);
            foreach($exists_subjectids as $subject_id=>$teacher_school_id) {
                if(!in_array($subject_id, $add_keys)) {
                    $delete_schoolteacherids[$subject_id] = $teacher_school_id;
                }
            }
            unset($dataarr);
        }

        //删除用户的相关记录
        if(!empty($delete_schoolteacherids)) {
            foreach($delete_schoolteacherids as $school_teacher_id) {
                $this->delSchoolTeacher($school_teacher_id);
            }
        }

        //增加相关记录
        $flag = true;
        if(!empty($add_arr)) {
            $this->addSchoolTeacherBat($add_arr);
        }
        
        return $flag;
    }

    //统计该学校实际注册的老师
    public function getSchoolTeacherTotal($schoolid){
        if(empty($schoolid)){
            return false;
        }
        $schoolid = implode(',' , (array)$schoolid);
        $whereSql = array(
            "school_id in ($schoolid)"
        );
        $list = $this->_dSchoolTeacher->getInfo($whereSql);
        
        if(!empty($list)) {
            foreach($list as $info) {
                $uids[] = $info['client_account'];
            }
        }  
        $uids = array_unique($uids); //有重复，需去重
        
        return count($uids);
    }
//通过学校id得到老师学校信息表里的信息
    function getSchoolTeacherInfoBySchoolIdPage($schoolIds, $offset=0, $limit=10){
        if(empty($schoolIds)) {
            return false;
        }
        $wherearr[] = 'school_id in(' . implode(',', (array)$schoolIds) . ')';
        $SchoolTeacherInfos = $this->_dSchoolTeacher->getInfo($wherearr, null, $offset, $limit);

        return !empty($SchoolTeacherInfos) ? $SchoolTeacherInfos : false;
    }
}