<?php
class mStringHomework {
    private $_dStringHomework = null;
    
    public function __construct() {
        $this->_dStringHomework = ClsFactory::Create("RData.Msg.dStringHomework");        
    }
    
	/**
     * 得到关于$uid的未查看的作业消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringHomework->stringGet($uid);
    }
    
    /**
     * 添加关于$uid的未查看的作业消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringHomework->stringSet($uid, $value);
    }
    
    /**
     * 清楚关于$uid的未查看的作业消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringHomework->clearMsg($uid);
    }
    
    /**
     * 关于$uid的未查看的作业消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringHomework->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的作业消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringHomework->stringDecr($uids);
    }

	/**
     * 推送消息to id
     * @param $id
     */
    public function publishMsg($id, $msg_type = ''){

        if(empty($id) || empty($msg_type) ){
            return false;
        }
        
        $to_accounts = $this->getToAccount($id);
        
        $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
        $send_accounts = $mLiveUsr->getSomeLiveUser($to_accounts);
        $mMsgHomework = ClsFactory::Create("RModel.Msg.mStringHomework");
        
        if ($send_accounts) {
            $this->_dStringHomework->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;        
    }

    private function getToAccount($homework_id){
        
        if(empty($homework_id)) {
            return false;
        }        
        
        $homework_m = ClsFactory::Create("Model.ClassHomework.mClassHomeworkSend");
        $homework_list = $homework_m->getHomeworkSendByhomeworkid($homework_id);

        $homework_list = reset($homework_list);
        $to_account = array();
        if(!empty($homework_list)){
            foreach($homework_list as $id => $homework_info){
                $to_account[$homework_info['client_account']] = $homework_info['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }    
    
}