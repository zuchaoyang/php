<?php
class mClassExam extends mBase {
    protected $_dClassExam = null;
    
    public function __construct() {
		$this->_dClassExam = ClsFactory::Create('Data.dClassExam');    	
    }
    
    /*
     * 通过where 条件 查询记录
     *  
     */
    public function getClassExam($where, $orderby, $offset, $limit) {

        return $this->_dClassExam->getInfo($where, $orderby, $offset, $limit);
    }
    
    /**
     * 通过主键获取考试的基本信息
     * @param $exam_ids
     */
    public function getClassExamById($exam_ids) {
        if(empty($exam_ids)) {
            return false;
        }
        
        return $this->_dClassExam->getClassExamById($exam_ids);
    }
    
    /**
     * 删除考试主表中的信息
     * @param $exam_id
     */
    public function delClassExam($exam_id) {
        if(empty($exam_id)) {
            return false;
        }
        
        return $this->_dClassExam->delClassExam($exam_id);
    }

	// 保存考试信息
	public function addClassExam($ClassExamData , $is_return_id = false) {
		if(empty($ClassExamData)) {
			return false;
		}
		
		return $this->_dClassExam->addClassExam($ClassExamData , $is_return_id);
    }

	//批量 保存考试信息
	public function addBatClassExam($datas) {
		if(empty($datas)) {
			return false;
		}

		return $this->_dClassExam->addBat($datas);
    }

	// 修改考试信息
	public function modifyClassExam($data,$exam_id) {
		if(empty($data) || empty($exam_id)) {
			return false;	
		}
		
		return $this->_dClassExam->modifyClassExam($data,$exam_id);
    }
    
    /**
     * 通过班级class_code 获取考试信息
     * @param $subject_ids
     */
    public function getClassExamByClassCodeTO($class_code) {
    	if(empty($class_code)) {
    		return false;
    	}
    	
        return $this->_dClassExam->getClassExamByClassCode($class_code);
    }
}




?>