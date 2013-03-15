<?php
class IndexAction extends SnsController {

    public function _initialize(){
        import('@.Common_wmw.Pathmanagement_sns');
        parent::_initialize();
    }
    
    public function index() {
        $class_code = $this->objInput->getInt('class_code');

        //todolist 跳转到个人空间
        $class_code = $this->checkoutClassCode($class_code);

        if(empty($class_code)) {
            $this->showError('您暂时不能进入班级空间!', '/Sns/');
        }
        
        $this->assign('user', $this->user);
        $this->assign('class_code', $class_code);
        $this->display('main');
    }
    
    /**
     * 获取用户列表
     */
    public function getclientclass() {
        $class_code = $this->objInput->getInt('class_code');
        
        $current_uid = $this->user["client_account"];
        
        $mClientClass = ClsFactory::Create("Model.mClientClass");  
        $client_arr = $mClientClass->getClientClassByClassCode($class_code, array('client_type'=>array(CLIENT_TYPE_STUDENT, CLIENT_TYPE_TEACHER)));
        $client_list = & $client_arr[$class_code];
        $client_accounts = array();
        if(!empty($client_list)) {
            foreach($client_list as $client_class_id => $client_class_info) {
                $client_accounts[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
            unset($client_list, $client_arr);
        }
        
        $mActive = ClsFactory::Create("Model.Active.mActive");
        $active_info = $mActive->getActiveByClientAccount($client_accounts, 'value desc');
        $active_list = array();
        if(!empty($active_info)) {
            foreach($active_info as $uid=>$list) {
                foreach($list as $v) {
                    $active_list[$v['client_account']] = $v['value'];
                }
            }
        }
        
        //获取在线用户信息
        $mSetLiveUser = ClsFactory::Create("RModel.Common.mSetLiveUser");
        $LiveUserList = $mSetLiveUser->getSomeLiveUser($client_accounts);
        if(!empty($LiveUserList)) {
            foreach($LiveUserList as $uid) {
                $LiveUserList[$uid] = $uid;
            }
        }
        
        $mUser = ClsFactory::Create("Model.mUser");
        $user_list = $mUser->getUserBaseByUid($client_accounts);
        
        if(!empty($LiveUserList) && !empty($user_list)) {
            $sort_keys = array();
            foreach($user_list as $uid => $user){
                if(in_array($uid, (array)$LiveUserList)) {
                    $user['is_live'] = true; 
                }else{
                    $user['is_live'] = false; 
                } 
                $sort_keys[$uid] = intval($active_list[$uid]);
                $user_list[$uid] = $user;
            }
            
            array_multisort($sort_keys, SORT_DESC, $user_list);
        }
        if(empty($user_list)) {
            $this->ajaxReturn(null, "获取活跃成员失败！", -1, 'json');
        }
        $this->ajaxReturn($user_list, "获取活跃成员成功！", 1, 'json');
    }
    
    public function getinfotab() {
        $uid = $this->objInput->getStr("uid");
        $mUser = ClsFactory::Create("Model.mUser");
        
        $current_uid = $this->user["client_account"];
        
        $userinfo = $mUser->getUserByUid($uid);
        $new_info['client_name'] = $userinfo[$uid]['client_name'];
        $school_info = reset($userinfo[$uid]['school_info']);
        $class_info = reset($userinfo[$uid]['class_info']);
        $new_info['school_address'] = $school_info['school_name'] . $class_info["grade_id_name"] . $class_info['class_name'];
        $new_info['client_account'] = $uid;
        
        $new_info["header_pic"] = $userinfo[$uid]['client_headimg_url'];
        
        $mActive = ClsFactory::Create("Model.Active.mActive");
        $active_info = $mActive->getActiveByClientAccount($uid);
        if(empty($active_info)) {
            $dataarr = array(
                'client_account' => $uid,
                'value'=>0
            );
            $mActive->addActive($dataarr);
            $new_info['active_value'] = 0;
        }else{
            $active_info = reset($active_info[$uid]);
            $new_info['active_value'] = $active_info['value'];
        }
        
        //好友检测
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($current_uid);
        $account_relation_infos = $account_relation_infos[$current_uid];
        $friend_list = array();
        
        foreach($account_relation_infos as $val){
            $friend_list[] = $val['friend_account'];
        }
        
        $new_info['client_type'] = CLIENT_TYPE_TEACHER;
        if($userinfo[$uid]['client_type'] == CLIENT_TYPE_STUDENT) {
            $new_info['client_type'] = CLIENT_TYPE_STUDENT;
            $mFamilyRelation = ClsFactory::Create("Model.mFamilyRelation");
            $FamilyRelation = $mFamilyRelation->getFamilyRelationByUid($uid);
            $FamilyRelation = $FamilyRelation[$uid];
            if(!empty($FamilyRelation)) {
                $family_relation = array();
                $family_account = array();
                foreach($FamilyRelation as $relation_info){
                    $family_account[] = $relation_info['family_account'];
                    $family_relation[$relation_info['family_type']] = array(
                    	'family_account' => $relation_info['family_account'],
                    	'family_type' => $relation_info['family_type']
                    );
                }
            }
            
            //父母的好友检测
            $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($family_account);
            $account_relation_infos = $account_relation_infos[$current_uid];
            $friend_list = array();
            
            foreach($account_relation_infos as $val){
                $parent_friend_list[] = $val['friend_account'];
            }
            
            $family_info = $mUser->getClientInfoById($family_account);
            foreach($family_relation as $family_type => $familyinfo) {
                if($family_type == 1) {
                    $new_info['father_name'] = !empty($family_info[$familyinfo['family_account']]['client_name']) ? $family_info[$familyinfo['family_account']]['client_name'] : "父亲";
                    $new_info['father_account'] = $familyinfo['family_account'];
                    $new_info["is_father_friend"] = !empty($parent_friend_list[$familyinfo['family_account']]) ? true : false;
                    $new_info['is_father_self'] = $familyinfo['family_account'] == $current_uid ? true : false;
                }else{
                    $new_info['mather_name'] = !empty($family_info[$familyinfo['family_account']]['client_name']) ? $family_info[$familyinfo['family_account']]['client_name'] : "母亲";
                    $new_info['mather_account'] = $familyinfo['family_account'];
                    $new_info["is_mather_friend"] = !empty($parent_friend_list[$familyinfo['family_account']]) ? true : false;
                    $new_info['is_mather_self'] = $familyinfo['family_account'] == $current_uid ? true : false;
                }
                unset($family_relation[$family_type]);
            }
        }
        
        $new_info['is_friend'] = !in_array($uid, $friend_list) ? false : true;
        $new_info['is_self'] = $uid == $current_uid ? true : false;
//        dump($new_info);die;
        if(empty($new_info)) {
            $this->ajaxReturn(null, "获取用户选项卡失败！", -1, 'json');
        }
        
        $this->ajaxReturn($new_info, "获取用户选项卡成功！", 1, 'json');
    }
}