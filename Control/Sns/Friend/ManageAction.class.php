<?php
class ManageAction extends SnsController{
    public function _initialize(){
        parent::_initialize();
    }
    
    /**********第一视觉我的好友***************/
    
    public function index() {
        $this->display('my_friend');
    }
    
   
    
    /**
     * 解除好友关系
     */
    public function delAccountRelationAjax() {
        $relation_account = $this->objInput->getInt('relation_account');
        
        $client_account   = $this->user['client_account'];
        //检测帐号正确性
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($client_account);
        $friends_account_arr = array();
        foreach($account_relation_infos[$client_account] as $relation_id => $account_relation) {
            $friends_account_arr[] = $account_relation['friend_account'];        
        }
        
        if(!in_array($relation_account,$friends_account_arr)){
            $this->ajaxReturn(null, '删除失败!', -1, 'json');
        }
        
        $AccountRelationInfos = $mAccountRelation->delAccountRelationByCompositeKey($client_account,$relation_account);
        
        $this->ajaxReturn(null,'删除成功！',1,'json');
    }
    
   /**
    * 搜索好友(全局)
    */
    public function search_friend() {
        $this->display('search_friend');
    }
    
    //初始化加载数据及搜索
    public function search_friend_json() {
        $search_name = $this->objInput->postStr('search_name');
        $search_account = $this->objInput->postStr('search_account');
        $client_account = $this->user['client_account'];
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $mUser = ClsFactory::Create('Model.mUser');
        if(!empty($search_account)) {
             $client_account_list = $mUser->getUserBaseByUid($search_account);
        } elseif (!empty($search_name)) {
             $client_account_info = $mUser->getUserByUsername($search_name,$offset,$limit);
             $client_account_arr = array_keys($client_account_info);
             $client_account_list = $mUser->getUserBaseByUid($client_account_arr);
        } else {
            $this->ajaxReturn(null,'暂无数据',-1,'json');
        }
        
        $mClientRelation = ClsFactory::Create('Model.mAccountrelation');
        $client_friends = $mClientRelation->getAccountRelationByClientAccout($client_account);
        
        
        $new_friend_accounts = array();
        foreach($client_friends[$client_account] as $relation_id => $friendrelation_info) {
            $new_friend_accounts[] = $friendrelation_info['friend_account'];
        }
        
        
        $new_friend_friend_arr = array();
        //判断好友的好友是否是登录人的好友
        $new_falg_arr = array();//标识是否是好友数组
        foreach ($client_account_list as $account=>$friend_account_val) {
            if(in_array($account,$new_friend_accounts)) {
                $client_account_list[$account]['is_friend'] = 1;//已是好友的标识
            } else {
                $new_friend_friend_arr[$account] = $account;
            }
        }
        
        //判断是否已给好友发送请求
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $MyFriendRequireInfo = $mMsgRequire->getMsgRequireByAddAccount($client_account,$new_friend_friend_arr);
        
        $to_account_arr = array();
        foreach($MyFriendRequireInfo as $req_id=>$require) {
            $to_account_arr[$require['to_account']] = $require['to_account'];
        }
        
        foreach ($client_account_list as $account=>$friend_account_val) {
            if(isset($friend_account_val['is_friend']) && $friend_account_val['is_friend'] == 1) {
                continue;
            }
            if(!empty($to_account_arr[$account])) {
                $client_account_list[$account]['is_friend'] = 2;//已发送好友请求标识
            } else {
                $client_account_list[$account]['is_friend'] = 3;//未发送好友请求标识
            }
        }
        
        if(empty($client_account_list)) {
            $this->ajaxReturn(null,'获取好友失败！',-1,'json');
        }
        
            $this->ajaxReturn($client_account_list,'获取好友成功！',1,'json');
    }
    
    /**
     * 我的好友中的搜索
     */
    public function search_my_friend() {
        $page        = $this->objInput->getInt('page');
        $search_name = $this->objInput->postStr('search_name');
        
        if(empty($search_name)) {
            $this->ajaxReturn(null, '搜索失败!', -1, 'json');
        }
        
        $page = max($page,1);
        $limit = 5;
        $offset = ($page - 1) * $limit;
        
        $client_account = $this->user['client_account'];
        
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($client_account, 'relation_id desc');
        $friends_account_arr = array();
        foreach($account_relation_infos[$client_account] as $relation_id => $account_relation) {
            $friends_account_arr[] = $account_relation['friend_account'];        
        }
        
        //通过好友名称获取好友帐号
        $mUser = ClsFactory::Create('Model.mUser');
        $client_account_info = $mUser->getClientAccountByAccountAndName($search_name, $friends_account_arr,$offset,$limit);
        
        //获取帐号查询client_info表
        $account_arr = array_keys($client_account_info);
        $client_info = $mUser->getUserBaseByUid($account_arr);
        if(empty($client_info)) {
            $this->ajaxReturn(null, '没有更多好友!', -1 ,'json');
        }
        
        $this->ajaxReturn($client_info,'获取好友成功!', 1 ,'json');
    }
    
   /**
     * 加载用户的好友列表
     */
    public function getMyFriendListAjax() {
        //$class_code = $this->objInput->getInt('class_code');
        //$class_code = $this->checkoutClassCode($class_code);
        
        $client_account = $this->user['client_account'];
        $page = $this->objInput->getInt('page');
        $limit = 5;
        $page = max($page,1);
        $offset = ($page-1)* $limit;
        
        $relation_infos = $this->getAccountRelationByClientAccount($client_account, 'relation_id desc', $offset, $limit);
        
        if(empty($relation_infos)) {
            $this->ajaxReturn(null, '没有更多好友!' , -1,'json');
        }
        
        $this->ajaxReturn($relation_infos,'获取列表成功！!' ,1,'json');
    }
    
    /**
     * 根据好友分组id获取好友列表
     */
    public function getMyFriendByGroupIdAjax() {
        $page = $this->objInput->getInt('page');
        $group_id = $this->objInput->postInt('group_id');
        
        if($group_id < 0) {
             $this->ajaxReturn(null,'获取好友列表失败!',-1,'json');
        }
        
        $page = max($page, 1);
        $limit = 10;
        $offset = ($page-1) * $limit;
        
        $arr_data = array(
            'add_account=' . $this->user['client_account'],
            'friend_group=' . $group_id,
        );
        $mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
        $friend_list = $mAccountrelation->getGroupFriendsByarrData($arr_data, $offset, $limit);
        //获取好友帐号
        $friend_uids = array();
        foreach($friend_list as $relation_id => $group_info) {
            $friend_uids[] = $group_info['friend_account'];
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid($friend_uids);
        
        if(!empty($user_list)) {
            foreach($friend_list as $relation_id=>$relation) {
                $friend_account = $relation['friend_account'];
                if(isset($user_list[$friend_account])) {
                    $relation = array_merge($relation, $user_list[$friend_account]);
                }
                $friend_list[$relation_id] = $relation;
            }
        }
        
        
        $this->ajaxReturn($friend_list, '获取好友列表成功!', 1, 'json');
    }
    
    /**
     * 他的好友（好友的好友列表）
     */
    
    public function getFriendByAccountFriend() {
        $friend_account = $this->objInput->getInt('friend_account');
        $this->assign('friend_account',$friend_account);
        $this->display('secondhand_friend');
    }
    
    public function getFriendByAccountFriend_json() {
        $search_name = $this->objInput->postStr('search_name');
        $friend_account = $this->objInput->postInt('friend_account');
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        $limit = 12;
        $offset = ($page-1) * $limit;
        
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_list = $mAccountRelation->getAccountRelationByClientAccout($friend_account,'relation_id desc',$offset,$limit);
       
        $friends_account_arr = array();
         //获取好友的好友帐号
        foreach($account_relation_list[$friend_account] as $relation_id => $account_relation) {
            $friends_account_arr[] = $account_relation['friend_account'];     
        }
        if(!empty($search_name)) {
            //通过好友名称获取好友帐号
            $mUser = ClsFactory::Create('Model.mUser');
            $client_account_info = $mUser->getClientAccountByAccountAndName($search_name, $friends_account_arr,$offset,$limit);
            $friends_account_arr = array_keys($client_account_info);
        }
        
        //获取当前登录人的好友信息
        $client_account = $this->user['client_account'];
        $client_account_relation_list = $mAccountRelation->getAccountRelationByClientAccout($client_account);
        //获取当前登录人的好友帐号
        $client_friends_account_arr = array();
        foreach($client_account_relation_list[$client_account] as $relation_id => $account_relation) {
            $client_friends_account_arr[$account_relation['friend_account']] = $account_relation['friend_account'];        
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $client_account_list = $mUser->getUserBaseByUid($friends_account_arr);
        
        $new_friend_friend_arr = array();
        //判断好友的好友是否是登录人的好友
        $new_falg_arr = array();//标识是否是好友数组
        foreach ($client_account_list as $account=>$friend_account_val) {
            if(in_array($account,$client_friends_account_arr)) {
                $client_account_list[$account]['is_friend'] = 1;//已是好友的标识
            } else {
                $new_friend_friend_arr[$account] = $account;
            }
        }
         //判断是否已给好友发送请求
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $MyFriendRequireInfo = $mMsgRequire->getMsgRequireByAddAccount($client_account,$new_friend_friend_arr);
        
        $to_account_arr = array();
        foreach($MyFriendRequireInfo as $req_id=>$require) {
            $to_account_arr[$require['to_account']] = $require['to_account'];
        }
        
        foreach ($client_account_list as $account=>$friend_account_val) {
            if(isset($friend_account_val['is_friend']) && $friend_account_val['is_friend'] == 1) {
                continue;
            }
            if(!empty($to_account_arr[$account])) {
                $client_account_list[$account]['is_friend'] = 2;//已发送好友请求标识
            } else {
                $client_account_list[$account]['is_friend'] = 3;//未发送好友请求标识
            }
        }
        
        if(empty($client_account_list)) {
            $this->ajaxReturn(null,'获取好友列表失败！',-1,'json');
        }
            $this->ajaxReturn($client_account_list,'获取好友列表成功!',1,'json');
    }
    
    
    /***************************************************************************
     * 好友关系管理
     **************************************************************************/
    public function sendPrivateSmgAjax() {
        $content         = $this->objInput->postStr('content');
        $friend_account  = $this->objInput->postStr('friend_account');
    
        //发送的图片信息在   $_FILES['pic']
        
        
        $this->ajaxReturn(null, '发送成功!', 1, 'json');
    }
    
    
    /***********************************************关于好友分组的管理******************************************/
    
    /**
     * 通过添加人账号获取好友分组列表
     *
     */
    public function getFriendGroupAjax() {
        
        $add_account = $this->user['client_account'];
        $mClientgroup = ClsFactory::Create('Model.mClientgroup');
        $ClientGrouplist = $mClientgroup->getClientGroupByaddAccount($add_account);
        $ClientGrouplist[0] =  array('group_id'=>0, 'group_name'=>'未分组');
        ksort($ClientGrouplist);
        $group_ids = array_keys($ClientGrouplist);
        
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        
        $account_relation_list = $mAccountRelation->getaccountrelationbyuid($add_account);
        $new_account_relation_list = array();
        foreach($account_relation_list as $relation_id => $relation_list) {
            if(!empty($relation_list['friend_group'])) {
                $new_account_relation_list[$relation_list['friend_group']][] = $relation_list;
            } else {
                $new_account_relation_list[0][] = $relation_list;
            }
        }
        
        $total_count = 0;
        //统计每个组里面有多少人
        foreach($ClientGrouplist as $group_id => $group_list) {
            $count = count($new_account_relation_list[$group_id]);
            if(empty($count)) {
                $count = 0;
            }
            $ClientGrouplist[$group_id]['count'] = $count;
            $total_count += $count;
        }
        
        $this->ajaxReturn($ClientGrouplist, $total_count, 1, 'json');
    }
    
    
    /**
     * 添加分组
     */
    
    public function addGroupAjax() {
        $client_account = $this->user['client_account'];
        $group_name = $this->objInput->postStr('group_name');
        $group_type = $this->objInput->postInt('group_type');
        if(empty($group_name)) {
            $group_type = 0;
        }
        
        $group_datas = array(
            'group_name'=> $group_name,
            'group_type' => $group_type,
            'add_account' => $client_account,
            'client_account' => $client_account,
            'add_date' => time(),
        );
        
        $mClientgroup = ClsFactory::Create('Model.mClientgroup');
        $group_id = $mClientgroup->addClientGroup($group_datas, true);
        if(empty($group_id)) {
            $this->ajaxReturn(null, '添加分组失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '添加分组成功!', 1, 'json');
    }
    
    /**
     * 修改分组
     */
    public function modify_group() {
        $group_id = $this->objInput->getInt('group_id');
        $group_name = $this->objInput->postStr('group_name');
        $dataarr = array(
            'group_name' => $group_name,
        );
        
        $mClientgroup = ClsFactory::Create('Model.mClientgroup');
        $modifyresault = $mClientgroup->modifyClientGroup($dataarr,$group_id);
        
        if(!empty($modifyresault)) {
             $this->ajaxReturn(null, '修改分组失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '修改分组成功!', 1, 'json');
    }
    
    /**
     * 移动好友到指定的分组
     */
    public function moveFriendGroupAjax() {
        $group_id       = $this->objInput->getInt('group_id');
        //$friend_account = $this->objInput->getStr('friend_account');
        $relation_id    = $this->objInput->getInt('relation_id');
        
        $dataarr = array(
            'friend_group'=>$group_id,
        );
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $resault = $mAccountRelation->modifyAccountRelation($dataarr, $relation_id);
        
        if(empty($resault)) {
            $this->ajaxReturn(null,'移动分组失败！',-1,'json');
        }
        
        $this->ajaxReturn(null,'移动分组成功！', 1, 'json');
    }
    
    
    /**
     * 删除分组(删除需要先将该组下的帐号移入默认分组)
     */
    public function del_group() {
        $group_id = $this->objInput->getInt('group_id');
        $client_account = $this->user['client_account'];
        
        //检测group_id正确性
        $mClientgroup = ClsFactory::Create('Model.mClientgroup');
        $client_group = $mClientgroup->getClientGroupByaddAccount($client_account);
        
        if(!in_array($group_id,array_keys($client_group))){
            $this->ajaxReturn(null, '删除分组失败!', -1, 'json');
        }
        
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $AccountRelationlist = $mAccountRelation->getGroupFriendsByFriendGroup($group_id);
        
        if(!empty($AccountRelationlist)) {
            $relation_ids_arr = array_keys($AccountRelationlist);
            $datarr = array(
                'friend_group' => 0
            );
            
            foreach($relation_ids_arr as $relation_id) {
                $effect_row = $mAccountRelation->modifyAccountRelation($datarr,$relation_id);
            }
        }
        
        //删除组名字
        $del_resault = $mClientgroup->delClientGroup($group_id);
       
        if(empty($del_resault)) {
            $this->ajaxReturn(null, '删除分组失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '删除分组失败!', 1, 'json');
    }
    
    
    /*****************************************************好友请求管理*******************************************/
 	
 	
 	/**
 	 * 
     *添加好友请求
     */
    public function add_friend() {
        $content = $this->objInput->postStr('content');
        $accept_account = $this->objInput->postInt('accept_account');
        $client_account = $this->user['client_account'];
        
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $req_msg_info = $mMsgRequire->getMsgRequireByAddAccount($client_account,$accept_account);
        
        if(empty($req_msg_info)) {
            $dataarr = array(
                'content' => $content,
                'to_account' => $accept_account,
                'add_account' => $client_account,
                'add_time' => time(),    
            );
            $req_id = $mMsgRequire->addMsgRequire($dataarr,'true');
        } else {
            $req_id = key($req_msg_info);
            $dataarr = array(
                'content' => $content,
                'to_account' => $accept_account,
                'add_account' => $client_account,
                'add_time' => time(),    
            );
            
            $resault = $mMsgRequire->modifyMsgRequire($dataarr,$req_id);
        }
        
        $mMsgNoticeList = ClsFactory::Create("RModel.Msg.mStringRequest");
        $mMsgNoticeList->publishMsg($req_id, 'req');
        if(empty($req_id)) {
            $this->ajaxReturn(null,'添加好友发送失败！',-1,'json');
        }
            $this->ajaxReturn($req_id,'添加好友发送成功！',1,'json');
    }
    
    
    /**
     * 展示我的好友请求列表
     */
    public function friend_request() {
        $this->display('friend_request');
    }
    
    public function friend_request_list() {
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $client_account  = $this->user['client_account'];
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $to_account_infos = $mMsgRequire->getMsgRequireByToAccount($client_account,$offset,$limit);
        
        $add_account_arr = array();
        foreach($to_account_infos[$client_account] as $req_id=>$req_info) {
            $add_account_arr[] = $req_info['add_account'];
            $add_account_req_info[$req_info['add_account']] = $req_id;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $client_account_list = $mUser->getUserBaseByUid($add_account_arr);
        
        foreach($client_account_list as $account=>$client_info) {
           $client_info['req_id'] = $add_account_req_info[$account];
           $client_account_list[$account] = $client_info;
        }
        
        if(empty($client_account_list)) {
            $this->ajaxReturn(null,'获取列表失败！',-1,'json');
        }
            $this->ajaxReturn($client_account_list,'获取列表成功！',1,'json');
    }
    
    
    /**
     * 处理好友请求
     */
    public function do_friend_response() {
        $friend_account = $this->objInput->postInt('friend_account');
        $client_account = $this->user['client_account'];
        $friend_name = $this->objInput->postStr('friend_name');
        $req_id = $this->objInput->postInt('req_id');
        
        
        $mMsgNoticeList = ClsFactory::Create("RModel.Msg.mStringRequest");
        if(!empty($friend_account)) {
            //同意好友请求
            $dataarr = array(
                'content'=> $client_account . '同意了您的好友请求',
                'to_account' => $friend_account,
                'add_account' => $client_account,
                'add_time' => time(),
            );
            
            $mMsgResponse = ClsFactory::Create('Model.Message.mMsgResponse');
            $res_id = $mMsgResponse->addMsgResponse($dataarr,'true');
            $mMsgNoticeList->publishMsg($res_id, 'res');
            if($res_id) {
                $accountrelation_arr = array(
                    'client_account' => $client_account,
                    'friend_account' => $friend_account,
                    'friend_group' => 0,
                    'add_account' => $client_account,
                    'add_date' => time()
                );
                $mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
                $resault = $mAccountrelation->addAccountRelation($accountrelation_arr);
            }
        }
        
        //删除消息请求
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $resault = $mMsgRequire->delMsgRequire($req_id);
        $mMsgNoticeList->decrMsg($client_account);
        $this->ajaxReturn(null,'同意请求成功！',1,'json');
    }
    
    
    /*********************************************************最近访客管理***********************************************/
    
    /**
     * 最近访客
     */
    
    public function person_vistior() {
        $client_account = $this->user['client_account'];
       
        import('@.Control.Api.VistiorApi');
        $Vistior = new VistiorApi();
        
        $total_count = $Vistior->total_count($client_account);
        $total_count_day = $Vistior->total_count_day($client_account);
        
        $this->assign('total_count_day',$total_count_day);
        $this->assign('total_count',$total_count);
        
        $this->display('nearest_visitor');
    }
    
    /**
     * 最近访客列表
     */
    public function person_vistior_list_ajax() {
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $client_account = $this->user['client_account'];
        
         $wheresql = array(
            'uid = ' . $client_account,
            'timeline <= ' . time(), 
        );
        
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $vistior_list = $mPersonVistior->getPersonVistiorInfo($wheresql,'id desc',$offset,$limit);
        
        $vistior_account_arr = array_keys($vistior_list);
        
        //最近访客的信息
        $mUser = ClsFactory::Create('Model.mUser');
        $client_account_list = $mUser->getUserBaseByUid($vistior_account_arr);
        
        //获取当前登录人的好友信息
        $client_account_relation_list = $this->getAccountRelationByClientAccount($client_account);
        //获取当前登录人的好友帐号
        $client_friends_account_arr = array();
        foreach($client_account_relation_list[$client_account] as $relation_id => $account_relation) {
            $client_friends_account_arr[$account_relation['friend_account']] = $account_relation['friend_account'];        
        }
        
        
        $new_friend_friend_arr = array();
      //判断好友的好友是否是登录人的好友
        $new_falg_arr = array();//标识是否是好友数组
        foreach ($client_account_list as $account=>$vistior_val) {
            if(in_array($account,$client_friends_account_arr)) {
                $client_account_list[$account]['is_friend'] = 1;//已是好友的标识
            } else {
                $new_friend_friend_arr[$account] = $account;
            }
        }
        
         //判断是否已给好友发送请求
        $mMsgRequire = ClsFactory::Create('Model.Message.mMsgRequire');
        $MyFriendRequireInfo = $mMsgRequire->getMsgRequireByAddAccount($client_account,$new_friend_friend_arr);
        
        $to_account_arr = array();
        foreach($MyFriendRequireInfo as $req_id=>$require) {
            $to_account_arr[$require['to_account']] = $require['to_account'];
        }
        
        $new_client_account_list_sort = array();
        foreach ($client_account_list as $account=>$account_val) {
            
            if(isset($account_val['is_friend']) && $account_val['is_friend'] == 1) {
                continue;
            }
            if(!empty($to_account_arr[$account])) {
                $client_account_list[$account]['is_friend'] = 2;//已发送好友请求标识
            } else {
                $client_account_list[$account]['is_friend'] = 3;//未发送好友请求标识
            }
            
            $new_client_account_list_sort[] = $vistior_list[$account]['timeline'];
            $client_account_list[$account]['timeline'] = date('Y-m-d H:i:s',$vistior_list[$account]['timeline']);
            $client_account_list[$account]['id'] = $vistior_list[$account]['id'];
        }
        
        //按照时间排序
        array_multisort($new_client_account_list_sort, SORT_DESC,$client_account_list);
        if($client_account_list) {
            $this->ajaxReturn($client_account_list,'获取访客列表成功',1,'json');
        }
        
        $this->ajaxReturn(null,'获取访客列表失败',-1,'json');
    }
    
    //删除最近访客信息
    public function del_vistior() {
        $id = $this->objInput->getInt('id');
        if(empty($id)) {
            $this->ajaxReturn(null,'参数错误',-1,'json');
        }
        
        $uid = $this->user['client_account'];
        //获取当前登录人的所有访客id检测$id是否正确
        $mPersonVistior = ClsFactory::Create('Model.PersonVistior.mPersonVistior');
        $PersonVistiorInfo = $mPersonVistior->getPersonVistiorByUid($uid);
        
        $ids = array_keys(array_shift($PersonVistiorInfo));
        if(in_array($id,$ids)) {
            $resault = $mPersonVistior->delPersonVistior($id);
        }
        if($resault) {
             $this->ajaxReturn($resault,'删除最近访客成功！',1,'json');
        }
        
        $this->ajaxReturn(null,'删除最近访客失败',-1,'json');
    }
    
    
    /**
     * 公共的根据账号查询好友信息
     */
    private function getAccountRelationByClientAccount($client_account, $orderby, $offset, $limit) {
        //获取帐号的好友帐号
        $mAccountRelation = ClsFactory::Create('Model.mAccountrelation');
        $account_relation_infos = $mAccountRelation->getAccountRelationByClientAccout($client_account,$orderby,$offset,$limit);
        $friends_account_arr = array();
        foreach($account_relation_infos[$client_account] as $relation_id => $account_relation) {
            $friends_account_arr[] = $account_relation['friend_account'];        
        }
        //帐号追加组信息
        $mClientgroup = ClsFactory::Create('Model.mClientgroup');
        $client_group_infos = $mClientgroup->getClientGroupByaddAccount($client_account);
        
        //通过帐号获取好友信息
        $mUser = ClsFactory::Create('Model.mUser');
        $client_infos = $mUser->getUserBaseByUid($friends_account_arr);
        
        $new_client_info = array();
        foreach($account_relation_infos[$client_account] as $relaiton_id=>$relation_infos) {
            $relation_infos['client_name'] = $client_infos[$relation_infos['friend_account']]['client_name'];
            $relation_infos['client_headimg_url'] = $client_infos[$relation_infos['friend_account']]['client_headimg_url'];
            $relation_infos['client_sex'] = $client_infos[$relation_infos['friend_account']]['client_sex'];
            $relation_infos['group_name'] = $client_group_infos[$relation_infos['friend_group']]['group_name'];
            $relation_infos['group_id'] = $client_group_infos[$relation_infos['friend_group']]['group_id'];
            $new_client_info[$relation_infos['friend_account']] = $relation_infos;
        }
        
        return !empty($new_client_info) ? $new_client_info : false;
    }
    
}