<?php
/**
 * 班级说说部分的实现
 * @author Administrator
 *
 */
class PersonMood {
    private $mMoodPersonRelation;
    private $objMoodInfo;
    
    public function __construct() {
        $this->mMoodPersonRelation = ClsFactory::Create('Model.Mood.mMoodPersonRelation');
        
        import('@.Control.Api.MoodImpl.MoodInfo');
        $this->objMoodInfo = new MoodInfo();
    }
    
    /**
     * 获取用户对应的说说列表
     * @param $client_account
     * @param $offset
     * @param $limit
     */
    public function getPersonMoodList($client_account, $offset = 0, $limit = 10) {
        if(empty($client_account)) {
            return false;
        }
        
        $mMoodPersonRelation = ClsFactory::Create('Model.Mood.mMoodPersonRelation');
        $person_mood_arr = $mMoodPersonRelation->getMoodPersonRelationByClientAccount($client_account, null, $offset, $limit);
        $person_mood_list = & $person_mood_arr[$client_account];
        //解析说说相关的信息
        $person_mood_list = $this->objMoodInfo->parseMood($person_mood_list);
        
        return !empty($person_mood_list) ? $person_mood_list : false;
    }
    
    /**
     * 通过班级code获取说说信息
     * @param $class_codes
     * todolist
     */
    public function getPersonMood($client_account, $mood_id) {
        if(empty($client_account) || empty($mood_id)) {
            return false;
        }
        
        $person_mood_arr = $this->mMoodPersonRelation->getMoodPersonRelationByClientAccount($client_account, "mood_id='$mood_id'");
        $person_mood_list = & $person_mood_arr[$client_account];
        
        //解析说说相关的信息
        $person_mood_list = $this->objMoodInfo->parseMood($person_mood_list);
        $mood_info = & $person_mood_list[$mood_id];
        
        return !empty($mood_info) ? $mood_info : false;
    }
    
    /**
     * 添加班级说说
     * @param $mood_datas
     * @param $return_insert_id
     */
    public function addPersonMood($client_account, $mood_datas) {
        if(empty($client_account) || empty($mood_datas)) {
            return false;
        }
        
        $mood_id = $this->objMoodInfo->addMood($mood_datas);
        if(empty($mood_id)) {
            return false;
        }
        
        $mood_person_relation_datas = array(
            'client_account' => $client_account,
            'mood_id' => $mood_id,
        );
        
        $relation_id = $this->mMoodPersonRelation->addMoodPersonRelation($mood_person_relation_datas, true);
        if(empty($relation_id)) {
            $this->objMoodInfo->delMood($mood_id);
            return false;
        }
        
        return $mood_id;
    }
    
    /**
     * 删除班级说说
     * @param $mood_id
     */
    public function delPersonMood($client_account, $mood_id) {
        if(empty($client_account) || empty($mood_id)) {
            return false;
        }
        
        $person_mood_arr = $this->mMoodPersonRelation->getMoodPersonRelationByClientAccount($client_account, "mood_id='$mood_id'", 0, 1);
        $person_mood_list = & $person_mood_arr[$client_account];
        $mood_info = & $person_mood_list[$mood_id];
        if(empty($mood_info)) {
            return false;
        }
        
        //删除说说关系
        if(!$this->mMoodPersonRelation->delMoodPersonRelation($mood_info['id'])) {
            return false;
        }
        //删除实体信息
        $this->objMoodInfo->delMood($mood_id);
        
        return true;
    }
    
}