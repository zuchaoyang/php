<?php
/**
 * author:xuewen<13651268009@139.com>
 * 功能：Active manage
 * 说明：用户活跃度接口
 */
class ActiveApi extends ApiController {
    
    public function __construct() {
        parent::__construct();
    }    
    
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 用户活跃度的添加异步队列
     * @param $uid
     * @param $module
     * @param $action
     */
    public function setactive($uid, $module, $action) {
        if(empty($uid) || empty($module) || empty($action)) {
            return false;
        }
        
        $param_list = array(
            "module" => $module,
            "action" => $action,
            "uid" => $module,
        );
        
        $param_list = serialize($param_list);
        
        $result = Gearman::send('sns_active', $param_list, PRIORITY_NORMAL, false);
    }
    
    /**
     * 得到用户的活跃度值
     * @param unknown_type $client_account
     */
    public function client_active($client_account) {
        $mActive = ClsFactory::Create("Model.Active.mActive");
        $Active_list = $mActive->getActiveByClientAccount($client_account);
        return !empty($Active_list) ? reset($Active_list) : false;
    }
    
    /**
     * 得到用户活跃度记录
     * @param unknown_type $client_account
     */
    public function client_active_log($client_account, $start_time=null, $end_time=null){
        $end_time = !empty($end_time) ? $end_time : mktime(0, 0, 0, date("m")   , date("d")+1, date("Y"))-1; //本天结束时间
        $start_time = !empty($start_time) ? $start_time : mktime(0, 0, 0, date("m")   , date("d"), date("Y")); //本天开始时间
        
        $mActiveLog = ClsFactory::Create("Model.Active.mActiveLog");
        $ActiveLog_list = $mActiveLog->getActiveLogByClientAccount($client_account,$start_time,$end_time);
        $new_ActiveLog_list = array();
        $today_val = 0;
        
        
        C(include_once WEB_ROOT_DIR . '/Config/SnsActive/config.php');
        $mUser = ClsFactory::Create("Model.mUser");
        $UserInfog = $mUser->getClientAccountById($client_account);
        $active_list = C("active_list");
        $current_person_active = $active_list[$UserInfog[$client_account]['client_type']];
        $action_list = C("action");
        $module_list = C("module");
        
        if(!empty($ActiveLog_list)) {
            foreach($ActiveLog_list as $log_id => $active_log) {
                if(isset($new_ActiveLog_list[$active_log['module'].$active_log['action']])){
                    $new_ActiveLog_list[$active_log['module'].$active_log['action']]['value'] += $active_log["value"];
                }else{
                    $new_ActiveLog_list[$active_log['module'].$active_log['action']] = $active_log;
                }
                
                $today_val += $active_log["value"];
            }
        }
        
        foreach($current_person_active as $module => $action){
            
            if(empty($new_ActiveLog_list[$module.$action])){
                $new_ActiveLog_list[$module.$action]["message"] = $action_list[$action].$module_list[$module]['msg'];
                $new_ActiveLog_list[$module.$action]["value"] = 0;
            }

            $new_ActiveLog_list[$module.$action]["day_limit"] = $module_list[$module][$action]["day_limit"];
            $new_ActiveLog_list[$module.$action]["add_value"] = $module_list[$module][$action]["value"];
        }
        
        return !empty($new_ActiveLog_list) ? array($new_ActiveLog_list, $today_val) : false;
    }

    public function getactivemember() {
        $class_code = $this->objInput->getInt('class_code');
        import('@.Control.Api.ActiveImpl.Activemember');
        
        $Activemember = new Activemember();
        $active_list = $Activemember->getActivemember($class_code);
        
        if(empty($active_list)) {
            $this->ajaxReturn(null,"获取活跃成员失败！", -1, 'json');
        }
        $this->ajaxReturn($active_list,"获取活跃成员成功！", 1, 'json');
    }
}