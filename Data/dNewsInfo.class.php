<?php
class dNewsInfo extends dBase {
    
    protected $_tablename = 'wmw_news_info';
    protected $_fields = array(
        'news_id',
        'news_type',
        'news_title',
        'news_toaccount',
        'news_content',
        'class_code',
        'subject_id',
        'sendMessage',
        'add_account',
        'add_date',
        'upd_account',
        'upd_date',
        'expiration_date',
        'attachment',
    );
    protected $_pk = 'news_id';
    protected $_index_list = array(
        'news_id',
       	'add_account',
        'news_toaccount',
        'class_code',
    );
    
    /**
     * 通过班级id获取班级的公告信息
     * @param   $class_codes
     */
    public function getNewsInfoByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code');
    }
    
    //todo delete
    /**
     * 根据用户信息获取用户当前的消息
     */
    public function getNewsInfoByToUid($newsToaccount) {
        return $this->getInfoByFk($newsToaccount, 'news_toaccount');
    }
    
    
    /**
     * 通过news_id获取相关信息
     * @param $news_id
     */
    public function getNewsInfoById($news_id) {
        return $this->getInfoByPk($news_id);
    }
    
    /**
     * 增加消息记录
     * @param $dataarr
     */
    public function addNewsInfo($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }
    
    /**
     * 修改作业信息
     * @param $dataarr
     * @param $news_id
     */
    public function modifyNewsInfo($dataarr , $news_id) {
        return $this->modify($dataarr, $news_id);
    }
    
    //通过主键news_id删除消息
    public function delNewsInfo($news_id) {
    	return $this->delete($news_id);
    }
}
