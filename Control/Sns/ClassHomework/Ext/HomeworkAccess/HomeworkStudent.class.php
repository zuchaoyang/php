<?php
import('@.Control.Sns.ClassHomework.Ext.HomeworkAccess.HomeworkAccessBase');
class HomeworkStudent extends HomeworkAccessBase {
    
   public function getUserAccessList() {
        $admin_access_list = $this->getAdminAccess(); 
        
        $user_access_list =  array(
            'scan_view'     => true,
            'show_know_btn' => true,
            'is_student' => true,
        );
        
        return array_merge((array)$admin_access_list, $user_access_list);
    }
    
    protected function isClassAdmin() {
        
        $client_class = $this->getCurrentClientClass();
        
        return $client_class['class_admin'] == IS_CLASS_ADMIN ? true : false;
    }
}