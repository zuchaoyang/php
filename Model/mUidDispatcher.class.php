<?php
class mUidDispatcher extends mBase {
    protected $_dUidDispatcher = null;
    
    public function __construct() {
        $this->_dUidDispatcher = ClsFactory::Create('Data.dUidDispatcher');
    }
    
    /**
     * 通过分发起获取单个账号信息
     */
    public function getUidByUidDispatcher() {
        $max_test_times = 500;
        $test_times = 1;
        do {
            $uid = $this->addUidDispatcher();
            $uid = reset($this->filterLockedUids($uid));
            
        } while(empty($uid) && $test_times++ < $max_test_times);
        
        return $uid;
    }
    
    /**
     * 通过分发器批量获取账号信息
     * @param $nums
     */
    public function getUidBatByUidDispatcher($nums) {
        $nums = max(intval($nums), 1);
        
        $total_uids = array();
        //记录剩余的数目，因为程序可能通过多次尝试获取到所有的uid
        $remainder_nums = $nums;
        do {
            $uids = array();
            for($i=1; $i<=$remainder_nums; $i++) {
                $uids[] = $this->addUidDispatcher();
            }
            $uids = $this->filterLockedUids($uids);
            $total_uids = array_merge((array)$total_uids, (array)$uids);
            
            $remainder_nums = $nums - count($total_uids);
        } while($remainder_nums > 0);
        
        return !empty($total_uids) ? $total_uids : false;
    }
    
    public function addUidDispatcher() {
        return $this->_dUidDispatcher->addUidDispatcher();
    }
    
    public function delUidDispatcher($uid) {
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dUidDispatcher->delUidDispatcher($uid);
    }
    
    public function setAutoIncrement($auto_increment) {
        return $this->_dUidDispatcher->setAutoIncrement($auto_increment);
    }
    
    /**
     * 过滤账号是否被锁定
     */
    protected function filterLockedUids($uids) {
        if(empty($uids)) {
            return false;
        }
        
        $uids = (array)$uids;
        
        $mAccountLock = ClsFactory::Create('Model.mAccountLock');
		$lock_list = $mAccountLock->getAccountLockById($uids);
		$locked_accounts = array_keys($lock_list);
		unset($lock_list);
		
		if (!empty($locked_accounts)) {
		    foreach($locked_accounts as $account) {
		        if(($key = array_search($account, $uids)) !== false) {
		            unset($uids[$key]);
		        }
		    }
		}
		
		return !empty($uids) ? $uids : false;
    }
    
}