<?php
class mStringPrivateMsg {
    private $_dStringComments = null;
    
    public function __construct() {
        $this->_dStringPrivateMsg = ClsFactory::Create("RData.Msg.dStringPrivateMsg");
    }

	/**
     * 得到关于$uid的未查看的评论消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringPrivateMsg->stringGet($uid);
    }

    /**
     * 添加关于$uid的未查看的评论消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringPrivateMsg->stringGet($uid, $value);
    }
    
    /**
     * 清除关于$uid的未查看的评论消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringPrivateMsg->keyDel($uid);
    }
    
    /**
     * 关于$uid的未查看的评论消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringPrivateMsg->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的评论消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringPrivateMsg->stringDecr($uids);
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
            $this->_dStringPrivateMsg->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;
    }
    
    private function getToAccount($msg_id){
    
        if(empty($msg_id)){
            return false;
        }         
        $PrivateMsg = ClsFactory::Create('Model.PrivateMsg.mPrivateMsg');
        $PrivateMsgInfo = $PrivateMsg->getPrivateMsgById($msg_id);
        
        if(!empty($PrivateMsgInfo)) {
            $PrivateMsgInfo = reset($PrivateMsgInfo);
            $to_account[$PrivateMsgInfo['to_uid']] = $PrivateMsgInfo['to_uid'];
        }
        
        return !empty($to_account) ? $to_account : false;
    }
    
    
}