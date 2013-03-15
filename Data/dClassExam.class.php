<?php
class dClassExam extends dBase {
	protected $_tablename = 'wmw_class_exam';
	protected $_fields = array(
		'exam_id',
		'class_code',
		'subject_id',
		'exam_name',
		'exam_time',
		'add_account',
		'add_time',
		'upd_account',
		'upd_time',
		'exam_well',
		'exam_good',
		'exam_bad',
		'is_published',
		'is_sms',
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
    public function getClassExamById($exam_ids) {
        return $this->getInfoByPk($exam_ids);
    }
    
    /**
     * 通过班级class_code 获取考试信息
     * @param $subject_ids
     */
    public function getClassExamByClassCode($class_code) {
        return $this->getInfoByFk($class_code, 'class_code');
    }
    
    /**
     * 删除考试表中的信息
     * @param $exam_id
     */
    public function delClassExam($exam_id) {
        return $this->delete($exam_id);
    }
    
    // 保存考试信息
    public function addClassExam($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }


	//修改考试信息
    public function modifyClassExam($datas, $exam_id) {
        return $this->modify($datas, $exam_id);
    }
}

?>