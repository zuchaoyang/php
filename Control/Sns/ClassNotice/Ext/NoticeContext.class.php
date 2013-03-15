<?php
class NoticeContext {
    private $user = array();
    private $class_code = 0;
    
    public function __construct($user, $class_code) {
        $this->user = $user;
        $this->class_code = $class_code;    
    }
    
    public function getUserAccessList() {
        $object = $this->createObject();
        
        if(!is_object($object)) {
            return false;
        }
        
        return $object->getUserAccessList();
    }
    
    private function createObject() {
        $client_type = $this->user['client_type'];
        
        switch($client_type) {
            case CLIENT_TYPE_STUDENT:
                import('@.Control.Sns.ClassNotice.Ext.NoticeAccess.NoticeStudent');
                $object = new NoticeStudent($this->user, $this->class_code);
                break;
            case CLIENT_TYPE_TEACHER:
                import('@.Control.Sns.ClassNotice.Ext.NoticeAccess.NoticeTeacher');
                $object = new NoticeTeacher($this->user, $this->class_code);
                break;
            case CLINET_TYPE_FAMILY:
                import('@.Control.Sns.ClassNotice.Ext.NoticeAccess.NoticeFamily');
                $object = new NoticeFamily($this->user, $this->class_code);
                break;
        }
        
        return $object;
    }
}