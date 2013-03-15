<?php
abstract class uBase {
    //当前操作的班级code
    protected $class_code = 0;
    
    //调用的优先级别，值越高越先调用
    protected $level = 0;
    
    public function __construct($class_code) {
        $this->class_code = $class_code;
    }
    
    /**
     * 升级操作要执行的函数
     */
    abstract public function runGraduate();
    
    /**
     * 获取函数调用的优先级别
     */
    public function getLevel() {
        return !empty($this->level) ? $this->level : 0;
    }
    
    /**
     * 班级毕业要执行的操作
     */
    abstract public function runUpgrade($next_grade_id);
    
    /**
     * 数据打包,会将要移动到历史库中的数据按照特定的格式处理
     */
    protected function packDatas() {
        static $packedDatas = array();
        
        if(empty($packedDatas)) {
        
            //获取打包类名
            $objReflection = new ReflectionObject($this);
            $class_name = $objReflection->getName();
            $main_pack_classname = 'pack' . substr($class_name, 1);
            
            //处理异常情况
            if(!class_exists($main_pack_classname)) {
                trigger_error("致命错误, 主要的数据打包类:{$main_pack_classname}缺失!", E_USER_ERROR);
            }
            
            $mainPackObject = new $main_pack_classname();
            $mainPackObject->setIds($this->class_code);
            $mainPackObject->operation();
            $packedDatas = $mainPackObject->getResultList();
        }
        
        return $packedDatas;
    }
}