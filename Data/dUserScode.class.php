<?php
class dUserScode extends dBase{
    public $_tablename = 'wmw_user_scode';
    public $_fields = array( 
        'client_account', 
    	'client_email',
    	'security_code', 
    	'end_time'
    );
    public $_pk = 'client_account';
    public $_index_list = array(
        'client_account'
    );
    
    /**
     * 存储验证码 (包括手机和邮箱 发送的验证码)
     * @param array $data
     */
	public function addUserScode($data) { 
	    
	    return $this->add($data, false);
	}
	
	/**
     * 根据用户获取最后一条验证码记录
     * @param int $ids
     */
    public function getUserScodeById($ids) {
        
        return $this->getInfoByPk($ids);
    }
    
    /**
     * 更新修改验证码 包括修改过期时间
     * @param array $data
     * @param ind $ids
     */
    public function modifyUserScodeById($data, $id) {
        
        return $this->modify($data, $id);
    }
}