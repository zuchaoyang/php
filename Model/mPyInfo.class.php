<?php
class mPyInfo extends mBase {
	
    protected $_dPyInfo = null;
    
    public function __construct() {
        $this->_dPyInfo = ClsFactory::Create('Data.dPyInfo');
    }
    
    
    //按评语ID获取内容
	public function getPyInfoById($py_ids) {
	    if(empty($py_ids)) {
	        return false;
	    }
	    
		return  $this->_dPyInfo->getPyInfoById($py_ids);
	}
    
	
	/*按评语类型读取评语
	public function getpyCollectBypytype($pytype) {
		return  $this->_dPyInfo->getpyCollectBypytype($pytype);
	}
	*/

	/*************************************************************************
	 * 特殊业务涉及到的函数
	 ************************************************************************/
	//按评语属性读取评语
	public function getpyCollectBypyatt($py_atts) {
	    if(empty($py_atts)) {
	        return false;
	    }
	    
	    $wheresql = "py_att in('" . implode("','", (array)$py_atts) . "')";
	    return $this->_dPyInfo->getInfo($wheresql);
		//return  $this->_dPyInfo->getpyCollectBypyatt($pyatt);
	}
	
	//评语关键词模糊搜索
	public function getPycontentLikekey($searchdata) {
	    if(!empty($searchdata)) {
	        return false;
	    }
	    
	    $searchdata = str_replace(array('_', '%'), "", $searchdata);
	    $wheresql = "py_content like '%$searchdata%'";
	    
	    return $this->_dPyInfo->getInfo($wheresql);
		//return  $this->_dPyInfo->getPycontentLikekey($searchdata);
	}
	
	//按评语类型和属性读取评语
	public function getpyCollectBypytypeatt($pytype, $pyatt) {
	    if(empty($pyatt) && !empty($pytype)) {
	        return false;
	    }
	    
	    $wherearr = array();
	    $wherearr[] = "py_type in('" . implode("','", (array)$pytype) . "')";
	    $wherearr[] = "py_att in('" . implode("','", (array)$pyatt) . "')";
	    
	    return $this->_dPyInfo->getInfo($wherearr);
	}
	
	/**
	 * 通过评语类型和评语属性获取评语信息
	 * @param $py_types
	 * @param $py_atts
	 */
	public function getPyInfoByPyTypeAndPyAtt($py_types, $py_atts, $offset=0, $limit=10) {
	    $wherearr = array();
	    if(!empty($py_types)) {
	        $wherearr[] = "py_type in('" . implode("','", (array)$py_types) . "')";
	    }
	    
	    if(!empty($py_atts)) {
	        $wherearr[] = "py_att in('" . implode("','", (array)$py_atts) . "')";
	    }
	    
	    return $this->_dPyInfo->getInfo($wherearr, 'py_id desc', $offset, $limit);
	}
	
}
