<?php
class dClassNotice extends dBase {
	protected $_tablename = 'wmw_class_notice';
    protected $_fields = array(
        'notice_id',
        'class_code',
        'notice_title',
        'notice_content',
        'add_account',
        'add_time',
        'is_sms',
    );
    protected $_pk = 'notice_id';
    protected $_index_list = array(
        'class_code',
        'add_account',
        'add_time',
    );
        
    //获取班级公告信息
    public function getClassNotice($ids) {
        
         return $this->getInfoByPk($ids);
    }
    
    //获取班级公告信息
    public function getClassNoticeById($notice_ids) {
        return $this->getInfoByPk($notice_ids);
    }
    
    //获取班级最新的公告信息
    public function getLastNoticeByClassCode($class_code,$orderby,$offset,$limit) {
        return $this->getInfo($class_code,$orderby,$offset,$limit);
    }
    
    //发布班级公告
    public function addClassNotice($dataarr,$is_return_id) {
         return $this->add($dataarr,$is_return_id);
    }
    
    //修改班级公告
    public function modifyClassNotice($datas,$id) {
        return $this->modify($datas,$id);
    }
    
    //删除班级公告
    public function delClassNotice($id) {
        return $this->delete($id); 
    }
    
    
}