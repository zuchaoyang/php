<?php
class dResourceChapter extends dBase{
    protected $_pk = 'chapter_id';
    protected $_tablename = 'resource_chapter';
    protected $_fields = array(
        'chapter_id',
        'chapter_name',
        'display_order',
        'md5_key',
        'add_time'
    );
    protected $_index_list = array(
        'chapter_id',
        'md5_key',
    );
    
    public function _initialize() {
        $this->connectDb('resource', true);
    }
    
    public function getResourceChapterById($section_ids) {
        return $this->getInfoByPk($section_ids);
    }
    
    public function getResourceChapterByMd5key($md5_keys) {
        if(empty($md5_keys)) {
            return false;
        }
        
        return $this->getInfoByFk($md5_keys, 'md5_key');
    }
    
    
    public function addResourceChapter($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function delResourceChapter($section_id) {
        return $this->delete($section_id);
    }
}