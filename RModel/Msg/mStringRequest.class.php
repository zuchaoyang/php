<?php
class mStringRequest {
    private $_dStringRequest = null;
    
    public function __construct() {
        $this->_dStringRequest = ClsFactory::Create("RData.Msg.dStringRequest");
    }
    
	/**
     * 得到关于$uid的未查看的请求消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringRequest->stringGet($uid);
    }
    
    /**
     * 添加关于$uid的未查看的请求消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringRequest->setMsg($uid, $value);
    }
    
    /**
     * 清楚关于$uid的未查看的请求消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringRequest->clearMsg($uid);
    }
    

    /**
     * 关于$uid的未查看的请求消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringRequest->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的请求消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }

        return $this->_dStringRequest->stringDecr($uids);
    }     

	/**
     * 推送消息to uid
     * @param $uid
     */
    public function publishMsg($id, $msg_type = ''){
        
        
        if(empty($id) || empty($msg_type) ){
            return false;
        }
        
        $to_accounts = $this->getToAccount($id);
       
        $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
        $send_accounts = $mLiveUsr->getSomeLiveUser($to_accounts);        
        
        if ($send_accounts) {
            $this->_dStringRequest->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;
    }
    
    private function getToAccount($req_id){
        $Req_m = ClsFactory::Create("Model.Message.mMsgRequire");
        $Req_list = $Req_m->getMsgRequireById($req_id);
        $Req_list = reset($Req_list);
        $to_account[$Req_list['to_account']] = $Req_list['to_account'];
        
        return !empty($to_account) ? $to_account : false;
    }    
    
}