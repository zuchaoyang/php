<?php
class mMoodClassRelation extends mBase {
    protected $_dMoodClassRelation = null;
    
    public function __construct() {
        $this->_dMoodClassRelation = ClsFactory::Create('Data.Mood.dMoodClassRelation');
    }
    
    public function getMoodClassRelationById($ids) {
        if(empty($ids)) {
            return false;
        }
        
        return $this->_dMoodClassRelation->getMoodClassRelationById($ids);
    }
    
    /**
     * 通过class_code获取班级说说信息
     * @param $class_codes
     * @param $where_appends
     * @param $offset
     * @param $limit
     */
    public function getMoodClassRelationByClassCode($class_codes, $where_appends, $offset = 0, $limit = 10) {
        if(empty($class_codes)) {
            return false;
        }
        
        $wherearr = array();
        $wherearr[] = "class_code in('" . implode("','", (array)$class_codes) . "')";
        if(!empty($where_appends)) {
            $wherearr = array_merge($wherearr, (array)$where_appends);
        }
        
        $class_mood_relations = $this->_dMoodClassRelation->getInfo($wherearr, 'mood_id desc', $offset, $limit);
        if(empty($class_mood_relations)) {
            return false;
        }
        
        $class_mood_arr = $mood_ids = array();
        //获取mood的内容信息
        foreach($class_mood_relations as $relation) {
            $mood_ids[] = $relation['mood_id'];
        }
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mood_list = $mMood->getMoodById($mood_ids);
        
        //数组重组保持业务的一致性
        if(!empty($class_mood_relations)) {
            foreach($class_mood_relations as $relation_id=>$mood_relation) {
                $mood_id = $mood_relation['mood_id'];
                if(isset($mood_list[$mood_id])) {
                    $mood_relation = array_merge($mood_relation, $mood_list[$mood_id]);
                }
                $class_mood_arr[$mood_relation['class_code']][$mood_id] = $mood_relation;
            }
        }
        
        return $class_mood_arr;
    }
    
    public function addMoodClassRelation($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false; 
        }
        
        return $this->_dMoodClassRelation->addMoodClassRelation($datas, $is_return_id);
    }
    
    public function modifyMoodClassRelation($datas, $id) {
        if(empty($datas) || !is_array($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dMoodClassRelation->modifyMoodClassRelation($datas, $id);
    }
    
    public function delMoodClassRelation($id) {
        if(empty($id)) {
            return false;
        }
        
        return $this->_dMoodClassRelation->delMoodClassRelation($id);
    }
    
}