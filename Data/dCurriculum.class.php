<?php

class dCurriculum extends dBase {
    //protected $_tablename = 'wmw_curriculum_info';
    protected  $_tablename = 'old_wmw_curriculum_info';  //todo sns 改版暂时改变表名 
    protected $_fields = array(
		'curriculum_id',
		'class_code', 
		'am_content', 
		'pm_content',
		'upd_account',
		'upd_time',
		'subject_content',
    );
    protected $_pk = 'curriculum_id';
    protected $_index_list = array(
        'curriculum_id',
        'class_code',
    );
    
	/**
     * 按课程表Id读取课程表是否存在
     * @param $curriculum_ids
     */
	//todolist 数据维度的问题
	public function getCurriculumInfoById($curriculum_ids) {
	    return $this->getInfoByPk($curriculum_ids);
	}

     /**
     * 按班级编号读取课程表是否存在
     * @param $class_code
     */
	public function getCurriculumInfoByClassCode($class_code) {
        return $this->getInfoByFk($class_code , 'class_code');
	}
	
	/**
	 * 添加班级课程表信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addCurriculumInfo($datas, $is_return_id = false) {
	    return $this->add($datas, $is_return_id);
	}

	/**
	 * 通过主键修改课程表信息
	 * @param $datas
	 * @param $curriculum_id
	 */
	public function modifyCurriculumInfo($datas, $curriculum_id) {
	    return $this->modify($datas, $curriculum_id);
	}
}
