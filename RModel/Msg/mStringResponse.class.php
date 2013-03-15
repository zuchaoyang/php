<?php
class mStringResponse {
    private $_dStringResponse = null;
    
    public function __construct() {
        $this->_dStringResponse = ClsFactory::Create("RData.Msg.dStringResponse");
    }
    
	/**
     * 得到关于$uid的未查看的成绩消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringResponse->stringGet($uid);
    }
    
    /**
     * 添加关于$uid的未查看的成绩消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringResponse->setMsg($uid, $value);
    }
    
    /**
     * 清楚关于$uid的未查看的成绩消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringResponse->clearMsg($uid);
    }
    

    /**
     * 关于$uid的未查看的成绩消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringResponse->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的成绩消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringResponse->stringDecr($uids);
    }     
    
	/**
     * 推送消息to uid
     * @param $uid
     */
    public function publishMsg($id, $msg_type = '') {
        
        
        if(empty($id) || empty($msg_type) ){
            return false;
        }
        
        $to_accounts = $this->getToAccount($id);
        
        $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
        $send_accounts = $mLiveUsr->getSomeLiveUser($to_accounts);        
        
        if ($send_accounts) {
            $this->_dStringResponse->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;
    }
    
    private function getToAccount($res_id){
        $Res_m = ClsFactory::Create("Model.Message.mMsgResponse");
        $Res_list = $Res_m->getMsgResponseById($res_id);
        $Res_list = reset($Res_list);
        $to_account[$Res_list['to_account']] = $Res_list['to_account'];
        
        return !empty($to_account) ? $to_account : false;
    }    
    
}