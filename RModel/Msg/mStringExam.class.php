<?php
class mStringExam {
    private $_dStringExam = null;
    
    public function __construct() {
        $this->_dStringExam = ClsFactory::Create("RData.Msg.dStringExam");
    }
    
	/**
     * 得到关于$uid的未查看的成绩消息数量
     * @param bigint $uid
     */
    public function getMsg($uid){
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dStringExam->stringGet($uid);
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
        
        return $this->_dStringExam->setMsg($uid, $value);
    }
    
    /**
     * 清楚关于$uid的未查看的成绩消息
     * @param bigint $uid
     */
    public function clearMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        return $this->_dStringExam->clearMsg($uid);
    }
    

    /**
     * 关于$uid的未查看的成绩消息+1
     * @param bigint $uid
     */
    public function incrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringExam->stringIncr($uids);
    }    
    
    /**
     * 关于$uid的未查看的成绩消息-1
     * @param bigint $uid
     */
    public function decrMsg($uids){
        if(empty($uids)){
            return false;
        }
        
        return $this->_dStringExam->stringDecr($uids);
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
            $this->_dStringExam->stringPublic($send_accounts, $msg_type);
        }
        
        if(!empty($to_accounts)) {
            $this->incrMsg($to_accounts);
        }
        
        return true;
    }
    
    private function getToAccount($exam_id){
        if(empty($exam_id)) {
            return false;
        }
        $exam_id = is_array($exam_id) ? array_shift($exam_id):$exam_id;
        $Exam_m = ClsFactory::Create('Model.mClassExam');
        $exam_info = $Exam_m->getClassExamById($exam_id);
        $exam_info = reset($exam_info);
        
        $class_code = $exam_info['class_code'];
        $Class_m = ClsFactory::Create("Model.mClientClass");
        $client_class_list = $Class_m->getClientClassByClassCode($class_code,array('client_type'=>array(CLIENT_TYPE_FAMILY,CLIENT_TYPE_STUDENT)));
        $client_class_list = reset($client_class_list);
        $to_account = array();
        if(!empty($client_class_list)){
            foreach($client_class_list as $class_code => $client_class_info){
                $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }    
    
}