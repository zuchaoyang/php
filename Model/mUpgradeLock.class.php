<?php
class mUpgradeLock extends mBase {
    protected $_dUpgradeLock = null;
    
    public function __construct() {
        $this->_dUpgradeLock = ClsFactory::Create('Data.dUpgradeLock');
    }
    
    public function getUpgradeLockByUid($uids) {
        if(empty($uids)) {
            return false;
        }
        
        return $this->_dUpgradeLock->getUpgradeLockByUid($uids);
    }
    
    public function getUpgradeLockByClassCode($class_codes) {
        if(empty($class_codes)) {
            return false;
        }
        
        return $this->_dUpgradeLock->getUpgradeLockByClassCode($class_codes);
    }
    
    public function modifyUpgradeLock($datas , $upgrade_task_id) {
        if(empty($datas) || !is_array($datas) || empty($upgrade_task_id)) {
            return false;
        }
        
        return $this->_dUpgradeLock->modifyUpgradeLock($datas, $upgrade_task_id);
    }
    
    public function addUpgradeLock($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dUpgradeLock->addUpgradeLock($datas, $is_return_id);
    }
    
    public function delUpgradeLock($upgrade_task_id) {
        if(empty($upgrade_task_id)) {
            return false;
        }
        
        return $this->_dUpgradeLock->delUpgradeLock($upgrade_task_id);
    }
}