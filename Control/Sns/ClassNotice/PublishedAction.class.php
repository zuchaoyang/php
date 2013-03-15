<?php
class PublishedAction extends SnsController {
    public function _initialize() {
          parent::_initialize();
    }
    
    /**
     * 展示公告列表信息
     */
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
         
        //获取用户的管理权限
        import('@.Control.Sns.ClassNotice.Ext.NoticeContext');
        $context = new NoticeContext($this->user);
        $access_list = $context->getUserAccessList();
        
        $this->assign('user', $this->user);
        $this->assign('access_list', $access_list);
        $this->assign('class_code', $class_code);
        
        $this->display('list');
    }
    
    /**
     * list页面加载ajax
     */
    public function getNoticeListAjax() {
        $class_code = $this->objInput->postStr('class_code');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        $page       = $this->objInput->postInt('page');
        
        $page = max(1,$page);
        //管理员拥有管理权限
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, "您暂时没有权限查看该班公告信息!", -1, 'json');
        }
        
        $limit = 10;
        $offset = ($page-1) * $limit;
        
        $where_appends = array(
        	"class_code='$class_code'"
        );
        //开始时间限制
        if(!empty($start_time) && ($start_time = strtotime($start_time)) !== false) {
            $where_appends[] = "add_time>='$start_time'";
        }
        //结束时间限制
        if(!empty($end_time) && ($end_time = strtotime($end_time)) !== false) {
            $where_appends[] = "add_time<='" . ($end_time + 86400) . "'";
        }
        //获取公告的列表信息
        $mClassNotice = ClsFactory::Create('Model.ClassNotice.mClassNotice');
        $notice_list = $mClassNotice->getClassNoticeByClassCodeAndDate($where_appends, 'notice_id desc', $offset, $limit);
        
        if(empty($notice_list)) {
            $this->ajaxReturn($notice_list, '没有更多了!', -1, 'json');
        }
        
        //解析公告的基本信息
        $notice_list = $this->formatNoticeList($notice_list);
        
        //获取用户的管理权限
        import('@.Control.Sns.ClassNotice.Ext.NoticeContext');
        $context = new NoticeContext($this->user, $class_code);
        $manage_access_list = $context->getUserAccessList();
        
        //解析用户对公告的管理权限
        $notice_list = $this->appendUserNoticeAccess($notice_list, $manage_access_list);
        
        $this->ajaxReturn($notice_list, '公告获取成功!', 1, 'json');
    } 
    
    /**
     * 根据帐号查看回执信息
     */
    public function getNoticeAcceptersAjax() {
        $notice_id = $this->objInput->postInt('notice_id');
        $class_code = $this->objInput->postInt('class_code');
        
        if(empty($class_code)) {
            $class_code = key($this->user['class_info']);
        }
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $class_infos = $mClientClass->getStudentInfoByClassCodeAndType($class_code,CLIENT_TYPE_STUDENT);
        //获取学生帐号
        $student_accounts = array();
        foreach($class_infos as $client_class_info) {
            $student_accounts[] = $client_class_info['client_account'];
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $student_infos = $mUser->getClientAccountById($student_accounts);
        
        $mClassNoticeFoot = ClsFactory::Create('Model.ClassNotice.mClassNoticeFoot');
        $ClassNoticeFootInfos = array_shift($mClassNoticeFoot->getClassNoticeFootByNoticeId($notice_id));
        $New_ClassNoticeFootInfos = array();
        foreach($ClassNoticeFootInfos as $id=>$notice_infos) {
            $New_ClassNoticeFootInfos[$notice_infos['client_account']] = $notice_infos;
        }
        
        $viewed_num = count($ClassNoticeFootInfos);
        $total_account = count($student_accounts);
        $no_view_num = $total_account - $viewed_num;
        
        $countarr = array(
        	'no_view_list'=>array(),
        	'viewed_list'=>array(),
            'no_view_num'=> $no_view_num,
            'viewed_num'=> $viewed_num,
        );
        
        foreach($student_infos as $client_account=>$client_infos) {
            if(!empty($New_ClassNoticeFootInfos[$client_account])) {
                $countarr['viewed_list'][$client_account]=$client_infos;
            } else {
                $countarr['no_view_list'][$client_account]=$client_infos;
            }
            unset($student_infos[$client_account]);
        }
        
        return !empty($countarr) ?  $this->ajaxReturn($countarr, '获取对象成功', 1, 'json') :  $this->ajaxReturn($countarr, '获取对象失败', -1, 'json');
    }
    
    /**
     * 解析公告的相关信息
     * @param $notice_list
     */
    private function formatNoticeList($notice_list) {
        if(empty($notice_list)) {
            return false;
        }
        
         //获取添加用户的相关信息
        $uids = array();
        foreach($notice_list as $notice_id=>$notice) {
            $uids[] = $notice['add_account'];
        }
        //根据帐号获取姓名
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getClientAccountById($uids);
        
        foreach($notice_list as $notice_id=>$notice) {
            $notice['add_time'] = date('Y-m-d', $notice['add_time']);
            $notice['notice_content'] = htmlspecialchars_decode($notice['notice_content']);
            //添加人姓名
            $add_account = $notice['add_account'];
            $notice['client_name'] = isset($user_list[$add_account]) ? $user_list[$add_account]['client_name'] : "暂无";
            
            $notice_list[$notice_id] = $notice;
        }
        
        return $notice_list;
    }
    
    /**
     * 解析用户对公告的权限
     * @param $notice_list
     * @param $manage_access_list
     */
    private function appendUserNoticeAccess($notice_list, $manage_access_list = array()) {
        if(empty($notice_list)) {
            return false;
        }
        
        //批量获取用户对公告的回执状态(只有用户类型为学生时处理)
        $notice_foot_list = array();
        if($this->user['client_type'] == CLIENT_TYPE_STUDENT) {
            $where_appends = array(
                "notice_id in('" . implode("','", array_keys($notice_list)) . "')"
            );
            $mClassNoticeFoot = ClsFactory::Create('Model.ClassNotice.mClassNoticeFoot');
            $exists_notice_foot_list = $mClassNoticeFoot->getNoticeFootByClientAccount($this->user['client_account'], $where_appends);
            
            //以notice_id为key重新组织数据
            if(!empty($exists_notice_foot_list)) {
                foreach($exists_notice_foot_list as $id=>$notice_foot) {
                    $notice_foot_list[$notice_foot['notice_id']] = $notice_foot;
                }
            }
        }
        
        foreach($notice_list as $notice_id=>$notice) {
            //处理用户是否显示"老师，我知道了"按钮
            $append_access_list = array();
            if($manage_access_list['show_know_btn'] && isset($notice_foot_list[$notice_id])) {
                $append_access_list['show_know_btn'] = false;
            }
            //处理是否显示"删除"按钮
            if($notice['add_account'] == $this->user['client_account']) {
                $append_access_list['can_delete'] = true;
            }
            $notice['notice_access_list'] = array_merge((array)$manage_access_list, (array)$append_access_list);
            
            $notice_list[$notice_id] = $notice;
        }
        
        return $notice_list;
    }
    
}