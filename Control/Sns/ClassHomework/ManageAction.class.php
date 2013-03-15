<?php
class ManageAction extends SnsController {
    
    public function _initialize(){
        parent::_initialize();
    }
    
    /**
     * 删除作业信息
     */
    public function deleteHomeworkAjax() {
        $homework_id = $this->objInput->getInt('homework_id');
        
        $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
        $homework_list = $mClassHomework->getClassHomeworkById($homework_id);
        $homework = & $homework_list[$homework_id];
        if(empty($homework)) {
            $this->ajaxReturn(null, '作业信息不存在,删除失败!!', -1, 'json');
        }
        
        $class_code = $homework['class_code'];
        //判断用户是否有权限删除，用户是班级对应的管理员或者是作业的添加人
        //获取用户的管理权限
        import('@.Control.Sns.ClassHomework.Ext.HomeworkContext');
        $context = new HomeworkContext($this->user, $class_code);
        $access_list = $context->getUserAccessList();
        
        //判断用户是否是作业的添加人
        if(!$access_list['can_delete'] && $homework['add_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除该作业信息!', -1, 'json');
        }
        
        //删除作业的相关信息
        if(!$mClassHomework->delHomework($homework_id)) {
           $this->ajaxReturn(null, '系统繁忙, 作业删除失败!', -1, 'json');
        }
        
        //删除作业的发送对象信息
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        $homeworksend_arr = $mClassHomeworkSend->getHomeworkSendByhomeworkid($homework_id);
        $homeworksend_list = & $homeworksend_arr[$homework_id];
        //循环删除发送对象表
        foreach((array)$homeworksend_list as $id=>$homeworksend) {
            $mClassHomeworkSend->delHomeworkSend($id);
        }
        
        $this->ajaxReturn(null, '作业删除成功!', 1, 'json');
    }
    
    /**
     * 学生点击老师我知道了
     */
    public function setHomeworkKnowAjax() {
        $homework_id = $this->objInput->getInt('homework_id');
        if(empty($homework_id)) {
           $this->ajaxReturn(null, '作业信息不存在!', -1, 'json');
        }
        
        $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
        
        $client_account = $this->user['client_account'];
        $wherearr = array(
            "client_account='$client_account'",
            "homework_id='$homework_id'",
        );
        $homeworksend_list = $mClassHomeworkSend->getHomeworkSendByhomeworkidAndAccount($wherearr);
        if(empty($homeworksend_list)) {
            $this->ajaxReturn(null, '作业信息不存在!', -1, 'json');
        }
        
        //修改用户查看状态
        $homeworksend_id = key($homeworksend_list);
        $homeworksend_datas = array(
        	'is_view' => 1
        );
        if(!$mClassHomeworkSend->modifyHomeworkSend($homeworksend_datas, $homeworksend_id)) {
            $this->ajaxReturn(null, '回执失败!', -1, 'json');
        }
        
        $this->showSuccess(null, '回执成功!', 1, 'json');
    }
}