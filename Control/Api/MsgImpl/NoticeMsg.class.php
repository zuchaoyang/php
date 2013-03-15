<?php
class NoticeMsg{
    public function getToAccount($notice_id){
        $Notice_m = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $Notice_list = $Notice_m->getClassNotice($notice_id);
        
        $to_account = array();
        $class_code = array();
        if(!empty($Notice_list)){
            foreach($Notice_list as $notice_id => $notice_info){
                $class_code[$notice_info['class_code']] = $notice_info['class_code'];
            }
        }
        
        $Class_m = ClsFactory::Create("Model.mClientClass");
        $client_class_list = $Class_m->getClientClassByClassCode($class_code);
        
        if(!empty($client_class_list)){
            foreach($client_class_list as $class_code => $client_class_info) {
                $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}