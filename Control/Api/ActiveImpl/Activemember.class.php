<?php
import('@.Control.Api.AlbumImpl.Core');

class Activemember {
    
    private $_limit = 10;
    public function getActivemember($class_code){
        $mClientClass = ClsFactory::Create("Model.mClientClass");  
        $client_list = $mClientClass->getClientClassByClassCode($class_code, array('client_type'=>array(CLIENT_TYPE_STUDENT,CLIENT_TYPE_TEACHER)));
        
        $mActive = ClsFactory::Create("Model.Active.mActive");
        if(!empty($client_list)) {
            $client_list = reset($client_list);
            $client_account = array();
            $uid_list = array();
            
            $teacher_num = 0;
            $student_num = 0;
            foreach($client_list as $client_class_id => $client_class_info) {
                $client_account[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
            
            
            $mUser = ClsFactory::Create("Model.mUser");
            $user_info = $mUser->getUserBaseByUid($client_account);
            unset($client_account);
            foreach($client_list as $client_class_id => $client_class_info) {
                if(empty($user_info[$client_class_info['client_account']])) {
                    unset($client_list[$client_class_id]);
                    continue;
                }
                $client_account[$client_class_info['client_account']] = $client_class_info['client_account'];
                if($client_class_info['client_type'] == CLIENT_TYPE_TEACHER) {
                    $teacher_num ++;
                }else if($client_class_info['client_type'] == CLIENT_TYPE_STUDENT){
                    $student_num ++;
                }
            }
            
            $active_member_list = $mActive->getActiveByClientAccount($client_account, 'value desc' , 0, 10);
            $active_list = array();
            if(!empty($active_member_list)) {
                foreach($active_member_list as $uid=>$list) {
                    foreach($list as $active_info) {
                        $active_list[$active_info['client_account']] = $active_info['value'];
                    }
                }
            }
            $active_uid = array_keys($active_list);
            $mSetLiveUser = ClsFactory::Create("RModel.Common.mSetLiveUser");
            $LiveUserList = $mSetLiveUser->getSomeLiveUser($client_account);
            
            
            $uid_sort = array();
            
            if(!empty($user_info) && !empty($LiveUserList)) {
                $sort_keys = array();
                foreach($user_info as $uid => $user){
                    $sort_keys[$uid] = intval($active_list[$uid]);
                    if(in_array($uid, (array)$LiveUserList)) {
                        $user['is_live'] = true; 
                    }else{
                        $user['is_live'] = false; 
                    }
                    
                    $user_info[$uid] = $user;
                }
               
                array_multisort($sort_keys, SORT_DESC, $user_info);
            }

        }
        $user_info = array_slice($user_info, 0, $this->_limit);
        return !empty($user_info) ? array('teacher_num'=>$teacher_num,'student_num'=>$student_num,'active_member'=>$user_info) : false;
    }
    
}