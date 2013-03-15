<?php
/**
 * 抽象类，主要用户数据的迁移是的逻辑处理
 * @author Administrator
 *
 */
abstract class packAbstract {
    protected $ids = array();
    protected $childNodes = array();
    protected $info_list = array();
    
    //初始化数据列表
    abstract protected function initInfoList();
    /**
     * 添加孩子节点信息
     * @param $field
     * @param $object
     */
    public function addChildNode($field, $object) {
        $this->childNodes[$field] = $object;
    }
    
    public function setIds($ids) {
        $this->ids = $ids;
    }
    
    /**
     * 通过字段名获取数组中的数据信息
     * @param $field
     */
    public function getIdsByField($field) {
        if(empty($this->info_list) || empty($field)) {
            return false;
        }
        
        $return_ids = array();
        foreach($this->info_list as $info) {
            if(empty($info[$field])) {
                continue;
            }
            $return_ids[] = $info[$field];
        }
        
        return array_unique($return_ids);
    }
    
    /**
     * 通过key获取当前数据列表中的信息
     * @param $key
     */
    public function getInfoBykey($key) {
        return !empty($this->info_list[$key]) ? $this->info_list[$key] : false;
    }
    
    /**
     * 向下初始化数据
     */
    protected function downOperation() {
        if(empty($this->childNodes)) {
            return false;
        }
        foreach($this->childNodes as $field=>$object) {
            $object->setIds($this->getIdsByField($field));
        }
    }
    
    /**
     * 回溯处理
     */
    protected function recallOperation() {
         if(empty($this->childNodes)) {
             return false;
         }
         
         foreach($this->info_list as $key=>$info) {
            $history_json = array();
            foreach($this->childNodes as $field=>$object) {
               $history_json[$field] = $object->getInfoBykey($info[$field]);
            }
            $this->info_list[$key] = array_merge((array)$info, array('history_json' => $history_json));
        }
    }
    
    /**
     * 对外调用接口
     */
    public function operation() {
        $this->initInfoList();
        
        //设置向下的处理
        $this->downOperation();
        
        //调用的孩子节点的operation方法
        foreach($this->childNodes as $field=>$object) {
            $object->operation();
        }
        
        //回溯处理
        $this->recallOperation();
    }
    
    /**
     * 获取最终处理结果
     * todolist json
     */
    public function getResultList() {
        $json_info_list = array();
        foreach((array)$this->info_list as $key=>$info) {
            if(isset($info['history_json'])) {
                //$info['history_json'] = json_encode($info['history_json']);
            }
            $json_info_list[$key] = $info;
        }
        
        return $json_info_list;
    }
}