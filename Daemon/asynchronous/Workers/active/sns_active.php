<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class sns_active extends BackGroundController {

    public function run($job, &$log) {
        $workload = $job->workload();
        $workload = unserialize($workload);

        if($this->isDailyLimit($workload['uid'], $workload['module'], $workload['action'])) {
            $this->addActiveinfo($workload['uid'], $workload['module'], $workload['action']);
            $this->addHeaderActiveInfo($workload['uid'], $workload['module'], $workload['action']);
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
     * 
     */
    private function addHeaderActiveInfo($uid, $module, $action) {
        
        $mUserVm = ClsFactory::Create("RModel.mUserVm");
        $current_user_info = $mUserVm->getUserBaseByUid($uid);
        $client_type = $current_user_info[$uid]['client_type'];
        
        if($client_type == 1 || $client_type === false || !($action == 21 || $module == 301)) {
            return false;
        }
        
        $class_code = key($current_user_info[$uid]["client_class"]);
        $mClassInfo = ClsFactory::Create("RModel.Common.mHashClass");

        $ClassInfo = $mClassInfo->getClassById($class_code);

        $module = $module == 301 ? 307 : $module;
        $action = $action == 21 && $client_type == 0 ? 25 : $action;
        $action = $action == 21 && $client_type == 3 ? 26 : $action;

        $headteacher_account = $ClassInfo['headteacher_account'];

        $this->addActiveinfo($headteacher_account, $module, $action);
    }
    
    
    /**
     * 添加用户的活跃度
     * @param unknown_type $module
     * @param unknown_type $action
     */
    private function addActiveinfo($uid, $module, $action){
        $active_config = $this->getActiveConfig();
        $value = $active_config['module'][$module][$action]['value'];
        $msg = $active_config['module'][$module]['msg'];
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
        $ActiveLog_list = $mActiveLog->getActive($uid, $module, $action);

        $active_config = $this->getActiveConfig();
        
        
        $is_once = $active_config['module'][$module][$action]['is_once'];
        
        if (!empty($is_once)) {
            if(empty($ActiveLog_list)){
                return true;
            }else{
                return false;
            }
        }else{
            //当天无记录
            $ActiveLog_list = $mActiveLog->getActive($uid, $module, $action, $time);
            if (empty($ActiveLog_list)) {
                return true;
            }
        }
        
        
        //查找配置每天上限值
        $day_limit = $active_config['module'][$module][$action]['day_limit'];

        $max = 0;
        foreach ($ActiveLog_list as $key => $value) {
            if (isset($value['value'])) {
                $max += (int)$value['value'];
            }
        }

        return $max >=  (int)$day_limit ? false : true;
    }
}