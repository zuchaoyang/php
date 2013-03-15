<?php
class mClassHomeworkSend extends mBase{
    protected $_dClassHomeworkSend = null;
	
	public function __construct() {
		$this->_dClassHomeworkSend = ClsFactory::Create('Data.ClassHomework.dClassHomeworkSend');
	}
    
	//添加作业发布对象批量
	public function addHomeworkSend($dataarr) {
	    if(empty($dataarr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomeworkSend->addBat($dataarr);
	    return !empty($resault) ? $resault : false;
	}
	
	
	//修改作业对象
	public function modifyHomeworkSend($datas, $id) {
	    if(empty($datas) || empty($id)) {
	        return false;
	    }
	    
	    return $this->_dClassHomeworkSend->modifyHomeworkSend($datas,$id);
	}
	
	
	//删除班级对象
	public function delHomeworkSend($id) {
	    if(empty($id)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomeworkSend->delHomeworkSend($id);
	    
	    return !empty($resault) ? $resault : false;
	}
	
	//根据作业id查询发送对象信息
	public function getHomeworkSendByhomeworkid($homeworkid) {
	    if(empty($homeworkid)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomeworkSend->getHomeworkSendByhomeworkid($homeworkid);
	    return !empty($resault) ? $resault : false;
	}
	
	/**
	 * 获取某个人的作业信息
	 * @param $accounts
	 * @param $where_appends
	 */
	public function getHomeworkSendByAccount($accounts, $where_appends = array(), $offset = 0, $limit = 10) {
	    if(empty($accounts)) {
	        return false;
	    }
	    
	    $wherearr = array();
	    $wherearr['client_account'] = "client_account in('" . implode("','", (array)$accounts) . "')";
	    if(!empty($where_appends)) {
	        $wherearr = array_merge($wherearr, (array)$where_appends);
	    }
	    
	    return $this->_dClassHomeworkSend->getInfo($wherearr, null, $offset, $limit);
	}
	
	//根据作业id和接收人查询发送对象信息
	public function getHomeworkSendByhomeworkidAndAccount($wherearr) {
	    if(empty($wherearr)) {
	        return false;
	    }
	    
	    $resault = $this->_dClassHomeworkSend->getInfo($wherearr);
	    
	    return !empty($resault) ? $resault : false;
	}
	
}