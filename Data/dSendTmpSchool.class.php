<?php
class dSendTmpSchool extends dBase {
    protected $_tablename = 'sms_send_tmp_school';
    protected $_fields = array(
        'id',
        'school_id',
        'send_tmp_id',
        'operation_strategy',
        'add_date'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
            'id',
    );
    
    public function delSendSchoolIdsById($id) {
        return $this->delete($id);
    }
}