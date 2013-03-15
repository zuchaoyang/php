<?php
//锁定的时间限制,单位为(分钟)
define('MAX_LOCK_TIME', 5);

import("@.Control.Api.Upgrade.Extra.uAutoLoader");
import("@.Control.Api.Upgrade.Extra.packAbstract");
import("@.Control.Api.Upgrade.Extra.uBase");

/**
 * 
 * @author Administrator
 * 1. 如何控制执行的先后顺序
 * 2. 所有函数的入口参数只有一个class_code;
 * 3. 因为ams和前台sns都有可能触发
 * 
 */
class UpgradeApi extends ApiController {
    protected $object_list = array();
    protected $classinfo = array();
    protected $class_code = 0;
    protected $grade_type = 0;
    protected $grade_config = array();
    
    public function _initialize() {
        parent::_initialize();
        header('Content-Type:text/html;charset=utf-8');
    }
    
    /**
     * 升级主程序
     */
    public function index() {
        $class_code  = $this->objInput->getInt('class_code');
        $uid         = $this->objInput->getStr('uid');
        $secret_key  = $this->objInput->getStr('secret_key');
        
//        $uid = 11070004;
//        $class_code = 94;
        
        /*****************************************************
         * 注明：为什么没有验证操作用户的合法性?
         * 1. 对于用户权限的验证，可以根据$secret_key的值去验证，该key用户没有办法伪造，并且一个班级对于一个。
         * 2. 针对于用户的uid信息只是用户升级日志信息表，对业务的正确性无影响。
         * 3. 避免检测uid的合法性可以减少sql查询；
         ****************************************************/
        if(empty($uid)) {
            $this->ajaxReturn(null, '用户信息不能为空!', -1, 'json');
        }
        
        import('@.Control.Api.Upgrade.Core.reflectClassInfo');
        $classReflectObj = new reflectClassInfo($class_code);
        
        if($classReflectObj->getSecretKey() != $secret_key) {
           $this->ajaxReturn(null, '非法操作', -1, 'json');
        }
        
        //判断班级是否需要升级
        if(!$classReflectObj->needUpgrade()) {
            $this->ajaxReturn(null, '班级已经升级到最新!', 1, 'json');
        }
        
        if($classReflectObj->isComplete()) {
            $this->ajaxReturn(null, '班级升级已完成!', 1, 'json');
        }
        
        if($classReflectObj->isLocked() || $classReflectObj->isDoing()) {
            $this->ajaxReturn(null, '班级升级中，请等待!', -1, 'json');
        }
        
        //加锁
        $lock_id = $classReflectObj->lock();
        
        //加载要调用的对象信息
        $this->loadObjectList($class_code);
        //排序要操作的对象信息
        $this->sortByLevel();
        //执行升级操作
        if($classReflectObj->isGraduate()) {
            $this->runGraduate();
        } else {
            $this->runUpgrade($classReflectObj->getNextGradeId());
        }

        //解锁
        $unlock_datas = array(
            'add_account' => $uid, 
            'end_time' => time()
        );
        $classReflectObj->unlock($lock_id, $unlock_datas);
        
        $this->ajaxReturn(null, '班级升级已成功!', 1, 'json');
    }
    
    /**
     * 自动加载要操作的对象信息
     */
    private function loadObjectList($class_code) {
        //定位要加载目录
        $dir_path = WEB_ROOT_DIR . "/Control/Api/Upgrade";
        
        $dir = dir($dir_path);
        while(($file = $dir->read()) !== false) {
            $class_name = reset(explode('.', $file));
            if(in_array($file, array('.', '..')) || is_dir($dir_path . "/" . $file)) {
                continue;
            }
            $this->object_list[] = new $class_name($class_code);
        }
    }
    
    /**
     * 按照执行等级排序要操作的对象
     */
    private function sortByLevel() {
        if(empty($this->object_list)) {
            return false;
        }
        
        $sort_keys = array();
        foreach($this->object_list as $key=>$object) {
            $sort_keys[$key] = $object->getLevel();
        }
        
        array_multisort($sort_keys, SORT_DESC, SORT_NUMERIC, $this->object_list);
    }
    
    /**
     * 执行升级操作
     */
    private function runUpgrade($next_grade_id) {
        foreach($this->object_list as $key=>$object) {
            $object->runUpgrade($next_grade_id);
            unset($this->object_list[$key]);
        }
    }
    
    /**
     * 执行毕业
     */
    private function runGraduate() {
        foreach($this->object_list as $key=>$object) {
            $object->runGraduate();
            unset($this->object_list[$key]);
        }
    }
}