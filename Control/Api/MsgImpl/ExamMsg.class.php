<?php
class ExamMsg{
    public function getToAccount($class_code){
        
        $Class_m = ClsFactory::Create("Model.mClientClass");
        $client_class_list = $Class_m->getClientClassByClassCode($class_code,array('client_type'=>CLIENT_TYPE_STUDENT,'client_type'=>CLIENT_TYPE_FAMILY));
        
        $to_account = array();
        if(!empty($client_class_list)){
            foreach($client_class_list as $class_code => $client_class_info){
                $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}