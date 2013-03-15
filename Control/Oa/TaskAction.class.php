<?php
define("TASK_CONTENT_LENGTH", 5000);    //工作内容长度
define('SECRET_KEY', 'oa_task');        //设置草稿的密钥
define('PAGINAL_NUM',10);
class TaskAction extends OaController {
    protected $user = array();

    public function _initialize() {
        parent::_initialize();

        import("@.Common_wmw.WmwString");
        
	    $this->assign('uid', $this->user['client_account']);
    }

    protected $default_list = array(
        'task_title' => '输入标题，内容不超过55个字...',
        'tag_names' => '请输入标签名...',
    );

    public function index() {
        $this->display('task');
    }

    public function pushTask() {

        //获取系统默认的工作分类类型
        $mTaskType = ClsFactory::Create('Model.mTaskType');
        $tasktype_list = $mTaskType->getTaskTypeSystemAll();

        $school_info = reset($this->user['school_info']);
        $school_id = $this->checkSchoolId();
        $client_account = $this->user['client_account'];
        $school_name = $school_info['school_name'];
        $system_date = date('Y-m-d', time());
        $push_date = date('Y年m月d日', time());

        $this->assign('tasktype_list', $tasktype_list);
        $this->assign('school_id', $school_id);
        $this->assign('school_name', $school_name);
        $this->assign('client_account', $client_account);
        $this->assign('task_title_default', $this->default_list['task_title']);
        $this->assign('tag_names_default', $this->default_list['tag_names']);
        $this->assign('system_date', $system_date);
        $this->assign('push_date', $push_date);

        $this->display('push_task');

    }

    /**
     * 保存工作发布信息,实际的过期时间点要在页面接受到的基础上增加24小时
     *
     */
    public function savaTask() {
        $school_id = $this->objInput->getInt('school_id');

        //判断是否是草稿中提取的信息
        $from = $this->objInput->postStr('from');
        $draft_id = $this->objInput->postInt('draft_id');
        $md5_key = $this->objInput->postStr('md5_key');

        $task_title = $this->objInput->postStr('task_title');
        $task_content = $this->objInput->postStr('task_content');
        $task_type = $this->objInput->postInt('task_type');

        $expiration_time = $this->objInput->postStr('expiration_time');
        $deadline_hours = $this->objInput->postInt('deadline_hours');

        $need_reply = $this->objInput->postInt('need_reply');
        $need_sms_remind = $this->objInput->postInt('need_sms_remind');
        $need_sms_push = $this->objInput->postInt('need_sms_push');

        //这里是用户提交是选择的保存类型数据
        $is_draft = $this->objInput->postInt('is_draft');

        $tag_names = $this->objInput->postStr('tag_names');

        $dpt_arr = $this->objInput->postStr('dpt_arr');
        $member_arr = $this->objInput->postStr('member_arr');

        $school_id = $this->checkSchoolId($school_id);

        if(!empty($expiration_time)) {
            $expiration_time = strtotime($expiration_time) + 86399;
        }
        $expiration_time = !empty($expiration_time) ? $expiration_time : 0;

        if(defined('TASK_CONTENT_LENGTH')) {
            $task_content = cutstr($task_content, TASK_CONTENT_LENGTH);
        }

        //短信信息的最大长度
        $msg_length = 70;

        $errmsg_list = array();
        if(empty($task_type)) {
            $errmsg_list[] = "请选择工作发布类型!";
        } elseif(empty($task_content)) {
            $errmsg_list[] = "请填写工作内容!";
        } elseif(!$is_draft && empty($dpt_arr) && empty($member_arr)) {
            $errmsg_list[] = "正式发布的工作需要添加收件人信息!";
        } elseif(!empty($expiration_time) && $expiration_time < time()) {
            $errmsg_list[] = "工作的过期时间小于当前时间!";
        }

        //检测工作发布的类型是否正确
        $task_type_name = "";
        if(!empty($task_type)) {
            $mTaskType = ClsFactory::Create('Model.mTaskType');
            $tasktype_list = $mTaskType->getTaskTypeById($task_type);
            if(empty($tasktype_list) || !isset($tasktype_list[$task_type])) {
                $errmsg_list[] = "您选择的工作类型不存在!";
            } else {
                $task_type_name = $tasktype_list[$task_type]['type_name'];
            }
        }

        //处理收件人列表信息
        $dpt_arr = !empty($dpt_arr) ? explode(',', $dpt_arr) : false;
        $member_arr = !empty($member_arr) ? explode(',', $member_arr) : false;
        $dptmember_list = $this->mergeDptAndMember($dpt_arr, $member_arr);
        if(!$is_draft && empty($dptmember_list)) {
            $errmsg_list[] = "正式发布的工作需要添加收件人信息!";
        }

        $mTask = ClsFactory::Create('Model.mTask');
        //判断用户是否有权限更新草稿信息
        if($from == 'draft') {
            $has_access = false;
            if($md5_key == $this->getDraftMd5key($draft_id)) {
                $task_list = $mTask->getTaskById($draft_id);
                $draft = & $task_list[$draft_id];
                if(!empty($draft) && $draft['add_account'] == $this->user['client_account'] && $draft['is_draft'] == 1) {
                   $has_access = true;
                }
            }
            if(!$has_access) {
                $errmsg_list[] = "您无权操作该草稿信息!";
            }
        }
        //数据格式错误
        if(!empty($errmsg_list)) {
            $this->showError(array_shift($errmsg_list), "/Oa/Task/pushTask/school_id/$school_id");
        }

        //处理工作标签信息，单个标签最多为8个字符(一个汉字占2个字符)
        $tag_ids = array();
        if(!empty($tag_names)) {
            $tag_names_list = explode(' ', $tag_names);
            foreach($tag_names_list as $key=>$tag_name) {
                $tag_name = trim($tag_name);
                if(empty($tag_name) || $tag_name == $this->default_list['tag_names']) {
                    unset($tag_names_list[$key]);
                    continue;
                }
                $tag_names_list[$key] = cutstr($tag_name, 8);
            }
            $tag_ids = $this->getTagIdsByTagNames($tag_names_list, $school_id);
        }
        $tag_ids_str = !empty($tag_ids) ? implode(',', $tag_ids) : "";

        $school_info = $this->user['school_info'][$school_id];
        $operation_strategy = intval($school_info['operation_strategy']);
        //判断对Task表的操作类型
        $action = ($from == 'draft' && !empty($draft_id)) ? 'update' : 'add';

        $return_msg = "";
        //区分保存草稿和正式发布的信息
        if($is_draft) {
            $task_datas = array(
                'task_title' => $task_title,
                'task_content' => $task_content,
                'task_type' => $task_type,
                'to_accounts' => json_encode($dptmember_list),
                'add_time' => time(),
                'upd_time' => time(),
                'tag_ids' => $tag_ids_str,
                'add_account' => $this->user['client_account'],
                'school_id' => 0,
                'is_draft' => 1,
            );
            if($action == 'add') {
                $mTask->addTask($task_datas);
                $return_msg = "草稿保存成功!";
            } else {
                $mTask->modifyTask($task_datas, $draft_id);
                $return_msg = "草稿更新成功!";
            }

            $this->showSuccess($return_msg, "/Oa/Task/pushTask/school_id/$school_id");
        } else {
            $task_datas = array(
                'task_title' => $task_title,
                'task_content' => $task_content,
                'task_type' => $task_type,
                'to_accounts' => json_encode($dptmember_list),
                'expiration_time' => $expiration_time,
                'deadline_hours' => $deadline_hours,
                'add_time' => time(),
                'upd_time' => time(),
                'need_reply' => $need_reply,
                'need_sms_remind' => $need_sms_remind,
                'need_sms_push' => $need_sms_push,
                'tag_ids' => $tag_ids_str,
                'add_account' => $this->user['client_account'],
                'school_id' => $school_id,
                'is_draft' => 0,
            );
            if($action == 'add') {
                $task_id = $mTask->addTask($task_datas, true);
            } else {
                $mTask->modifyTask($task_datas, $draft_id);
                $task_id = $draft_id;
            }
            $return_msg = "工作已经成功发布!";

            //处理工作信息的标签问题
            $this->saveTaskTag($tag_ids, $task_id);

            //初始化推送关系表
            if(!empty($dptmember_list)) {
                $uids = array();
                foreach($dptmember_list as $dpt_id=>$uid_list) {
                    $uids = array_merge((array)$uids, (array)$uid_list);
                }
                if(!empty($uids)) {
                    $uids = array_unique($uids);
                    $dataarr = array();
                    foreach($uids as $uid) {
                        $datas = array(
                            'client_account' => $uid,
                            'task_id' => $task_id,
                            'is_viewed' => 0,
                            'is_replied' => 0,
                            'add_time' => time(),
                            'task_type' => $task_type,
                        );
                        $dataarr[] = $datas;
                    }
                    $mTaskPush = ClsFactory::Create('Model.mTaskPush');
                    $mTaskPush->addTaskPushBat($dataarr);
                }
            }

            //处理信息推送
            if($need_sms_push) {
                $expiration_str = !empty($expiration_time) ? "到期时间:" . date('Y-m-d', $expiration_time - 86399) . " " : "";
                //$msg = $school_info['school_name'] . "-" . $task_type_name . ":" . $task_title . "。" . $expiration_str  . "请登录集中办公平台查看全文";
                //2012-11-20 客服部门提出修改短信内容
                $msg = "学校通知：" . $task_title . "详情登录【我们网】";
                $push_msg = cutstr($msg, $msg_length, true);

                $uid_phone_list = $this->parseBusinessphone($dptmember_list);
                import('@.Control.Api.Smssend.Smssendapi');
                $smssendapi_obj = new Smssendapi();
                //70859265//18620456699
                $smssendapi_obj->send($uid_phone_list, $push_msg, $operation_strategy);
            }

            //延迟提醒
            if($need_sms_remind && !empty($expiration_time)) {
                //个人提醒信息格式
                $sms_msg = "您的工作:{task_title}($task_type_name),将于{$deadline_hours}小时后到期。";
                $cut_len = $msg_length - WmwString::mbstrlen($sms_msg) + strlen("{task_title}");
                if(WmwString::mbstrlen($task_title) > $cut_len) {
                    //需要预留3个字符来显示:"..."
                    $task_title = cutstr($task_title, $cut_len - 3, true);
                }
                $sms_msg = str_replace('{task_title}', $task_title, $sms_msg);

                $push_time = $expiration_time - $deadline_hours * 3600;
                //需要提醒的时间大于当前的时间，信息才是有效的
                if($push_time > time()) {
                    $pretreat_sms_datas = array(
                        'accept_phone' => $this->user['client_account'],
                        'sms_message' => $sms_msg,
                        'push_time' => $push_time,
                        'business_type' => $operation_strategy,
                        'add_time' => time(),
                    );
                    $mPretreatSms = ClsFactory::Create('Model.mPretreatSms');
                    $mPretreatSms->addPretreatSms($pretreat_sms_datas);
                } else {
                    $return_msg .= "短信提醒时间小于当前时间。系统取消了短信提醒服务!";
                }
            }

            $this->showSuccess($return_msg, "/Oa/Task/taskListFromMe");
        }
    }

    /**
     * 通过标签名字获取用户添加的标签信息
     * @param  $tag_names
     * @param  $school_id
     */
    protected function getTagIdsByTagNames($tag_names, $school_id) {
        if(empty($tag_names) || empty($school_id)) {
            return false;
        }

        $tag_names = (array)$tag_names;

        $mTaskTag = ClsFactory::Create('Model.mTaskTags');
        $tasktag_list = $mTaskTag->getTaskTagByNamesWithSchoolId($tag_names, $school_id);

        $search_tag_names = array();
        if(!empty($tasktag_list)) {
            foreach($tasktag_list as $tag_id=>$tag) {
                $search_tag_names[$tag_id] = $tag['tag_name'];
            }
        }

        $diff_arr = array_diff((array)$tag_names, (array)$search_tag_names);
        if(!empty($diff_arr)) {
            foreach($diff_arr as $tag_name) {
                $datas = array(
                    'school_id' => $school_id,
                    'tag_name' => $tag_name,
                    'add_time' => time(),
                );
                $mTaskTag->addTaskTag($datas);
            }
            $tasktag_list = $mTaskTag->getTaskTagByNamesWithSchoolId($tag_names, $school_id);
        }
        return !empty($tasktag_list) ? array_keys($tasktag_list) : false;
    }

    /**
     * 将用户的列表信息转换成账号和手机号的对应关系
     * @param $dptmember_list
     */
    protected function parseBusinessphone($dptmember_list) {
        if(empty($dptmember_list)) {
            return false;
        }

        $uids = array();
        foreach($dptmember_list as $key=>$uid_list) {
            $uids = array_merge($uids, (array)$uid_list);
            unset($dptmember_list[$key]);
        }

        //数据去重
        $new_uids = array();
        foreach($uids as $uid) {
            if($uid > 0) {
               $new_uids[$uid] = $uid;
            }
        }

        $mBusinessPhone = ClsFactory::Create('Model.mBusinessphone');
        $businessphone_list = $mBusinessPhone->getbusinessphonebyalias_id($new_uids);

        //数据过滤处理，部分用户的手机号码是无效的
        $filters = array(
            'business_enable' => 1,
            'phone_status' => 1,
        );

        $uid_phone_list = array();
        if(!empty($businessphone_list)) {
            foreach($businessphone_list as $uid=>$phone) {
                $flag = true;
                if(!empty($filters)) {
                    foreach($filters as $field=>$val) {
                        $val = (array)$val;
                        if(isset($phone[$field]) && !in_array($phone[$field], $val)) {
                            $flag = false;
                            break;
                        }
                    }
                }
                $flag && $uid_phone_list[$uid] = $phone['phone_id'];

                unset($businessphone_list[$uid]);
            }
        }

        return !empty($uid_phone_list) ? $uid_phone_list : false;
    }

    /**
     * 供id提取工作的草稿信息
     */
    public function getDraftById() {
        $draft_id = $this->objInput->getInt('draft_id');
        $md5_key = $this->objInput->getStr('md5_key');

        //检测用户的操作权限
        $draft_task = array();
        if($md5_key == $this->getDraftMd5key($draft_id)) {
            if(!empty($draft_id)) {
                $mTask = ClsFactory::Create('Model.mTask');
                $tasklist = $mTask->getTaskById($draft_id);
                $draft_task = $tasklist[$draft_id];
            }

            //加密草稿信息
            $draft_task['md5_key'] = $this->getDraftMd5key($draft_id);

            $is_draft = !empty($draft_task) && $draft_task['is_draft'] == 1 ? true : false;
            if($is_draft){
                $dpt_list = $append_userlist = array();
                //数据回显
                $to_accounts = $draft_task['to_accounts'];
                if(!empty($draft_task['to_accounts'])) {
                    if(!is_array($draft_task['to_accounts'])) {
                        $draft_task['to_accounts'] = json_decode($draft_task['to_accounts'], true);
                    }

                    //初始化追加用户信息
                    if(isset($to_accounts[0])) {
                          $mUser = ClsFactory::Create('Model.mUser');
                          $append_userlist = $mUser->getUserBaseByUid($to_accounts[0]);
                          if(!empty($append_userlist)) {
                              foreach($append_userlist as $uid=>$user) {
                                  $user = array(
                                      'client_account' => $uid,
                                      'client_name' => !empty($user['client_name']) ? $user['client_name'] : $uid,
                                  );
                                  $append_userlist[$uid] = $user;
                              }
                          }
                          unset($to_accounts[0]);
                    }
                    //初始化追加部门信息
                    if(!empty($to_accounts)) {
                        $dpt_ids = array_keys($to_accounts);
                        $mDpt = ClsFactory::Create('Model.mDepartment');
                        $dpt_list = $mDpt->getDepartmentById($dpt_ids);
                        if(!empty($dpt_list)) {
                            foreach($dpt_list as $dpt_id=>$dpt) {
                                $dpt = array(
                                    'dpt_id' => $dpt_id,
                                    'dpt_name' => $dpt['dpt_name'],
                                );
                                $dpt_list[$dpt_id] = $dpt;
                            }
                        }
                    }
                    unset($draft_task['to_accounts']);
                }

                $draft_task['dpt_list'] = $dpt_list;
                $draft_task['append_userlist'] = $append_userlist;

                //处理标签问题
                $task_tags = "";
                if(!empty($draft_task['tag_ids'])) {
                    $tag_ids = explode(',', $draft_task['tag_ids']);
                    $mTaskTag = ClsFactory::Create('Model.mTaskTags');
                    $taglist = $mTaskTag->getTaskTagById($tag_ids);
                    if(!empty($taglist)) {
                        foreach($taglist as $tag) {
                            $task_tags .= $tag['tag_name'] . " ";
                        }
                    }
                }
                $draft_task['task_tags'] = $task_tags;
            }
        }

        if(!empty($draft_task)) {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '草稿提取成功!',
                ),
                'data' => & $draft_task,
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '相关草稿信息不存在!'
                ),
                'data' => array(),
            );
        }

        echo json_encode($json_data);
    }

    /**
     * todo 没有单独取草稿   逻辑错误
     * 通过添加人获取工作的草稿信息
     */
    public function getDraftByUidWithTasktype() {
        $task_type = $this->objInput->getInt('task_type');
        $page = $this->objInput->getInt('page');
        $page = max(1, $page);
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $client_account = $this->user['client_account'];
        $mTask = ClsFactory::Create('Model.mTask');

        $task_arr = $mTask->getTaskByAddaccount($client_account, array('task_type' => $task_type, 'is_draft' => 1));
        $task_list = & $task_arr[$client_account];

        //点击加载更多
		$filters = array('task_type' => $task_type, 'is_draft' => 1);
        $task_arr = $mTask->getTaskByAddAccount($client_account, $filters, $offset, $limit);
        //dump($task_arr);exit;
        //$task_list = array_slice($task_list, $offset, $perpage, true); 
		$task_list = & $task_arr[$client_account];
		$load_page = count($task_list) > $limit * $page ? $page + 1 : 0;
		
        $return_task_list = array();
        if(!empty($task_list)) {
            foreach($task_list as $task_id=>$task) {
                $short_title = cutstr($task['task_title'] . ":" . $task['task_content'], 40, true);
                $return_task_list[$task_id] = array(
                    'task_id' => $task_id,
                    'task_title' => $short_title,
                    'upd_time' => date('m月-d日 H时', $task['upd_time']),
                    'md5_key' => $this->getDraftMd5key($task_id),
                );
            }
        }

        if(empty($return_task_list)) {
            $json_datas = array(
                'error' => array(
                    'code' => -1,
                    'message' => '无相关草稿!',
                ),
                'data' => array(),
                'load_page' => 0,
            );
        } else {
            $json_datas = array(
                'error' => array(
                    'code' => 1,
                    'message' => '加载成功!'
                ),
                'data' => $return_task_list,
                'load_page' => $load_page,
            );
        }

        echo json_encode($json_datas);
    }

    /**
     * 将传入的部门id和用户列表进行合并
     * @param $dpt_arr
     * @param $member_arr
     */
    protected function mergeDptAndMember($dpt_arr, $member_arr) {
        if(empty($dpt_arr) && empty($member_arr)) {
            return false;
        }

        $dpt_userlist = array();
        if(!empty($dpt_arr)) {
            $mDptMembers = ClsFactory::Create('Model.mDepartmentMembers');
            $dptmembers_list = $mDptMembers->getDepartmentMembersByDptId($dpt_arr);
            if(!empty($dptmembers_list)) {
                foreach($dptmembers_list as $dpt_id=>$list) {
                    $uids = array();
                    foreach($list as $member) {
                        $uid = $member['client_account'];
                        if(empty($uid)) {
                            continue;
                        }
                        $uids[$uid] = $uid;
                    }
                    $dpt_userlist[$dpt_id] = (array)array_values($uids);
                    unset($dptmembers_list[$dpt_id]);
                }
            }
        }
        //使用array_merge会导致键值重置
        if(!empty($member_arr)) {
            $dpt_userlist[0] = (array)$member_arr;
        }

        return !empty($dpt_userlist) ? $dpt_userlist : false;
    }

    /**
     * 获取用户已经选择的用户列表
     */
    public function getSelectedMembers() {
        $dpt_arr = $this->objInput->postArr('dpt_arr');
        $member_arr = $this->objInput->postArr('member_arr');

        $dpt_userlist = $this->mergeDptAndMember($dpt_arr, $member_arr);
        list($json_data, ,) = $this->getDptMembers($dpt_userlist);

        echo json_encode($json_data);
    }

    /**
     * 通过部门id获取部门成员列表
     */
    public function getDptMembersByDptId() {
        $dpt_id = $this->objInput->getInt('dpt_id');

        $mDptmembers = ClsFactory::Create('Model.mDepartmentMembers');
        $dptmembers_list = $mDptmembers->getDepartmentMembersByDptId($dpt_id);
        $dptmembers = & $dptmembers_list[$dpt_id];

        $dpt_uids = array();
        if(!empty($dptmembers)) {
            foreach($dptmembers as $member) {
                $dpt_uids[] = $member['client_account'];
            }
            $dpt_uids = array_unique($dpt_uids);
        }

        $new_userlist = array();
        if(!empty($dpt_uids)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserbaseByUid($dpt_uids);

            foreach($dpt_uids as $uid) {
                $user = $userlist[$uid];
                $new_user = array(
                    'client_account' => $uid,
                    'client_name' => $user['client_name'] ? $user['client_name'] : $uid,
                );
                $new_userlist[$uid] = $new_user;
            }
        }

        if(empty($new_userlist)) {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '当前部门下没有人员信息!',
                ),
                'data' => array(),
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '获取信息成功!',
                ),
                'data' => $new_userlist,
            );
        }

        echo json_encode($json_data);
    }

    /**
     * 获取相应部门下的成员信息
     * @param  $dptmembers_list
     * @return array($json_data, $dpt_list, $userlist)
     */
    protected function getDptMembers($dptmembers_list) {
        if(empty($dptmembers_list)) {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '没有相关信息!',
                ),
                'data' => array(),
            );

            return array($json_data, array(), array());
        }

        $dpt_ids = $uids = array();
        foreach($dptmembers_list as $dpt_id=>$uid_list) {
            $dpt_id && $dpt_ids[] = $dpt_id;
            $uids = array_merge($uids, (array)$uid_list);
        }

        $dpt_list = $userlist = array();
        //查询用户相关的实际数据
        if(!empty($dpt_ids)) {
            $mDpt = ClsFactory::Create('Model.mDepartment');
            $dpt_list = $mDpt->getDepartmentById($dpt_ids);
        }

        //查询用户的基本信息
        if(!empty($uids)) {
            $uids = array_unique($uids);
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($uids);
            if(!empty($userlist)) {
                foreach($userlist as $uid=>$user) {
                    $userlist[$uid] = array(
                        'client_account' => $uid,
                        'client_name' => $user['client_name'],
                    );
                }
            }
        }

        $total_nums = 0;    //总人数统计
        $new_userlist = array();
        if(!empty($dptmembers_list)) {
            foreach($dptmembers_list as $dpt_id=>$list) {
                $datas = array();
                if($dpt_id > 0) {
                    $datas['dpt_name'] = $dpt_list[$dpt_id]['dpt_name'];
                } else {
                    $datas['dpt_name'] = "追加人员";
                }

                foreach($list as $uid) {
                    $user = $userlist[$uid];
                    $datas['item'][$uid] = array(
                        'client_account' => $uid,
                        'client_name' => !empty($user['client_name']) ? $user['client_name'] : $uid,
                    );
                }
                $datas['nums'] = !empty($datas['item']) ? count($datas['item']) : 0;
                $new_userlist[$dpt_id] = $datas;

                $total_nums += $datas['nums'];
            }
        }

        if(empty($new_userlist)) {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '没有相关信息!',
                ),
                'data' => array(),
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '成功!',
                ),
                'data' => $new_userlist,
                'stat' => array(
                    'total_nums' => $total_nums,
                    'real_nums' => count($userlist),
                    'repeat_nums' => $total_nums - count($userlist),
                ),
            );
        }

        return array($json_data, $dpt_list, $userlist);
    }

    /**
     * 对用户的草稿Id进行加密处理
     * @param $draft_id
     */
    protected function getDraftMd5key($draft_id) {
        $md5_str = $this->user['client_account'] . substr(time(), 0, 4) . $draft_id;
        if(constant('SECRET_KEY')) {
            $md5_str .= SECRET_KEY;
        }

        return substr(md5($md5_str), 0, 16);
    }

    /**
     * 只有正式发布的工作才会建立相应的标签关系
     * @param  $tag_ids
     * @param  $task_id
     */
    protected function saveTaskTag($tag_ids, $task_id) {
        if(empty($task_id)) {
            return false;
        }

        $tag_ids = !empty($tag_ids) ? array_unique($tag_ids) : array();

        $mTaskTag = ClsFactory::Create('Model.mTaskTagRelation');
        $relation_arr = $mTaskTag->getTaskTagRelationByTaskId($task_id);
        $relation_list = & $relation_arr[$task_id];
        //建立task_id到tag_id的对应关系
        $exists_tags = array();
        if(!empty($relation_list)) {
            foreach($relation_list as $ttr_id=>$relation) {
                $exists_tags[$relation['tag_id']] = $ttr_id;
            }
        }
        //需要删除的记录
        $del_arr = array_diff((array)array_keys($exists_tags), (array)$tag_ids);
        if(!empty($del_arr)) {
            foreach($del_arr as $del_tag_id) {
                $ttr_id = $exists_tags[$del_tag_id];
                $ttr_id && $mTaskTag->delTaskTagRelation($ttr_id);
            }
        }
        //需要增加的记录
        $add_arr = array_diff((array)$tag_ids, (array)array_keys($exists_tags));
        if(!empty($add_arr)) {
            foreach($add_arr as $tag_id) {
                if(empty($tag_id)) {
                    continue;
                }
                $datas = array(
                    'tag_id' => $tag_id,
                    'task_id' => $task_id,
                    'add_time' => time(),
                );
                $mTaskTag->addTaskTagRelation($datas);
            }
        }
    }

    //检测用户的学校id信息
    protected function checkSchoolId($school_id) {

        $school_ids = array();
        if(!empty($this->user['school_info'])) {
            $school_ids = array_keys($this->user['school_info']);
        }

        if(!empty($school_ids)) {
            $school_id = $school_id && in_array($school_id, $school_ids) ? $school_id : reset($school_ids);
        } else {
            $school_id = false;
        }

        return $school_id;
    }

    public function taskListFromMe(){//我发布的工作列表
        $page = intval($this->objInput->getInt('page'));
        $task_type = intval($this->objInput->getInt('task_type'));
        $uid = $this->user['client_account'];

        $access_name_arr = $this->user['access_name_arr']; //是否有布置工作的权限
        if(empty($access_name_arr[0])){
            $this->showError("您没有布置工作的权限", "/Oa/Index/index");
        }

        $page = max(1, $page);
        define('OFFSET', 5);//每页x条
        $offset = ($page-1) * OFFSET;
        $mTask = ClsFactory::Create('Model.mTask');
        $task_list = $mTask->getTaskByUidAndType($uid, $task_type, $offset, OFFSET+1);//工作列表
        $mTaskType = ClsFactory::Create('Model.mTaskType');
        $type_list = $mTaskType->getTaskTypeSystemAll(); //类型列表
        if(!empty($task_list)){
            if(count($task_list)<OFFSET+1){
                $this->assign('end_flag', 1);
            }else{
                array_pop($task_list);
            }
            
            foreach($task_list as $task_id=>$info){
                $info['task_type_str'] = $type_list[$info['task_type']]['type_name']; //类型名称
                $info['upd_date'] = date("Y-m-d H:i:s", $info['upd_time']); //时间格式
                $info['task_content_part'] =  cutstr(strip_tags(htmlspecialchars_decode($info['task_content'])), 200, true);//截取文章内容,并去除样式
                $info['task_title'] = cutstr($info['task_title'], 10, true);
                $task_list[$task_id] = $info;
            }
            
        }else{
            $this->assign('end_flag', 1);
        }

        //工作类型导航栏
        $mTaskType = ClsFactory::Create('Model.mTaskType');
        $type_list = $mTaskType->getTaskTypeSystemAll();
        $this->assign('page', $page);
        $this->assign('task_type', $task_type);
        $this->assign('type_list', $type_list);
//        dump($task_list);
        $this->assign('task_list', $task_list);
        $this->display('task_list');
    }

    public function taskDetail(){
        $task_id = $this->objInput->getInt('task_id');

        $is_to_me = $this->objInput->getInt('is_to_me');//is_to_me 及page用于标示是否来自‘布置给我的工作’列表，用于‘返回’刷新数据
        $list_page = $this->objInput->getInt('list_page');

        $mUser = ClsFactory::Create('Model.mUser');
        $mTask = ClsFactory::Create('Model.mTask');
        $task_info = $mTask->getTaskById($task_id);
        $task_info = &$task_info[$task_id];
        if(!empty($task_info)){
            $is_published = $is_received = 0;
            $all_accounts = $all_department_ids = array();
            foreach ($task_info['to_accounts'] as $dpt_id=> $accounts){ //获取部门及成员 id 集合
                $all_accounts = array_merge($all_accounts, $accounts);
                $all_department_ids[] = $dpt_id;
            }
            if( in_array($this->user['client_account'],$all_accounts)){//作为接受者
                $is_received = 1;
            }
            if($task_info['add_account'] == $this->user['client_account']){//作为发布者
                $is_published = 1;
            }

            if($is_published){//我发布的工作，需要查看成员的回复状态
                $mTaskPush = ClsFactory::Create('Model.mTaskPush'); //查看成员工作回应状态
                $push_list = $mTaskPush->getTaskPushByTaskId($task_id);
                $push_list = &$push_list[$task_id];

                $flag_list = array();//获取接受者账号列表
                $num_viewed = $num_reply = 0;  //记录查看及回复人数
                $num_all = count($push_list);
                foreach($push_list as $push_id => $info){ //回复统计
                    if($info['is_viewed']){
                        $handle_flag = 1;
                    }
                    if($info['is_replied']){ //查看并回复
                        $handle_flag = 2;
                    }
                    if(!$info['is_viewed'] && !$info['is_replied']){
                        $handle_flag = 0;
                    }

                    if($info['is_replied']){ //回复
                        $num_reply += 1;
                    }
                    if($info['is_viewed'] ){ //查看
                        $num_viewed += 1;
                    }
                    $flag_list[$info['client_account']] = $handle_flag;
                }

                $statistics = array(
                    'num_viewed'  => $num_viewed,
                    'num_reply'   => $num_reply,
                    'num_noviewed'=> max(($num_all-$num_viewed),0),
                    'rate_viewed' => number_format(($num_viewed/$num_all)*100, 2, '.', ''),
                    'rate_reply'  => number_format(($num_reply/$num_all)*100, 2,'.','')
                );

                $mDepartment = ClsFactory::Create('Model.mDepartment');//将名称信息按部门组织后放入信息中
                $dpt_list = $mDepartment->getDepartmentById($all_department_ids);
                $user_list = $mUser->getUserBaseByUid($all_accounts);
                foreach ($task_info['to_accounts'] as $dpt_id=> $accounts){
                    $dpt_name = $dpt_list[$dpt_id]['dpt_name'];
                    if($dpt_id == 0){
                        $dpt_name = "追加人员";
                    }
                    foreach($accounts as $account){
                        $task_info['to_accounts_name'][$dpt_name][$account]['client_name'] = $user_list[$account]['client_name'];
                        $task_info['to_accounts_name'][$dpt_name][$account]['handle_flag'] = $flag_list[$account];
                    }
                }
            }
            if($is_received){//我接收的工作，查看是否已经回复过,只回复一次
                $need_me_reply = 0;
                $mTaskPush = ClsFactory::Create('Model.mTaskPush');
        		$taskPush_list = $mTaskPush->getTaskPushByTaskId($task_id);
        		foreach($taskPush_list[$task_id] as $push_id=>$push_info){
                    if($push_info['client_account'] == $this->user['client_account']){
                        $need_me_reply = intval(!($push_info['is_replied']));
                        $this->assign('push_id', $push_info['push_id']); //用于回复时更新状态
                        if($push_info['is_viewed'] == 0){
                            $push_data = array(   //回复状态表oa_task_push
                    			'push_id'=>$push_id,
                    			'is_viewed'=> 1
                    		);
                    		$mTaskPush->modifyTaskPush($push_data,$push_id); //记录查看状态
                        }
                    }
        		}
            }
            $task_info['upd_date'] = date("Y-m-d H:i:s", $task_info['upd_time']);
            if(!empty($task_info['expiration_time'])){
                $task_info['expiration_date'] = date("Y-m-d H:i:s", ($task_info['expiration_time']));
            }
            $add_account = &$task_info['add_account']; //查询发布人姓名
            $publisher = $mUser->getUserBaseByUid($add_account);
            $task_info['publisher_name'] = $publisher[$add_account]['client_name'];
        }
        if($task_info['need_reply']){
            $task_need_replied = 1;
            //查询最后10条回复
            $mTaskReply = ClsFactory::Create('Model.mTaskReply');
    		$TaskReplyInfos = $mTaskReply->getTaskReplyByTaskId($task_id, 0, PAGINAL_NUM);
    		$reply_list = &$TaskReplyInfos[$task_id];
            foreach($reply_list as $reply_id => $info){
    			$reply_list[$reply_id]['add_time'] = date('Y-m-d H:i:s',$info['add_time']);
    			$accounts[] = $info['add_account'];
    		}
    		$mUser = ClsFactory::Create('Model.mUser');//查询用户姓名
            $user_list = $mUser->getUserBaseByUid($accounts);
            foreach($reply_list as $reply_id => $info){
                $client_account = $reply_list[$reply_id]['add_account'];
                $reply_list[$reply_id]['client_name'] = $user_list[$client_account]['client_name'];
                $last_key = $reply_id;
            }
        }else{
            $task_need_replied = 0;
        }
        $this->assign('task_id', $task_id);
        $this->assign('task_info', $task_info);
        $this->assign('statistics', $statistics);
        $this->assign('is_published', $is_published);
        $this->assign('need_me_reply', $need_me_reply);
        $this->assign('task_need_replied', $task_need_replied);
        $this->assign('reply_list', $reply_list);
        $this->assign('page', 2); //回复的分页
        $this->assign('list_page',$list_page); //上级工作列表的页码
        $this->assign('last_key', $last_key);
        $this->assign('is_to_me', $is_to_me);

        $this->display('task_detail');
    }

    //查询工作回复内容
	public function replyList(){
		$page = $this->objInput->getInt('page');
		$task_id = $this->objInput->getInt('task_id');
		if(empty($page) || $page < 1){
			$page = 1;
		}
		$offset = ($page-1)* PAGINAL_NUM;
		$mTaskReply = ClsFactory::Create('Model.mTaskReply');
		$TaskReplyInfos = $mTaskReply->getTaskReplyByTaskId($task_id, $offset, PAGINAL_NUM+1);
		$reply_list = &$TaskReplyInfos[$task_id];
		if(count($reply_list) < PAGINAL_NUM+1){
			$fl = true;
		}else{
			array_pop($reply_list);
		}
		if(!empty($TaskReplyInfos[$task_id])){
    		foreach($reply_list as $reply_id => $info){
    			$reply_list[$reply_id]['add_time'] = date('Y-m-d H:i:s',$info['add_time']);
    			$accounts[] = $info['add_account'];
    		}

    		$mUser = ClsFactory::Create('Model.mUser');//查询用户姓名
            $user_list = $mUser->getUserBaseByUid($accounts);
            foreach($reply_list as $reply_id => $info){
                $client_account = $reply_list[$reply_id]['add_account'];
                $reply_list[$reply_id]['client_name'] = $user_list[$client_account]['client_name'];
            }
            $code = 1;
			$message = "查询成功";
		}else{
			$code = -1;
			$message = "暂无回复";
		}
		$json_arr = array(
			'error'=>array(
				'code'=> $code,
				'message'=> $message
		     ),
			'data'=>$reply_list
		);
		echo json_encode($json_arr);
	}
	//对分配给我的工作，添加工作回复
	public function addTaskReply(){
		$task_id = $this->objInput->postInt('task_id');
		$push_id = $this->objInput->postInt('push_id');
		$reply_content = $this->objInput->postStr('reply_content');

		$reply_data = array(  //回复内容  oa_task_reply
			'task_id' => $task_id,
			'add_account' => $this->user['client_account'],
			'reply_content' => $reply_content,
			'add_time' => $_SERVER['REQUEST_TIME']
		);
		$mTaskReply = ClsFactory::Create('Model.mTaskReply');
		$reply_result = $mTaskReply->addTaskReply($reply_data);

		$push_data = array(   //回复状态表oa_task_push
			'push_id'=>$push_id,
			'is_replied'=> 1
		);
		$mTaskPush = ClsFactory::Create('Model.mTaskPush');
		$mTaskPush->modifyTaskPush($push_data,$push_id);

		if($reply_result){
			$json_arr = array(
				'error'=>array(
					'code' => 1,
					'message' => '回复成功'
			    ),
			    'data' => array(
			        'add_date'=>date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']),
			        'client_name' => $this->user['client_name']
			    )
			);
		}else{
			$json_arr = array(
				'error'=>array(
					'code' => -1,
					'message' => '回复失败，请勿重复回复'
				)
			);
		}
		echo json_encode($json_arr);
	}
}