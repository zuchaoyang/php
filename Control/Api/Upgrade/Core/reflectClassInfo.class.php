<?php
/**
 * @author Administrator
 * 班级升级的检测反射类
 */
Class reflectClassInfo {
    private $classinfo = array();
    private $class_code = 0;
    
    public function __construct($class_code) {
        $this->class_code = $class_code;
        
        C(include WEB_ROOT_DIR . "/Control/Api/Upgrade/Extra/Config.php");
        $this->grade_config = C('grade_config');
        
        $this->initClassInfo();
        $this->check();
    }
    
    /**
     * 检测班级信息是否存在
     */
    protected function check() {
        if(empty($this->classinfo)) {
            throw new Exception('班级信息不存在!', -1);
        }
    }
    
    /**
     * 获取下一年级的grade_id值
     */
    public function getNextGradeId() {
        if($this->isGraduate()) {
            return false;
        }
        
        $grade_id = intval($this->classinfo['grade_id']);
        return $this->grade_config[$this->grade_type][$grade_id];
    }
    
    /**
     * 判断是否是毕业
     */
    public function isGraduate() {
        $grade_id = intval($this->classinfo['grade_id']);
        if(is_null($this->grade_config[$this->grade_type][$grade_id])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 判断是否是升级
     */
    public function isUpgrade() {
        $grade_id = intval($this->classinfo['grade_id']);
        if(!is_null($this->grade_config[$this->grade_type][$grade_id])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 判断班级是否需要升级
     */
    public function needUpgrade() {
        if(empty($this->classinfo)) {
            return false;
        }
        
        //班级的最后升级时间
        $upgrade_year = $this->classinfo['upgrade_year'];
        //系统当前时间
        $year = date('Y', time());
        
        //正常的升迁年份,需要限制升级月份
        $month = constant('UPGRADE_MONTH');
        $month = $month > 0 && $month <= 12 ? $month : 8;
        $month_list = range($month, 12, 1);
        
        $current_month = intval(date('m'));
        if(($upgrade_year == $year - 1 && in_array($current_month, $month_list)) || $upgrade_year < $year - 1) {
            return true;
        }
        
        return false;
    }
    
      /**
     * 判断升级是否已经完成
     * @param $class_code
     */
    public function isComplete() {
        list($upgrade_year, $current_lock) = $this->getUpgradeyearAndCurrentLock();
        
        return $current_lock['is_complete'] == 2 ? true : false;
    }
    
    /**
     * 判断升级是否正在进行中
     */
    public function isDoing() {
        list($upgrade_year, $current_lock) = $this->getUpgradeyearAndCurrentLock();
        
        return $current_lock['is_complete'] == 1 ? true : false;
    }
    
    /**
     * 判断是否被锁定
     * @param $class_code
     */
    public function isLocked() {
        /**************************************************************
         * 判定被锁定的条件: (补集为非锁定状态)
         * 1. 班级信息对应的upgrade_year下的班级信息在wmw_upgrade_lock表中存在；
         * 2. 并且记录的锁定时间在系统允许的时间范围内；
         *************************************************************/
        list($upgrade_year, $current_lock) = $this->getUpgradeyearAndCurrentLock();
        //判断是否锁定的其他条件
        if(!empty($current_lock['start_time']) && $current_lock['start_time'] + MAX_LOCK_TIME * 60 <= time()) {
            return true;
        }
        
        return false; 
    }
    
    /**
     * 锁定记录, 成功返回锁id
     * @param $class_code
     */
    public function lock() {
        if(empty($this->class_code)) {
            return false;
        }
        
        list($upgrade_year, $current_lock) = $this->getUpgradeyearAndCurrentLock();
        
        $mUpgradeLock = ClsFactory::Create('Model.mUpgradeLock');
        
        $lock_id = 0;
        if(!empty($current_lock)) {
            $lock_datas = array(
                'is_complete' => 1,
                'start_time' => time(),
                'end_time' => 0,
                'add_account' => $this->user['client_account'],
                'upgrade_year' => $upgrade_year
            );
            $effect_rows = $mUpgradeLock->modifyUpgradeLock($lock_datas, $current_lock['upgrade_task_id']);
            
            $lock_id = ($effect_rows !== false) ? $current_lock['upgrade_task_id'] : 0;
        } else {
             $lock_datas = array(
                'class_code' => $this->class_code,
                'is_complete' => 1,
                'start_time' => time(),
                'end_time' => 0,
                'add_account' => $this->user['client_account'],
                'upgrade_year' => $upgrade_year,
            
            );
            $upgrade_task_id = $mUpgradeLock->addUpgradeLock($lock_datas, true);
            
            $lock_id = !empty($upgrade_task_id) ? $upgrade_task_id : 0;
        }
        
        return $lock_id ? $lock_id : false;
    }
    
    /**
     * 解锁
     * @param $lock_id
     */
    public function unlock($lock_id, $datas = null) {
        if(empty($lock_id)) {
            return false;
        }
        
        $lock_datas = array(
            'is_complete' => 2,
        );
        $lock_datas = array_merge($lock_datas, (array)$datas);
        
        $mUpgradeLock = ClsFactory::Create('Model.mUpgradeLock');
        $effect_rows = $mUpgradeLock->modifyUpgradeLock($lock_datas, $lock_id);
        
        return $effect_rows !== false ? true : false;
    }
    
    /**
     * 获取班级的加密信息
     */
    public function getSecretKey() {
        return md5(UPGRADE_SECRET_KEY . $this->class_code);
    }
    
	/**
     * 初始化班级的相关信息
     */
    private function initClassInfo() {
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $classinfo_list = $mClassInfo->getClassInfoById($this->class_code);
        $classinfo = & $classinfo_list[$this->class_code];
        
        $grade_type = intval($classinfo['school_info']['grade_type']);
        $grade_type = in_array($grade_type, array(1, 2)) ? $grade_type : 1;
        
        $this->classinfo = $classinfo;
        $this->grade_type = $grade_type;
    }
    
    /**
     * 获取当前班级的升级年份和当前的锁信息
     * @param $class_code
     */
    private function getUpgradeyearAndCurrentLock() {
        static $static_lock_list = array();
        
        $class_code = $this->class_code;
        if(empty($class_code)) {
            return false;
        }
        
        if(empty($static_lock_list[$class_code])) {
            $upgrade_year = intval($this->classinfo['upgrade_year']);
            if($upgrade_year <= 0) {
                $upgrade_year = date('Y') - 1;
                $datas = array(
                    'upgrade_year' => $upgrade_year,
                );
                $mClassInfo = ClsFactory::Create('Model.mClassInfo');
                $mClassInfo->modifyClassInfo($datas, $class_code);
            }
            
            $mUpgradeLock = ClsFactory::Create('Model.mUpgradeLock');
            $upgrade_lock_arr = $mUpgradeLock->getUpgradeLockByClassCode($class_code);
            $upgrade_lock_list = & $upgrade_lock_arr[$class_code];
            
            $current_lock = array();
            if(!empty($upgrade_lock_list)) {
                //通过班级信息的upgrade_year过滤数据
                foreach((array)$upgrade_lock_list as $upgrade_task_id=>$lock) {
                    if($lock['upgrade_year'] == $upgrade_year) {
                        $current_lock = $lock;
                        break;
                    }
                }
                unset($upgrade_lock_list, $upgrade_lock_arr);
            }
            $static_lock_list[$class_code] = array($upgrade_year, $current_lock);
        }
        
        return isset($static_lock_list[$class_code]) ? $static_lock_list[$class_code] : false;
    }
}