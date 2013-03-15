<?php
class mResourceVersion extends mBase{
    protected $_dResourceVersion = null;
    public function __construct() {
        $this->_dResourceVersion = ClsFactory::Create("Data.Resource.dResourceVersion");
    }
    
    public function getAllResourceVersion() {
        return $this->_dResourceVersion->getInfo();
    }
    
    public function getResourceVersionById($version_ids) {
        if(empty($version_ids)) {
            return false;
        }
        
        return $this->_dResourceVersion->getResourceVersionById($version_ids);
    }
    
    public function addResourceVersion($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceVersion->addResourceVersion($dataarr, $is_return_id);
    }
    
    public function delResourceVersion($version_id) {
        if(empty($version_id)) {
            return false;
        }
        
        return $this->_dResourceVersion->delResourceVersion($version_id);
    }
    
	/**
      * 去掉章节的前缀
      * @param $str
      */
     private function dropPrefix($str) {
         if(empty($str)) {
             return false;
         }
         
         $arr = explode('_', $str);
         if(count($arr) > 1 && preg_match("/^[a-zA-Z0-9]+$/", $arr[0])) {
             unset($arr[0]);
         }
         
         return implode('_', $arr);
     }
}