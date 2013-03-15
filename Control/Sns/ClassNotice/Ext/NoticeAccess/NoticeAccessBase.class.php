<?php
abstract class NoticeAccessBase {
    protected $user = array();
    protected $class_code = 0;
    
    public function __construct($user, $class_code) {
        $this->user = $user;
        $this->class_code = $class_code;
    }
    
    abstract protected function isClassAdmin();
    
    abstract public function getUserAccessList();
    
    /**
     * 获取用户的管理权限
     */
    protected function getAdminAccess() {
        if(!$this->isClassAdmin()) {
            return array();
        }
        
        return array(
           'can_delete' => true,//删除标识
           'is_receipt'       => true,//回执标识
           'is_send'     => true,//发短信标识
           'is_show_fbgg' => true,
        );
    }
    
    /**
     * 获取用户的当前班级关系信息
     */
    protected function getCurrentClientClass() {
        
        $client_class_list = $this->user['client_class'];
        
        $current_client_class = array();
        foreach($client_class_list as $client_class) {
            if($client_class['class_code'] == $this->class_code) {
                $current_client_class = $client_class;
                break;
            }
        }
        
        return !empty($current_client_class) ? $current_client_class : false;
    }
    
}