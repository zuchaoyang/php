<?php
/**
 * 注：只有通过用户uid获取部门信息的时候追加用户的权限信息
 * @author Administrator
 */
class mDepartmentMembers extends mBase {
	
	protected $_dDepartmentMembers = null;
	
	public function __construct() {
		$this->_dDepartmentMembers = ClsFactory::Create('Data.dDepartmentMembers');
	}
	
    /**
     * 通过uid得到部门成员信息
     */
    function getDepartmentMembersByUid($uids, $vocation = null) {
        if(empty($uids)) {
            return false;
        }
        
        $dptmember_list = $this->_dDepartmentMembers->getDepartmentMembersByUid($uids);
        
        if(!empty($dptmember_list)) {
            if($vocation == constant('GET_OA_DPT_MEMBER_WITH_ACCESS')) {
               $total_role_ids = array();
               foreach($dptmember_list as $uid=>$list) {
                   foreach($list as $dptmb_id=>$member) {
                       $role_ids = !empty($member['role_ids']) ? explode(',', $member['role_ids']) : array();
                       $total_role_ids = array_merge((array)$total_role_ids, (array)$role_ids);
                   }
               }
               
               $role_list = array();
               if(!empty($total_role_ids)) {
                   $mRole = ClsFactory::Create('Model.mRole');
                   $role_list = $mRole->getRoleById($total_role_ids);
               }
               
               if(!empty($role_list)) {
                   foreach($dptmember_list as $uid=>$list) {
                       foreach($list as $dptmb_id=>$member) {
                           $member['access_name_arr'] = $this->megerRoleAcsess($member['role_ids'], $role_list);
                           $list[$dptmb_id] = $member;
                       }
                       $dptmember_list[$uid] = $list;
                   }
               }
            }
        }
        
        return !empty($dptmember_list) ? $dptmember_list : false;
    }

    //通过部门id得到部门的成员
    function getDepartmentMembersByDptId($dpt_ids) {
        if(empty($dpt_ids)) {
            return false;
        }
        
        return $this->_dDepartmentMembers->getDepartmentMembersByDptId($dpt_ids);
    }

    //添加部门成员信息
    function addDepartmentMembers($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dDepartmentMembers->addDepartmentMembers($dataarr);
    }

    //修改部门信息
    function modifyDepartmentMembers($dataarr, $dptmb_id) {
        if(empty($dptmb_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dDepartmentMembers->modifyDepartmentMembers($dataarr, $dptmb_id);
    }
    
    //删除部门成员
     function delDepartmentMembers($dptmb_id) {
        if(empty($dptmb_id)) {
            return false;
        }
        
        return $this->_dDepartmentMembers->delDepartmentMembers($dptmb_id);
     }

     /**
      * 外键删除部门成员
      */
     function delDepartmentMembersByDptId($dpt_id) {
        if(empty($dpt_id)) {
            return false;
        }
        
        $dpt_id = is_array($dpt_id) ? array_shift($dpt_id) : $dpt_id;
        $dptmembers_arr = $this->_dDepartmentMembers->getDepartmentBydpt($dpt_id);
        $dptmembers_list = & $dptmembers_arr[$dpt_id];
        
        $effect_row = 0;
        if(!empty($dptmembers_list)) {
            foreach($dptmembers_list as $dptmb_id=>$dptmember) {
                $this->delDepartmentMembers($dptmb_id) && $effect_row++;
            }
        }
        
        return $effect_row;
    }
    
    /**
     * 合并用户的角色权限信息
     * @param  $role_ids
     * @param  $role_list
     */
    public function megerRoleAcsess($role_ids, $role_list) {
        if(empty($role_ids) || empty($role_list)) {
            return false;
        }
        
        $role_ids = is_array($role_ids) ? $role_ids : explode(',', $role_ids);
        $access_list = array();
        foreach($role_ids as $role_id) {
            $role_access_list = $role_list[$role_id]['access_name_arr'];
            if(empty($role_access_list)) {
                continue;
            }
            foreach($role_access_list as $key=>$val) {
                if(!isset($access_list[$key])) {
                    $access_list[$key] = $val;
                }
            }
            
        }
        
        return $access_list;
    }

}