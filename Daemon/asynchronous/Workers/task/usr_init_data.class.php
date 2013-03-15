<?php
/**
 * 用户登录数据初始化
 * @author lnczx
 *
 */
class usr_init_data {
    
    /**
     * 主函数执行
     */
    public function run($client_account = NULL) {
        
        if (empty($client_account)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "client_account is null", Log::ERR);
            return false;
        }

    	$m = ClsFactory::Create('RModel.mUserVm');
        return $m->init_user_data($client_account);
    }

}