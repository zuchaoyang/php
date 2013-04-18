<?php
import('RData.RedisFeedKey');

class dStringRequest extends rBaseString {
    
    /**
     * 获取相应的Key
     * @param $id = client_account
     */
    public function getKey($id) {
        if(empty($id)) {
            return false;
        }

        return RedisFeedKey::getUserReqMsgKey($id);
    }
}
