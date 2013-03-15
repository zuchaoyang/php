<?php
import('@.Control.Api.FeedImpl.Dispatch.Scope.ScopeBase');

class ClassMembersScope extends ScopeBase {
    
    public function handle($class_code, $add_time, $feed_id, $feed_type) {
        if(empty($class_code) || empty($feed_id)) {
            
            FEED_DEBUG && trigger_error('动态分发时，缺失必要参数!', E_USER_ERROR);
            
            return false;
        }
        
        $follows = $this->getFollows($class_code);
        
        if(empty($follows)) {
            FEED_DEBUG && trigger_error("动态分发时，获取班 -- {$class_code} --级范围内的follows关系失败!", E_USER_ERROR);
            
            return false;
        }
        
        $this->dispatchUserAll($follows, $add_time, $feed_id);
        
        $this->dispactchClassFeed($class_code, $add_time, $feed_id);
    }
    
    /**
     * 获取班级在线成员的follow关系
     * @param $class_code
     */
    protected function getFollows($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取用户的班级成员列表
        $mClassStudentSet = ClsFactory::Create('RModel.Common.mClassStudentSet');
        $student_list = $mClassStudentSet->getOnlineClassStudentSet($class_code);
        
        $mClassTeacherSet = ClsFactory::Create('RModel.Common.mClassTeacherSet');
        $teacher_list = $mClassTeacherSet->getOnlineClassTeacherSet($class_code);
        
        return array_merge((array)$student_list, (array)$teacher_list);
    }
    
}