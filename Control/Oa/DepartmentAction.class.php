<?php
class DepartmentAction extends OaController {
    protected $user = array();
	const LENGTH = 2;
	private $_school_info;
	public function _initialize() {
	    parent::_initialize();
	    
		$this->assign('uid', $this->user['client_account']);
	}
	
	//通过学校$school_id得到这个学校的标签列表信息
	public function guide(){
	    
	    $school_id = key($this->user['school_info']);
	    
	    $this->assign('uid', $this->user['client_account']);
		$this->assign('school_id', $school_id);
		
		$this->display("department_guide");
	}
	
	/**
	 * ajax获取部门的信息
	 */
	public function getDptById() {
	    if(!$this->isAjax()) {
	        $this->ajaxReturn(null, "非法操作!", -1, 'json');
	    }
	    
	    $dpt_id = $this->objInput->getInt('dpt_id');
	    
	    $school_id = key($this->user['school_info']);
	    
	    $mDpt = ClsFactory::Create('Model.mDepartment');
	    $dpt_list = $mDpt->getDepartmentById($dpt_id);
	    $dpt = & $dpt_list[$dpt_id];
	    
	    //判断用户是否有权限查看
	    $error_msg = array();
	    if(empty($dpt)) {
	        $error_msg[] = "该部门信息不存在!";
	    } elseif($school_id != $dpt['school_id']) {
	       $error_msg[] = "您无权查看该部门信息!";
	    }
	    
	    if(empty($error_msg)) {
	        $mDptMember = ClsFactory::Create('Model.mDepartmentMembers');
	        $member_arr = $mDptMember->getDepartmentMembersByDptId($dpt_id);
	        $member_list = & $member_arr[$dpt_id];
	        
	        $userlist = $phone_list = $uids = $new_member_list = array();
	        if(!empty($member_list)) {
	            $uids = array();
	            foreach($member_list as $key=>$member) {
	                $uid = $member['client_account'];
	                $uids[$uid] = $uid;
	                $new_member_list[$uid] = array(
	                    'client_account' => $uid,
	                    'duty_name' => $member['duty_name'],
	                );
	                unset($member_list[$key]);
	            }
	        }
	        //获取用户手机号码信息
	        if(!empty($uids)) {
	            $uids = array_unique($uids);
	            $mUser = ClsFactory::Create('Model.mUser');
	            $userlist = $mUser->getUserBaseByUid($uids);
	            if(!empty($userlist)) {
	                foreach($userlist as $uid=>$user) {
	                    $userlist[$uid] = array(
	                        'client_name' => $user['client_name'],
	                    );
	                }
	            }
	            $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
	            $phone_list = $mBusinessphone->getBusinessPhone($uids);
	            
	            if(!empty($phone_list)) {
	                foreach($phone_list as $uid=>$phone) {
	                    $phone_list1[$uid] = array(
	                        'phone_id' => $phone['account_phone_id2'],
	                    );
	                }
	            }
	        }
	        //追加用户名信息
	        if(!empty($new_member_list)) {
	            foreach($new_member_list as $uid=>$member) {
	                $member['client_name'] = !empty($userlist[$uid]) ? $userlist[$uid]['client_name'] : '暂无';
	                $member['phone_id'] = !empty($phone_list1[$uid]) ? $phone_list1[$uid]['phone_id']  : '暂无';
	                $new_member_list[$uid] = $member;
	            }
	        }
	        $dpt['dpt_member_list'] = $new_member_list;
	        
	        $dpt['dpt_photo_src'] = !empty($dpt['dpt_photo_small_url']) ? $dpt['dpt_photo_small_url'] : "";
	        if(!empty($dpt['dpt_photo_src'])){
	        	if(!file_exists(WEB_ROOT_DIR.$dpt['dpt_photo_src'])){
	        		$dpt['dpt_photo_src'] = "";
	        	}
	        }
	        unset($dpt['dpt_photo'], $dpt['dpt_photo_url'], $dpt['dpt_photo_small'], $dpt['dpt_photo_small_url']);
	        $json_data = array(
	            'error' => array(
	                'code' => 1,
	                'message' => '获取部门信息成功!',
	            ),
	            'data' => $dpt,
	        );
	    } else {
	         $json_data = array(
	            'error' => array(
	                'code' => -1,
	                'message' => array_shift($error_msg),
	            ),
	            'data' => array(),
	        );
	    }
	    
	    echo json_encode($json_data);
	}
	
}