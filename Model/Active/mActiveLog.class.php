<?php
class mActiveLog extends mBase {
    protected $_dActiveLog = null;
    public function __construct(){
        $this->_dActiveLog = ClsFactory::Create('Data.Active.dActiveLog');
    }    
    public function getActiveLogById($log_id){
        if(empty($log_id)) {
            return false;
        }
        
        return $this->_dActiveLog->getActiveLogById($log_id);
    }
    
    public function getActiveLogByClientAccount($client_account, $start_time, $end_time){
        if(empty($client_account) || empty($start_time) || empty($end_time)) {
            return false;
        }
        
        $wherearr = array();
        $wherearr[] = "add_time>$start_time";
        $wherearr[] = "add_time<=$end_time";
        $wherearr[] = "client_account in(".implode((array)$client_account).")";
        
        return $this->_dActiveLog->getInfo($wherearr);
    }
    
    /**
     * 
     */
    
    /**
     * 根据时间动作和类型得到活跃信息
     */
    public function getActive($uid, $module, $action, $time = null){
        if(empty($module) || empty($action)) {
            return false;
        }
        
        $wherearr = array(
            'client_account='.$uid, 
            'module='.$module,
            'action='.$action,
        );
        
        if(!empty($time)) {
            $wherearr[] =  'add_time > '.$time;
        }
        
        return $this->_dActiveLog->getInfo($wherearr);
    }
    
    public function addActiveLog($dataarr, $is_return_id = false){
        if(empty($dataarr)) {
            return false;
        }

        return $this->_dActiveLog->addActiveLog($dataarr, $is_return_id);
    }
    
    public function delActiveLog($log_id){
        if(empty($log_id)) {
            return false;
        }
        
        return $this->_dActiveLog->delActiveLog($log_id);
    }
}