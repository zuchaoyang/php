<?php
/**
 * 任务抽象类
 * @author lnczx
 *
 */
abstract class TaskAbstract {
    
    /**
     * 主函数执行
     */
    public function run() {
        //extend me
    }
    
    
    public function before_run() {
       // [option]
    }
    
    public function after_run() {
       // [option] 
    }
}