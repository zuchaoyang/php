<?php
class dUpgradeLock extends dBase{
	protected $_tablename = 'wmw_upgrade_lock';
    protected $_fields = array(
        'upgrade_task_id',
        'class_code',
        'is_complete',
        'start_time',
        'end_time',
        'add_account',
    	'upgrade_year'
    );
    protected $_pk = 'upgrade_task_id';
    protected $_index_list = array(
        'upgrade_task_id',
    	'class_code',
        'add_account',
    );
    
    public function getUpgradeLockByUid($uids) {
        return $this->getInfoByFk($uids, 'add_account');
    }
    
    public function getUpgradeLockByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code');
    }
    
    public function modifyUpgradeLock($datas , $upgrade_task_id) {
        return $this->modify($datas, $upgrade_task_id);
    }
    
    public function addUpgradeLock($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    public function delUpgradeLock($upgrade_task_id) {
        return $this->delete($upgrade_task_id);
    }
}
