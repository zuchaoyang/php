<?php
class dActiveLog extends dBase {
    protected $_tablename = 'wmw_client_active_log';
    protected $_pk = 'log_id';
    protected $_index_list = array(
        'client_account',
        'log_id',
    );
    protected $_fields = array(
        'log_id',
        'client_account',
        'value',
        'message',
        'add_time',
        'module',
        'action',
    );
    
    public function getActiveLogById($log_id){
        return $this->getInfoByPk($log_id);
    }
    
    public function addActiveLog($dataarr, $is_return_id){
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delActiveLog($log_id){
        return $this->delete($log_id);
    }
}