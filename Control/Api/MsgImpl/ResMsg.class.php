<?php
class ResMsg{
    
    public function getToAccount($res_id){
        $Res_m = ClsFactory::Create("Model.Message.mMsgRequire");
        $Res_list = $Res_m->getMsgResponseById($res_id);
        
        $to_account = array();
        if(!empty($Res_list)){
            foreach($Res_list as $res_id => $Res_info){
                $to_account[$Res_info['to_account']] = $Res_info['to_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}