<?php
import('@.Control.Sns.ClassNotice.Ext.NoticeAccess.NoticeAccessBase');
class NoticeFamily extends NoticeAccessBase {
    
    public function getUserAccessList() {
        return array(
            'scan_view' => true,
            'is_family' => true,
        );
    }
    
    protected function isClassAdmin() {
        return false;
    }
    
}