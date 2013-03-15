<?php
/**
 * 班级说说部分的实现
 * @author Administrator
 * 注明：
 * 1. 班级说说关系表和说说实体表的关系是持有关系
 *
 */
import('@.Control.Api.MoodImpl.MoodInfo');

class ClassMood {
    private $mClassMoodRelation;
    private $objMoodInfo;
    
    public function __construct() {
        $this->mClassMoodRelation = ClsFactory::Create('Model.Mood.mMoodClassRelation');
        $this->objMoodInfo = new MoodInfo();
    }
    
 	/**
     * 获取班级说说信息
     * @param $class_code
     * @param $mood_id
     */
    public function getClassMood($class_code, $mood_id) {
        if(empty($class_code) || empty($mood_id)) {
            return false;
        }
        
        //获取班级的说说信息
        $where_appends = array(
            'mood_id' => "mood_id='$mood_id'",
        );
        $mMoodClassRelation = ClsFactory::Create('Model.Mood.mMoodClassRelation');
        $class_mood_arr = $mMoodClassRelation->getMoodClassRelationByClassCode($class_code, $where_appends);
        $class_mood_list = & $class_mood_arr[$class_code];
        
        //解析说说相关的信息
        $class_mood_list = $this->objMoodInfo->parseMood($class_mood_list);
        $mood_info = & $class_mood_list[$mood_id];
        
        return !empty($mood_info) ? $mood_info : false;
    }
    
    /**
     * 添加班级说说
     * @param $mood_datas
     * @param $return_insert_id
     */
    public function addClassMood($class_code, $mood_datas) {
        if(empty($class_code) || empty($mood_datas)) {
            return false;
        }
        
        $mood_id = $this->objMoodInfo->addMood($mood_datas);
        if(empty($mood_id)) {
            return false;
        }
        
        $mood_class_relation_datas = array(
            'mood_id' => $mood_id,
            'class_code' => $class_code
        );
        if(!$this->mClassMoodRelation->addMoodClassRelation($mood_class_relation_datas, true)) {
            $this->objMoodInfo->delMood($mood_id);
            return false;
        }
        
        return $mood_id;
    }
    
    /**
     * 删除班级说说
     * @param $mood_id
     */
    public function delClassMood($class_code, $mood_id) {
        if(empty($class_code) || empty($mood_id)) {
            return false;
        }
        
        //获取相关的说说的信息
        $class_mood_arr = $this->mClassMoodRelation->getMoodClassRelationByClassCode($class_code, "mood_id='$mood_id'", 0, 1);
        $class_mood_list = & $class_mood_arr[$class_code];
        $mood_info = reset($class_mood_list);
        if(empty($mood_info)) {
            return false;
        }
        //删除关系信息
        $this->mClassMoodRelation->delMoodClassRelation($mood_info['id']);
        //删除实体信息
        $this->objMoodInfo->delMood($mood_id);
        
        return true;
    }
    
}