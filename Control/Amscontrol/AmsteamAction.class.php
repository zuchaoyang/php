<?php

class AmsteamAction extends AmsController {
    protected $is_school = true;
    public function _initialize() {
        parent::_initialize();
        header("Content-Type:text/html;charset=utf-8");
    }

    public function index() {
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $class_code = $this->objInput->getInt('class_code');
        $check_class_code = $this->checkUserAccess($class_code);

        $this->redirect("Amsteam/teamManage/class_code/$check_class_code/schoolid/$schoolid/gradeid/$gradeid/uid/$uid");
    }

    public function teamManage() {
        $class_code = $this->objInput->getInt('class_code');
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $getuid       = $this->objInput->getInt('uid');
        $mTeam = ClsFactory::Create('Model.mTeam');
        $teamarr = $mTeam->getTeamBySquadronId($class_code);
        $teamlist = & $teamarr[$class_code];

        //数据分组
        if(!empty($teamlist)) {
            foreach($teamlist as $team_id=>$teaminfo) {
                $member_list = & $teaminfo['member_list'];

                $all_member = $append_arr = array();
                $head_team = $team_assistant = "";
                foreach($member_list as $member) {
                    $team_duties_id = intval($member['team_duties_id']);
                    //重新组织数据
                    $new_member = array(
                    	'wmw_uid' => $member['wmw_uid'],
                    	'client_name' => !empty($member['client_name']) ? $member['client_name'] : '--',
                    );
                    if($team_duties_id == TEAM_DUTUDIES_TEAM_HEAD) {
                        $head_team = $new_member['client_name'];
                        $append_arr[$new_member['wmw_uid']] = $new_member;
                    } elseif($team_duties_id == TEAM_DUTUDIES_TEAM_ASSISTANT) {
                        $team_assistant = $new_member['client_name'];
                        $append_arr[$new_member['wmw_uid']] = $new_member;
                    } else {
                        $all_member[$member['wmw_uid']] = $new_member;
                    }
                }

                //重新合并数组，利用key相同去掉重复数据
                $tmp_all_member = !empty($append_arr) ? $append_arr : array();
                if(!empty($all_member)) {
                    foreach($all_member as $uid=>$member) {
                        $tmp_all_member[$uid] = $member;
                    }
                }
                $all_member = $tmp_all_member;

                $name_list = $comma = "";
                foreach((array)$all_member as $member) {
                    $name_list .= $comma . $member['client_name'];
                    $comma = " ";
                }

                $teaminfo['member_list'] = array(
                    'head_team' => $head_team ? $head_team : "--",
                    'team_assistant' => $team_assistant ? $team_assistant : "--",
                    'name_list' => $name_list,
                );
                $teaminfo['total_members'] = count($all_member);
                $teamlist[$team_id] = $teaminfo;
            }
        }
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$getuid);

        $this->assign('class_code', $class_code);
        $this->assign('teamlist', $teamlist);
        $this->display('teamManage');
    }

    /**
     * 增加小队的数据保存
     */
    public function savaTeam() {
    	$gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $class_code = $this->objInput->postInt('class_code');
        $team_name = $this->objInput->postStr('team_name');
        $team_head = $this->objInput->postInt('team_head');
        $team_head_assistant = $this->objInput->postInt('team_head_assistant');
        $member_list = $this->objInput->postArr('member_list');

        //数组检测,数据重组
        if(!empty($member_list)) {
            $new_member_list = array();
            foreach($member_list as $key=>$val) {
                $val = intval($val);
                if($val > 0) {
                   $new_member_list[$val] = $val;
                }
            }
            $member_list = & $new_member_list;
        }
        //检测用户是否有权限管理
        $check_class_code = $this->checkUserAccess($class_code);
        $is_manage = $check_class_code && $check_class_code === $class_code ? true : false;

        //上传的所有用户必须在改中队中未被分配
        $mTeam = ClsFactory::Create('Model.mTeam');
        if($is_manage) {
            $classmember_list = $mTeam->getClassMemberList($class_code);
            //处理非法数据
            $diff_arr = array_diff($member_list, $classmember_list);
            if(!empty($diff_arr)) {
                foreach($diff_arr as $client_account) {
                    unset($member_list[$client_account]);
                }
            }
        }
        //页面传入参数的检测
        if(!$is_manage) {
            $this->showError("您没有权限管理该班级", "/Amscontrol/Amsteam/teamManage/class_code/$check_class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } elseif(empty($classmember_list)) {
            $this->showError("该班的所有成员都已分配", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } elseif(empty($team_name) || $mTeam->checkTeamSameName($team_name, $class_code)) {
            $this->history_back('小队名称重复或为空,请重新输入!');
        } elseif(empty($member_list)) {
            $this->history_back('请选择小队成员!');
        } elseif(empty($team_head)) {
            $this->history_back('请选择小队长!');
        } elseif(empty($team_head_assistant)) {
            $this->history_back('请选择 副小队长!');
        } elseif(!in_array($team_head, $member_list) || !in_array($team_head_assistant, $member_list)) {
            $this->history_back('小队长或副小队长应该包含在成员列表中!');
        }

        $now_date = date('Y-m-d H:i:s', time());
        //增加小队的相关信息
        $team_data_arr = array(
            'team_name' => $team_name,
            'squadron_id' => $class_code,
            'db_createtime' => $now_date,
            'db_updatetime' => $now_date,
        );
        $team_id = $mTeam->addTeam($team_data_arr, true);
        //增加小队的用户关系
        if(!empty($team_id) && !empty($member_list)) {
            //队长信息
            $head_team_data = array(
                'team_id' => $team_id,
                'wmw_uid' => $team_head,
                'team_duties_id' => TEAM_DUTUDIES_TEAM_HEAD,
                'db_createtime' => $now_date,
                'db_updatetime' => $now_date,
            );
            $mTeam->addTeamNumberDuties($head_team_data);
            //副队长信息
            $team_assistant_data = array(
                'team_id' => $team_id,
                'wmw_uid' => $team_head_assistant,
                'team_duties_id' => TEAM_DUTUDIES_TEAM_ASSISTANT,
                'db_createtime' => $now_date,
                'db_updatetime' => $now_date,
            );
            $mTeam->addTeamNumberDuties($team_assistant_data);
            //普通成员信息
            unset($member_list[$team_head], $member_list[$team_head_assistant]);
            if(!empty($member_list)) {
                foreach($member_list as $uid) {
                    $team_number_duties_data = array(
                        'team_id' => $team_id,
                        'wmw_uid' => $uid,
                        'team_duties_id' => TEAM_DUTUDIES_TEAM_MEMBER,
                        'db_createtime' => $now_date,
                        'db_updatetime' => $now_date,
                    );
                    $mTeam->addTeamNumberDuties($team_number_duties_data);
                }
            }
        }
        //成功后调整
        $this->showSuccess("创建小队成功", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
    }

    public function savaModifyTeam() {
    	$gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $team_id = $this->objInput->postInt('team_id');
        $team_name = $this->objInput->postStr('team_name');
        $team_head = $this->objInput->postInt('team_head');
        $team_head_assistant = $this->objInput->postInt('team_head_assistant');
        $member_list = $this->objInput->postArr('member_list');
        //数组检测,数据重组
        if(!empty($member_list)) {
            $new_member_list = array();
            foreach($member_list as $key=>$val) {
                $val = intval($val);
                if($val > 0) {
                   $new_member_list[$val] = $val;
                }
            }
            $member_list = & $new_member_list;
        }
        
        $mTeam = ClsFactory::Create('Model.mTeam');
        $team_list = $mTeam->getTeamById($team_id);

        $teaminfo = !empty($team_list[$team_id]) ? $team_list[$team_id] : false;
        $class_code = intval($teaminfo['squadron_id']);
        $exists_member_list = & $teaminfo['member_list'];

        //检测用户是否有权限管理
        $check_class_code = $this->checkUserAccess($class_code);
        $is_manage = $check_class_code && $check_class_code == $class_code ? true : false;

        //获取已经存在的成员列表
        $old_member_list = $old_team_number_duties_ids = array();
        $old_team_head = $old_team_head_assistant = 0;
        if(!empty($exists_member_list)) {
            foreach($exists_member_list as $id=>$member) {
                $wmw_uid = intval($member['wmw_uid']);
                $team_duties_id = intval($member['team_duties_id']);
                if($team_duties_id == TEAM_DUTUDIES_TEAM_HEAD) {
                    $old_team_head = $wmw_uid;
                } elseif($team_duties_id == TEAM_DUTUDIES_TEAM_ASSISTANT) {
                    $old_team_head_assistant = $wmw_uid;
                }
                $old_member_list[$wmw_uid] = $wmw_uid;
                //建立用户id到记录id的映射关系,要保证数据关系的唯一
                $key = strval($wmw_uid . "_" . $team_duties_id);
                $old_team_number_duties_ids[$key] = $id;
            }
        }

        //上传的所有用户必须在改中队中未被分配
        if($is_manage) {
            $classmember_list = $mTeam->getClassMemberList($class_code);
            //修改时提交的成员列表允许包含当前成员信息
            $classmember_list = !empty($classmember_list) ? $classmember_list : array();
            $old_member_list = !empty($old_member_list) ? $old_member_list : array();
            $classmember_list = array_merge($classmember_list, $old_member_list);
            //处理非法数据
            if(!empty($member_list) && !empty($classmember_list)) {
                $diff_arr = array_diff($member_list, $classmember_list);
                if(!empty($diff_arr)) {
                    foreach($diff_arr as $client_account) {
                        unset($member_list[$client_account]);
                    }
                }
            }
        }
        //页面传入参数的检测
        if(!$is_manage) {
            $this->showError("您没有权限管理该班级", "/Amscontrol/Amsteam/teamManage/class_code/$check_class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } elseif(empty($teaminfo)) {
        	$this->showError("中队信息不存在", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } elseif(empty($classmember_list)) {
        	$this->showError("该班的所有成员都已分配", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } elseif(empty($team_name) || ($team_name != $teaminfo['team_name'] && $mTeam->checkTeamSameName($team_name, $class_code))) {
            $this->history_back('小队名称重复或为空,请重新输入!');
        } elseif(empty($member_list)) {
            $this->history_back('请选择小队成员!');
        } elseif(empty($team_head)) {
            $this->history_back('请选择小队长!');
        } elseif(empty($team_head_assistant)) {
            $this->history_back('请选择 副小队长!');
        } elseif(!in_array($team_head, $member_list) || !in_array($team_head_assistant, $member_list)) {
            $this->history_back('小队长或副小队长应该包含在成员列表中!');
        }

        $now_date = date('Y-m-d', time());
        //更新小队的基本信息
        $team_data = array(
            'team_name' => $team_name,
            'squadron_id' => $class_code,
            'db_updatetime' => $now_date,
        );
        $mTeam->modifyTeam($team_data, $team_id);

        //处理小队和副小队长的更新
        $update_arr = array();
        if($team_head != $old_team_head) {
            $update_arr[] = array($old_team_head, $team_head, TEAM_DUTUDIES_TEAM_HEAD);
        }
        if($team_head_assistant != $old_team_head_assistant) {
            $update_arr[] = array($old_team_head_assistant, $team_head_assistant, TEAM_DUTUDIES_TEAM_ASSISTANT);
        }
        //数据更新，如果对应的数据存在则更新，否则添加
        if(!empty($update_arr)) {
            foreach($update_arr as $update) {
                list($old_uid, $new_uid, $team_number_duties_id) = $update;

                $key = strval($old_uid . "_" . $team_number_duties_id);
                $old_key = $old_team_number_duties_ids[$key];

                if(!empty($old_key)) {
                    $team_number_duties_data = array(
                        'team_id' => $team_id,
                        'wmw_uid' => $new_uid,
                        'team_duties_id' => $team_number_duties_id,
                        'db_updatetime' => $now_date,
                    );
                    $mTeam->modifyTeamNumberDuties($team_number_duties_data, $old_key);
                } else {
                    $team_number_duties_data = array(
                        'team_id' => $team_id,
                        'wmw_uid' => $new_uid,
                        'team_duties_id' => $team_number_duties_id,
                        'db_createtime' => $now_date,
                        'db_updatetime' => $now_date,
                    );
                    $mTeam->addTeamNumberDuties($team_number_duties_data);
                }
            }
        }
        //更新普通成员的信息 ,要排除队长和副队长的数据
        unset($member_list[$team_head], $member_list[$team_head_assistant]);
        unset($old_member_list[$old_team_head], $old_member_list[$old_team_head_assistant]);
        if(!empty($member_list) || !empty($old_member_list)) {
           $del_arr = $add_arr = array();
           if(!empty($old_member_list) && !empty($member_list)) {
               $add_arr = array_diff($member_list, $old_member_list);
               $del_arr = array_diff($old_member_list, $member_list);
           } elseif(!empty($member_list)) {
               $add_arr = & $member_list;
               $del_arr = array();
           } elseif(!empty($old_member_list)) {
               $add_arr = array();
               $del_arr = & $old_member_list;
           }
           //要删除的数据
           if(!empty($del_arr)) {
               foreach($del_arr as $wmw_uid) {
                   $key = strval($wmw_uid. "_" . TEAM_DUTUDIES_TEAM_MEMBER);
                   $old_id = $old_team_number_duties_ids[$key];
                   if(!empty($old_id)) {
                       $mTeam->delTeamNumberDuties($old_id);
                   }
               }
           }
           //增加用户的数据
           if(!empty($add_arr)) {
               foreach($add_arr as $wmw_uid) {
                   $team_number_duties_data = array(
                   		'team_id' => $team_id,
                        'wmw_uid' => $wmw_uid,
                        'team_duties_id' => TEAM_DUTUDIES_TEAM_MEMBER,
                        'db_createtime' => $now_date,
                        'db_updatetime' => $now_date,
                   );
                   $mTeam->addTeamNumberDuties($team_number_duties_data);
               }
           }
        }
        $this->showSuccess("修改成功", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
    }

    /**
     * 解散小队
     */
    public function dismissTeam() {
        $team_id = $this->objInput->getInt('team_id');
        $team_id = max(0, intval($team_id));
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $mTeam = ClsFactory::Create('Model.mTeam');
        $del_team_arr = $mTeam->getTeamById($team_id);
        $del_team = & $del_team_arr[$team_id];
        $class_code = intval($del_team['squadron_id']);

        //检测用户是否有权限管理改班级
        $check_class_code = $this->checkUserAccess($class_code);
        $is_manage = $check_class_code && $check_class_code == $class_code ? true : false;
        //如果用户没有权限管理该班级
        if(!$is_manage) {
            if(!empty($check_class_code)) {
                $this->showError("您没有权限管理该小队", "/Amscontrol/Amsteam/teamManage/class_code/$check_class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
            } else {
                $this->showError("您没有权限管理班级小队", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
            }
            exit;
        }

        //删除小队信息
        if(!empty($del_team)) {
            $mTeam->delTeam($team_id);
            //删除该小队的成员信息
            $team_number_duties_arr = $mTeam->getTeamNumberDutiesBaseByTeamId($team_id);
            $team_number_duties_list = & $team_number_duties_arr[$team_id];
            foreach((array)$team_number_duties_list as $team_number_duties) {
                $id = intval($team_number_duties['team_number_duties_id']);
                $id > 0 && $mTeam->delTeamNumberDuties($id);
            }
            $this->showSuccess("小队解散成功", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        } else {
            $this->showError("该小队的信息不存在", "/Amscontrol/Amsteam/teamManage/class_code/$class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        }
    }


    /**
     * 修改小队的基本信息
     */
    public function modifyTeam() {
        $team_id = $this->objInput->getInt('team_id');
        $gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $mTeam = ClsFactory::Create('Model.mTeam');
        $teamlist = $mTeam->getTeamById($team_id);
        $team_info = & $teamlist[$team_id];

        $member_list = $team_info['member_list'];
        unset($team_info['member_list']);

        //判断用户的权限
        $class_code = intval($team_info['squadron_id']);
        $check_class_code = $this->checkUserAccess($class_code);
        $is_manage = $class_code && $class_code == $check_class_code ? true : false;
        if(!$is_manage) {
        	$this->showError("您没有权限管理该小队", "/Amscontrol/Amsteam/teamManage/class_code/$check_class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        }

        $head_team_uid = $team_assistant_uid = 0;
        if(!empty($member_list)) {
            $new_member_list = array();
            foreach($member_list as $key=>$member) {
                $wmw_uid = intval($member['wmw_uid']);
                if($member['team_duties_id'] == TEAM_DUTUDIES_TEAM_HEAD) {
                    $head_team_uid = $wmw_uid;
                } elseif($member['team_duties_id'] == TEAM_DUTUDIES_TEAM_ASSISTANT) {
                    $team_assistant_uid = $wmw_uid;
                }
                $new_member_list[$wmw_uid] = array('wmw_uid'=>$wmw_uid, 'client_name'=>$member['client_name']);
            }
            $member_list = & $new_member_list;
        }
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$uid);
        $this->assign('class_code', $class_code);
        $this->assign('team_id', $team_id);
        $this->assign('member_list', $member_list);
        $this->assign('head_team_uid', $head_team_uid);
        $this->assign('team_assistant_uid', $team_assistant_uid);
        $this->assign('team_info', $team_info);
        $this->display('team_modify');
    }

    /**
     * 修改小队信息时获取小队的成员列表,
     * 数据又2部分组成：小队现有的成员和班级中未分配的成员
     */
    public function getClassMemberListForModify() {
        $team_id = $this->objInput->getInt('team_id');

        $mTeam = ClsFactory::Create('Model.mTeam');
        $team_list = $mTeam->getTeamById($team_id);
        $teaminfo = & $team_list[$team_id];
        //获取当前小队中已经存在的成员列表
        $team_member_list = & $teaminfo['member_list'];
        if(!empty($team_member_list)) {
            $new_team_member_list = array();
            foreach($team_member_list as $member) {
                $wmw_uid = intval($member['wmw_uid']);
                $new_team_member_list[$wmw_uid] = array(
                	'uid'=>$wmw_uid,
                	'username'=>$member['client_name'],
                	'checked'=>true,
                );
            }
            $team_member_list = & $new_team_member_list;
        }
        /**
         * 修改时未分配的成员和改小队的成员都要出现
         * 获取该中队未分配的成员列表
         */
        $class_code = intval($teaminfo['squadron_id']);
        $class_member_list = $mTeam->getClassMemberList($class_code);

        /*
         * 获取显示的成员uid列表,小队中已有的成员信息单独处理了,
         * 因此在获取用户的基本信息时排出了小队中已有的成员信息
         */
        $wmw_uids = array();
        if(!empty($class_member_list)) {
            if(!empty($team_member_list)) {
                $wmw_uids = array_diff($class_member_list, array_keys($team_member_list));
            } else {
                $wmw_uids = $class_member_list;
            }
        }

        if(!empty($wmw_uids)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($wmw_uids);
            $new_userlist = array();
            if(!empty($userlist)) {
                foreach($userlist as $uid=>$user) {
                     $new_userlist[$uid] = array(
                         'uid'=>$uid,
                         'username'=>$user['client_name'],
                         'checked'=>false,
                     );
                }
                unset($userlist);
            }
        }

        //要显示的成员列表的组合，在使用和数组相关的函数时要小心数据的类型
        $team_member_list = !empty($team_member_list) ? $team_member_list : array();
        $new_userlist = !empty($new_userlist) ? $new_userlist : array();
        $member_list = array_merge($team_member_list, $new_userlist);

        $jsondata = array();
        if(!empty($member_list)) {
            $jsondata = array(
                'error' => array(
                    'code' => 1,
                    'message' => '获取成员列表成功!',
                ),
                'data' => $member_list,
            );
        } else {
            $jsondata = array(
            	'error' => array(
                    'code' => -1,
                    'message' => '系统繁忙!',
                ),
                'data' => array(),
            );
        }

        echo json_encode($jsondata);
    }


    /**
     * 增加小队信息
     */
    public function addTeam() {
    	$gradeid   = $this->objInput->getInt('gradeid');
        $schoolid  = $this->user['schoolinfo']['school_id'];
        $uid       = $this->objInput->getInt('uid');
        $class_code = $this->objInput->getInt('class_code');
        $check_class_code = $this->checkUserAccess($class_code);

        $is_manage = $class_code && $class_code == $check_class_code ? true : false;
        if(!$is_manage) {
        	$this->showError("账号规则修改失败", "/Amscontrol/Amsteam/teamManage/class_code/$check_class_code/uid/$uid/gradeid/$gradeid/schoolid/$schoolid");
        }
        $this->assign('gradeid',$gradeid);
        $this->assign('schoolid',$schoolid);
        $this->assign('uid',$uid);
        $this->assign('class_code', $class_code);
        $this->display('team_add');
    }


    /**
     * 检测小队名是否重复
     */
    public function checkTeamSameName() {
        $team_name = $this->objInput->postStr('team_name');
        $class_code = $this->objInput->postInt('class_code');

        $exists = true;
        if(!empty($team_name) && !empty($class_code)) {
            $mTeam = ClsFactory::Create('Model.mTeam');
            $exists = $mTeam->checkTeamSameName($team_name, $class_code);
        }

        if(!$exists) {
            $jsondata = array(
                'code' => 1,
                'message' => '小队名可用!',
            );
        } else {
             $jsondata = array(
                'code' => -1,
                'message' => '名字重复，请重新命名!',
            );
        }
        echo json_encode($jsondata);
    }


    public function getClassMemberList() {
        $class_code = $this->objInput->getInt('class_code');

        $mTeam = ClsFactory::Create('Model.mTeam');
        $diff_uids = $mTeam->getClassMemberList($class_code);

        $mUser = ClsFactory::Create('Model.mUser');
        $userlist = $mUser->getUserBaseByUid($diff_uids);
        $new_userlist = $jsondata = array();
        if(!empty($userlist)) {
            foreach($userlist as $uid=>$user) {
                if(empty($user['client_name'])) {
                    continue;
                }
                $new_userlist[] = array(
                	'uid'=>$uid,
                	'username'=>$user['client_name'],
                );
            }
        }

        if(!empty($new_userlist)) {
            //返回数据
            $jsondata = array(
                'error' => array(
                    'code' => 1,
                    'message' => '成功!',
                ),
                'data' => $new_userlist,
            );
        } else {
            //返回数据
            $jsondata = array(
                'error' => array(
                    'code' => -1,
                    'message' => "该中队的所有成员都已分配到小队\r\n或者该中队还没有添加学生信息!",
                ),
                'data' => array(),
            );
        }

        echo json_encode($jsondata);
    }

    /**
     * 检测用户是否有权限操作该班数据
     * 只有学校管理员和班主任有权限管理相应的班级
     * @param $class_code 检测用户管理的班级信息
     */
    public function checkUserAccess($class_code) {
        $client_type = intval($this->user['client_type']);
        $class_code_list = array();
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
            $schoolinfo_arr = $mSchoolInfo->getSchoolInfoByNetManagerAccount($this->user['ams_account']);
            $schoolinfolist = & $schoolinfo_arr[$this->user['ams_account']];
            //如果用户存在对应管理的学校则管理的班级限制在该学校之内
            if(!empty($schoolinfolist)) {
                $schoolids = array_keys($schoolinfolist);
                $mClassInfo = ClsFactory::Create('Model.mClassInfo');
                $classinfo_arr = $mClassInfo->getClassInfoBySchoolId($schoolids);
                foreach($classinfo_arr as $schoolid=>$list) {
                    $class_code_list = array_merge($class_code_list, array_keys($list));
                }
                $class_code_list = array_unique($class_code_list);
            } elseif(!empty($class_code)) {
                $class_code_list =  array($class_code);
            }

        //处理返回数据
        $return_code = false;
        if(!empty($class_code_list)) {
            $return_code = $class_code && in_array($class_code, $class_code_list) ? $class_code : array_shift($class_code_list);
        }

        return $return_code;
    }

	/**
	 * 页面回退
	 * @param $msg
	 */
	function history_back($msg = "") {
	    if(!empty($msg)) {
    	    echo '<script type="text/javascript">'.
    	         'alert("' . $msg . '")' .
    	         '</script>';
	    }
	    echo '<script type="text/javascript">history.back(-1);</script>';
	    exit;
	}


}

?>