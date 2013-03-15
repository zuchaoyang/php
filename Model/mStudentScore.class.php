<?php

class mStudentScore extends mBase {
    protected $_dStudentScore = null;
    
    public function __construct() {
        $this->_dStudentScore = ClsFactory::Create('Data.dStudentScore');       
    }
    
    /*通过where条件查询*/
    public function getStudentScoreInfo($where, $orderby, $offset, $limit) {
        
        return $this->_dStudentScore->getInfo($where, $orderby, $offset, $limit);
    }

    //通过主键 score_id获取学生成绩信息
    public function getStudentScoreById($score_ids) {
        if(empty($score_ids)) {
            return false;
        }
        
        return $this->_dStudentScore->getStudentScoreById($score_ids);
    } 
       
    //通过考试id获取学生的成绩信息
    public function getStudentScoreExamId($exam_ids) {
        if(empty($exam_ids)) {
            return false;
        }
        
        return $this->_dStudentScore->getStudentScoreByExamId($exam_ids);
    }
    
    // 通过考试id和账号获取成绩信息
    public function getStudentScoreByExamIdAccount($exam_ids,$Account) {
        if(empty($exam_ids) || empty($Account)) {
            return false;
        }
        $exam_ids = implode("," , (array)$exam_ids);
        $Account =  is_array($Account) ? array_shift($Account) : $Account;

        $wheresql = array(
        	"client_account='$Account'",
            "exam_id in($exam_ids)",
            "exam_score!=-1"
        );
             
        return $this->_dStudentScore->getInfo($wheresql);        
    }
    
    // 成绩保存
	public function addStudentScore($ScoreData, $return_insertid = false) {
	    return $this->_dStudentScore->addStudentScore($ScoreData, $return_insertid );
    }
    
    public function modifyStudentScore($score_data,$exam_id,$client_account) {
        if(empty($score_data) || empty($exam_id) || empty($client_account)) {
            return false;
        }
        $score_list = $this->getStudentScoreByUid($client_account); //获取该学生参加过的所有的考试信息
        foreach($score_list as $id=>$score_info ) {//获取本次考试中该学生的score_id（主键）
            if($score_info['exam_id'] == $exam_id) {
                $score_id = $id;
            }
        }
        if(!empty($score_id)) {
            $mdf_rs = $this->_dStudentScore->modifyStudentScore($score_data, $score_id);
        } else {
            return false;
        }
		
		return $mdf_rs;
    }
    
    //删除学生成绩信息
    public function delStudentScore($score_id) {
        if(empty($score_id)) {
            return false;
        }
        
        return $this->_dStudentScore->delStudentScore($score_id);
    }
    
}


