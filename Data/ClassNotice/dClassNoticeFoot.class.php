<?php
class dClassNoticeFoot extends dBase {
	protected $_tablename = 'wmw_class_notice_foot';
    protected $_fields = array(
        'id',
        'notice_id',
        'client_account',
        'add_time',
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'notice_id',
        'client_account',
    );
        
    //获取班级公告浏览记录表信息
    public function getClassNoticeFoot($ids) {
        
         return $this->getInfoByPk($ids);
    }
    
    /**
     * 通过notice_id获取公告浏览记录
     * @param $notice_ids
     */
    public function getClassNoticeFootByNoticeId($notice_ids) {
        return $this->getInfoByFk($notice_ids, 'notice_id');
    }
    
    //发布班级公告浏览记录表
    public function addClassNoticeFoot($dataarr,$is_return_id) {
         return $this->add($dataarr,$is_return_id);
    }
    
    //修改班级公告浏览记录表
    public function modifyClassNoticeFoot($datas,$id) {
        return $this->modify($datas,$id);
    }
    
    //删除班级公告浏览记录表主键删除
    public function delClassNoticeFoot($id) {
        return $this->delete($id); 
    }
    
    
}