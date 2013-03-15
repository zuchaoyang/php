<?php
import('@.Control.Sns.ClassHomework.Ext.HomeworkAccess.HomeworkAccessBase');

class HomeworkTeacher extends HomeworkAccessBase {
    
  public function getUserAccessList() {
        $admin_access_list = $this->getAdminAccess(); 
        
        $user_access_list =  array(
            'scan_view' => true,//查看标识
            'is_receipt' => true,//回执标识
            'is_teacher' => true,//老师标识
            'is_send' => true,//是否显示短信
            'is_show_fbzy' => true,//页面是否显示发布作业标识
        );
        
        return array_merge((array)$admin_access_list, $user_access_list);
    }
    
    protected function isClassAdmin() {
        $client_class = $this->getCurrentClientClass();
        
        $class_teacher_role_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN,
            TEACHER_CLASS_ROLE_CLASSBOTH,
        );
        
        return in_array($client_class['teacher_class_role'], $class_teacher_role_list) || $client_class['class_admin'] == IS_CLASS_ADMIN ? true : false;
    }
}