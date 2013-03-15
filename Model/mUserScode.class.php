<?php
class mUserScode extends mBase{
    protected $_dUserScode = null;
    
    public function __construct() {
        $this->_dUserScode = ClsFactory::Create("Data.dUserScode");
    }
    
   /**
     * 存储验证码 (包括手机和邮箱 发送的验证码)
     * @param array $data
     */
	public function addUserScode($data) { 
	    
	    return $this->_dUserScode->add($data, false);
	}
	
	/**
     * 根据用户获取最后一条验证码记录
     * @param int $ids
     */
    public function getUserScodeById($ids) {
        
        return $this->_dUserScode->getInfoByPk($ids);
    }
    
    /**
     * 更新修改验证码 包括修改过期时间
     * @param array $data
     * @param ind $ids
     */
    public function modifyUserScodeById($data, $id) {
        
        return $this->_dUserScode->modify($data, $id);
    }
} 