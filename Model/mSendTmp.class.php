<?php
class mSendTmp extends mBase {
    protected $_dSendTmp = null;
    
    public function __construct() {
        $this->_dSendTmp = ClsFactory::Create('Data.dSendTmp');
    }
    
	/**
     * 通过id获取相关信息
     * @param $ids
     */
    public function getSendTmpInfoById($ids) {
        
        return $this->_dSendTmp->getSendTmpInfoById($ids);
    }
    
    //修改信息
    public function modifySendTmpInfoById($dataarr, $id) {
        
        return $this->_dSendTmp->modifySendTmpInfoById($dataarr, $id);
    }
    
    //添加信息delSendTmpInfoById
    public function addSendTmpInfo($dataarr, $is_return_id = false) {
        
        return $this->_dSendTmp->addSendTmpInfo($dataarr, $is_return_id);
    }
    //添加信息
    public function delSendTmpInfoById($ids) {
        return $this->_dSendTmp->delSendTmpInfoById($ids);
    }
    // 通过发送时间获取相关信息
    public function getSendTmpInfoBySendtime($times, $offset=0, $limit=10) {
        if(!empty($times)) {
            $end_time = $times+86400;
            $wherearr[] = "send_time<$end_time";
            $wherearr[] = "send_time>=$times";
        }else{
            $wherearr = '';
        }
        
        $orderby = "send_time desc";
        
        return $this->_dSendTmp->getInfo($wherearr, $orderby, $offset, $limit);
    }
    // 通过发送时间获取相关信息
    public function getSendTmpInfoBySendSendTimeAndStatus($send_times, $status, $orderby='') {
        
        if(empty($send_times)) {
            $send_times = time();
        }
        $wherearr[] = "send_time<=$send_times";
        $wherearr[] = "send_status=$status";
        if(empty($orderby)) {
            $orderby = "id desc";
        }
        
        return $this->_dSendTmp->getInfo($wherearr, $orderby);
    }
}