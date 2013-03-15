<?php
class dPretreatSms extends dBase {
    protected $_tablename = 'oa_pretreat_sms';
    protected $_fields = array(
		'pretreat_id', 
		'accept_phone', //工作添加人的手机号码
        'sms_message', //推送信息
        'push_time', //push_time
        'business_type', //学校对应的业务类型
        'add_time' 
    );
    protected $_pk = 'pretreat_id';
    protected $_index_list = array(
        'pretreat_id'
    );
    
    public function _initialize() {
        $this->connectDb ('oa', true);
    }
    
    /*添加预发短信信息
     * @param $dataarr
     * return $effect_rows
     */
    function addPretreatSms($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    /*删除与发短信信息
     * @param $pretreat_id
     * return $effect_rows
     */
    public function delPretreatSms($pretreat_id) {
        return $this->delete($pretreat_id);
    }

}

