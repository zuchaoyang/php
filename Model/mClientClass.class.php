<?php
//todolist 代码涉及的业务可能有问题
class mClientClass extends mBase {

	protected $_dClientClass = null;
	
	public function __construct() {
		$this->_dClientClass = ClsFactory::Create('Data.dClientClass');
	}
	
	public function getClientClassByUid($uids) {
	    if (empty ( $uids )) {
	        return false;
	    }
	    
	    $client_class_arr = $this->_dClientClass->getClientClassByUid($uids );
	    
	    return !empty($client_class_arr) ? $client_class_arr : false;
	}
	
	public function addClientClass($clientClassInfo) {
	    if (empty($clientClassInfo)) {
	        return false;
	    }
	    
	    return $this->_dClientClass->addClientClass ( $clientClassInfo );
	}
	
	public function addClientClassBat($dataarr) {
	    if (empty($dataarr ) || !is_array($dataarr)) {
	        return false;
	    }
	    
	    return $this->_dClientClass->addBat($dataarr);
	}
	/**
	 * 通过班级id获取班级成员信息
	 * @param  $classCodes
	 * @param  $filters
	 */
	//todolist 代码涉及的业务可能有问题
	public function getClientClassByClassCode($classCodes, $filters = array()) {
	    if (empty ($classCodes)) {
	        return false;
	    }
	    
	    $tmp_clientclasslist = $this->_dClientClass->getClientClassByClassCode($classCodes);
	    $clientclasslist = array();
	    if ( !empty($tmp_clientclasslist) ) {
	    	foreach ( $tmp_clientclasslist as $key=>$list ) {
	    		foreach ($list as $key1=>$val) {
	    			$clientclasslist[$val['class_code']][$val['client_account']] =$val; 
	    		}
	    	}	
	    }
	    unset($tmp_clientclasslist);
	    if (! empty ( $clientclasslist ) && ! empty ( $filters )) {
	    	
	        foreach ( $filters as $field => $values ) {
	        	
	            $values = is_array ( $values ) ? $values : array ($values );
	            foreach ( $clientclasslist as $class_code => & $clientClassList ) {
	            	
	                foreach ( $clientClassList as $clientclassid => $clientclassInfo ) {
	                	
	                    if (isset ( $clientclassInfo [$field] ) && !in_array ( $clientclassInfo [$field], $values )) {
	                        unset ( $clientClassList [$clientclassid] );
	                    }
	                }
	                
	                $clientclasslist [$class_code] = $clientClassList;
	            }
	        }
	    }
	    
	    //数据转换
	    if (! empty ( $clientclasslist )) {
	    	
	        foreach ( $clientclasslist as $class_code => & $cclist ) {
	        	
	            foreach ( $cclist as $clientclassid => & $clientclass ) {
	            	
	                if (intval ( $clientclass ['client_class_role'] ) > 0) {
	                    $cclist [$clientclassid] = $this->parseClientClass ( $clientclass );
	                }
	            }
	            
	            $clientclasslist [$class_code] = $cclist;
	        }
	    }
	    return ! empty ( $clientclasslist ) ? $clientclasslist : false;
	}
	
	public function getClientClassId($class_code, $uid){
	    if(empty($class_code) || empty($uid)) {
	        return false;
	    }
	    
	    $wheresql = array(
	        'class_code='.$class_code,
	    	'client_account='.$uid
	    );
	    
	    
	    return key($this->_dClientClass->getInfo($wheresql));
	}
	
	public function modifyClientClass($clientInfo, $id) {
	    if (empty ( $clientInfo ) || empty ( $id )) {
	    	return false;
	    }
	       
	    return $this->_dClientClass->modifyClientClass ( $clientInfo, $id );
	}
	
	
	//查询该学校实际注册的老师，家长，学生人数  //author: Luan
	//todolist 特殊业务
    public function  getSchoolUserTypeTotal($classCodes,$clientType) {
        if (empty($classCodes) || empty($clientType)) {
            return false;
        }
        
        $clientType = array_unique((array)$clientType);
        $whereSql = " class_code in(" . implode(',' ,(array)$classCodes) . ")  and client_type in(" . implode(',' , (array)$clientType) . ")";
        
        return $this->_dClientClass->getInfo($whereSql);
    }
	
	public function delClientClass($id) {
	    if (empty ( $id )) {
	        return false;
	    }
	    
	    return $this->_dClientClass->delClientClass ( $id );
	}
	
	/**
	 * 转换数据库表中的数据
	 * @param $clientclass
	 */
	private function parseClientClass($clientclass) {
	    if (empty ( $clientclass )) {
	        return false;
	    }
	    
	    import ( '@.Common_wmw.Constancearr' );
	    
	    if (isset ( $clientclass ['client_class_role'] )) {
	        $client_class_role = intval ( $clientclass ['client_class_role'] );
	        $clientclass ['client_class_role_name'] = Constancearr::classleader ( $client_class_role );
	    }
	    
	    return ! empty ( $clientclass ) ? $clientclass : false;
	}
	
	/*
	 * 根据班级编号和类型查询学生帐号
	 */
	public function getStudentInfoByClassCodeAndType($class_code,$client_type,$offset=null,$lenght=null) {
	    if(empty($class_code) || $client_type != 0) {
	        return false;
	    }
	    $dataarr = array(
	        'class_code = ' .$class_code,
	        'client_type = ' .$client_type,
	    );
	    
	    $resault = $this->_dClientClass->getInfo($dataarr,'client_class_id',$offset,$lenght);
	    return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 根据需求添加 按条件查询方法 并对查询结果按要求进行排序
	 * @param $where array 条件数组
	 * @param $orderby 排序字段
	 * @param $offset 分页 默认查询所有
	 * @param $limit 每页条数
	 * @return $client_class_list 用户id作为键 方便数据调用
	 */
	public function getClientClassInfo($where, $orderby, $offset=null, $limit=null) {
            
        $resault = $this->_dClientClass->getInfo($where, $orderby, $offset, $limit);
        
        return !empty($resault) ? $resault : false;
	}
	
}

