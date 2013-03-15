<?php
class mClassInfo extends mBase{

	protected $_dClassInfo = null;
	
	public function __construct() {
		$this->_dClassInfo = ClsFactory::Create('Data.dClassInfo');
	}
    /**
     * 通过班级id获取班级的基本信息
     * @param  $class_codes
     * @param  $filters
     */
	public function getClassInfoBaseById($class_codes , $filters = array()) {
	    if (empty($class_codes)) {
	        return false;
	    }
	    
		$ClassInfoList = $this->_dClassInfo->getClassInfoById($class_codes);
		
		if (!empty($ClassInfoList) && !empty($filters)) {
			
            foreach ($filters as $field=>$values) {
                $values = is_array($values) ? $values : array($values);
                
                foreach ($ClassInfoList as $classcode=>$classinfo) {
                	
                    if (isset($classinfo[$field]) && !in_array($classinfo[$field] , $values)) {
                        unset($ClassInfoList[$classcode]);
                    }
                }
            }
         }
        
	    if(!empty($ClassInfoList)) {
    	    //转换班级中的部分字段的含义
    	    foreach($ClassInfoList as $classcode=>$classinfo) {
    	        $ClassInfoList[$classcode] = $this->parseClassInfo($classinfo);
    	    }
        }
         
         return !empty($ClassInfoList) ? $ClassInfoList : false;
	}

    /**
     * 通过班级id获取班级的基本信息和扩展信息，基本上只整理用户相关的id信息
     * 数据太多会导致内存问题，只支持单个班级的基本信息的获取
     * =>函数只能处理班级对应的学校的基本信息，用户的数据是不能在这里处理的
     * @param  $class_codes
     * @param  $filters
     */
	public function getClassInfoById($class_codes , $filters = array()) {
	    if (empty($class_codes)) {
	        return false;
	    }
	    
	    $classinfolist = $this->getClassInfoBaseById($class_codes , $filters);
	    //不存在相关的班级数据
	    if (empty($classinfolist)) {
	        return false;
	    }
	    //建立班级到学校和班主任的映射关系
	    $schoolidlist = $schoolids = $headteacherlist = array();
	    
        foreach ($classinfolist as $classcode=>$classinfo) {
            $schoolidlist[$classcode] = $classinfo['school_id'];
            $schoolids[] = $classinfo['school_id'];
        }
        //获取班级的学校信息
        if (!empty($schoolids)) {
            $schoolids = array_unique($schoolids);
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
            $schoolinfolist = $mSchoolInfo->getSchoolInfoById($schoolids);
            
            if(!empty($schoolinfolist)) {
            	
                foreach($schoolidlist as $classcode=>$schoolid) {
                	
                    if(!empty($schoolinfolist[$schoolid])) {
                        $classinfolist[$classcode]['school_info'] = $schoolinfolist[$schoolid];
                    }
                }
            }
        }
        
        if(!empty($classinfolist)) {
    	    //转换班级中的部分字段的含义
    	    foreach($classinfolist as $classcode=>$classinfo) {
    	        $classinfolist[$classcode] = $this->parseClassInfo($classinfo);
    	    }
        }
        
	    return !empty($classinfolist) ? $classinfolist : false;
	}
	
	/**
	 * 转换班级信息中的字段含义
	 * author:anlicheng$
	 */
	public function parseClassInfo($classinfo) {
	    
	    if (empty($classinfo)) {
	        return false;
	    }
	    
	    import("@.Common_wmw.Constancearr");
	    
	    if (isset($classinfo['grade_id'])) {
	        $grade_id = intval($classinfo['grade_id']);
	        $classinfo['grade_id_name'] = Constancearr::class_grade_id($grade_id);
	    }
	    
	    return !empty($classinfo) ? $classinfo : false;
	}
	
	public function getClassInfoBySchoolId($schoolIds , $filters = array()) {
		if (empty($schoolIds)) {
			return false;
		}
		
	    $dClassInfoList = $this->_dClassInfo->getClassInfoBySchoolId($schoolIds);
	    
	    if (!empty($dClassInfoList) && !empty($filters)) {
	    	
            foreach ($filters as $field=>$values) {
                $values = is_array($values) ? $values : array($values);
                
                foreach ($dClassInfoList as $schoolId=>$cclist) {
                	
                    foreach ($cclist as $classcode=>$ccinfo) {
                    	
                        if (isset($ccinfo[$field]) && !in_array($ccinfo[$field] , $values)) {
                            unset($cclist[$classcode]);
                        }
                    }
                    $dClassInfoList[$schoolId] = $cclist;
                }
            }
         }
         
         return !empty($dClassInfoList) ? $dClassInfoList : false;
	}
	
	public function getClassInfoByUid($uids,$filters=array()) {
	    if (empty($uids)) {
	    	return false;
	    }
	    
	    $dClassInfolist = $this->_dClassInfo->getClassInfoByUid($uids);
	    
	    if (!empty($dClassInfolist) && !empty($filters)) {
	    	
            foreach ($filters as $field=>$values) {
                $values = is_array($values) ? $values : array($values);
                
                foreach ($dClassInfolist as $uid=>$cclist) {
                	
                    foreach ($cclist as $classcode=>$ccinfo) {
                    	
                        if (isset($ccinfo[$field]) && !in_array($ccinfo[$field] , $values)) {
                            unset($cclist[$classcode]);
                        }
                    }
                    $dClassInfolist[$uid] = $cclist;
                }
            }
         }
         
         return !empty($dClassInfolist) ? $dClassInfolist : false;
	}
	
	public function modifyClassInfo($datas , $class_codes) {
		if (empty($datas) || empty($class_codes)) {
			return false;
		}
		
		return $this->_dClassInfo->modifyClassInfo($datas , $class_codes);
	}
	
	public function addClassInfo($datas, $is_return_id = false) {
		if (empty($datas)) {
			return false;
		}
		return $this->_dClassInfo->addClassInfo($datas,$is_return_id);
	}
	
	public function delClassInfo($class_codes){
		if (empty($class_codes)) {
			return false;
		}
		
		$this->_dClassInfo->delClassInfo($class_codes);
	}
	
//统计学校对应的班级的数目//author:Luan
    //todolist 特殊应用
    public function getSchoolClassTotal($school_id) {
    	if (empty($school_id)) {
    		return false;
    	}
    	
    	$school_id = is_array($school_id) ? array_shift($school_id) : $school_id;
        $whereSql = "school_id='$school_id'";
        
        $class_nums = $this->getCount($whereSql);
        
        return !empty($class_nums) ? $class_nums : 0;
    }
    
}
