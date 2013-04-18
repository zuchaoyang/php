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
            $this->ajaxReturn(null, '获取最新公告失败！', -1, 'json');
        }
        import("@.Control.Api.Class.NoticeImpl.Notice");
        $notice_obj = new Notice();
        $NoticeInfo = $notice_obj->getLastNoticeByClassCode($class_code);
        if(empty($NoticeInfo)) {
            $this->ajaxReturn(null, '获取最新公告失败！', -1, 'json');
        }
        
        $this->ajaxReturn($NoticeInfo, '获取最新公告成功！', 1, 'json');
    }
}
