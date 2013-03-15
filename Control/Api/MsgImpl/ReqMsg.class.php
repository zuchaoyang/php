<?php
class ReqMsg{
    
    public function getToAccount($req_id){
        $Req_m = ClsFactory::Create("Model.Message.mMsgRequire");
        $Req_list = $Req_m->getMsgRequireById($req_id);
        
        $to_account = array();
        if(!empty($Req_list)){
            foreach($Req_list as $req_id => $Req_info){
                $to_account[$Req_info['to_account']] = $Req_info['to_account'];
            }
        }
        
        return !empty($to_account) ? $to_account : false;
    }
}