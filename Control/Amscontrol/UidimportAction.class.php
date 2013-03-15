<?php
class UidimportAction extends AmsController{
    protected $is_school = true;
    public function _initialize() {
        parent::_initialize();
        $this->assign('username', $this->user['ams_name']);
    }
    
    public function show_uid_import() {
        $class_code = $this->objInput->getInt('class_code');
        $grade_id = $this->objInput->getInt('grade_id');
        $uid = $this->objInput->getInt('uid');
        $this->assign('classCode', $class_code);
        $this->assign('gradeid', $grade_id);
        $this->assign('uid', $uid);
        $this->assign('schoolid', $this->user['schoolinfo']['school_id']);
        $this->display('importuid');
    }
    
    //账号导入
    public function uidimport() {
        $class_code = $this->objInput->getInt('class_code');
        $grade_id = $this->objInput->getInt('gradeid');
        $school_id = $this->user['schoolinfo']['school_id'];
        $client_account = $this->uids = $this->objInput->postStr('client_account');
        $uid = $this->objInput->getInt('uid');
        if(!empty($client_account)) {
            $client_account_arr = explode("\n",trim($client_account));
             foreach($client_account_arr as $key=>&$val){
                 if(!empty($val)){
                     $client_account_uid[trim($val)] = trim($val);
                 }
             }
        }
        $uidarr = $this->checkuid($client_account_uid);
        if(!empty($uidarr['ok'])) {
            $resault = $this->addClientClass($uidarr['ok'], $class_code);
        }
        $this->assign('uid', $uid);
        $this->assign('schoolid', $this->user['schoolinfo']['school_id']);
        $this->assign('gradeid', $grade_id);
        $this->assign('class_code', $class_code);
        $this->assign('classCode', $class_code);
        $this->assign('err', $uidarr['err']);
        if(!empty($uidarr['err'])) {
            $this->display('importuid_result');
            exit;
        }
        $this->redirect("Classclientmanage/showclassClient/classCode/$class_code/gradeid/$grade_id/schoolid/$school_id/uid/$uid");
    }
    
    private function addClientClass($uidarr, $class_code) {
        $dataarr = array();
        foreach($uidarr['children'] as $val) {
            $dataarr[] = array(
                'client_account'=>$val,
                'class_code'=>$class_code,
                'add_time'=>time(),
                'add_account'=>$this->user['ams_account'],
                'upd_account'=>$this->user['ams_account'],
                'upd_time'=>time(),
                'client_type'=>0
            ); 
        }
        
        foreach($uidarr['mother'] as $val) {
            $dataarr[] = array(
                'client_account'=>$val,
                'class_code'=>$class_code,
                'add_time'=>time(),
                'add_account'=>$this->user['ams_account'],
                'upd_account'=>$this->user['ams_account'],
                'upd_time'=>time(),
                'client_type'=>2
            ); 
        }
        
        foreach($uidarr['father'] as $val) {
            $dataarr[] = array(
                'client_account'=>$val,
                'class_code'=>$class_code,
                'add_time'=>time(),
                'add_account'=>$this->user['ams_account'],
                'upd_account'=>$this->user['ams_account'],
                'upd_time'=>time(),
                'client_type'=>2
            ); 
        }
        $mClientClass = ClsFactory::Create("Model.mClientClass");
        return $mClientClass->addClientClassBat($dataarr);
    }
    
    /**
     * 检测uid是否合法
     * @param $uidarr
     */
    private function checkuid($uidarr) {
        $mUser = ClsFactory::Create("Model.mUser");
        $UserList =  $mUser->getUserBaseByUid($uidarr);
        $err_uid = array();
        $mClientClass = ClsFactory::Create("Model.mClientClass");
        if(!empty($UserList)) {
            foreach($UserList as $uid => $userinfo) {
                if($userinfo['client_type'] != CLIENT_TYPE_STUDENT) {
                    $err_uid[$uid] = '非学生账号';
                    unset($uidarr[$uid]);
                    unset($UserList[$uid]);
                }
            }
            $is_set_uid = array_keys($UserList);
            $tmp_arr = array_diff($uidarr, (array)$is_set_uid);
            foreach($tmp_arr as $uid) {
                $err_uid[$uid] = '账号不存在';
                unset($uidarr[$uid]);
            }
            $ClientClass_list = $mClientClass->getClientClassByUid($is_set_uid);
            $is_class = array_keys($ClientClass_list);
            foreach($is_class as $uid) {
                $err_uid[$uid] = '班级关系未解除';
                unset($uidarr[$uid]);
            }
            
            $uids = array_diff($is_set_uid, (array)$is_class);
        }else{
            foreach($uidarr as $uid) {
                $err_uid[$uid] = '账号不存在';
                unset($uidarr[$uid]);
            }
        }
        
        //查看可用账号的父母账号是否在班级
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $uids_arr['children'] = $uids; 
        $FamilyRelation = $mFamilyRelation->getFamilyRelationByUid($uids_arr['children']);
        foreach($FamilyRelation as $relation) {
            foreach($relation as $relation_id => $info) {
                $family_uid[] = $info['family_account'];
                if($info['family_type'] == 1) {
                    $uids_arr['mother'][] = $info['family_account'];
                }else {
                    $uids_arr['father'][] = $info['family_account'];
                }
            }
        }
        $ClientClass_list = $mClientClass->getClientClassByUid($family_uid);
        foreach($ClientClass_list as $uid => $client_info) {
            foreach($client_info as $client_class_id => $client_class_info) {
                $client_class_ids[] = $client_class_id;
            }
            
        }
        
        if(!empty($client_class_ids)) {
            foreach($client_class_ids as $client_class_id) {
                $mClientClass->delClientClass($client_class_id);
            }
        }
        
        return array('ok' => $uids_arr, 'err'=>$err_uid);
    }
    
    protected function checkAccess() {
        $school_id = $this->objInput->getInt('school_id');
        $is_pass = $this->checkLoginerInSchool($this->user['ams_account'], $this->user['schoolinfo']['school_id']);
        if(!$is_pass) {
            $this->showError("您没有此模块的操作权限", "/Amscontrol/Classclientmanage/showclassClient");
        }
        
    }
    
}