<?php
class dStudentScore extends  dBase {
    /**
     * 通过考试id获取学生的成绩信息
     * @param $exam_ids
     */
    protected $_tablename = 'old_wmw_student_score';
    protected $_fields = array(
        'score_id',
        'client_account',
        'exam_id',
        'exam_score',
        'score_py',
        'add_date',
        'add_account',
        'upd_date',
        'upd_account',
    );
    protected $_pk = 'score_id';
    protected $_index_list = array(
        'score_id',
        'client_account',
        'exam_id',
    );
    
    //通过主键 score_id获取学生成绩信息
    public function getStudentScoreById($score_ids) {
        return $this->getInfoByPk($score_ids);
    }
    
    //通过exam_id获取学生成绩信息
    public function getStudentScoreByExamId($exam_ids) {
        return $this->getInfoByFk($exam_ids, 'exam_id');
    }
	
	// 添加学生成绩信息
    public function addStudentScore($dataarr, $is_return_id=false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    //获取某考生的所有考试信息
    public function getStudentScoreByUid($client_account) {
        return $this->getInfoByFk($client_account, 'client_account');
    }
    
    //修改学生成绩
    public function modifyStudentScore($score_data, $score_id) {
        return $this->modify($score_data, $score_id);
    }
    
    //删除学生成绩信息
    public function delStudentScore($score_id) {
        return $this->delete($score_id);
    }
}
 
