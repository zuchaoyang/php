<?php
include_once WEB_ROOT_DIR . "/Control/Insert/InsertInterface.php";

class InsertAmsForAmsControlMenu implements InsertInterface {
    public function run($params, & $smarty) {
        $login_uid = $params['uid'];
        $school_id = $params['school_id'];
        $grade_id = $params['grade_id'];
        $class_code = $params['class_code'];
        $mod = $params['mod'];
        
        $show_params_keys = array(
            'uid',
            'school_id', 
            'grade_id', 
            'class_code',
        );
        
        $params_arr = array();
        
        $login_uid = is_array($login_uid) ? reset($login_uid) : $login_uid;
        $login_uid = intval($login_uid);
        
        if(empty($login_uid)) {
            return false;
        }
        $mUser = ClsFactory::Create('Model.mUser');
        
        $userlist = $mUser->getUserByUid($login_uid);
        $user = $userlist[$login_uid];
        
        if(!empty($user['school_info'])) {
            $school_info = reset($user['school_info']);
            $operation_strategy = $school_info['operation_strategy'];
            if($operation_strategy == OPERATION_STRATEGY_DEFAULT) {
                $operation_strategy = false;
            }
        }
        
        if(!empty($user['class_info'])) {
            if(!isset($user['class_info'][$class_code])) {
                $class_code = key($user['class_info']);
            }
            $grade_id = intval($user['class_info'][$class_code]['grade_id']);
        }
        
        $total_params = array(
            'uid' => $login_uid,
            'school_id' => $school_id,
            'class_code' => $class_code,
            'grade_id' => $grade_id,
        );
        
        $show_params_arr = array();
        if(!empty($show_params_keys)) {
            foreach($show_params_keys as $key) {
                if(!empty($total_params[$key])) {
                    $show_params_arr[$key] = $key . "/" . $total_params[$key];
                }
            }
        }
        $params_str = !empty($show_params_arr) ? "/" . implode("/", $show_params_arr) : "";
        
        $smarty->assign('params_str', $params_str);
        $smarty->assign('mod', $mod);
        $smarty->assign('operation_strategy', $operation_strategy);
        $smarty->assign('open_oldaccount_import', constant('IS_SET_OLDACCOUNT_IMPORT'));
        
        return $smarty->fetch('./Public/amscontrol_menu.html'); 
    }
}