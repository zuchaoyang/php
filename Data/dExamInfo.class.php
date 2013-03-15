<?php
class dExamInfo extends dBase {
	protected $_tablename = 'old_wmw_exam_info';
	protected $_fields = array(
		'exam_id',
		'school_id',
		'class_code',
		'subject_id',
		'exam_name',
		'exam_date',
		'add_account',
		'add_date',
		'upd_account',
		'upd_date',
		'exam_well',
		'exam_good',
		'exam_bad',
		'subtype'
	);
	protected $_pk = 'exam_id';
	protected $_index_list = array(
	    'exam_id',
	    'subject_id',
	    'class_code',
	);
    /**
     * 通过考试id获取考试的基本信息
     * @param $exam_ids
     */
    public function getExamInfoBaseById($exam_ids) {
        return $this->getInfoByPk($exam_ids);
    }
    
    /**
     * 通过科目id获取考试信息
     * @param $subject_ids
     */
    public function getExamInfoBySubjectId($subject_ids) {
        return $this->getInfoByFk($subject_ids, 'subject_id');
    }
    
    /**
     * 通过班级class_code 获取考试信息
     * @param $subject_ids
     */
    public function getExamInfoByClassCode($class_code) {
        return $this->getInfoByFk($class_code, 'class_code');
    }
    
    /**
     * 删除考试表中的信息
     * @param $exam_id
     */
    public function delExamInfo($exam_id) {
        return $this->delete($exam_id);
    }
    
    // 保存考试信息
    public function addExamInfo($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }


	//修改考试信息
    public function modifyExamInfo($datas, $exam_id) {
        return $this->modify($datas, $exam_id);
    }
}

?>