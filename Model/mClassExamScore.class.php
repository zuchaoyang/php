<?php

class mClassExamScore extends mBase {
    protected $_dClassExamScore = null;
    
    public function __construct() {
        $this->_dClassExamScore = ClsFactory::Create('Data.dClassExamScore');       
    }
    
    
    /**
     * 通过where 条件获取成绩列表
     * 
     */
    public function getClassExamScoreInfo($where, $orderby, $offset = null, $limit = null) {
        
        return $this->_dClassExamScore->getInfo($where, $orderby, $offset, $limit);
    }
    
    //通过主键 score_id获取学生成绩信息
    public function getClassExamScoreById($score_ids) {
        if(empty($score_ids)) {
            return false;
        }
        
        return $this->_dClassExamScore->getClassExamScoreById($score_ids);
    } 
       
    //通过考试id获取学生的成绩信息
    public function getClassExamScoreByExamId($exam_ids) {
        if(empty($exam_ids)) {
            return false;
        }
        
        return $this->_dClassExamScore->getClassExamScoreByExamId($exam_ids);
    }
    
    // 通过考试id和账号获取成绩信息
    public function getClassExamScoreByExamIdAndAccount($exam_ids, $Account) {
        if(empty($exam_ids) || empty($Account)) {
            return false;
        }
        $exam_ids = implode("," , (array)$exam_ids);
        $Account =  is_array($Account) ? array_shift($Account) : $Account;

        $wheresql = array (
        	"client_account='$Account'",
            "exam_id in($exam_ids)"
        );
             
        return $this->_dClassExamScore->getInfo($wheresql);        
    }
    
    // 成绩保存
	public function addClassExamScore($ScoreData, $return_insertid = false) {
	    return $this->_dClassExamScore->addClassExamScore($ScoreData, $return_insertid );
    }

    // 批量添加成绩
	public function addBatClassExamScore($datas) {
	    if (empty($datas)){
	        return false;
	    }
	    
	    return $this->_dClassExamScore->addBat($datas);
    }
    
    //修改成绩
    public function modifyClassExamScore($score_data, $score_id) {
        if(empty($score_data) || empty($score_id)) {
            return false;
        }

        $mdf_rs = $this->_dClassExamScore->modifyClassExamScore($score_data, $score_id);
		return !empty($mdf_rs) ? true : false;
    }
    
    
    //批量修改成绩信息 (只是针对一次考试的批量修改)
    public function modeifyBatExamScore($datas, $exam_id) {
        if (empty($datas) || !is_array($datas) || empty($exam_id)) {
            return false;
        }
        
        //循环修改
         $res = true;
        foreach ($datas as $score_id=>$score_info) {
            //验证成绩是否属于该考试
            $score_arr = $this->getClassExamScoreById($score_id);
            $score = & $score_arr[$score_id];
            if ($score['exam_id'] != $exam_id) {
                continue;
            }
            
            //修改成绩信息       
            $res = $this->modifyClassExamScore($score_info, $score_id);
            if (empty($res)) {
                $res = false;  //所有都成功才算成功
            }
        }
        
        return $res;
    }
    
    //删除学生成绩信息
    public function delClassExamScore($score_id) {
        if(empty($score_id)) {
            return false;
        }
        
        return $this->_dClassExamScore->delClassExamScore($score_id);
    }
    
    /**
     * 根据考试id批量删除学生成绩信息
     */
    public function delBatClassExamScoreByExamId($exam_id) {
        if(empty($exam_id)) {
            return false;
        }
        
        //获取成绩列表
        $score_list = $this->getClassExamScoreByExamId($exam_id);
        $score_list = & $score_list[$exam_id];
        if (empty($score_list)) {
            return true;
        }
        
        $score_ids = array_keys($score_list);
        $res = true;
        foreach($score_ids as $score_id) {
           $success =  $this->_dClassExamScore->delClassExamScore($score_id);
           if (!$success) {
               $res = false;
           }
        }
        
        return $res;
    }
    
}


