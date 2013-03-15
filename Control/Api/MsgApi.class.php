<?php
class MsgApi extends ApiController{
	public function __construct() {
        parent::__construct();
    }
    
    public function _initialize(){
		parent::_initialize();
    }
    
    /**
     * 添加班级作业新消息
     * @param bigint $uid
     */
    public function addHomeworkMsg($homework_id){
        if(empty($homework_id)){
            return false;
        }
        $this->publishmsg($homework_id, 'homework');
    }
    
	/**
     * 添加私信新消息
     * @param bigint $uid
     */
    public function addPrivateMsg($msg_id){
        if(empty($msg_id)){
            return false;
        }
        $this->publishmsg($msg_id, 'privatemsg');
    }
    
    /**
     * 添加评论新消息
     * @param bigint $uid
     */
    public function addCommentsMsg($comments_id) {
        if(empty($comments_id)){
            return false;
        }
        
        $this->publishmsg($comments_id, 'comments');
    }
    
    /**
     * 添加班级成绩新消息
     * @param bigint $uid
     */
    public function addExamMsg($exam_id){
        if(empty($exam_id)){
            return false;
        }
        
        $this->publishmsg($exam_id, 'exam');
    }
    
    /**
     * 添加班级公告新消息
     * @param bigint $uid
     */
    public function addNoticeMsg($notice_id) {
        if(empty($notice_id)){
            return false;
        }

        $this->publishmsg($notice_id, 'notice');

    }
    
    /**
     * 添加好友请求新消息
     * @param $dataarr
     * array(
	 *	'req_id',
	 *	'content',
	 *	'to_account',
	 *	'add_account',
	 *	'add_time',
	 * );
     */
    public function addReqMsg($req_id){
        if(empty($req_id)){
            return false;
        }
        
        $this->publishmsg($req_id, 'req');
    }
    
    /**
     * 添加好友请求回复新消息
     * @param bigint $uid
     * array(
	 *	'res_id',
	 *	'content',
	 *	'to_account',
	 *	'add_account',
	 *  'add_time',
	 * );
     */
    public function addResMsg($res_id){
        if(empty($res_id)){
            return false;
        }
        $this->publishmsg($res_id, 'res');
    }
    
    /**
     * 清空作业新消息
     * @param bigint $uid
     */
    public function clearHomeworkMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgHomework = ClsFactory::Create("RModel.Msg.mStringHomework");
        $mMsgHomework->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 清空评论新消息
     * @param bigint $uid
     */
    public function clearCommentsMsg($uid) {
        if(empty($uid)){
            return false;
        }
        
        $mMsgComments = ClsFactory::Create("RModel.Msg.mStringComments");
        $mMsgComments->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 清空班级成绩新消息
     * @param bigint $uid
     */
    public function clearExamMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgExam = ClsFactory::Create("RModel.Msg.mStringExam");
        $mMsgExam->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 清空班级公告新消息
     * @param bigint $uid
     */
    public function clearNoticeMsg($uid) {
        if(empty($uid)){
            return false;
        }
        
        $mMsgNotice = ClsFactory::Create("RModel.Msg.mStringNotice");
        $mMsgNotice->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 清空请求新消息
     * @param bigint $uid
     */
    public function clearReqMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgRequest = ClsFactory::Create("RModel.Msg.mStringRequest");
        $mMsgRequest->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 删除已经处理过得请求消息
     * @param bigint $uid
     */
    public function delReqMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgRequest = ClsFactory::Create("RModel.Msg.mStringRequest");
        $mMsgRequest->delRequestMsg($uid);
        
        return true;
    }
    
 	/**
     * 清空请求新消息
     * @param bigint $uid
     */
    public function clearResMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgResponse = ClsFactory::Create("RModel.Msg.mStringResponse");
        $mMsgResponse->clearMsg($uid);
        
        return true;
    }
    
    /**
     * 删除已经处理过得请求消息
     * @param bigint $uid
     */
    public function delResMsg($uid){
        if(empty($uid)){
            return false;
        }
        
        $mMsgResponse = ClsFactory::Create("RModel.Msg.mStringResponse");
        $mMsgResponse->delMsg($uid);
        
        return true;
    }
    
    /**
     * 在线用户的消息推送
     * @param unknown_type $uid
     * @param unknown_type $msg_type
     */
    private function publishmsg($id, $msg_type){
        
        if(empty($id)){
            return false;
        }        
        
        $parm_list = serialize(array('id' => $id, 'msg_type' => $msg_type));
        $result = Gearman::send('message_publish', $parm_list, PRIORITY_HIGH, false);
    }
}