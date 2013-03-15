<?php
class dPrivateMsgRelation extends dBase{
    protected $_tablename = 'wmw_private_msg_relation';
    protected $_pk = 'id';
    protected $_fields = array(
        'id',
        'send_uid',
        'to_uid',
        'new_msg_id',
        'msg_count',
    );
    
    protected $_index_list = array(
        'id',
        'send_uid',
        'to_uid',
    );
    
    public function getPrivateMsgRelationById($id){
        return $this->getInfoByPk($id);
    }
    
    public function getPrivateMsgRelationBySendUid($send_uid, $orderby, $offset, $limit){
        return $this->getInfoByFk($send_uid, 'send_uid', $orderby, $offset, $limit);
    }
    
    public function getPrivateMsgRelationByToUid($to_uid, $orderby, $offset, $limit){
        return $this->getInfoByFk($to_uid, 'to_uid', $orderby, $offset, $limit);
    }
    
    public function modifyPrivateMsgRelation($dataarr, $id){
        return $this->modify($dataarr, $id);
    }
    
    public function delPrivateMsgRelation($id){
        return $this->delete($id);
    }
    
    public function addPrivateMsgRelation($dataarr, $is_return_id){
        return $this->add($dataarr, $is_return_id);
    }
}