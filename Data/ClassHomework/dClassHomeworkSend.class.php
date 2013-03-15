<?php
class dClassHomeworkSend extends dBase{
	protected $_tablename = 'wmw_class_homework_send';
    protected $_fields = array(
        'id',
        'homework_id',
        'client_account',
        'add_time',
        'is_view',
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'homework_id',
        'client_account'
    );
        
    //获取班级作业信息
    public function getHomeworkSend($ids) {
        
         return $this->getInfoByPk($ids);
    }
    
    //发布班级作业
    public function addHomeworkSend($dataarr) {
         return $this->add($dataarr);
    }
    
    //修改班级作业
    public function modifyHomeworkSend($datas,$id) {
        return $this->modify($datas,$id);
    }
    
    //删除班级作业
    public function delHomeworkSend($id) {
        return $this->delete($id); 
    }
    
    //根据外键作业id查询信息
    public function getHomeworkSendByhomeworkid($homework_id) {
       return  $this->getInfoByFk($homework_id,'homework_id');
    }
    
}