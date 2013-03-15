<?php
/**
 * 班级公告api 
 */
class NoticeApi extends ApiController{
    public function __construct() {
        parent::__construct();
    }    
    
    public function _initialize(){
		parent::_initialize();        
    }	
    /**
     * 通过班级code获取该班级最新的公告   路径：/Api/Class/Notice/getTodayNoticeByClassCode/class_code/13415
     */
    function getLastNoticeByClassCode() {
        $class_code = $this->objInput->getInt('class_code');
        if(empty($class_code)) {
            return false;
        }
        
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $NoticeInfo = $mClassNotice->getLastNoticeByClassCode($class_code);
        if(empty($NoticeInfo)) {
            $this->ajaxReturn(null, '获取最新公告失败！', -1, 'json');
        }
        $NoticeInfo = reset($NoticeInfo);
        $NoticeInfo['add_time'] = date('Y.m.d H:i', $NoticeInfo['add_time']);
        $this->ajaxReturn($NoticeInfo, '获取最新公告成功！', 1, 'json');
    }
}
