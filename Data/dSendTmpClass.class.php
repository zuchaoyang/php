<?php
class dSendTmpClass extends dBase {
    protected $_tablename = 'sms_send_tmp_class';
    protected $_fields = array(
        'id',
        'class_code',
        'send_tmp_id',
        'operation_strategy',
        'add_date'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
            'id',
            'send_tmp_id',
    );
    
    public function delSendClasstmpById($id) {
        return $this->delete($id);
    }
}