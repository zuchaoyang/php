<?php
class IndexAction extends SnsController {

    public function _initialize(){
        parent::_initialize();
    }
    
    
    public function index() {
        $vuid = $this->objInput->getInt('client_account');
        $client_account = $this->user['client_account'];
        
        $vuid = empty($vuid) ? $client_account : $vuid;
        if($vuid != $client_account) {
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
        
        $this->assign('vuid',$vuid);
        $this->assign('friend_total_count',$friend_total_count);
        $this->assign('client_account',$client_account);
        $this->assign('total_count_day',$total_count_day);
        $this->assign('total_count',$total_count);
        $this->display("main_first");
    }
    
    
    public function get_vistior_list_ajax() {
        $client_account = $this->objInput->getInt('vuid');
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
        $vuid = $this->objInput->getInt('vuid');
        $client_account = $this->user['client_account'];
        $vuid = empty($vuid) ? $client_account : $vuid;
        
        //获取帐号的好友帐号
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($vuid,'relation_id desc');
        $friends_account_arr = array();
        foreach($account_relation_infos[$client_account] as $relation_id => $account_relation) {
            $friends_account_arr[$account_relation['friend_account']] = $account_relation['friend_account'];        
        }
        
        $wheresql = array(
            'uid='.$client_account,
        );
        
        //获取最近访客列表及是该帐号好友的头像
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $vistior_list = $mPersonVistior->getPersonVistiorInfo($wheresql,'timeline desc');
        
        
        foreach($vistior_list as $vuid=>$vistior_val) {
            if(in_array($vuid,$friends_account_arr)) {
                $new_vistior_account_arr[] = $vuid;
                unset($friends_account_arr[$vuid]);
            }
        }
        $new_vistior_count = count($new_vistior_account_arr);
        $tmp_friends_arr = array();
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
        $vistior_id = array_shift($resault_vistior['id']);
        
        $dataarr = array(
                'uid' => $client_account,
                'vuid'=> $vuid,
                'timeline' => time()
         );
        if(empty($resault_vistior)) {
            $resault = $mPersonVistior->addPersonVistior($dataarr);
        } else {
          $resault = $mPersonVistior->modifyPersonVistior($dataarr,$vistior_id);
        }
        
        return !empty($resault) ? $resault : false;
    }
        
}