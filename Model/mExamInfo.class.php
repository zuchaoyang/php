<?php
class mExamInfo extends mBase {
    protected $_dExamInfo = null;
    
    public function __construct() {
		$this->_dExamInfo = ClsFactory::Create('Data.dExamInfo');    	
    }
    
    /*
     * 通过where 条件 查询记录
     *  
     */
    public function getExamInfo($where, $orderby, $offset, $limit) {
        
        return $this->_dExamInfo->getInfo($where, $orderby, $offset, $limit);
        
    }
    
    /**
     * 通过主键获取考试的基本信息
     * @param $exam_ids
     */
    public function getExamInfoBaseById($exam_ids) {
        if(empty($exam_ids)) {
            return false;
        }
        
        return $this->_dExamInfo->getExamInfoBaseById($exam_ids);
    }
    
     /**
     * 只允许单个考试的全部成绩信息的获取
     * @param unknown_type $exam_id
     */
    public function getExamInfoById($exam_ids) {
        if(empty($exam_ids)) {
            return false;
        }
        
        $examinfoarr = $this->getExamInfoBaseById($exam_ids);
        if(!empty($examinfoarr)) {
            $mStudentScore = ClsFactory::Create('Model.mStudentScore');
            $studentscorearr = $mStudentScore->getStudentScoreExamId($exam_ids);
            if(!empty($studentscorearr)) {
                foreach($studentscorearr as $examid=>$studentscore) {
                    if(isset($examinfoarr[$examid]) && !empty($studentscore)) {
                        foreach($studentscore as $studentinfo) {
                            $examinfoarr[$examid]['student_score'][$studentinfo['client_account']] = $studentinfo;
                        }
                        
                    }
                }
            }
        }
        return !empty($examinfoarr) ? $examinfoarr : false;
    }
    
    

    /**
     * 通过科目id获取考试信息
     * @param $subject_ids
     */
    public function getExamInfoBySubjectId($subject_ids , $filters = array()) {
        if(empty($subject_ids)) {
            return false;
        }
        
        $examinfolist = $this->_dExamInfo->getExamInfoBySubjectId($subject_ids);
        if(!empty($examinfolist) && !empty($filters)) {
            foreach($examinfolist as $subjectid=>$tilist) {
                foreach($tilist as $key=>$examinfo) {
                    foreach($filters as $field=>$values) {
                        if($field == 'exam_name') {
                            $values = is_array($values) ? array_shift($values) : $values;
                            $values = strval($values);
							//echo $examinfo[$field]."----".$values."---".strpos($examinfo[$field],$values)."<br>";
							if(strpos($examinfo[$field],$values) === false) {
                                 unset($tilist[$key]);
                                 break;
                            }
                        } else if ($field == 'exam_date') { 
							$seardate = explode(",",$values);
							if (!empty($seardate[0]) && !empty($seardate[1])) {
								if(strtotime($examinfo[$field]) < strtotime($seardate[0]) || strtotime($examinfo[$field]) > strtotime($seardate[1])) {
									 unset($tilist[$key]);
									 break;
								}
							}
						} else {
                            $values = is_array($values) ? $values : array($values);
                            if(isset($examinfo[$field]) && !in_array($examinfo[$field] , $values)) {
                                unset($tilist[$key]);
                                break;
                            }
                        }
                    }
                    $examinfolist[$subjectid] = $tilist;
                }
            }
        }

        return !empty($examinfolist) ? $examinfolist : false;
    }




    /**
     * 删除考试主表中的信息
     * @param $exam_id
     */
    public function delExamInfo($exam_id) {
        if(empty($exam_id)) {
            return false;
        }
        
        return $this->_dExamInfo->delExamInfo($exam_id);
    }
    
    // 我的考试成绩信息
    public function getExamInfoByClassCode($school_id, $classcode, $firter, $offset, $limit) {
        if(empty($classcode) || empty($school_id)) {
            return false;
        }
        
        $offset = max(0,$offset);
        $limit = max(0,$limit);
        $classcode = (array)$classcode;
        
		if(!empty($firter[0])) {
			$wheresql[] = " subject_id=".$firter[0];
		}
		if(!empty($firter[1])) {
			$wheresql[] = " exam_name like '%".$firter[1]."%'";
		}
		if(!empty($firter[2]) && !empty($firter[3])) {
			$wheresql[] = " (exam_date between '".$firter[2]."' and '".$firter[3]."')";
		}
		if(!empty($firter[4])) {
		    $wheresql[] = "add_account=$firter[4]";
		}
        return $this->_dExamInfo->getInfo($wheresql, 'exam_id desc', $offset, $limit);
        
    }
    

	// 保存考试信息
	public function addExamInfo($ExamInfoData , $is_return_id) {
		if(empty($ExamInfoData)) {
			return false;
		}
		
		return $this->_dExamInfo->addExamInfo($ExamInfoData , $is_return_id);
    }


	// 保存考试信息
	public function modifyExamInfo($ExamInfoData,$exam_id) {
		if(empty($ExamInfoData) || empty($exam_id)) {
			return false;	
		}
		
		return $this->_dExamInfo->modifyExamInfo($ExamInfoData,$exam_id);
    }
    
    
    /**
     * 通过班级class_code 获取考试信息
     * @param $subject_ids
     */
    public function getExamInfoByClassCodeTO($class_code) {
    	if(empty($class_code)) {
    		return false;
    	}
    	
        return $this->_dExamInfo->getExamInfoByClassCode($class_code);
    }
}




?>