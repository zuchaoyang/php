<?php
class mClassCourse extends mBase {
	
	protected $_dClassCourse = null;
	
	public function __construct() {
		$this->_dClassCourse = ClsFactory::Create('Data.dClassCourse');
	}
	
    /*
     * 按照$where 条件查询数据
     * @param $where 查询条件可以为空 
     */
    public function getClassCourse($where, $orderby, $offset = 0 , $limit = 10) {
        
        return $this->_dClassCourse->getInfo($where, $orderby, $offset, $limit);
    }
	
	/**
     * 按课程表Id读取课程表是否存在
     * @param $curriculum_ids
     */
	public function getClassCourseById($course_id) {
        if(empty($course_id)) {
           return false; 
        }
        
		return $this->_dClassCourse->getClassCourseById($course_id);
	}
     /**
     * 按班级编号读取课程表是否存在
     * @param $class_code
     */
	public function getClassCourseByClassCode($class_code) {
		if(empty($class_code)) {
			return false;
		}
		
		return $this->_dClassCourse->getClassCourseByClassCode($class_code);
	}
	
	/**
	 * 添加课程信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addClassCourse($datas, $is_return_id = false) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dClassCourse->addClassCourse($datas, $is_return_id);
	}
	
	/**
	 * 批量添加班级课程表信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addBatClassCourse($datas) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dClassCourse->addBatClassCourse($datas);
	}
	
	/**
	 * 修改班级课程表信息
	 * @param  $datas
	 * @param  $curriculum_id
	 */
	public function modifyClassCourse($datas, $course_id) {
	    if(empty($datas) || !is_array($datas) || empty($course_id)) {
	        return false;
	    }
	    
	    return $this->_dClassCourse->modifyClassCourse($datas, $course_id);
	}
	
	/**
	 * 删除课程信息
	 * @param $course_id
	 */
	public function delClassCourse($course_id) {
	    if(empty($course_id)) {
	        return false;
	    }
	    
	    return $this->_dClassCourse->delClassCourse($course_id);
	}
	
}
