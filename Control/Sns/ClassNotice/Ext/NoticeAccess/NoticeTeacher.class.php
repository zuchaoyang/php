<?php
import('@.Control.Sns.ClassNotice.Ext.NoticeAccess.NoticeAccessBase');

class NoticeTeacher extends NoticeAccessBase {
    
  public function getUserAccessList() {
        $admin_access_list = $this->getAdminAccess(); 
        
        $user_access_list =  array(
            'scan_view' => true,//查看标识
            'is_receipt' => true,//回执标识
            'is_send' => true, //是否显示短信标识
            'is_show_fbgg' => true,//是否显示发布公告按钮
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