<?php
class dAudiobooks extends dBase {
    protected $_tablename = 'kd_online_voice';
    protected $_fields = array(
      'title',
      'category',
      'url',
      'summary',
      'type',
      'grade',
      'subject',
      'pic_url',
      'author'
    ); 
    
    protected $_pk = 'id';
    
    //通过主键查询有声图书信息
    public function getAudiobooksInfoById($id) {
        return $this->getInfoByPk($id);
    }
    
    
}