<?php
/**
 * 班级公告api 
 */
class Notice{
    /**
     * 通过班级code获取该班级最新的公告   路径：/Api/Class/Notice/getTodayNoticeByClassCode/class_code/13415
     */
    function getLastNoticeByClassCode($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $NoticeInfo = $mClassNotice->getLastNoticeByClassCode($class_code);
        if(empty($NoticeInfo)) {
            return false;
        }
        $NoticeInfo = reset($NoticeInfo);
        $NoticeInfo['add_time'] = date('Y.m.d H:i', $NoticeInfo['add_time']);
        
        return $NoticeInfo;
    }
}
