<?php
class SmsmultipleAction extends WmsController {

    public function _initialize() {
        header ( "Content-Type:text/html; charset=utf-8" );
        parent::_initialize();
    }
    //短信信息入库
    public function sendmultiple() {
        $send_id = $this->objInput->getInt('send_id');
        if (empty($send_id )) {
            $mUser = ClsFactory::Create ('Model.mUser');
            $client_account = $this->user['wms_account'];
            $Userinfo = $mUser->getUserBaseByUid ( $client_account );
            $this->assign ( 'username', $Userinfo [$client_account] ['client_name'] );
            unset ( $mUser );
            unset ( $client_account );
            unset ( $Userinfo );
            
        } else {
            $this_time = $this->objInput->getInt('this_time');
            $mSendTmp = ClsFactory::Create('Model.mSendTmp');
            $sms_list = $mSendTmp->getSendTmpInfoById($send_id);
            foreach($sms_list as $key=>$val) {
                $sms_list[$key] = array_merge($val,array(
                    'date_ymd' => date('Y-m-d',$val['send_time']),
                    'date_h' => date('H',$val['send_time']),
                    'date_i' => date('i',$val['send_time']),
                    'operation_strategy_keys' => explode(',', $val['operation_strategy']),
                    'send_range_keys' => explode(',', $val['send_range'])
                ));
            }
            $this->assign('this_time',$this_time);
            $this->assign('sms_info',$sms_list[$send_id]);
            $this->assign('modify','modifySmsInfo');
        }
        
        $this->display ('smsbluk');
    }
    
    
    function addsendtmp() {
        $message = '';
        $backurl = $_SERVER['HTTP_REFERER'];
        $rs=$this->getReasult($message,$backurl);
        if(empty($rs)) {
            $this->showError('添加失败，见返回添加页面！', $backurl);
        }else{
            $this->showSuccess('添加成功！', $backurl);//未添加返回连接
        }
        
    }
    public function getReasult($message,$backurl,$modify_id) {
        $recipient_type = $this->objInput->postArr ( 'recipient_type' );
        $operation_strategy = $this->objInput->postArr('operation_strategy');
        $sms_content = $this->objInput->postStr ( 'smscontent' );
        $list_time = $this->objInput->postStr ( 'list_time' );
        $hour_time = $this->objInput->postStr ( 'hour_time' );
        $sec_time = $this->objInput->postStr ( 'sec_time' );
        $message = '';
        if(empty($recipient_type)){
            $message = "请选择发送范围！";
        }elseif(empty($operation_strategy)){
            $message = "请收件学校！";
        }elseif(empty($sms_content)) {
            $message = "请填写发送内容！";
        }
        if(!empty($message)) {
            $this->showError($message, $backurl);
        }
        //发送时间为空时，默认当前时间推后5分钟发送（及时发送）
        if(empty($list_time) && empty($hour_time) && empty($sec_time)){
            $send_time = time();
            $real_send_time = $send_time+5*60;
        }else{
            $send_time_str = $list_time .' '. $hour_time .':'. $sec_time .':00';
            $send_time =  strtotime($send_time_str);
            $real_send_time = $send_time;
        }
        if($send_time<time()) {
            $this->showError('所选时间不能比当前时间小，见返回添加页面！', $backurl);
        }
        import('@.Control.Api.Smssend.Smssendtmp');
        $Smssendtmp = new Smssendtmp();
        $dataarr = array(
            'sms_content'		 => $sms_content,
            'operation_strategy' => $operation_strategy,
            'recipient_type'	 => $recipient_type,
            'send_time'		     => $send_time,
            'real_send_time'	 => $real_send_time,
            'add_uid'            => $this->user['wms_account'],
        	'id'                 => $modify_id,
        );
        $rs = $Smssendtmp->operateSmsTmp($dataarr);
        
        return $rs;
    }
    
    public function getSmslist() {
        $search_time = $this->objInput->postStr('search_time');
        $this_time = $this->objInput->getInt('this_time');
        
        if(!empty($this_time)) {
            $search_time = date('Y-m-d', $this_time);
        }else{
            $this_time = '';
        }
        
        if(!empty($search_time)) {
            $send_time = strtotime($search_time);
        }else{
            $send_time = '';
            $search_time = '';
        }
        $page = $this->objInput->getInt('page');
        $page = max(1,$page);
        $limit = 10;
        $offset = ($page-1)*$limit;
        $page_list['pre_page'] = $page-1;
        
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $sms_list = $mSendTmp->getSendTmpInfoBySendtime($send_time,$offset,$limit+1);
        if(count($sms_list)>$limit) {
            array_shift($sms_list);
            $page_list['next_page'] = $page+1;
        }else{
            $this->assign('is_last_page',true);
            $page_list['next_page'] = $page;
        }
        import("@.Common_wmw.WmwString");
        foreach($sms_list as $key=>$val) {
            $sms_list[$key] = array_merge($val,array(
                'send_date' => date('Y-m-d H:i',$val['send_time']),
                'pre_content' => WmwString::mbstrcut($val['content'], 0, 15)
            ));
        }
        
        $this->assign('sms_list',$sms_list);
        $this->assign('page_list',$page_list);
        $this->assign('page',$page);
        $this->assign('search_time',$search_time);
        $this->assign('this_time',$send_time);
        $this->display('sms_list');
    }
    
    public function viewSmsInfo() {
        $id = $this->objInput->getInt('send_id');
        $message = '';
        $backurl = $_SERVER['HTTP_REFERER'];
        if(empty($id)) {
           $this->showError('系统错误请重试！！！', $backurl);
        }
        $operation_strategy_arr = array(
            OPERATION_STRATEGY_HLJ=>"黑龙江区域所有学校",
            OPERATION_STRATEGY_JL=>"吉林区域所有学校",
            OPERATION_STRATEGY_GD=>"广东区域所有学校",
            OPERATION_STRATEGY_LN=>"辽宁区域所有学校"
        );
        $send_range_arr = array(
            1=>"老师",
            2=>"家长",
        );
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $sms_list = $mSendTmp->getSendTmpInfoById($id);
        foreach($sms_list as $key=>$val) {
            $sms_list[$key]['send_date'] = date('Y/m/d H时i分',$val['send_time']);
            $operation_strategy_keys = explode(',', $val['operation_strategy']);
            $send_range_keys = explode(',', $val['send_range']);
            
            $sms_list[$key]['operation_strategy_str'] = $sms_list[$key]['send_range_str'] = '';
            foreach($operation_strategy_keys as $operation_strategy_key) {
                $sms_list[$key]['operation_strategy_str'] .= $operation_strategy_arr[$operation_strategy_key].'&nbsp;&nbsp';
            }
            foreach($send_range_keys as $send_range_key) {
                $sms_list[$key]['send_range_str'] .= $send_range_arr[$send_range_key].'&nbsp;&nbsp';
            }
        }
        
        $this->assign('sms_info', $sms_list[$id]);
        
        $this->display('viewSmsinfo');
    }
    
    public function modifySmsInfo() {
        $id = $this->objInput->postInt('send_id');
        $this_time = $this->objInput->postInt('this_time');
        $message = '';
        $backurl = $_SERVER['HTTP_REFERER'];
        if(empty($id)) {
            $this->showError('系统错误请重试！！！', $backurl);
        }
        
        $rs=$this->getReasult($message, $backurl, $id);
       
        if(empty($rs)) {
            $this->showError('修改失败，见返回修改页面！', $backurl);
        }else{
            $backurl = '/Smssend/Smsmultiple/getSmslist';
           if(!empty($this_time)) {
               $backurl .='/this_time/'.$this_time;
           }
            $this->showSuccess('修改成功！', $backurl);
        }
    }
    
    public function delSmsInfo() {
        $id = $this->objInput->getInt('send_id');
        $this_time = $this->objInput->getInt('this_time');
        $message = '';
        $backurl = $_SERVER['HTTP_REFERER'];
        if(empty($id)) {
           $message = '系统错误请重试！！！';
           $this->showError($message, $backurl);
        }
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $rs = $mSendTmp->delSendTmpInfoById($id);
        
        if(empty($rs)) {
           $message = '删除失败！';
           $this->showError($message, $backurl);
        }else{
           $message = '删除成功！';
           $backurl = '/Smssend/Smsmultiple/getSmslist';
           if(!empty($this_time)) {
               $backurl .='/this_time/'.$this_time;
           }
           $this->showSuccess($message, $backurl);
        }
        
    }

}