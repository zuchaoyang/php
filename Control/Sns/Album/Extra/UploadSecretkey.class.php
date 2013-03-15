<?php
/**
 * 
 * @author Administrator
 * 上传时的密钥校验
 */
class UploadSecretkey {
    public function getSecretkey($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getClientAccountById($uid);
        $user = & $userlist[$uid];
        
        if(empty($user)) {
            return false;
        }
        
        return $this->getMd5key($user);
    }
    
    /**
     * 校验密钥
     * @param $uid
     * @param $secret_key
     */
    public function checkSecretkey($uid, $secret_key) {
        if(empty($uid) || empty($secret_key)) {
            return false;
        }
        
        return ($this->getSecretkey($uid) == $secret_key) ? true : false;
    }
    
    /**
     * 获取加密key
     * @param $user
     */
    private function getMd5key($user) {
        return md5($user['client_account'] . substr($user['client_password '], 0, 16) . $user['client_type']);
    }
}