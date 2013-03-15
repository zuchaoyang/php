<?php
class mPrivateMsgRelation extends mBase{
    protected $_dPrivateMsgRelation = null;
    
    public function __construct(){
        $this->_dPrivateMsgRelation = ClsFactory::Create("Data.PrivateMsg.dPrivateMsgRelation");
    }
    
    public function getPrivateMsgRelationById($id){
        if(empty($id)){
            return false;
        }
        
        return $this->_dPrivateMsgRelation->getPrivateMsgRelationById($id);
    }
    
    public function getPrivateMsgRelationBySendUid($send_uid, $orderby=null, $offset=null, $limit=null){
        if(empty($send_uid)){
            return false;
        }
        
        return $this->_dPrivateMsgRelation->getPrivateMsgRelationBySendUid($send_uid, $orderby, $offset, $limit);
    }
    
    public function getPrivateMsgRelationByToUid($to_uid, $orderby=null, $offset=null, $limit=null){
        if(empty($to_uid)) {
            return false;
        }
        
        return $this->_dPrivateMsgRelation->getPrivateMsgRelationByToUid($to_uid, $orderby, $offset, $limit);
    }
    
    public function modifyPrivateMsgRelation($dataarr, $id){
        if(empty($id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsgRelation->modifyPrivateMsgRelation($dataarr, $id);
    }
    
    public function delPrivateMsgRelation($id){
        if(empty($id)) {
            return false;
        }
        
        return $this->_dPrivateMsgRelation->delPrivateMsgRelation($id);
    }
    
    public function addPrivateMsgRelation($dataarr, $is_return_id = false){
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsgRelation->addPrivateMsgRelation($dataarr, $is_return_id);
    }
    
    public function addPrivateMsgRelationBat($dataarr){
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPrivateMsgRelation->addBat($dataarr);
    }
    
    public function getPrivateMsgRelationBySendUidAdnToUid($send_uid, $to_uid){
        if(empty($send_uid) || empty($to_uid)) {
            return false;
        }
        
        $wherelist = array(
            'send_uid = ' . $send_uid,
            'to_uid = ' . $to_uid,
        );
        
        return $this->_dPrivateMsgRelation->getInfo($wherelist);
    }
}