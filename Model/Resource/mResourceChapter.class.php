<?php
class mResourceChapter extends mBase{
    protected $_dResourceChapter = null;
    
    public function __construct() {
        $this->_dResourceChapter = ClsFactory::Create("Data.Resource.dResourceChapter");
    }
    
    public function getResourceChapterById($chapter_ids) {
        if(empty($chapter_ids)) {
            return false;
        }
        
        $chapter_list = $this->_dResourceChapter->getResourceChapterById($chapter_ids);
        
        foreach($chapter_list as $chapter_id => $chapter_info) {
            $chapter_info['chapter_name'] = $this->dropPrefix($chapter_info['chapter_name']);
            $chapter_list[$chapter_id] = $chapter_info;
        }
        return $chapter_list;
    }
    
    public function getResourceChapterByMd5key($md5_keys) {
        if(empty($md5_keys)) {
            return false;
        }
        
        $chapter_list = $this->_dResourceChapter->getResourceChapterByMd5key($md5_keys);
        
        //建立md5_key到章信息的一一映射
        if(!empty($chapter_list)) {
            foreach($chapter_list as $md5_key => $list) {
                $chapter_list[$md5_key] = reset($list);
            }
        }
        
        return !empty($chapter_list) ? $chapter_list : false;
    }
    
    public function addResourceChapter($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceChapter->addResourceChapter($dataarr, $is_return_id);
    }
    
    public function addResourceChapterBat($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        return $this->_dResourceChapter->addBat($dataarr);
    }
    
    public function delResourceChapter($chapter_id) {
        if(empty($chapter_id)) {
            return false;
        }
        
        return $this->_dResourceChapter->delResourceChapter($chapter_id);
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