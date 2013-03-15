<?php
class dMood extends dBase{
    protected $_tablename = 'wmw_mood'; //主表
    protected $_fields = array(
        'mood_id',
        'content',
        'img_url',
        'add_account',
        'add_time',
        'comments',
    );
    protected $_pk = 'mood_id';
    protected $_index_list = array(
        'mood_id',
        'add_account',
    );
    
    public function getMoodById($mood_ids) {
        return $this->getInfoByPk($mood_ids);
    }
    
    public function getMoodByAddAccount($add_accounts) {
        return $this->getInfoByFk($add_accounts, 'add_account', 'mood_id desc');
    }
    
    public function addMood($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    public function modifyMood($datas, $mood_id) {
        return $this->modify($datas, $mood_id);
    }
    
    public function delMood($mood_id) {
        return $this->delete($mood_id);
    }
}