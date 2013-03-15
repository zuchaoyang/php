<?php
class mMoodPersonRelation extends mBase{
    protected $_dMoodPersonRelation = null;
    
    public function __construct() {
        $this->_dMoodPersonRelation = ClsFactory::Create('Data.Mood.dMoodPersonRelation');
    }
    
    public function getMoodPersonRelationById($ids) {
        if(empty($ids)) {
            return false;
        }
        
        return $this->_dMoodPersonRelation->getMoodPersonRelationById($ids);
    }
    
    /**
     * 获取个人说说信息
     * @param $client_accounts
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getMoodPersonRelationByClientAccount($client_accounts, $where_appends, $offset = 0, $limit = 10) {
        if(empty($client_accounts)) {
            return false;
        }
        
        $where_arr = array();
        $where_arr[] = "client_account in('" . implode("','", (array)$client_accounts) . "')";
        if(!empty($where_appends)) {
            $where_arr = array_merge($where_arr, (array)$where_appends);
        }
        
        $person_mood_relation_list = $this->_dMoodPersonRelation->getInfo($where_arr, 'mood_id desc', $offset, $limit);
        if(empty($person_mood_relation_list)) {
            return false;
        }
        
        $mood_ids = array();
        foreach($person_mood_relation_list as $mood) {
            $mood_ids[] = $mood['mood_id'];
        }
        //获取mood的内容信息
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mood_list = $mMood->getMoodById($mood_ids);
        
        $mood_arr = array();
        foreach($person_mood_relation_list as $relation) {
            $mood_id = $relation['mood_id'];
            if(isset($mood_list[$mood_id])) {
                $relation = array_merge($relation, $mood_list[$mood_id]);
            }
            $mood_arr[$relation['client_account']][$mood_id] = $relation;
        }
        
        return $mood_arr;
    }
    
    /**
     * 统计用户的说说个数
     * @param $client_accounts
     */
    public function statPersonMood($client_accounts) {
        if(empty($client_accounts)) {
            return false;
        }
        
        $selectsql = "select client_account,count(*) as nums";
        $fromsql = "from wmw_mood_person_relation";
        $wheresql = "where client_account in('" . implode("','", (array)$client_accounts) . "')";
        $groupsql = "group by client_account";
        
        $stat_arr = $this->_dMoodPersonRelation->query("$selectsql $fromsql $wheresql $groupsql");
        $new_stat_arr = array();
        if(!empty($stat_arr)) {
            foreach($stat_arr as $stat) {
                $new_stat_arr[$stat['client_account']] = $stat['nums'];
            }
        }
        
        return !empty($new_stat_arr) ? $new_stat_arr : false;
    }
    
    public function addMoodPersonRelation($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false; 
        }
        
        return $this->_dMoodPersonRelation->addMoodPersonRelation($datas, $is_return_id);
    }
    
    public function modifyMoodPersonRelation($datas, $id) {
        if(empty($datas) || !is_array($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dMoodPersonRelation->modifyMoodPersonRelation($datas, $id);
    }
    
    public function delMoodPersonRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dMoodPersonRelation->delMoodPersonRelation($id);
    }
}