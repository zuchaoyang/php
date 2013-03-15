<?php
class mCurriculum extends mBase {
	
	protected $_dCurriculum = null;
	
	public function __construct() {
		$this->_dCurriculum = ClsFactory::Create('Data.dCurriculum');
	}
	
    /*
     * 按照$where 条件查询数据
     * @param $where 查询条件可以为空 
     */
    public function getCurriculumInfo($where, $orderby, $offset = 0 , $limit = 10) {

        return $this->_dCurriculum->getInfo($where, $orderby, $offset, $limit);
    }
	
	
	
	/**
     * 按课程表Id读取课程表是否存在
     * @param $curriculum_ids
     */
	public function getCurriculumInfoById($curriculum_ids) {
        if(empty($curriculum_ids)) {
           return false; 
        }
        
		return $this->_dCurriculum->getCurriculumInfoById($curriculum_ids);
	}
     /**
     * 按班级编号读取课程表是否存在
     * @param $class_code
     */
	//todolist C层相应的代码需要调整
	public function getCurriculumInfoByClassCode($class_code) {
		if(empty($class_code)) {
			return false;
		}
		
		return $this->_dCurriculum->getCurriculumInfoByClassCode($class_code);
	}
	
	/**
	 * 添加课程信息
	 * @param $datas
	 * @param $is_return_id
	 */
	public function addCurriculumInfo($datas, $is_return_id = false) {
	    if(empty($datas) || !is_array($datas)) {
	        return false;
	    }
	    
	    return $this->_dCurriculum->addCurriculumInfo($datas, $is_return_id);
	}
	
	/**
	 * 修改班级课程表信息
	 * @param  $datas
	 * @param  $curriculum_id
	 */
	public function modifyCurriculumInfo($datas, $curriculum_id) {
	    if(empty($datas) || !is_array($datas) || empty($curriculum_id)) {
	        return false;
	    }
	    
	    return $this->_dCurriculum->modifyCurriculumInfo($datas, $curriculum_id);
	}
	
	/***********************************************************************
	 * 特殊业务处理函数
	 ***********************************************************************/
 	/**
	 * 按班级编号修改课程表信息
	 * @param $class_code
	 */
     public function modifyCurriculumInfoByClassCode($datas, $class_code) {
        if(empty($datas) || !is_array($datas) || empty($class_code)) {
            return false;
        }
        
        $class_code = is_array($class_code) ? array_shift($class_code) : $class_code;
        $curriculum_arr = $this->getCurriculumInfoByClassCode($class_code);
        $curriculum_list = & $curriculum_arr[$class_code];
        
        $effect_rows = 0;
        if(!empty($curriculum_list)) {
            foreach($curriculum_list as $curriculum_id=>$curriculum) {
                $this->modifyCurriculumInfo($datas, $curriculum_id) && $effect_rows++;
            }
        }
        
        return $effect_rows;
    }
}
