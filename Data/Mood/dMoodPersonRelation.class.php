<?php
class dMoodPersonRelation extends dBase{
    protected $_tablename = 'wmw_mood_person_relation'; //主表
    protected $_fields = array(
        'id',
        'client_account',
        'mood_id',
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'id',
        'client_account',
        'mood_id',
    );
    
    public function getMoodPersonRelationById($ids) {
        return $this->getInfoByPk($ids);
    }
    
    /**
     * 通过用户账号获取用户的说说列表
     * @param $client_accounts
     */
    public function getMoodPersonRelationByClientAccount($client_accounts) {
        return $this->getInfoByFk($client_accounts, 'client_account', 'mood_id desc');
    }
    
    /**
     * 添加用户说说关系
     * @param $datas
     * @param $is_return_id
     */
    public function addMoodPersonRelation($datas, $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }
    
    /**
     * 修改用户说说关系
     * @param $datas
     * @param $mood_id
     */
    public function modifyMoodPersonRelation($datas, $id) {
        return $this->modify($datas, $id);
    }
    
    /**
     * 通过主键删除用户说说关系
     * @param $mood_id
     */
    public function delMoodPersonRelation($id) {
        return $this->delete($id);
    }
}