<?php
class IndexAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    public function index() {
        $client_account = $this->user['client_account'];
        $class_code = $this->objInput->getInt('class_code');
        if(empty($class_code)) {
            $class_code = key($this->user['class_info']);
        }
        if(!in_array($class_code,array_keys($this->user['class_info']))) {
            $this->showError('操作错误','/Sns/HomePage/Index');
        }
        //日期
        $today_date = date('m.d',time());
        //星期
        $weekarray=array("日","一","二","三","四","五","六");  
        $week_day = $weekarray[date("w",time())];
        
        //检测是否签到
        import('@.Control.Api.CheckinTmpl.Checkin');
        $checkinObj = new Checkin();
        $is_qd = $checkinObj->is_checkin($client_account);
       
        //统计今日访客和总访客数
        import('@.Control.Api.VistiorApi');
        $Vistior = new VistiorApi();
        $total_count = $Vistior->total_count($client_account);
        $total_count_day = $Vistior->total_count_day($client_account);
        
        $this->assign('total_count', $total_count);
        $this->assign('total_count_day', $total_count_day);
        
        $this->assign('is_qd', $is_qd);
        $this->assign('today_date', $today_date);
        $this->assign('week_day', $week_day);
        $this->assign('user_info',$this->user);
        $this->assign('client_account', $client_account);
        $this->assign('class_code', $class_code);
//        $this->assign('user', $this->user);
//        echo '<pre>';
//        print_r($this->user);
        $this->display("main");    
    }
    
    public function chckinOk() {
        $client_account = $this->user['client_account'];
        //检测是否签到
        import('@.Control.Api.CheckinTmpl.Checkin');
        $checkinObj = new Checkin();
        if($checkinObj->is_checkin()) {
            $this->ajaxReturn(null, '已签到', 1, 'json');
        }
        $is_qd = $checkinObj->add_sign($client_account);
        if(empty($is_qd)) {
            $this->ajaxReturn(null, '签到失败', -1, 'json');
        }
        $this->ajaxReturn(null, '签到成功', 1, 'json');
    }
}