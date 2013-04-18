<?php
class Checkin{
    //签到
    public function add_sign($check_account) {
        
        $mCheckin = ClsFactory::Create('Model.Checkin.mCheckin');
        
        if($this->is_checkin($check_account)) {
            return true;    
        }
        
        $dataarr = array(
            'client_account' => $check_account,
            'add_time' => time()
        );
        
        
        $resault = $mCheckin->add_checkin($dataarr);
        
        return $resault;
    }
    
    //检测是否已签到
    public function is_checkin($checkin_account) {
        if(empty($checkin_account)) {
            return false;
        }
        
        $mCheckin = ClsFactory::Create('Model.Checkin.mCheckin');
        $today_time = strtotime(date('Y-m-d', time()));
        $wheresql = array(
            'client_account = '.$checkin_account,
            "add_time>$today_time",
            'add_time<' . ($today_time+86400)
        );
        
        $resault = $mCheckin->getCheckinByInfo($wheresql);
        
        return !empty($resault) ? true : false;
    }
    
    
}