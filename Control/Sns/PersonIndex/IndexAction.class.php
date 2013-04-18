<?php
class IndexAction extends SnsController {

    public function _initialize(){
        parent::_initialize();
    }
    
    
    public function index() {
        $vuid = $this->objInput->getInt('client_account');
        $client_account = $this->user['client_account'];
        
        $vuid = empty($vuid) ? $client_account : $vuid;
        
        //判断是否有新的访客
        if($vuid != $client_account) {
            if(!$this->check_space($vuid)) {
                $this->showError('个人空间访问受限！','/Sns/ClassIndex/Index/index');
            }
            $resault = $this->write_vistior($vuid);
        }
        //统计帐号好友
         //获取帐号的好友帐号
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($vuid,'relation_id desc');
        $friend_total_count = count($account_relation_infos[$vuid]);
        
        //统计今日访客和总访客数
        import('@.Control.Api.VistiorApi');
        $Vistior = new VistiorApi();
        $total_count = $Vistior->total_count($vuid);
        $total_count_day = $Vistior->total_count_day($vuid);
        
        //统计当前用户的活跃值和当天获取的活跃值
        import('@.Control.Api.ActiveApi');
        $Active = new ActiveApi();
        $active_count = array_shift($Active->client_active($vuid));
        list($active_log, $active_count_day) = $Active->client_active_log($vuid);
        
        //获取个人帐号的基本信息
        $mUSer = ClsFactory::Create('Model.mUser');
        $user_list = $mUSer->getUserBaseByUid($vuid);
        
        $user_list[$vuid]['active_count_day'] = $active_count_day;
        $user_list[$vuid]['active_count'] = $active_count['value'];
        $user_list[$vuid]['friend_total_count'] = $friend_total_count;
        $user_list[$vuid]['total_count_day'] = $total_count_day;
        $user_list[$vuid]['total_count'] = $total_count;
        
        $this->assign('user_list',array_shift($user_list));
        $this->assign('vuid',$vuid);
        $this->assign('client_account', $client_account);
        
        $this->display("main_first");
    }
    
    public function get_vistior_list_ajax() {
        $client_account = $this->objInput->getStr('vuid');
        if(empty($client_account) ) {
            $client_account = $this->user['client_account'];
        }
       
        import('@.Control.Api.VistiorApi');
        $Vistior = new VistiorApi();
        $offset = 0;
        $limit = 5;
        
        $vistior_list = $Vistior->vistior_list($client_account,'timeline desc',$offset,$limit);
        
        if(empty($vistior_list)) {
            $this->ajaxReturn(null,'获取访客列表失败！',-1,'json');
        }
        
            $this->ajaxReturn($vistior_list,'获取访客列表成功！',1,'json');
    }
    
    
    //首页获取好友列表
    public function get_friend_list_ajax() {
        $friend_num = 8;
        $vuid = $this->objInput->getStr('vuid');
        
        //获取帐号的好友帐号
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($vuid,'relation_id desc');
        
        $friends_account_arr = array();
        foreach($account_relation_infos[$vuid] as $relation_id => $account_relation) {
            $friends_account_arr[$account_relation['friend_account']] = $account_relation['friend_account'];        
        }
        
        $wheresql = array(
            'uid='.$vuid,
        );
        
        //获取最近访客列表及是该帐号好友的头像
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $vistior_list = $mPersonVistior->getPersonVistiorInfo($wheresql,'timeline desc');
        
        $new_vistior_account_arr = array();
        if(!empty($vistior_list)) {
            foreach($vistior_list as $key_vuid=>$vistior_val) {
                if(in_array($key_vuid,$friends_account_arr)) {
                    $new_vistior_account_arr[] = $key_vuid;
                    unset($friends_account_arr[$key_vuid]);
                }
            }
        }
        
        $tmp_friends_arr = array();
        $new_vistior_count = count($new_vistior_account_arr);
        if($new_vistior_count < $friend_num) {
            $tmp_friends_arr = array_slice($friends_account_arr, 0, $friend_num-$new_vistior_count);
        }
        
        //获取好友帐号与访客的交集
        $new_tmp_friends_arr = array_merge($tmp_friends_arr, $new_vistior_account_arr);
        
        //通过帐号获取好友信息
        $mUser = ClsFactory::Create('Model.mUser');
        $client_infos = $mUser->getUserBaseByUid($new_tmp_friends_arr);
        
        if(empty($client_infos)) {
            $this->ajaxReturn(null,'获取好友列表失败！',-1,'json');
        }
            $this->ajaxReturn($client_infos,'获取好友列表成功！',1,'json');
    }
    
    
    /**
     * 记录最近访客列表
     */
    private function write_vistior($vuid) {
        $client_account = $this->user['client_account'];
        if(empty($vuid) || $vuid == $client_account) {
             $this->redirect("/Sns/Index/index/client_account/$client_account");
        }
        
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $wheresql = array(
            'uid='.$vuid,
            'vuid='.$client_account
        );
        $resault_vistior = $mPersonVistior->getPersonVistiorInfo($wheresql);
        $dataarr = array(
                'uid' => $vuid,
                'vuid'=> $client_account,
                'timeline' => time()
         );
        if(empty($resault_vistior)) {
            $resault = $mPersonVistior->addPersonVistior($dataarr);
        } else {
          $vistior_id = array_shift($resault_vistior['id']);
          $resault = $mPersonVistior->modifyPersonVistior($dataarr,$vistior_id);
        }
        
        return !empty($resault) ? $resault : false;
    }
    
    /*
     * 检测个人空间访问权限
     */
    
    private function check_space($vuid) {
        if(empty($vuid)) {
            return false;
        }
        
        $client_account = $this->user['client_account'];
        $mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
        $user_personconfig_list = $mPersonconfig->getPersonConfigByaccount($vuid);
        $space_access = $user_personconfig_list[$vuid]['space_access'];
        
        if(empty($space_access) || $space_access == 0) {
            $access_fl = true;
        } elseif($space_access == 1) {
            $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
            $vuid_relation_list = $mAccountRelation->getAccountRelationByClientAccout($vuid);
            //获取当前登录人的好友帐号
            $friend_accounts = array();
            foreach($vuid_relation_list[$vuid] as $relation_id => $account_relation) {
                $friend_accounts[$account_relation['friend_account']] = $account_relation['friend_account'];        
            }
            
            $access_fl = in_array($client_account,$friend_accounts) ?  true : false;
        } else {
            $access_fl = false;
        }
        
        return $access_fl;
    }
        
}