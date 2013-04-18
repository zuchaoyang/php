<?php
class mMood extends mBase{
    protected $_dMood = null;
    
    public function __construct() {
        $this->_dMood = ClsFactory::Create('Data.Mood.dMood');
    }
    
    public function getMoodById($mood_ids) {
        if(empty($mood_ids)) {
            return false;
        }
        
        $mood_list = $this->_dMood->getMoodById($mood_ids);
        if(!empty($mood_list)) {
            foreach($mood_list as $mood_id => $mood) {
                $mood_list[$mood_id] = $this->parseMood($mood);
            }
        }
        
        return !empty($mood_list) ? $mood_list : false;
    }
    
    public function getMoodByAddAccount($add_accounts) {
        if(empty($add_accounts)) {
            return false;
        }
        
        $mood_arr = $this->_dMood->getMoodByAddAccount($add_accounts);
        if(!empty($mood_arr)) {
            foreach($mood_arr as $uid=>$mood_list) {
                foreach($mood_list as $mood_id => $mood) {
                    $mood_list[$mood_id] = $this->parseMood($mood);
                }
                $mood_arr[$uid] = $mood_list;
            }
        }
        
        return !empty($mood_arr) ? $mood_arr : false;
    }
    
    public function addMood($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false; 
        }
        
        return $this->_dMood->addMood($datas, $is_return_id);
    }
    
    public function modifyMood($datas, $mood_id) {
        if(empty($datas) || !is_array($datas) || empty($mood_id)) {
            return false;
        }
        
        return $this->_dMood->modifyMood($datas, $mood_id);
    }
    
    public function delMood($mood_id) {
        if(empty($mood_id)) {
            return false;
        }
        
        return $this->_dMood->delMood($mood_id);
    }
    
    private function parseMood($mood) {
        if(empty($mood)) {
            return false;
        }
        
        //获取mood的图片信息
        import('@.Common_wmw.');
        if(!empty($mood['img_url'])) {
            $client_account = $mood['add_account'];
            $mood['img_url'] = Pathmanagement_sns::getMood($client_account) . '/' . $mood['img_url'];
        }
        
        return $mood;
    }
    
}