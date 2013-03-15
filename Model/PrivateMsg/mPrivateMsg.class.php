<?php
class mPrivateMsg extends mBase{
    private $_dPrivateMsg = null;
    
    public function __construct() {
        $this->_dPrivateMsg = ClsFactory::Create("Data.PrivateMsg.dPrivateMsg");
    }
    
    public function getPivateMsgByToUidAdnSendUid($uids, $orderby=null, $offset=null, $limit=null){
        if(empty($uids) || !is_array($uids) || count($uids) != 2){
            return false;
        }
        
        $wheresql = array(
            "to_uid in('" . implode("','", $uids) . "')",
        	"send_uid in('" . implode("','", $uids) . "')",
        );
        
        import('@.Common_wmw.WmwFace');
        $private_msg_list = $this->_dPrivateMsg->getInfo($wheresql, $orderby, $offset, $limit);
        
        if(!empty($private_msg_list)){
            foreach($private_msg_list as $private_msg_id => $private_msg) {
                $private_msg['content'] = WmwFace::parseFace($private_msg['content']);
                $private_msg_list[$private_msg_id] = $private_msg;
            }
        }
        
        return !empty($private_msg_list) ? $private_msg_list : false;
    }
    
    public function getPivateMsgOneByToUidAdnSendUid($to_uid, $send_uid, $orderby=null, $offset=null, $limit=null){
        if(empty($to_uid) || empty($send_uid)){
            return false;
        }
        
        $wheresql = array(
            "to_uid in = $to_uid",
        	"send_uid = $send_uid",
        );
        
        import('@.Common_wmw.WmwFace');
        $private_msg_list = $this->_dPrivateMsg->getInfo($wheresql, $orderby, $offset, $limit);
        
        if(!empty($private_msg_list)){
            foreach($private_msg_list as $private_msg_id => $private_msg) {
                $private_msg['content'] = WmwFace::parseFace($private_msg['content']);
                $private_msg_list[$private_msg_id] = $private_msg;
            }
        }
        
        return !empty($private_msg_list) ? $private_msg_list : false;
    }
    
    public function getPrivateMsgById($msg_ids, $orderby=null, $offset=null, $limit=null) {
        if(empty($msg_ids)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $private_msg_list = $this->_dPrivateMsg->getPrivateMsgById($msg_ids, $orderby, $offset, $limit);
        
        
        if(!empty($private_msg_list)){
            foreach($private_msg_list as $private_msg_id => $private_msg) {
                $private_msg['content'] = WmwFace::parseFace($private_msg['content']);
                $private_msg_list[$private_msg_id] = $private_msg;
            }
        }
        
        return !empty($private_msg_list) ? $private_msg_list : false;
    }
    
    public function getPrivateMsgBySendUid($send_uid, $orderby=null, $offset=null, $limit=null){
        if(empty($send_uid)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $private_msg_list = $this->_dPrivateMsg->getPrivateMsgBySendUid($send_uid, $orderby, $offset, $limit);
        
        
        if(!empty($private_msg_list)){
            foreach($private_msg_list as $send_uid => $private_msgs) {
                foreach($private_msgs as $private_msg_id => $private_msg ){
                    $private_msg['content'] = WmwFace::parseFace($private_msg['content']);
                    $private_msgs[$private_msg_id] = $private_msg;
                }
                
                $private_msg_list[$send_uid] = $private_msgs;
            }
        }
        
        return !empty($private_msg_list) ? $private_msg_list : false;
    }
    
    public function getPrivateMsgByToUid($to_uid, $orderby=null, $offset=null, $limit=null){
        if(empty($to_uid)) {
            return false;
        }
        
        import('@.Common_wmw.WmwFace');
        $private_msg_list = $this->_dPrivateMsg->getPrivateMsgByToUid($to_uid, $orderby, $offset, $limit);
        
        if(!empty($private_msg_list)){
            foreach($private_msg_list as $to_uid => $private_msgs) {
                foreach($private_msgs as $private_msg_id => $private_msg ){
                    $private_msg['content'] = WmwFace::parseFace($private_msg['content']);
                    $private_msgs[$private_msg_id] = $private_msg;
                }
                
                $private_msg_list[$to_uid] = $private_msgs;
            }
        }
        
        return !empty($private_msg_list) ? $private_msg_list : false;
    }
    
    public function delPrivateMsg($msg_id){
        if(empty($msg_id)) {
            return false;
        }
        
        return $this->_dPrivateMsg->delPrivateMsg($msg_id);
    }
    
    public function addPrivateMsg($dataarr, $is_return_id = false){
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsg->addPrivateMsg($dataarr, $is_return_id);
    }
}