<?php
class dMoodClassRelation extends dBase{
    protected $_tablename = 'wmw_mood_class_relation'; //主表
    protected $_fields = array(
        'id',
        'class_code',
        'mood_id',
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'id',
        'class_code',
        'mood_id',
    );
    
    public function getMoodClassRelationById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    /**
     * 通过班级code获取用户的说说列表
     * @param $client_accounts
     */
    public function getMoodClassRelationByClassCode($class_codes) {
        return $this->getInfoByFk($class_codes, 'class_code', 'mood_id desc');
    }
    
    /**
     * 添加班级说说关系
     * @param $datas
     * @param $is_return_id
     */
    public function addMoodClassRelation($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 修改班级说说关系
     * @param $datas
     * @param $mood_id
     */
    public function modifyMoodClassRelation($datas, $id) {
        return $this->modify($datas, $id);
    }
    
    /**
     * 通过主键删除班级说说关系
     * @param $mood_id
     */
    public function delMoodClassRelation($id) {
        return $this->delete($id);
    }
}