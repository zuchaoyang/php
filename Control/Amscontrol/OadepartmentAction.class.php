<?php
class OadepartmentAction extends AmsController {
	protected $is_school = true;
    public function _initialize() {
        parent::_initialize();
        header("Content-Type:text/html;charset=utf-8;");
        import("@.Common_wmw.Pathmanagement_oa");
	    $this->assign('username', $this->user['ams_name']);
    }
    
   //跳转验证检测登录账号是否属于此学校的
    private function checkUser($uid,$schoolid) {
        $isThis = $this->checkLoginerInSchool($uid,$schoolid);
        if($isThis) {
            return true;
        }else{
            return false;
        }
    }
    
    //ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
        exit;
    }
    
    public function index() {
        $action = $this->objInput->getStr('action');
        $school_id = $this->objInput->getInt('school_id');
        
        $action_list = array(
            'createDepartment' => '新建部门',
            'modify' => '编辑部门',
            'departmentMemberssort' => '人员排序',
        );
        
        $action_keys = array_keys($action_list);
        
        $action = !empty($action) && in_array($action, $action_keys) ? $action : reset($action_keys);
        if(!method_exists($this, $action)) {
            throw new Exception('主页的参数设置有误!', -1);
        }
        
        $open_url = "/Amscontrol/Oadepartment/$action/school_id/$school_id/dpt_id/#id#";
        $open_url_base64 = base64_encode($open_url);
        
        $this->assign('action', $action);
        $this->assign('action_name', $action_list[$action]);
        $this->assign('open_url_base64', $open_url_base64);
        
        $this->assign('school_id', $school_id);
        $this->assign('client_name', $this->user['client_name']);
        
        $this->display('oa_department_index');
    }
    
	//修改部门信息
	public function modifyDepartment(){
		$dpt_id     = $this->objInput->getInt('dpt_id');
		$sort_id    = $this->objInput->postInt('sort_id');
		$dpt_name   = $this->objInput->postStr('dpt_name');
		$up_id      = $this->objInput->postStr('up_id');
		$dpt_phone  = $this->objInput->postStr('dpt_phone');
		$pic_name   = $this->objInput->postStr('pic_name');
		$dpt_description = $this->objInput->postStr('dpt_description');
		
		$school_id = $this->user['schoolinfo']['school_id'];
		
		//权限控制，相应的部门是否是当前登录用户的管理范围之内
		$mDepartment = ClsFactory::Create('Model.mDepartment');
		$dpt_list = $mDepartment->getDepartmentById($dpt_id);
		$dpt = & $dpt_list[$dpt_id];
		
		$err_msg = array();
		
		//检测基本信息是否完整
		if($school_id != $dpt['school_id']) {
		    $err_msg[] = "你没有权限修改部门信息!";
		} else if(empty($dpt_name)) {
	        $err_msg[] = "请填写部门名称!";
	    } else if(empty($dpt_description)) {
	        $err_msg[] = "请填写部门相关职能!";
	    }
	    
	    //检测图片相关的错误信息
	    if(!empty($_FILES['dpt_photo']['name'])) {
	        $img_ext = pathinfo($_FILES['dpt_photo']['name'], PATHINFO_EXTENSION);
	        
	        if(!in_array($img_ext, array('jpg', 'gif', 'png', 'bmp'))) {
	            $err_msg[] = "部门图片类型不符合!";
	        } else if($_FILES['dpt_photo']['error'] != 0) {
	            $err_msg[] = "部门图片上传错误请稍后重试!";
	        } else if($_FILES['dpt_photo']['size'] > 2097152) {
	            $err_msg[] = "部门图片不能大于2M!";
	        }
	    }
	    
	    //检测上级分类id的值是否正确
	    if($up_id > 0) {
    	    $department_list = $mDepartment->getDepartmentById($up_id);
    	    $up_department = & $department_list[$up_id];
    	    
    	    if(empty($up_department) || $up_department['school_id'] != $school_id) {
    	        $err_msg[] = "上级分类信息不存在!";
    	    }
	    }
	    
	     //上传图片内容
	    $is_update_dpt_photo = false;
	    if(empty($err_msg) && !empty($_FILES['dpt_photo']['name'])) {
	        $photo_file = $this->upload('dpt_photo');
	        $dpt_photo = pathinfo($photo_file['getfilename'], PATHINFO_BASENAME);
	        if(empty($dpt_photo)) {
	            $err_msg[] = "部门照片上传失败!";
	        }
	        $is_update_dpt_photo = true;
	    }
	    
	    if(empty($err_msg)) {
	        $dpt_datas = array(
	            'sort_id' => $sort_id,
	            'dpt_name' => $dpt_name,
	            'up_id' => $up_id,
	            'dpt_phone' => $dpt_phone,
	            'dpt_description'=>$dpt_description,
	        );
	        
	        //判断是否更新了部门图片信息
	        if($is_update_dpt_photo && !empty($dpt_photo)) {
	            $dpt_datas['dpt_photo'] = $dpt_photo;
	        }
	        $effect_rows = $mDepartment->modifyDepartment($dpt_datas, $dpt_id);
	        
	        //清理以前的图片信息
	        if($effect_rows && $is_update_dpt_photo) {
	            
//	            import('@.Common_wmw.functions', null, '.php');
	            clear_file(Pathmanagement_oa::uploadDepartmentImg(), $dpt['dpt_photo_small']);
	            clear_file(Pathmanagement_oa::uploadDepartmentImg(), $dpt['dpt_photo']);
	        }
	        
	        $json_datas = array(
	            'error' => array(
	                'code' => 1,
	                'message' => '部门修改成功!',
	            ),
	            'data' => array(
	                'dpt_id' => $dpt_id,
	            ),
	        );
	    } else {
	        $json_datas = array(
	            'error' => array(
	                'code' => -1,
	            	'message' => array_shift($err_msg),
	            ),
	            'data' => array(),
	        );
	    }
	    
	    echo json_encode($json_datas);
	}
	
	//编辑部门信息回显
	public function modify(){
		$dpt_id = $this->objInput->getInt('dpt_id');
		$mDepartment = ClsFactory::Create('Model.mDepartment');
		
		$dpt_list = $mDepartment->getDepartmentById($dpt_id);
		$dptinfos = & $dpt_list[$dpt_id];
		
		$up_id = $dptinfos['up_id'];
		if(!empty($up_id)) {
		    $upinfos = $mDepartment->getDepartmentById($up_id);
		    $dptinfos['up_name'] = $upinfos[$up_id]['dpt_name'];
		}
		
		$school_id = $this->user['schoolinfo']['school_id'];
		$this->assign('dptinfos', $dptinfos);
		$this->assign('dpt_id', $dpt_id);
		$this->assign('school_id', $school_id);
		
		$this->display('oa_department_update');
	}
	
	public function createDepartment() {
	    $dpt_id = $this->objInput->getInt('dpt_id');
	    $school_id = $this->objInput->getInt('school_id');
	    $mDepartment = ClsFactory::Create('Model.mDepartment');
	    $dpt_arr = $mDepartment->getDepartmentBySchoolId($school_id);
	    $dpt_list = & $dpt_arr[$school_id];
	    $show_updpt = !empty($dpt_list) ? true : false;
	    
	    $this->assign('school_id', $school_id);
	    $this->assign('show_updpt', $show_updpt);
	    
	    $this->display('oa_department_create');
	}
	
	public function addDepartment() {
	    $school_id = $this->objInput->getInt('school_id');
	    $sort_id = $this->objInput->postInt('sort_id');
	    $dpt_name = $this->objInput->postStr('dpt_name');
	    $dpt_phone = $this->objInput->postStr('dpt_phone');
	    $dpt_description = $this->objInput->postStr('dpt_description');
	    $up_id = $this->objInput->postInt('up_id');
	    
	    $this->checkLoginerInSchool($this->user['ams_account'], $school_id);
	    
	    $mDepartment = ClsFactory::Create('Model.mDepartment');
	    
	    //错误信息检测
	    $err_msg = array();
	    
	    //检测部门的其他信息是否完整
	    if(empty($school_id)) {
	        $err_msg[] = "您没有权限创建该学校的部门!";
	    } else if(empty($dpt_name)) {
	        $err_msg[] = "请填写部门名称!";
	    } else if(empty($dpt_description)) {
	        $err_msg[] = "请填写部门相关职能!";
	    } else if(empty($_FILES['dpt_photo']['name'])) {
	        $err_msg[] = "请上传部门图片信息!";
	    }
	    
	    //检测图片相关的错误信息
	    if(!empty($_FILES['dpt_photo']['name'])) {
	        $img_ext = pathinfo($_FILES['dpt_photo']['name'], PATHINFO_EXTENSION);
	        
	        if(!in_array($img_ext, array('jpg', 'gif', 'png', 'bmp'))) {
	            $err_msg[] = "部门图片类型不符合!";
	        } else if($_FILES['dpt_photo']['error'] != 0) {
	            $err_msg[] = "部门图片上传错误请稍后重试!";
	        } else if($_FILES['dpt_photo']['size'] > 2097152) {
	            $err_msg[] = "部门图片不能大于2M!";
	        }
	    }
	    
	    //上传图片内容
	    if(empty($err_msg) && !empty($_FILES['dpt_photo']['name'])) {
	        $photo_file = $this->upload('dpt_photo');
	        $dpt_photo = pathinfo($photo_file['getfilename'], PATHINFO_BASENAME);
	        if(empty($dpt_photo)) {
	            $err_msg[] = "部门照片上传失败!";
	        }
	    }
	    
	    //检测上级分类id的值是否正确
	    if($up_id > 0) {
    	    $department_list = $mDepartment->getDepartmentById($up_id);
    	    $up_department = & $department_list[$up_id];
    	    if(empty($up_department) || $up_department['school_id'] != $school_id) {
    	        $err_msg[] = "上级分类信息不存在!";
    	    }
	    }
	    
	    $json_data = array();
	    if(empty($err_msg)) {
    	    $department_datas = array(
    	        'school_id' => $school_id,
    	        'sort_id' => $sort_id,
    	        'dpt_name' => $dpt_name,
    	        'dpt_description' => $dpt_description,
    	        'dpt_phone' => $dpt_phone,
    	        'dpt_photo' => !empty($dpt_photo) ? $dpt_photo : "",
    	        'up_id' => $up_id,
    	    );
    	    
    	    $dpt_id = $mDepartment->addDepartment($department_datas);
    	    $json_data = array(
    	        'error' => array(
    	            'code' => 1,
    	            'message' => "部门添加成功 !",
    	        ),
    	        'data' => array(
    	            'school_id' => $school_id,
    	            'dpt_id' => $dpt_id,
    	            'up_id' => $up_id,
    	        ),
    	    );
	    } else {
	         $json_data = array(
    	        'error' => array(
    	            'code' => -1,
    	            'message' => array_shift($err_msg),
    	        ),
    	        'data' => array(),
    	    );
	    }
	    
	    echo json_encode($json_data);
	}
	
	//删除部门信息及关系
	public function deldepartment(){
		$dpt_id = $this->objInput->getInt('dpt_id');
		
		$mDepartment = ClsFactory::Create('Model.mDepartment');
		$mDepartmentMembers = ClsFactory::Create('Model.mDepartmentMembers');
		
		$tmp_department = $mDepartment->getDepartmentByUpid($dpt_id);
		$department = $tmp_department[$dpt_id];
		
		$son_department = array();
		foreach($department as $val){
			$son_department['name'][] = $val['dpt_name'];
		}
		//$son_department['count'] = count($department);
		if(!empty($department)){
			  $resault['errorcode'] = -1;
			  $resault['data'] = $son_department;
		}else{
			$members_infos = $mDepartmentMembers->getDepartmentMembersByDptId($dpt_id);
			if(!empty($members_infos)){
				$del_members = $mDepartmentMembers->delDepartmentMembersByDptId($dpt_id);
			}
			if((empty($members_infos) && empty($del_members)) || (!empty($members_infos) && !empty($del_members))){
				$delresult = $mDepartment->delDepartmentByDptId($dpt_id);
				if($delresult){
					$resault['errorcode'] = 1;
			  		$resault['message'] = '删除成功';
				}else{
					$resault['errorcode'] = -2;
			  		$resault['message'] = '删除失败';
				}
			}else{
				$resault['errorcode'] = -2;
			  	$resault['message'] = '删除失败';
			}
		}
		echo json_encode($resault);
	}
	
	//删除部门的测试页面
	public function delhtml(){
		$this->display('deldepartment');
	}
	
	//人员排序
	public function departmentMemberssort(){
		$end = 10;
    	$i = $this->objInput->getInt('i');
    	$i = empty($i) || ($i<0) ? 0 : intval($i);
		$page = $this->objInput->getStr('page');
		$page = max(1, $page);
		$dpt_id = $this->objInput->getInt('dpt_id');
		$mDepartment = ClsFactory::Create("Model.mDepartment");
		$depart = $mDepartment->getDepartmentById($dpt_id);
		$mDepartmentMembers = ClsFactory::Create('Model.mDepartmentMembers');
		$membersinfos = $mDepartmentMembers->getDepartmentMembersByDptId($dpt_id);
		$uids = array();
		foreach($membersinfos[$dpt_id] as $key=>$val){
			$uids[]=$val['client_account'];
		}
		$mUser = ClsFactory::Create('Model.mUser');
		$client_infos = $mUser->getUserBaseByUid($uids);
		$totalpage = ceil(count($membersinfos[$dpt_id])/$end);
		$page = min($page, $totalpage);
		$offset = ($page-1)*$end;
		foreach($membersinfos[$dpt_id] as $key=>$val){
			foreach($client_infos as $key1=>$val1){
				if($key1 == $val['client_account']){
					$membersinfos[$dpt_id][$key]['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
					$membersinfos[$dpt_id][$key]['client_name'] = $val1['client_name'];
				}
			}
			
		}
		$sort_id = array();
		foreach($membersinfos[$dpt_id] as $val){
			$sort_id[] = $val['sort_id'];
    	}
    	array_multisort($sort_id, SORT_ASC, $membersinfos[$dpt_id]);

		foreach($membersinfos[$dpt_id] as $key=>$val){
			$membersinfos[$dpt_id][$key]['num'] = ++$i;
		}
		$new_membersinfos = array_slice($membersinfos[$dpt_id],$offset,$end);
		$this->assign('i',$i);
		$this->assign('page',$page);
		$this->assign('dpt_id',$dpt_id);
		$this->assign('dpart_name', $depart[$dpt_id]['dpt_name']);
		$this->assign('totalpage', $totalpage);
		$this->assign('membersinfos',$new_membersinfos);
		
		$this->display('oa_departmentmembers_sort');
	}
	
	//根据人员id修改排序
	public function modifyDepartmentMembers(){
		$dpt_id = $this->objInput->getInt('dpt_id');
		$dptmb_id = $this->objInput->getInt('dptmb_id');
		$sort_id = $this->objInput->getInt('sort_id');
		$mDepartmentMembers = ClsFactory::Create('Model.mDepartmentMembers');
		$datarr = array(
			'sort_id' => $sort_id,
		);
		
		$resault = $mDepartmentMembers->modifyDepartmentMembers($datarr, $dptmb_id);
		if($resault){
			$this->redirect('/Oadepartment/departmentMemberssort/dpt_id/'.$dpt_id);
		}else{
			$this->showError("修改失败", "/Amscontrol/Oadepartment/departmentMemberssort/dpt_id/$dpt_id");
		}
	}
	
	/*$inputname：input表单的名字
     *$name:生成上传文件的名字
     *$url:上传的文件存放的位置
     *$allow_type:允许上传的类型的数组，默认=====array('jpg','gif','png','bmp');
     */
    private function upload($inputname, $name, $allow_type = array('jpg','gif','png','bmp'), $resize_width = 150, $resize_height = 90) {
    	if(empty($inputname)) {
    	    return false;
    	}
    	
    	//判断上传路径是否存在
    	$upload_path = Pathmanagement_oa::uploadDepartmentImg();
    	if(!is_dir($upload_path)) {
    	    throw new Exception("部门上传照片保存路径未定义或{$upload_path}目录无读写权限!", -1);
    	}
    	
        if(!empty($_FILES[$inputname]['name'])) {
        	$up_init = array (
			  'attachmentspath' => $upload_path,
              'renamed' => true,
              'newname' => $name, //不包含扩展名
			  'ifresize' => true,
			  'resize_width' => $resize_width,
			  'resize_height' => $resize_height,
        	  'allow_type' => $allow_type,
    	    );
 
            $upload = ClsFactory::Create('@.Common_wmw.WmwUpload');
            $upload->_set_options($up_init);
            $file = $upload->upfile($inputname);
            
            if (empty ( $file )) {
                return false;
            }
            return $file;
        }
        return false;
    }
    
    /**
     * 显示左侧框架信息
     */
    public function showDepartmentMenuTree() {
        $school_id = $this->objInput->getInt('school_id');
        $open_url_base64 = $this->objInput->getStr('open_url_base64');
        $this->checkLoginerInSchool($this->user['ams_account'], $school_id);
        $open_url = base64_decode($open_url_base64);
        
        $school_name = "";
        if(!empty($school_id)) {
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
            $school_list = $mSchoolInfo->getSchoolInfoById($school_id);
            $school_name = $school_list[$school_id]['school_name'];
        }
        $school_name = empty($school_name) ? "暂无学校信息" : $school_name;
        $this->assign('school_id', $school_id);
        $this->assign('open_url', $open_url);
        $this->assign('school_name', $school_name);
        
        $this->display('oa_department_menu_tree');
    }
}