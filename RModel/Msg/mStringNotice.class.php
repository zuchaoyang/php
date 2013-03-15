<?php
class mStringNotice {
    private $_dStringNotice= null;
    
    public function __construct() {
        $this->_dStringNotice= ClsFactory::Create("RData.Msg.dStringNotice");
    }
    
	/**
     * 得到关于$uid的未查看的公告消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringNotice->stringGet($uid);
    }
    
    /**
     * 添加关于$uid的未查看的公告消息数量
     * @param bigint $uid
     * @param int $num
     */
    public function setMsg($uid, $value){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringNotice->setMsg($uid, $value);
    }
    
    /**
     * 清楚关于$uid的未查看的公告消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringNotice->clearMsg($uid);
    }

    /**
     * 关于$uid的未查看的公告消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringNotice->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的公告消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringNotice->stringDecr($uids);
    }     
    
	/**
     * 推送消息to uid
     * @param $id
     */
    public function publishMsg($id, $msg_type = ''){

        if(empty($id) || empty($msg_type) ){
            return false;
        }
        
        $to_accounts = $this->getToAccount($id);

        $mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');
        $send_accounts = $mLiveUsr->getSomeLiveUser($to_accounts);  
        
        if ($send_accounts) {
            $this->_dStringNotice->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;        
    }
    
    private function getToAccount($notice_id){
        $Notice_m = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $Notice_list = $Notice_m->getClassNotice($notice_id);
        $to_account = array();
        $class_code = $Notice_list[$notice_id]['class_code'];
        
        $Class_m = ClsFactory::Create("Model.mClientClass");
        $client_class_list = $Class_m->getClientClassByClassCode($class_code);
        
        $client_class_list = $client_class_list[$class_code];
        
        if(!empty($client_class_list)){
            foreach($client_class_list as $client_class_info) {
                $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
        }
        
        unset($to_account[$Notice_list[$notice_id]['add_account']]);
        return !empty($to_account) ? $to_account : false;
    }
}