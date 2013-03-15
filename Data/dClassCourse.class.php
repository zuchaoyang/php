<?php

class dClassCourse extends dBase {

    protected $_tablename = 'wmw_class_course'; 
    protected $_fields = array(
		'course_id',
		'class_code', 
		'weekday', 
		'num_th',
    	'name',
		'upd_account',
		'upd_time',
		
    );
    protected $_pk = 'course_id';
    protected $_index_list = array(
        'course_id',
        'class_code',
    );

    /*
     * 根据主键course_id 查询课程信息 
     */
    public function getClassCourseById($course_id) {
        return $this->getInfoByPk($course_id);
    }
    
    /**
     * 按班级编号读取课程表
     * @param $class_code
     */
	public function getClassCourseByClassCode($class_code) {
        return $this->getInfoByFk($class_code , 'class_code');
	}
	
	/**
	 * 添加班级课程表信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addClassCourse($datas, $is_return_id = false) {
	    return $this->add($datas, $is_return_id);
	}
	
	/**
	 * 批量添加班级课程表信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addBatClassCourse($datas) {
	    return $this->addBat($datas);
	}

	/**
	 * 通过主键修改课程表信息
	 * @param $datas
	 * @param $course_id
	 */
	public function modifyClassCourse($datas, $course_id) {
	    return $this->modify($datas, $course_id);
	}
	
    public function delClassCourse($course_id) {
	    return $this->delete($course_id);
	}
}
