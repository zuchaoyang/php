<?php

class mTeam extends mBase {
    protected $_dTeam = null;
    
    public function __construct() {
    	$this->_dTeam = ClsFactory::Create('Data.dTeam');	
    }
    
    /**
     * 通过小队Id获取小队的基本信息
     * @param $team_ids
     */
    public function getTeamById($team_ids) {
        if (empty($team_ids)) {
            return false;
        }
        
        $teamlist = $this->_dTeam->getTeamById($team_ids);
        //追加小队的成员信息
        $team_number_duties_list = $this->getTeamNumberDutiesByTeamId($team_ids);
        
        //将对应的成员信息拼接到返回数据中去
        if (!empty($team_number_duties_list)) {
            foreach($teamlist as $team_id=>$team) {
                if (isset($team_number_duties_list[$team_id])) {
                    $team['member_list'] = $team_number_duties_list[$team_id];
                }
                $teamlist[$team_id] = $team;
            }
        }
        
        return !empty($teamlist) ? $teamlist : false;
    }
    
    /**
     * 通过中队id获取小队的基本信息
     * @param $squadron_ids
     */
    public function getTeamBaseBySquadronId($squadron_ids) {
        if (empty($squadron_ids)) {
            return false;
        }

        return $this->_dTeam->getTeamBySquadronId($squadron_ids);
    }
    
    /**
     * 通过中队Id获取小队的详细信息包括成员列表
     * @param $squadron_ids
     */
    public function getTeamBySquadronId($squadron_ids) {
        if (empty($squadron_ids)) {
            return false;
        }
        
        $teamlist = $this->_dTeam->getTeamBySquadronId($squadron_ids);
        
        //获取team_id
        $team_ids = array();
        foreach ((array)$teamlist as $squadron_id=>$list) {
            if (!empty($list)) {
                $team_ids = array_merge($team_ids, array_keys($list));
            }
        }
        
        //获取班级成员列表信息
        if (!empty($team_ids)) {
            $team_ids = array_unique($team_ids);
            $team_number_duties_list = $this->getTeamNumberDutiesByTeamId($team_ids);
            //将对应的成员信息拼接到返回数据中去
            if (!empty($team_number_duties_list)) {
                foreach($teamlist as $squadron_id=>$list) {
                    foreach($list as $team_id=>$team) {
                        if (isset($team_number_duties_list[$team_id])) {
                            $team['member_list'] = $team_number_duties_list[$team_id];
                        }
                        $list[$team_id] = $team;
                    }
                    $teamlist[$squadron_id] = $list;
                }
            }
        }
        
        return !empty($teamlist) ? $teamlist : false;
    }
    
    /**
     * 增加小队信息
     * @param $dataarr
     * @param $return_insertid true表示返回小队Id，false表示不返回
     */
    public function addTeam($dataarr, $is_return_id = false) {
        if (empty($dataarr)) {
            return false;
        }
        
        return $this->_dTeam->addTeam($dataarr, $is_return_id);
    }
    
    /**
     * 修改小队的基本信息
     * @param $dataarr
     * @param $team_id
     */
    public function modifyTeam($dataarr, $team_id) {
        if (empty($dataarr) || empty($team_id)) {
            return false;
        }

        return $this->_dTeam->modifyTeam($dataarr, $team_id);
    }
    
    /**
     * 通过小队ID删除小队的基本信息
     * @param $team_id
     */
    public function delTeam($team_id) {
        if (empty($team_id)) {
            return false;
        }

        return $this->_dTeam->delTeam($team_id);
    }
    
    /**
     * 检测同一中队下的小队名是否重复
     * @param $name 要检测的小队名
     * @param $squadron_id 中队id
     * @return true表示名字存在，false表示不存在
     */
    public function checkTeamSameName($name, $squadron_id) {
    	$name = trim($name);
        if (empty($name) || empty($squadron_id)) {
            return false;
        }

        $this->_dTeam->switchToTeam();
        $wherearr = array(
        	"squadron_id='$squadron_id'",
        	"team_name='$name'"
        );
        
        return $this->_dTeam->getInfo($wherearr, null, 0, 1);
    }
    
    /**
     * 通过主键获取小队成员的基本职责信息
     * @param $team_duties_ids
     */
    public function getTeamDutiesById($team_duties_ids) {
        if (empty($team_duties_ids)) {
            return false;
        }

        return $this->_dTeam->getTeamDutiesById($team_duties_ids);
    }
    
    /**
     * 通过主键删除小队成员的职责信息
     * @param $team_duties_id
     */
    public function delTeamDuties($team_duties_id) {
        if (empty($team_duties_id)) {
            return false;
        }

        return $this->_dTeam->delTeamDuties($team_duties_id);
    }
    
    /**
     * 检查小队职务是否存在 不是模糊查询
     * @param $name 职务名称
     * return 存在返回 1 不存在返回  0
     * 
     **/
    public function checkSameName($name) {
    	$name = trim($name);
        if (empty($name)) {
            return false;
        }
		
        $this->_dTeam->switchToTeamDuties();
        $wherearr[] = "team_duties_name='$name'";
        $team_duties_list = $this->_dTeam->getInfo($wherearr, null, 0, 1);
        return !empty($team_duties_list) ? 1 : 0;
    }
    
    /**
     * 通过主键获取团队成员的相关信息
     * @param $team_number_duties_ids
     */
    public function getTeamNumberDutiesById($team_number_duties_ids) {
        if (empty($team_number_duties_ids)) {
            return false;
        }

        return $this->_dTeam->getTeamNumberDutiesById($team_number_duties_ids);
    }
    /**
     * 通过小队id获取团队成员的基本信息
     * @param $team_ids
     */
    public function getTeamNumberDutiesBaseByTeamId($team_ids) {
        if (empty($team_ids)) {
            return false;
        }

        return $this->_dTeam->getTeamNumberDutiesByTeamId($team_ids);
    }
    
    /**
     * 通过小队Id获取团队成员的完整信息
     * @param $team_ids
     */
    public function getTeamNumberDutiesByTeamId($team_ids) {
        if (empty($team_ids)) {
            return false;
        }
        
        $team_number_duties_list = $this->_dTeam->getTeamNumberDutiesByTeamId($team_ids);
        
        //获取用户的账号信息
        $uids = array();
        if (!empty($team_number_duties_list)) {
            foreach($team_number_duties_list as $team_id=>$member_list) {
                foreach($member_list as $val) {
                    $uid = intval($val['wmw_uid']);
                    if ($uid <= 0) {
                        continue;
                    }
                    $uids[$uid] = $uid;
                }
            }
        }
        
        //追加用户的基本信息
        if (!empty($uids)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($uids);
            //从文件获取小队成员的职责名
            $team_duties_list = $this->getTeamDutiesFromConfig();
            foreach($team_number_duties_list as $team_id=>$member_list) {
                foreach($member_list as $key=>$val) {
                    $uid = intval($val['wmw_uid']);
                    $val = isset($userlist[$uid]) ? array_merge($val, $userlist[$uid]) : $val;
                    //小队成员的职责名
                    $team_duties_id = intval($val['team_duties_id']);
                    $val['team_duties_name'] = isset($team_duties_list[$team_duties_id]) ? $team_duties_list[$team_duties_id] : "暂无";
                    
                    $member_list[$key] = $val;
                }
                $team_number_duties_list[$team_id] = $member_list;
            }
            unset($userlist);
        }
        
        return !empty($team_number_duties_list) ? $team_number_duties_list : false;
    }
    
    /**
     * 通过小队成员的uid获取小队成员的关系
     * @param $wmw_uids
     */
    public function getTeamNumberDutiesByUid($wmw_uids) {
        if (empty($wmw_uids)) {
            return false;
        }

        return $this->_dTeam->getTeamNumberDutiesByUid($wmw_uids);
    }
    
    /**
     * 添加小队成员的关系
     * @param $dataarr
     */
    public function addTeamNumberDuties($dataarr, $is_return_id = false) {
        if (empty($dataarr) || !is_array($dataarr)) {
            return false;
        }

        return $this->_dTeam->addTeamNumberDuties($dataarr, $is_return_id);
    }
    
    /**
     * 修改小队成员的关系
     * @param $dataarr
     * @param $team_number_duties_id
     */
    public function modifyTeamNumberDuties($dataarr, $team_number_duties_id) {
        if (empty($dataarr) || !is_array($dataarr) || empty($team_number_duties_id)) {
            return false;
        }

        return $this->_dTeam->modifyTeamNumberDuties($dataarr, $team_number_duties_id);
    }
    /**
     * 删除小队成员的关系
     * @param $id
     */
    public function delTeamNumberDuties($id) {
        if (empty($id)) {
            return false;
        }

        return $this->_dTeam->delTeamNumberDuties($id);
    }
    
    /**
     * 从文件获取小队的成员职责信息
     */
    public function getTeamDutiesFromConfig() {
        return array(
            1 => '小队长',
            2 => '副小队长',
            3 => '小队员',
        );
    }
    
    /**
     * 获取中队成员中还没有分配到小队的成员列表
     * @param $class_code
     */
    public function getClassMemberList($class_code) {
        if (empty($class_code)) {
            return false;
        }
        $class_code = is_array($class_code) ? array_shift($class_code) : $class_code;
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $clientclass_arr = $mClientClass->getClientClassByClassCode($class_code, array('client_type'=>CLIENT_TYPE_STUDENT));
        $clientclass_list = & $clientclass_arr[$class_code];
        //该班没有任何成员
        if (empty($clientclass_list)) {
            return false;
        }
        
        //获取所有的班级成员Uid
        $all_student_uids = array_keys($clientclass_list);
        
        //获取当前中队下的其他小队成员Id
        $squadron_id = $class_code;
        $team_arr = $this->getTeamBaseBySquadronId($squadron_id);
        $team_list = & $team_arr[$squadron_id];
        $team_id_list = !empty($team_list) ? array_keys($team_list) : false;
        unset($team_list, $team_arr);
        
        //获取中队下的成员列表
        $team_duties_arr = $this->getTeamNumberDutiesBaseByTeamId($team_id_list);
        //获取已经分配到小队的成员数据
        $exists_student_uids = array();
        if (!empty($team_duties_arr)) {
            foreach($team_duties_arr as $team_id=>$memberlist) {
                foreach($memberlist as $team_duties) {
                    $uid = intval($team_duties['wmw_uid']);
                    if ($uid > 0) {
                        $exists_student_uids[$uid] = $uid;
                    }
                }
            }
        }
        
        //处理可选用的用户信息
        $diff_uids = array();
        if (!empty($all_student_uids) && !empty($exists_student_uids)) {
            $diff_uids = array_diff($all_student_uids, $exists_student_uids);
        } else {
            $diff_uids = & $all_student_uids;
        }
        
        return !empty($diff_uids) ? $diff_uids : false;
    }
    
}
?>