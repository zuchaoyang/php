<?php
class mClassHomework extends mBase{
    protected $_dClassHomework = null;
	
	public function __construct() {
		$this->_dClassHomework = ClsFactory::Create('Data.ClassHomework.dClassHomework');
	}
	
	/**
	 * 通过主键获取班级作业信息
	 * @param $homework_ids
	 */
	public function getClassHomeworkById($homework_ids) {
	    if(empty($homework_ids)) {
	        return false;
	    }
	    
	    return $this->_dClassHomework->getClassHomeworkById($homework_ids);
	}
    
	//发布班级作业
	public function addHomework($dataarr,$is_return_id) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomework->addHomework($dataarr,$is_return_id);
	    return !empty($resault) ? $resault : false;
	}
	
	//修改班级作业
	public function modifyHomework($datas, $id) {
	    if(empty($datas) || empty($id)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomework->modifyHomework($datas,$id);
	    return !empty($resault) ? $resault : false;
	}
	
	//根据主键获取班级作业信息
	public function getHomeworkByIds($ids) {
	    if(empty($ids)) {
	        return false;
	    }
	    
	    $homeworkInfo = $this->_dClassHomework->getClassHomeworkById($ids);
	    
	    return !empty($homeworkInfo) ? $homeworkInfo : false;
	}
	
	//删除班级作业
	public function delHomework($id) {
	    if(empty($id)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomework->delHomework($id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	
    //获取班级作业信息（有条件和无条件）
    public function getClassHomework($wherearr, $orderby, $offset, $limit) {
        
        $offset = max($offset, 0);
        $limit = $limit > 0 ? $limit : 5;
        
        return $this->_dClassHomework->getInfo($wherearr, $orderby, $offset, $limit);
    }
}