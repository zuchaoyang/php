<?php
class HomdeworkMsg{
    
    public function getToAccount($homework_id){
        $homework_m = ClsFactory::Create("Model.ClassHomework.mClassHomeworkSend");
        $homework_list = $homework_m->getHomeworkSendByhomeworkid($homework_id);
        
        $to_account = array();
        if(!empty($homework_list)){
            foreach($homework_list as $id => $homework_info){
                $to_account[$homework_info['client_account']] = $homework_info['client_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}