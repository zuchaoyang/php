<?php
/**
 * 用户在线库维护
 * @author lnczx
 * 注意类名与文件名保持一致，默认小写开头
 */
class usr_last_login_time {
    
    /**
     * 主函数执行
     * 更新用户最后登录时间
     */
    public function run($client_account = NULL) {
                
        if (empty($client_account)) {            
            if(C('LOG_RECORD')) Log::write('task UserLastLoginTime run error:',  "client_account is null", Log::ERR);
            return false;
        }
        
        $mUser=ClsFactory::Create('Model.mUser');
        $mUser->modifyUserClientAccount(array('lastlogin_date' =>time()), $client_account);    

        return true;
    }

}