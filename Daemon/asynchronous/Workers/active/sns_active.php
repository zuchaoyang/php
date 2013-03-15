<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class sns_active extends BackGroundController {

    public function run($job, &$log) {
        $workload = $job->workload();
        $workload = unserialize($workload);

        if($this->isDailyLimit($workload['uid'], $workload['module'], $workload['action'])) {
            $this->addActiveinfo($workload['uid'], $workload['module'], $workload['action']);
        }
        
        return true;
    }
    
    /**
     * 得到用户活跃度的配置
     */
    private function getActiveConfig(){
        C(include_once WEB_ROOT_DIR . '/Config/SnsActive/config.php');
        $active_config_action = C('action');
        $active_config_module = C('module');
        return array('action' => $active_config_action, 'module' => $active_config_module);
    }
    
    /**
     * 添加用户的活跃度
     * @param unknown_type $module
     * @param unknown_type $action
     */
    private function addActiveinfo($uid, $module, $action){
        $active_config = $this->getActiveConfig();
        $value = $active_config['module'][$module][$action]['value'];
        $msg = $active_config['action'][$action] . $active_config['module'][$module]['msg'];
        $ative_info = array(
            'client_account' => $uid,
            'value' => $value,
            'message' => $msg,
            'add_time' => time(),
            'module' => $module,
            'action' => $action,
        );
        
        $mActiveLog = ClsFactory::Create("Model.Active.mActiveLog");
        $active_log_id = $mActiveLog->addActiveLog($ative_info, true);
        
        if(!empty($active_log_id)){
            $mActive = ClsFactory::Create("Model.Active.mActive");
            $active_result = $mActive->getActiveByClientAccount($uid);
            if(!empty($active_result)){
                $active_result = reset($active_result);
                $active_id = key($active_result);
                $active_info = array(
                    'client_account' => $uid,
                    'value' => "%value+$value%"
                );
                
                $mActive->modifyActive($active_info, $active_id);
            }else{
                $active_info = array(
                    'client_account' => $uid,
                    'value' => $value,
                );
                
                $active_id = $mActive->addActive($active_info, true);
                if(empty($active_id)) {
                    $mActiveLog->delActiveLog($active_log_id);
                }
            }
        }
    }
    
    /**
     * 判断该用户是否要添加用户活跃值
     * @param unknown_type $module
     * @param unknown_type $action
     */
    private function isDailyLimit($uid, $module, $action) {
        
        $time = strtotime(date('Y-m-d'));
        $mActiveLog = ClsFactory::Create("Model.Active.mActiveLog");
        $ActiveLog_list = $mActiveLog->getActive($uid, $module, $action, $time);

        //当天无记录
        if (empty($ActiveLog_list)) {
            return true;
        }

        $active_config = $this->getActiveConfig();
        
        //查找配置每天上限值
        $day_limit = $active_config['module'][$module][$action]['day_limit'];
        if (empty($day_limit)) {
            return false;
        }
        
        $max = 0;
        foreach ($ActiveLog_list as $key => $value) {
            if (isset($value['value'])) {
                $max += (int)$value['value'];
            }
        }
        
        return $max >=  (int)$day_limit ? false : true;
    }
}