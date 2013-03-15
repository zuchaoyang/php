<?php
class ActivateAction extends UcController {
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 用户激活页面
     */
    public function index() {
        //成功后调回页面
        $callback = $this->objInput->getStr('callback');
        if($this->isActivated()) {
            $this->showError("该用户已激活或不需要激活!", "/Uc/Index");
        }
        
        $client_type = intval($this->user['client_type']);
        if($client_type == CLIENT_TYPE_FAMILY) {
            //获取家长的家庭关系
            $family_uid = $this->user['client_account'];
            
            $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
            $family_relation_list = $mFamilyRelation->getFamilyRelationByFamilyUid($family_uid);
            $family_relation = reset($family_relation_list[$family_uid]);
            $child_uid = $family_relation['client_account'];
            //获取孩子的姓名信息
            if(!empty($child_uid)) {
                $mUser = ClsFactory::Create('Model.mUser');
                $childuser_list = $mUser->getClientAccountById($child_uid);
                $child = & $childuser_list[$child_uid];
            }
            $this->assign('child', $child);
        }
        
        $this->assign('callback', $callback);
        $this->assign('client_type', $client_type);
        $this->assign('user', $this->user);
        
        $this->display("user_activation");
    }
    
    /**
     * 判断用户是否需要激活
     */
    protected function isActivated() {
        return $this->user['status'] == constant('CLIENT_STOP_FLAG') ? false : true;
    }
    
    /**
     * 激活用户
     */
    public function activateUser() {
        $password = $this->objInput->postStr('password');
        $password_sure = $this->objInput->postStr('password_sure');
        $callback = $this->objInput->postStr('callback');
        
        //url进行解码
        $decode_callback = null;
        if(!empty($callback)) {
            $decode_callback = urldecode($callback);
        }
        
        if(empty($password)) {
            $this->showError('密码不能为空!', '/Uc/Activate');
        }
        if(strlen($password) < 6) {
            $this->showError('密码长度不能小于6位!', '/Uc/Activate');
        }
        //判断用户两次输入的秘密是否正确
        if($password !== $password_sure) {
            $this->showError('您两次输入的密码不一致!', '/Uc/Activate');
        }
        
        //激活当前用户
        $datas = array(
            'client_password' => md5($password),
            'status' => CLIENT_STOP_FLAG_NORMAL,
        	'upd_time'=>time(),
        	'active_date'=>time(),
        );
        $mUser = ClsFactory::Create('Model.mUser');
        $effect_row = $mUser->modifyUserClientAccount($datas, $this->user['client_account']);
        
        if(empty($effect_row)) {
            $this->showError('激活失败!', "/Uc/Activate" . (!empty($callback) ? "/callback/$callback" : ""));
        }
        $this->showSuccess('激活成功!', !empty($decode_callback) ? $decode_callback : '/Uc/Index');
    }
}