<?php
class mDepartment extends mBase {
	
	protected $_dDepartment = null;
	
	public function __construct() {
		$this->_dDepartment = ClsFactory::Create('Data.dDepartment');
	}
    //通过部门id得到部门信息
    function getDepartmentById($dpt_ids) {
        if(empty($dpt_ids)) {
            return false;
        }
        
        $dpt_list = $this->_dDepartment->getDepartmentById($dpt_ids);
        
        if(!empty($dpt_list)) {
            import("@.Common_wmw.Pathmanagement_oa");
            foreach($dpt_list as $dpt_id=>$dpt) {
                if(empty($dpt['dpt_photo'])) {
                    continue;
                }
                $dpt['dpt_photo_url'] = Pathmanagement_oa::getDepartmentImg() . $dpt['dpt_photo'];
                //小图的url地址
                list($name, $suffix) = explode(".", $dpt['dpt_photo']);
                $small_filename = $name . "_small." . $suffix;
                $dpt['dpt_photo_small'] = $small_filename;
                $dpt['dpt_photo_small_url'] = Pathmanagement_oa::getDepartmentImg() . $small_filename;
                
                $dpt_list[$dpt_id] = $dpt;
            }
        }
        
        return !empty($dpt_list) ? $dpt_list : false;
    }
    
    public function getDepartmentBySchoolId($school_ids) {
        if(empty($school_ids)) {
            return false;
        }
        
        return $this->_dDepartment->getDepartmentBySchoolId($school_ids);
    }

    //添加部门信息
    function addDepartment($dataarr, $return_insert_id) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dDepartment->addDepartment($dataarr, $return_insert_id);
    }

    //修改部门信息
    function modifyDepartment($dataarr, $dpt_id) {
        if(empty($dpt_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dDepartment->modifyDepartment($dataarr, $dpt_id);
    }

    //删除部门
    function delDepartmentByDptId($dpt_id) {
        if(empty($dpt_id)) {
            return false;
        }
        
        return $this->_dDepartment->delDepartmentByDptId($dpt_id);
    }
    //根据up_id获取部门信息
    public function getDepartmentByUpid($upid) {
    	if(empty($upid)) {
    	 	return false;
    	}
    	
    	return $this->_dDepartment->getDepartmentByUpid($upid);
    }
}