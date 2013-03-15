<?php
class dClassHomework extends dBase{
	protected $_tablename = 'wmw_class_homework';
    protected $_fields = array(
        'subject_id',
        'add_account',
        'class_code',
        'add_time',
        'upd_account',
        'upd_time',
        'end_time',
        'attachment',
        'content',
        'is_sms',
        'accepters'
    );
    protected $_pk = 'homework_id';
    protected $_index_list = array(
        'class_code'
    );
        
    //获取班级作业信息
    public function getClassHomeworkById($ids) {
         return $this->getInfoByPk($ids);
    }
    
    //发布班级作业
    public function addHomework($dataarr,$is_return_id) {
         return $this->add($dataarr,$is_return_id);
    }
    
    //修改班级作业
    public function modifyHomework($datas,$id) {
        return $this->modify($datas,$id);
    }
    
    //删除班级作业
    public function delHomework($id) {
        return $this->delete($id); 
    }
}