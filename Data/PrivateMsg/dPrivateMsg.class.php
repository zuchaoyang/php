<?php
class dPrivateMsg extends dBase{
    protected $_tablename = 'wmw_private_msg';
    protected $_pk = 'msg_id';
    protected $_fields = array(
        'msg_id',
        'send_uid',
        'to_uid',
        'content',
        'add_time',
        'img_url',
        'send_status',
        'to_status',
    );
    protected $_index_list = array(
        'msg_id',
        'send_uid',
        'to_uid',
    );
    
    public function getPrivateMsgById($msg_ids, $orderby, $offset, $limit) {
        return $this->getInfoByPk($msg_ids, $orderby, $offset, $limit);
    }
    
    public function getPrivateMsgBySendUid($send_uid, $orderby, $offset, $limit){
        return $this->getInfoByFk($send_uid, 'send_uid', $orderby, $offset, $limit);
    }
    
    public function getPrivateMsgByToUid($to_uid, $orderby, $offset, $limit){
        return $this->getInfoByFk($to_uid, 'to_uid', $orderby, $offset, $limit);
    }
    
    public function delPrivateMsg($msg_id){
        return $this->delete($msg_id);
    }
    
    public function addPrivateMsg($dataarr, $is_return_id){
        return $this->add($dataarr, $is_return_id);
    }
}