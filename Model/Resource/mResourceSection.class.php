<?php
class mResourceSection extends mBase{
    protected $_dResourceSection = null;
    
    public function __construct() {
        $this->_dResourceSection = ClsFactory::Create("Data.Resource.dResourceSection");
    }
    
     public function getResourceSectionById($section_ids) {
         if(empty($section_ids)) {
             return false;
         }
         
         $section_list = $this->_dResourceSection->getResourceSectionById($section_ids);

         foreach($section_list as $section_id => $section_info) {
            $section_info['section_name'] = $this->dropPrefix($section_info['section_name']);
            $section_list[$section_id] = $section_info;
        }
        
         return $section_list;
    }
    
    public function getResourceSectionByMd5key($md5_keys) {
        if(empty($md5_keys)) {
            return false;
        }
        
        $section_list = $this->_dResourceSection->getResourceSectionByMd5key($md5_keys);
        if(!empty($section_list)) {
            foreach($section_list as $md5_key => $list) {
                $section_list[$md5_key] = reset($list);
            }
        }
        
        return !empty($section_list) ? $section_list : false;
    }
    
    
    public function addResourceSection($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceSection->addResourceSection($dataarr, $is_return_id);
    }
    

    public function addResourceSectionBat($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dResourceSection->addBat($dataarr);
    }
    
    public function delResourceSection($section_id) {
        if(empty($section_id)) {
            return false;
        }
        return $this->_dResourceSection->delResourceSection($section_id);
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