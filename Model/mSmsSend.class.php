<?php
    /**
     +----------------------------------------------------------
     * 向开通手机业务的学生家长手机发送短信，自动将超过长度的短信内容及超过额定个数的电话分组。
     +----------------------------------------------------------
     * @access public
     * @author 栾红敏(luanhongmin@wmw.cn)
     * @since 2011-09-01
     +----------------------------------------------------------
     * @param string $content 短信内容 字符型
     * @param array  $phone  多手机号时使用数字索引数组
     +----------------------------------------------------------
     */

class mSmsSend extends mBase{
	protected $_dSmsSend = null;
	
	public function __construct() {
	    $this->_dSmsSend = ClsFactory::Create('Data.dSmsSend');
	}
	
    public function addSmsSend($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        $result = $this->_dSmsSend->addBat($dataarr); 
        return $result;
    }
    /**
     *批量添加发送短信
     * @param unknown_type $dataarr
     */
    public function addSmsSendBat($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        $result = $this->_dSmsSend->addBat($dataarr);
        return $result;
    }
    
    public function addSmsSend_test($phone, $content , $operationStrategy) {
        if(empty($phone) || $content=="" )
        $phonePieceArr = $this->slicePhone();
        $contentPieceArr = $this->sliceContent();
        foreach($contentPieceArr as $content) {
            foreach($phonePieceArr as $phonePiece) {
                $result = $this->_dSmsSend->addSmsSend($dataarr);
            }
        }
    }
    
    
    //用户找回密码时发送手机验证码(单人单条短信)
    function sendSingleMsg($dataarr){
        if(empty($dataarr)){
            return false;
        }
    	return $this->_dSmsSend->addSmsSend($dataarr);
    }
    
    //查看待发的短信
	function getOutgoingMessage($sms_send_bussiness_type){
		if(empty($sms_send_bussiness_type)) {
    	    return false;   
    	}
    	$wheresql = array(
    	    "sms_send_type=0 ", 
    	    "sms_send_bussiness_type=$sms_send_bussiness_type",
    	);
    	$orderby = "sms_send_id asc";
        $outgoingmessage = $this->_dSmsSend->getInfo($wheresql, $orderby);
        
        return !empty($outgoingmessage) ? $outgoingmessage : false;
	}
	
	function modifySmsSend($dataarr, $sms_send_id){
		if(empty($sms_send_id)||empty($dataarr)) return false;
		return $this->_dSmsSend->modifySmsSend($dataarr, $sms_send_id);
	}
	
}