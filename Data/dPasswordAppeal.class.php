<?php
class dPasswordAppeal extends dBase{
    protected $_tablename = 'wmw_password_appeal';
    protected $_pk = 'appeal_id';
    protected $_fields = array(
        'appeal_id',
        'client_name',
        'client_account',
        'client_phone',
        'client_email',
        'area_id',
        'school_name',
        'class_name',
        'question_description',
        'add_time',
    );
    protected $_index_list = array(
        'appeal_id'
    );
    
    public function getPasswordAppealById($appeal_ids) {
        return $this->getInfoByPk($appeal_ids);
    }
    
    public function addPasswordAppeal($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delPasswordAppeal($appeal_id) {
        return $this->delete($appeal_id);
    }
    
}