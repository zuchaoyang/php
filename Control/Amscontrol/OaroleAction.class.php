<?php
class OaroleAction extends AmsController{
    protected $_uid;
    protected $_school_id;
    private $_user = array();
    public function _initialize(){
        parent::_initialize();
        header('Content-Type:text/html; charset=utf-8');
        import("@.Common_wmw.Constancearr");
        import("@.Common_wmw.Pathmanagement_ams");
        $schoolModel = ClsFactory::Create('Model.mSchoolInfo'); //获取学校id
        $this->_user = $this->getCurrentUser();
        $this->_uid = $this->_user['ams_account'];
        $schoolinfo_arr = $schoolModel->getSchoolInfoByNetManagerAccount($this->_uid);
        $schoolInfo = & $schoolinfo_arr[$this->_uid];
        $temp = array_keys($schoolInfo);
        $this->_school_id = $temp[0];
        
        $this->assign('username', $this->_user['ams_name']);
        if(!($this->checkLoginerInSchool($this->_uid,$this->_school_id))) {
	        self::amsLoginTipMessage('您没有权限操作，请重新登录');
	        die;
	        return false;
	    }
    }
//ams提示信息
	private function amsLoginTipMessage($str){
        $this->assign('menage',$str);
        $this->display('Amscontrol/hyym');
    }
 //跳转验证检测登录账号是否属于此学校的
    private function checkUser($uid,$schoolid) {
        $mUser = ClsFactory::Create('Model.mUser');
        $isThis = $mUser->checkLoginerInSchool($uid,$schoolid);
        if($isThis) {
            return true;
        }else{
            return false;
        }
    }
    public function roleList(){   
        $mRole = ClsFactory::Create('Model.mRole');
        $roleSystemList = $mRole->getRoleSystemAll();
        $roleList = $mRole->getRoleBySchoolId($this->_school_id);
        $this->assign('roleSystemList', $roleSystemList);  
        $this->assign('roleList', $roleList); 
        $this->display('oa_role_list');
    }
    public function roleDetail(){
        $act_flag = $this->objInput->getStr('act_flag');
        $role_id = $this->objInput->getInt('role_id');

        $mRole = ClsFactory::Create('Model.mRole');
        $role_list = $mRole->getRoleById($role_id);
        $role_info = &$role_list[$role_id];
        

        $this->assign('act_flag', $act_flag);        
        $this->assign('role_info', $role_info);
        $this->assign('access_model', Constancearr::oaRoleAccessModel()); //权限对应模块
        $this->display('oa_role_detail');
    }
    public function modifyRole(){
        $role_id = $this->objInput->postInt('role_id');
        $act_flag = $this->objInput->postStr('act_flag');
        $role_name = $this->objInput->postStr('role_name');
        $role_name_old = $this->objInput->postStr('role_name_old');
        $role_access_arr = $this->objInput->postArr('role_access');
        $mRole = ClsFactory::Create('Model.mRole');
        if($role_name == ''){
             $this->showSuccess("请输入角色名称，正在跳转...", "/Amscontrol/Oarole/roleList");
        }else if($role_name != $role_name_old){
            $exist_list = $mRole->getRoleByRoleNameAndSchoolId($role_name, $this->_school_id);
            if(!empty($exist_list)){
                $this->showError("修改失败，角色名称已存在，正在跳转...", "/Amscontrol/Oarole/roleList");
            }
        }
        $datas = array(
        	'role_name'=>$role_name,
        	'role_access_arr' => $role_access_arr,
            'role_access' => '',
        );
        
        $mRole->modifyRole($datas,  $role_id);
        
        $this->assign('role_id', $role_id);
        $this->assign('act_flag', $act_flag); 
        $this->showSuccess("修改成功，正在跳转...", "/Amscontrol/Oarole/roleList");
        
    }
    public function addRole(){
        $role_name = $this->objInput->postStr('role_name');
        $role_access_arr = $this->objInput->postArr('role_access');
        $mRole = ClsFactory::Create('Model.mRole');
        if($role_name == ''){
             $this->showSuccess("请输入角色名称，正在跳转...", "/Amscontrol/Oarole/roleList");
        }else{
            $exist_list = $mRole->getRoleByRoleNameAndSchoolId($role_name, $this->_school_id);
            if(!empty($exist_list)){
                $this->showError("添加失败，角色名称已存在，正在跳转...", "/Amscontrol/Oarole/roleList");
            }
        }
        $datas = array(
        	'role_name'=>$role_name,
            'school_id' =>$this->_school_id,
            'role_access_arr' => $role_access_arr,
            'role_access' => '',
            'add_account'=> $this->_uid,
            'add_time' => $_SERVER['REQUEST_TIME'] 
        );
        $rs = $mRole->addRole($datas);
        if($rs){
        	$this->showSuccess("添加成功,正在跳转...", "/Amscontrol/Oarole/roleList");
        }else{
        	$this->showError("添加失败,正在跳转...", "/Amscontrol/Oarole/roleList");
        }
        
    }
    public function deleteRole(){
        $role_id = $this->objInput->getInt('role_id');
        $mRole = ClsFactory::Create('Model.mRole');
        $rs = $mRole->delRoleById($role_id);
        
        //由于部门成员role_ids字段支持多个角色逗号拼接存储，清理效率低故对使用改角色的成员不在此处处理
    	if($rs){
        	$this->showSuccess("删除成功,正在跳转...", "/Amscontrol/Oarole/roleList");
        }else{
        	$this->showError("删除失败,正在跳转...", "/Amscontrol/Oarole/roleList");
        }
    }
    public function schoolLogo(){
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $school_info = $mSchoolInfo->getSchoolInfoById($this->_school_id);
        
        $this->assign('logo_path', $school_info[$this->_school_id]['school_logo_url']);
        $this->display('oa_school_logo');
    }
    //上传学校logo
    public function uploadSchoolLogo() {
        if(empty($_FILES['school_logo']['name'])) {
            $message = '请选择要上传的图片';
        } else {
            $file_name_noext = $this->_school_id; //使用school_id重命名，无扩展名，再次上传时将覆盖原图片
            $init_data = array(//图片相关配置
                'renamed' => true, 
                'newname' => $file_name_noext,
                 
        	    'ifresize' => true, 
            	'resize_width'=>'150',
//        	    'resize_height' => '65', //等比例缩放，大小待调整
                'overwrite'=>true ,  //重名时允许覆盖
            	'maxsize'=> '2048', //最大2M
                'attachmentspath' =>  Pathmanagement_ams::uploadSchoolLogo(),
                'allow_type' => array('jpg','gif','png','bmp'),
            );
            
            $uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');
            $uploadObj->_set_options($init_data);
            $up_rs = $uploadObj->upfile('school_logo');
            
            $logo_name = pathinfo($up_rs['getfilename'], PATHINFO_BASENAME);
			$rand = rand(1,100000);
			            
            if(empty($up_rs)){
                $this->showError("上传失败，正在跳转...", "/Amscontrol/Oarole/schoolLogo/rand/$rand");
            }else{
                $datas = array(
                    'school_logo' => $logo_name,
                    'upd_date' => date("Y-m-d H:i:s"),
                ); 
                $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
                $mod_rs = $mSchoolInfo->modifySchoolInfo($datas, $this->_school_id);
                
                if($mod_rs){
                	$this->showSuccess("上传成功，正在跳转...", "/Amscontrol/Oarole/schoolLogo/rand/$rand");
                }else{
                	$this->showError("上传失败，正在跳转...", "/Amscontrol/Oarole/schoolLogo/rand/$rand");
                }  
            }
        }
    }
    
}