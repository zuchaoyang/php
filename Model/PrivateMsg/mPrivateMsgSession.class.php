<?php
class mPrivateMsgSession extends mBase {
    protected $_dPrivateMsgSession = null;
    
    public function __construct() {
        $this->_dPrivateMsgSession = ClsFactory::Create("Data.PrivateMsg.dPrivateMsgSession");
    }
    
    public function getPrivateMsgSessionById($id){
        if(empty($id)) {
            return false;
        }
        
        return $this->_dPrivateMsgSession->getPrivateMsgSessionById($id);
    }
    
    public function getPrivateMsgSessionBySendUidAndToUid($send_uid, $to_uid, $msg_id=null, $orderby=null, $offset=null, $limit=null) {
        if(empty($send_uid) || empty($to_uid)) {
            return false;
        }
        
        $wheresql = array(
            'send_uid = ' . $send_uid,
            'to_uid = ' . $to_uid,
        );
        
        !empty($msg_id) ? $wheresql[] = 'msg_id = ' . $msg_id : null;
        
        return $this->_dPrivateMsgSession->getInfo($wheresql, $orderby, $offset, $limit);
    }
    
    public function addPrivateMsgSession($dataarr, $is_return_id = false){
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsgSession->addPrivateMsgSession($dataarr, $is_return_id);
    }
    
    public function addPrivateMsgSessionBat($dataarr){
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsgSession->addBat($dataarr);
    }
    
    public function delPrivateMsgSession($id) {
        if(empty($id)) {
            return false;
        }
        return $this->_dPrivateMsgSession->delPrivateMsgSession($id);
    }
}