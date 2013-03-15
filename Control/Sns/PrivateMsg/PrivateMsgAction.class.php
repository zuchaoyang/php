<?php
class PrivateMsgAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index(){
        $current_uid = $this->user['client_account'];
        $page = $this->objInput->getInt("page");
        $page = max(1, $page);
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $mPrivateMsgRelation = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgRelation");
        
        $private_msg_list = $mPrivateMsgRelation->getPrivateMsgRelationBySendUid($current_uid, null, $offset, $limit + 1);
        
        $last_msg_ids = array();
        $msg_num = array();
        $uid_list = array();
        
        if(count($private_msg_list) < $limit){
            $is_end_page = true;
        }else{
            $is_end_page = false;
            array_pop($private_msg_list);
        }
        
        if(!empty($private_msg_list)){
            $private_msg_list = reset($private_msg_list);
            foreach($private_msg_list as $id => $msg_relation) {
                $relation_ids[$msg_relation['new_msg_id']] = $id;
                $last_msg_ids[] = $msg_relation['new_msg_id'];
                $msg_num[$msg_relation['new_msg_id']] = $msg_relation['msg_count'];
                $uid_list[$msg_relation['to_uid']] = $msg_relation['to_uid'];
                $uid_list[$msg_relation['send_uid']] = $msg_relation['send_uid'];
            }
        }
        
        $mUser = ClsFactory::Create("Model.mUser");
        $user_info = $mUser->getUserByUid($uid_list);
   
        $mPrivateMsg = ClsFactory::Create("Model.PrivateMsg.mPrivateMsg");
        $private_msg_list = $mPrivateMsg->getPrivateMsgById($last_msg_ids);
        foreach($private_msg_list as $private_msg_id => $private_msg){
            $private_msg['relation_id'] = $relation_ids[$private_msg_id];
            $private_msg['msg_count'] = $msg_num[$private_msg_id];
            $private_msg['send_name'] = $user_info[$private_msg['send_uid']]['client_name'];
            $private_msg['send_url'] = $user_info[$private_msg['send_uid']]['client_headimg_url'];
            $private_msg['to_name'] = $user_info[$private_msg['to_uid']]['client_name'];
            $private_msg['to_url'] = $user_info[$private_msg['to_uid']]['client_headimg_url'];
            $img_str = "<img src='" . $private_msg['img_url'] . "'/>";
            $private_msg['content'] = preg_replace("/#分享照片#/", $img_str, $private_msg['content']);
            $private_msg['add_time'] = date('Y-m-d H:i', $private_msg['add_time']);
            if($current_uid  == $private_msg['send_uid']) {
                $private_msg['is_send'] = true;
            }else{
                $private_msg['is_send'] = false;
            }
            $private_msg_list[$private_msg_id] = $private_msg;
        }
        
        $this->assign("is_end_page", $is_end_page);
        $this->assign("page", $page);
        $this->assign('private_list', $private_msg_list);
        $this->display("list");
    }
    
    //上传图片
    public function upload_img($name){
		if ( isset( $_FILES[$name]['name'] ) && $_FILES[$name]['name'] != "" ){
			
			$up_init = array(
					'attachmentspath' => Pathmanagement_sns::upload_private_msg_img(),
					'renamed' => true,
					'allow_type' => array('jpg','gif','png','bmp')
			);
			
			$uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
            $uploadObj->_set_options($up_init);
            $up_rs = $uploadObj->upfile($name);
            
			return '/'.str_replace(WEB_ROOT_DIR . '/', '', $up_rs['getfilename']);
		}
		
	    return '';
    }
    
    public function add_private_msg(){
        $current_uid = $this->user['client_account'];
        $to_uid = $this->objInput->getStr("to_uid");
        $content = $this->objInput->postStr("content");
        $img_url = $this->upload_img("pic");
        
        if(empty($to_uid) || empty($content)) {
            $this->ajaxReturn(null, "添加失败！", -1, 'json');
        }
        $dataarr=array(
            'send_uid' => $current_uid,
            'to_uid' => $to_uid,
            'content' => $content,
            'add_time' => time(),
            'img_url' => $img_url,
        );
        
        $mPrivateMsg = ClsFactory::Create("Model.PrivateMsg.mPrivateMsg");
        $private_msg_id = $mPrivateMsg->addPrivateMsg($dataarr, true);
        
        $mPrivateMsgRelation = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgRelation");
        
        $private_msg_relation_info = $mPrivateMsgRelation->getPrivateMsgRelationBySendUidAdnToUid($current_uid, $to_uid);
        $private_msg_relation_info1 = $mPrivateMsgRelation->getPrivateMsgRelationBySendUidAdnToUid($to_uid, $current_uid);
        if(!empty($private_msg_relation_info) && !empty($private_msg_relation_info1)){
            $dataarr_relation = 
                array(
                    'send_uid' => $current_uid,
                    'to_uid' => $to_uid,
                    'new_msg_id' => $private_msg_id,
                    'msg_count' => "%msg_count+1%",
                );
            $dataarr_relation1 = 
                array(
                    'send_uid' => $to_uid,
                    'to_uid' => $current_uid,
                    'new_msg_id' => $private_msg_id,
                    'msg_count' => "%msg_count+1%",
                );
            $id = key($private_msg_relation_info);
            $id1 = key($private_msg_relation_info1);
            $result = $mPrivateMsgRelation->modifyPrivateMsgRelation($dataarr_relation, $id);
            $result = $mPrivateMsgRelation->modifyPrivateMsgRelation($dataarr_relation1, $id1);
        }else{
            $dataarr_relation = array(
                array(
                    'send_uid' => $current_uid,
                    'to_uid' => $to_uid,
                    'new_msg_id' => $private_msg_id,
                ),
                array(
                    'send_uid' => $to_uid,
                    'to_uid' => $current_uid,
                    'new_msg_id' => $private_msg_id,
                ),
            );
            $result = $mPrivateMsgRelation->addPrivateMsgRelationBat($dataarr_relation);
        }
        
        if(!empty($result)) {
            $session_dataarr = array(
                array(
                    'send_uid' => $current_uid,
                    'to_uid' => $to_uid,
                    'msg_id' => $private_msg_id
                ),
                array(
                    'send_uid' => $to_uid,
                    'to_uid' => $current_uid,
                    'msg_id' => $private_msg_id
                )
            );
            
            $mPrivateMsgSession = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgSession");
            $result = $mPrivateMsgSession->addPrivateMsgSessionBat($session_dataarr);
        }
        
        import('@.Control.Api.MsgApi');
        $Msg = new MsgApi();
        $Msg->addPrivateMsg($private_msg_id);
        !empty($result) ? $this->ajaxReturn(null, "添加成功！", 1, 'json') : $this->ajaxReturn(null, "添加失败！", -1, 'json');
    }
    
    public function del_private_msg_session(){
        $relation_id = $this->objInput->getInt("id");
        $to_uid = $this->objInput->getStr("to_uid");
        
        $current_uid = $this->user['client_account'];
        $mPrivateMsgRelation = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgRelation");
        
        $mPrivateMsgSession = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgSession");
        $PrivateMsgSession = $mPrivateMsgSession->getPrivateMsgSessionBySendUidAndToUid($current_uid, $to_uid);
        
        $PrivateMsgSession1 = $mPrivateMsgSession->getPrivateMsgSessionBySendUidAndToUid($to_uid, $current_uid);
        $result = false;
        $private_msg_ids = array();
        foreach($PrivateMsgSession as $id => $session_info){
            $private_msg_ids[$session_info['msg_id']] = $session_info['msg_id'];
            $result = $mPrivateMsgSession->delPrivateMsgSession($id);
            if(empty($result))
                break;
        }
        
        if(empty($PrivateMsgSession1) && !empty($result)){
            if(!empty($private_msg_ids)) { 
                $mPrivateMsg = ClsFactory::Create("Model.PrivateMsg.mPrivateMsg");
                foreach($private_msg_ids as $private_msg_id){
                    $result = $mPrivateMsg->delPrivateMsg($private_msg_id);
                    if(empty($result))
                        break;
                }
            }
        }
        
        if(!empty($result)){
            $result = $mPrivateMsgRelation->delPrivateMsgRelation($relation_id);
        }
        
        !empty($result) ? $this->ajaxReturn(null, "删除成功！", 1, 'json') : $this->ajaxReturn(null, "删除失败！", -1, 'json');
    }
    
    public function del_private_msg(){
        $session_id = $this->objInput->getInt("session_id");
        
        $mPrivateMsgSession = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgSession");
        $session_info = $mPrivateMsgSession->getPrivateMsgSessionById($session_id);
        $result = $mPrivateMsgSession->delPrivateMsgSession($session_id);
        $mPrivateMsg = ClsFactory::Create("Model.PrivateMsg.mPrivateMsg");
        if(!empty($session_info)){
            $session_info = reset($session_info);
            $to_session_info = $mPrivateMsgSession->getPrivateMsgSessionBySendUidAndToUid($session_info['to_uid'], $session_info['send_uid'],$session_info['msg_id']);
            if(empty($to_session_info)) {
                $result = $mPrivateMsg->delPrivateMsg($session_info['msg_id']);
            }
        }
        
        $current_uid = $this->user["client_account"];
        $with_uid = $session_info['to_uid'];
        if(!empty($result)) {
            $mPrivateMsgRelation = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgRelation");
            $private_msg_relation = $mPrivateMsgRelation->getPrivateMsgRelationBySendUid($current_uid);
            $private_msg_relation_id = key($private_msg_relation[$current_uid]);
            $private_msg_relation = reset($private_msg_relation[$current_uid]);
            if(intval($private_msg_relation['msg_count']) < 2) {
                $mPrivateMsgRelation->delPrivateMsgRelation($private_msg_relation_id);
            }else{
                $count = $private_msg_relation['msg_count']-1;
                $priavte_msg_list = $mPrivateMsgSession->getPrivateMsgSessionBySendUidAndToUid($current_uid, $with_uid, null, 'msg_id desc', 0, 1);
                $new_private_msg = reset($priavte_msg_list);
                
                $priavte_msg_id = $new_private_msg['msg_id'];
                $dataarr = array(
                    'msg_count' => $count,
                    'new_msg_id' => $priavte_msg_id
                );
                $result = $mPrivateMsgRelation->modifyPrivateMsgRelation($dataarr, $private_msg_relation_id);
            }
        }
        
        !empty($result) ? $this->ajaxReturn(null, "删除成功！", 1, 'json') : $this->ajaxReturn(null, "删除失败！", -1, 'json');
    }
    
    public function private_msg_list(){
        $to_uid = $this->objInput->getStr("to_uid");
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $current_uid = $this->user['client_account'];
        
        $mPrivateMsgSession = ClsFactory::Create("Model.PrivateMsg.mPrivateMsgSession");
        $priavte_msg_session_list = $mPrivateMsgSession->getPrivateMsgSessionBySendUidAndToUid($current_uid, $to_uid, null, "id desc", $offset, $limit + 1);
        
        if(count($priavte_msg_session_list) < $limit){
            $is_end_page = true;
        }else{
            $is_end_page = false;
            array_pop($priavte_msg_session_list);
        }
        if(!empty($priavte_msg_session_list)) {
            $private_msg_id = array();
            $session_ids = array();
            foreach($priavte_msg_session_list as $id => $session_info) {
                $private_msg_id[] = $session_info['msg_id'];
                $session_ids[$session_info['msg_id']] = $id;
            }
            
            $mPrivateMsg = ClsFactory::Create("Model.PrivateMsg.mPrivateMsg");
            $priavte_msg_list = $mPrivateMsg->getPrivateMsgById($private_msg_id);
            
            if(!empty($priavte_msg_list)) {
                $mUser = ClsFactory::Create("Model.mUser");
                $user_info = $mUser->getUserBaseByUid($to_uid);
                $user_info[$current_uid] = $this->user;
                $keys = array();
                foreach($priavte_msg_list as $key => $private_msg) {
                    $private_msg['send_name'] = $user_info[$private_msg['send_uid']]['client_name'];
                    $private_msg['to_name'] = $user_info[$private_msg['to_uid']]['client_name'];
                    $private_msg['send_url'] = $user_info[$private_msg['send_uid']]['client_headimg_url'];
                    $private_msg['to_url'] = $user_info[$private_msg['to_uid']]['client_headimg_url'];
                    $img_str = "<img src='" . $private_msg['img_url'] . "'/>";
                    $private_msg['content'] = preg_replace("/#分享照片#/", $img_str, $private_msg['content']);
                    if($this->user['client_account']  == $private_msg['send_uid']) {
                        $private_msg['is_send'] = true;
                    }else{
                        $private_msg['is_send'] = false;
                    }
                    $private_msg['add_time'] = date("Y-m-d H:i", $private_msg['add_time']);
                    $private_msg['session_id'] = $session_ids[$private_msg['msg_id']];
                    $keys[] = $key;
                    $priavte_msg_list[$key] = $private_msg;
                }
                
                array_multisort($priavte_msg_list,SORT_DESC,$keys);
            }
        }
        
        $this->assign("to_name", $user_info[$to_uid]['client_name']);
        $this->assign("to_uid", $to_uid);
        $this->assign("is_end_page", $is_end_page);
        $this->assign("page", $page);
        $this->assign("to_uid", $to_uid);
        $this->assign("priavte_msg_list", $priavte_msg_list);
        $this->display("show");
    }
}