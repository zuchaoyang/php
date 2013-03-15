<?php
class dSmsSend extends dBase{
    protected $_tablename = 'sms_send';
    protected $_fields = array(
        'sms_send_id',
        'sms_send_mphone',
        'sms_send_content',
        'sms_send_mphone_num',
        'sms_send_type',
        'sms_send_result_info',
        'sms_send_transact_datetime',
        'db_createtime',   
        'db_updatetime',
        'db_delete',
        'sms_send_bussiness_type'
    );
    protected $_pk = 'sms_send_id';
    protected $_index_list = array(
        'sms_send_id'
    );
    
    //连接数据库sgip_sms
    public function _initialize() {
        $this->connectDb('sgip_sms');
    }
    
    //添加一条待发送的信息到库中
    public function addSmsSend($dataarr, $is_return_id=false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    //修改短信状态
	function modifySmsSend($dataarr, $sms_send_id) {
		return $this->modify($dataarr, $sms_send_id);
	}
    
}
