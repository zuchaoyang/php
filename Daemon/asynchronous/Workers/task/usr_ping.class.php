<?php
/**
 * 加入在线用户库
 * @author lnczx
 *
 */
class usr_ping {
    
    /**
     * 主函数执行
     */
    public function run($client_account = NULL) {
        
        if (empty($client_account)) {
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "client_account is null", Log::ERR);
            return false;
        }

    	$m = ClsFactory::Create('RModel.Common.mSetLiveUser');
        return $m->ping($client_account);
    }

}