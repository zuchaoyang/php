<?php
class dSendTmp extends dBase {
    protected $_tablename = 'sms_send_tmp';
    protected $_fields = array(
        'id',
        'content',
        'operation_strategy',
        'send_status',
        'send_range',
        'send_time',
        'real_send_time',
        'add_uid',
        'add_time'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
            'id',
            'send_time'
    );
    
	/**
     * 通过id获取相关信息
     * @param $ids
     */
    public function getSendTmpInfoById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    //修改信息
    public function modifySendTmpInfoById($dataarr, $id) {
        return $this->modify($dataarr, $id);
    }
    
    //添加信息
    public function addSendTmpInfo($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }

    // 通过发送时间获取相关信息
    public function delSendTmpInfoById($id) {
        return $this->delete($id);
    }
}