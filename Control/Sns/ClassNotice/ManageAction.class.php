<?php
class ManageAction extends SnsController {
    public function _initialize() {
      parent::_initialize();
    }
    
    /**
     * 根据公告id删除公告信息
     */
    public function deleteClassNoticeAjax() {
        $notice_id = $this->objInput->getInt('notice_id');
        
        if(empty($notice_id)) {
            $this->ajaxReturn(null, '公告信息不存在!', -1, 'json');
        }
        
        //获取公告的信息
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $notice_list = $mClassNotice->getClassNoticeById($notice_id);
        $notice_info = & $notice_list[$notice_id];
        if(empty($notice_info)) {
            $this->ajaxReturn(null, '公告信息不存在!', -1, 'json');
        }
        
        $class_code = $notice_info['class_code'];
        //判断用户的删除权限
        import('@.Control.Sns.ClassNotice.Ext.NoticeContext');
        $context = new NoticeContext($this->user, $class_code);
        $access_list = $context->getUserAccessList();
        if(!$access_list['can_delete'] && $notice_info['add_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该公告信息!', -1, 'json');
        }
        
        if(!$mClassNotice->delClassNotice($notice_id)) {
            $this->ajaxReturn(null, '系统繁忙，删除公告失败!', -1, 'json');
        }
        
        //删除公告的查看记录
        $mClassNoticeFoot = ClsFactory::Create('Model.ClassNotice.mClassNoticeFoot');
        $notice_foot_arr = $mClassNoticeFoot->getClassNoticeFootByNoticeId($notice_id);
        $notice_foot_list = & $notice_foot_arr[$notice_id];
        if(!empty($notice_foot_list)) {
            foreach($notice_foot_list as $id=>$notice_foot) {
                $mClassNoticeFoot->delClassNoticeFoot($id);
            }
        }
        
        $this->ajaxReturn(null, '公告删除成功', 1, 'json');
    }
    
    /**
     * 学生点击老师我知道了
     */
    public function setNoticeKnowAjax() {
        $notice_id = $this->objInput->getInt('notice_id');
        
        //获取公告的信息
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $notice_list = $mClassNotice->getClassNoticeById($notice_id);
        $notice_info = & $notice_list[$notice_id];
        if(empty($notice_info)) {
            $this->ajaxReturn(null, '公告信息不存在!', -1, 'json');
        }
        
        //检测用户所在的班级是否有权限查看该公告
        $class_code = $notice_info['class_code'];
        $class_code = $this->checkoutClassCode($class_code);
        //判断用户权限，学生是否是该班级的
        if($this->user['client_type'] != CLIENT_TYPE_STUDENT || empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限回复该公告!', -1, 'json');
        }
        
        $client_account = $this->user['client_account'];
        //判断用户是否对该公告做出了回复
        $mClassNoticeFoot = ClsFactory::Create('Model.ClassNotice.mClassNoticeFoot');
        $wherearr = array(
            "notice_id='$notice_id'",
            "client_account='$client_account'",
        );
        //如果对应的回复信息已经存在
        if($mClassNoticeFoot->getClassNoticeFootByNoticeIdAndAccount($wherearr)) {
            $this->showError(null, '您已经回执该公告，请不要重复提交!', -1, 'json');
        }
        
        $notice_foot_datas = array(
            'notice_id' => $notice_id,
            'client_account' => $client_account,
            'add_time' => time()
        );
        if(!$mClassNoticeFoot->addClassNoticeFoot($notice_foot_datas)) {
            $this->ajaxReturn(null, '系统繁忙，回执失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '公告回执成功!', 1, 'json');
    }
}