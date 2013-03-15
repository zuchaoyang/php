<?php
class Smssendtmp extends ApiController{
    /**
     * $datas  = array(
     *     'sms_content',		 string,
     *     'operation_strategy', array(),
     *     'recipient_type',	 string or array()
     *     'send_time',		             时间戳
     *     'add_uid',            操作人
     *     'id',                 modify id
     * )
     * @param $datas
     */
    
    public function _initialize() {
        parent::_initialize();
    }
    
    public function operateSmsTmp($datas) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        $id = $datas['id'];
        $datas = $this->formatData($datas);
        
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        if($this->isExists($id)) {
            return $mSendTmp->modifySendTmpInfoById($datas, $id);
        }
        return $mSendTmp->addSendTmpInfo($datas, true);
    }
    
    private function isExists($id) {
        if(empty($id)) {
            return false;
        }
        
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $tmp_list = $mSendTmp->getSendTmpInfoById($id);
        
        return !empty($tmp_list) ? true : false;
    }
    
    private function formatData($datas) {
        return array(
            'content'			 => $datas['sms_content'],
            'operation_strategy' => implode(',', (array)$datas['operation_strategy']),
            'send_status'	     => 1,//1,未发送;2,已发送
            'send_range'	     => implode(',', (array)$datas['recipient_type']),
            'send_time'		     => $datas['send_time'],
            'real_send_time'     => $datas['real_send_time'],
            'add_uid'            => $datas['add_uid'],
            'add_time'           => time()
        );
    }
}