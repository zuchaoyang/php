<?php
class SystemcenterAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index() {
        $client_account = $this->user['client_account'];
        $class_code = $this->objInput->getInt('class_code');
        
        $data_arr = array();
        
        if(empty($class_code)) {
            $class_code = key($this->user['class_info']);
        }
        
        //获取班级最新公告
        import("@.Control.Api.Class.NoticeImpl.Notice");
        $notice_api = new Notice();
        $notice_info = $notice_api->getLastNoticeByClassCode($class_code);
        if(!empty($notice_info)) {
            $client_info = $this->getClientInfo($notice_info['add_account']);
            $client_info = reset($client_info);
            $data_arr['notice']['sys_id'] = $notice_info['notice_id'];
            $data_arr['notice']['sys_name'] = '班级公告';
            $data_arr['notice']['sys_img'] = IMG_SERVER.'/Public/sns/images/HomePage/main/icon_notice.jpg';
            $data_arr['notice']['sys_title'] = '<a href="/Sns/ClassNotice/Published/index/class_code/'.$class_code.'" class="fc_4f9f32 f14">'.$notice_info['notice_title'].'</a>';
            $data_arr['notice']['sys_content'] = $notice_info['notice_content'];
            $data_arr['notice']['sys_time'] = $notice_info['add_time'];
            $data_arr['notice']['client_name'] = $client_info['client_name'];
            
            unset($notice_info);            
        }
        //end
        
        //获取班级最新作业
        $mClassHomework = ClsFactory::Create("Model.ClassHomework.mClassHomework");
        $wherearr[] = "class_code={$class_code}";
        $orderby = "homework_id desc";
        $home_work = $mClassHomework->getClassHomework($wherearr, $orderby, 0, 1);
        unset($wherearr,$orderby);
        if(!empty($home_work)) {
            $home_work = reset($home_work);
            $client_info = $this->getClientInfo($home_work['add_account']);
            $client_info = reset($client_info);
            $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subject_infos = $mSubjectInfo->getSubjectInfoById($home_work['subject_id']);
            $subject_infos = reset($subject_infos);
            $data_arr['homework']['sys_name'] = '班级作业';
            $data_arr['homework']['sys_img'] = IMG_SERVER.'/Public/sns/images/HomePage/main/icon_homework.jpg';
            $data_arr['homework']['sys_id'] = $home_work['homework_id'];
            $data_arr['homework']['sys_title'] = '<a href="/Sns/ClassHomework/Published/index/class_code/'.$class_code.'" class="fc_4f9f32 f14">'.$subject_infos['subject_name'].'</a>';
            $data_arr['homework']['sys_content'] = '交作业日期 '.date('Y.m.d H.i',$home_work['end_time']);
            $data_arr['homework']['sys_time'] = date('Y.m.d H.i',$home_work['add_time']);
            $data_arr['homework']['client_name'] = $client_info['client_name'];
            
            unset($home_work);
        }
        //end
        //获取班级最新公布的成绩
        $mClassExam = ClsFactory::Create("Model.mClassExam");
        $wherearr[] = "class_code={$class_code}";
        $orderby = "exam_id desc";
        $exam_info = $mClassExam->getClassExam($wherearr, $orderby, 0, 1);
        unset($wherearr,$orderby);
        if(!empty($exam_info)) {
            $exam_info = reset($exam_info);
            $client_info = $this->getClientInfo($exam_info['add_account']);
            $client_info = reset($client_info);
            $data_arr['exam']['sys_name'] = '班级成绩';
            $data_arr['exam']['sys_img'] = IMG_SERVER.'/Public/sns/images/HomePage/main/icon_exam.jpg';
            $data_arr['exam']['sys_id'] = $exam_info['exam_id'];
            $data_arr['exam']['sys_title'] = '<a href="/Sns/ClassExam/View/index/exam_id/'.$exam_info['exam_id'].'" class="fc_4f9f32 f14">'.$exam_info['exam_name'].'</a>';
            $data_arr['exam']['sys_content'] = '考试日期 '.date('Y.m.d',$exam_info['exam_date']);
            $data_arr['exam']['sys_time'] = date('Y.m.d H.i',$exam_info['add_time']);
            $data_arr['exam']['client_name'] = $client_info['client_name'];
            
            unset($exam_info);
        }
        //end
        if(empty($data_arr)) {
            $this->ajaxReturn(null, '', -1, 'json');
        }
        $this->ajaxReturn($data_arr, '', 1, 'json');
    }
    //根据账号获取账号信息
    private function getClientInfo($client_account) {
        if(empty($client_account)) {
            return array('client_name'=>'错误信息');
        }
        
        $mUserVm = ClsFactory::Create('RModel.mUserVm');
        $client_info = $mUserVm->getClientAccountById($client_account);
        
        if(empty($client_info)) {
            return array('client_name'=>'错误信息');
        }
        
        return $client_info;
    }
}