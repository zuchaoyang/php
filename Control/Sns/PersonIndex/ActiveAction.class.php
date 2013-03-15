<?php
class ActiveAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function showActiveInfo(){
        $current_uid = $this->user["client_account"];
        import("@.Control.Api/ActiveApi");
        $ActiveApi = new ActiveApi();
        $active_num = $ActiveApi->client_active($current_uid);
        $active_num = reset($active_num);
        list($client_log_list, $today_value) = $ActiveApi->client_active_log($current_uid);
        $this->assign("active_num", $active_num["value"]);
        $this->assign("active_log_list", $client_log_list);
        $this->assign("today_value", $today_value);
        $this->assign("current_uid", $current_uid);
        $this->display("active_info");
    }
    
    public function showActiveRule(){
        $this->display("active_rule");
    }
}