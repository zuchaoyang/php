<?php
class dPrivateMsgSession extends dBase {
    protected $_tablename = 'wmw_private_msg_session';
    protected $_pk = 'id';
    protected $_fields = array(
        'id',
        'msg_id',
        'send_uid',
        'to_uid',
    );
    protected $_index_list = array(
        'id',
        'send_uid',
        'to_uid',
    );
    
    public function getPrivateMsgSessionById($id){
        return $this->getInfoByPk($id);
    }
    
    public function addPrivateMsgSession($dataarr, $is_return_id){
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delPrivateMsgSession($id) {
        return $this->delete($id);
    }
}