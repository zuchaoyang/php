<?php
class UserFeedDispatch {
    protected $user = array();
    
    public function dispatch($uid, $add_time, $feed_id, $feed_type) {
        if(empty($uid) || empty($feed_id) || empty($feed_type)) {
            
             FEED_DEBUG && trigger_error('个人feed分发时必要参数缺失!', E_USER_ERROR);
            
            return false;
        }
        
        $mUserObjectHash = ClsFactory::Create('RModel.Common.mUserObjectHash');
        $this->user = $mUserObjectHash->getUserObjectHash($uid);
        
        if(empty($this->user)) {
             FEED_DEBUG && trigger_error('个人动态分发时，获取用户基本信息失败!', E_USER_ERROR);
        }
        
        $client_type = intval($this->user['client_type']);
        
        if(!in_array($client_type, array(CLIENT_TYPE_STUDENT, CLIENT_TYPE_TEACHER, CLIENT_TYPE_FAMILY))) {
            FEED_DEBUG && trigger_error('个人动态分发时，用户类型异常!', E_USER_ERROR);
        }
        
        if($client_type == CLIENT_TYPE_STUDENT) {
            return $this->dispatchStudent($add_time, $feed_id, $feed_type);
        } else if($client_type == CLIENT_TYPE_TEACHER) {
            return $this->dispatchTeacher($add_time, $feed_id, $feed_type);
        } else if($client_type == CLIENT_TYPE_FAMILY) {
            return $this->dispatchFamily($add_time, $feed_id, $feed_type);
        }
        
        return false;
    }
    
    /**
     * 分发学生添加的动态信息
     */
    private function dispatchStudent($add_time, $feed_id, $feed_type) {
        if(empty($feed_id) || empty($feed_type)) {
            
            FEED_DEBUG && trigger_error('个人动态分发时，分发学生必要参数缺失!', E_USER_ERROR);
            
            return false;
        }
        
        $add_time = intval($add_time);
        $uid = $this->user['client_account'];
        
        import('@.Control.Api.FeedImpl.Dispatch.Scope.FriendsScope');
        $friendsScope = new FriendsScope();
        $friendsScope->handle($uid, $add_time, $feed_id, $feed_type);
        
        $class_code = key($this->user['class_info']);
        if(empty($class_code)) {
            FEED_DEBUG && trigger_error('个人动态分发时，获取学生的班级信息失败!', E_USER_ERROR);
        }
        
        if(!empty($class_code)) {
            import('@.Control.Api.FeedImpl.Dispatch.Scope.ClassMembersScope');
            $classMembersScope = new ClassMembersScope();
            $classMembersScope->handle($class_code, $add_time, $feed_id, $feed_type);
        }
        
        import('@.Control.Api.FeedImpl.Dispatch.Scope.ParentsScope');
        $parentsScope = new ParentsScope();
        $parentsScope->handle($uid, $add_time, $feed_id, $feed_type);
    }
    
    /**
     * 添加老师添加的动态信息
     */
    private function dispatchTeacher($add_time, $feed_id, $feed_type) {
        if(empty($feed_id) || empty($feed_type)) {
            FEED_DEBUG && trigger_error('个人动态分发时，必要参数缺失!', E_USER_ERROR);
            
            return false;
        }
        
        $add_time = intval($add_time);
        $uid = $this->user['client_account'];
        
        import('@.Control.Api.FeedImpl.Dispatch.Scope.FriendsScope');
        $friendsScope = new FriendsScope();
        $friendsScope->handle($uid, $add_time, $feed_id, $feed_type);
        
        import('@.Control.Api.FeedImpl.Dispatch.Scope.ClassMembersScope');
        $classMembersScope = new ClassMembersScope();
        
        $class_code_list = array_keys($this->user['class_info']);
        foreach((array)$class_code_list as $class_code) {
            $classMembersScope->handle($class_code, $add_time, $feed_id, $feed_type);
        }
    }
    
    /**
     * 添加家长的动态信息
     */
    private function dispatchFamily($add_time, $feed_id, $feed_type) {
        if(empty($feed_id) || empty($feed_type)) {
            FEED_DEBUG && trigger_error('个人动态分发时，获取用户基本信息失败!', E_USER_ERROR);
            return false;
        }
        $uid = $this->user['client_account'];
        
        import('@.Control.Api.FeedImpl.Dispatch.Scope.FriendsScope');
        $friendsScope = new FriendsScope();
        $friendsScope->handle($uid, $add_time, $feed_id, $feed_type);
    }
}