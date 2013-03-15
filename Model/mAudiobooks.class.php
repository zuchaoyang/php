<?php
class mAudiobooks extends  mBase {
    protected $_dAudiobooks = null;
	
	public function __construct() {
		$this->_dAudiobooks = ClsFactory::Create('Data.dAudiobooks');
	}
	
    //通过类型id查询列表
    public function getAudiobooksByTypeid($typeid,$offset,$limit) {
        if(empty($typeid)) {
            return false;
        }
        
        $wheresql = 'category = '.$typeid;
        return $this->_dAudiobooks->getInfo($wheresql ,null,$offset,$limit);
    }
    
    
    //有声图书表中的所有数据
    public function getAudiobooksByType() {
        return $this->_dAudiobooks->getInfo();    
    }
    
    
    //通过主键查询
    public function getAudiobooksInfoById($id) {
        if(empty($id)) {
            return false;
        }
        
       return $this->_dAudiobooks->getAudiobooksInfoById($id);
        
    }
    
    
}