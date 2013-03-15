<?php
class HomepageAction extends SnsController{
    public $user;

	public function _initialize() {
	    parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");
	}

	function index(){
		$class_code = $_REQUEST['class_code'];
		$class_code_list = array_keys($this->user['class_info']);
		
		//如果没有传入对应的class_code值
		if(empty($class_code)) {
			$class_code = reset($class_code_list);
		}
		
		//如果没有班级信息了
		if(empty($class_code_list)) {
			$this->showError("您不属于任何班级!", "/Homeuser/Index/spacehome/spaceid/".$this->getCookieAccount());
		}
		//判断对应的班级是否是登陆用户
		if(!in_array($class_code, (array)$class_code_list)) {
			$this->showError("您不属于该班级!", "/Homepage/Homepage/index/class_code/" . reset($class_code_list));
		}
		
		list($need_show, $need_upgrade, $secret_key) = $this->Upgrade($class_code);
		if(!empty($need_show)) {
		    if($need_upgrade) {
		        $this->assign('uid', $this->user['client_account']);
		        $this->assign('class_code', $class_code);
		        $this->assign('secret_key', $secret_key);
		    }
		    $this->assign('need_upgrade', $need_upgrade);
		    
		    $this->display("Homeclass/upgrade_tip");
		    
		} else {
    		$client_type = ($this->user['client_type']);    //CLIENT_TYPE_TEACHER 1 老师
    		if($client_type == CLIENT_TYPE_TEACHER){
    			$this->redirect("../Homeclass/Myclass/index/class_code/".$class_code);
    		} else {
    			$this->redirect("../Homeclass/Account/index/".$class_code);
    		}
		}
	}

  
    //班级升级和毕业拦截器
    protected function Upgrade($class_code) {
    	if(empty($class_code)) {
    		return false;
    	}
    	
        $class_code_list = array_keys($this->user['class_info']);
        $class_code = in_array($class_code, (array)$class_code_list) ? $class_code : array_shift($class_code_list);
        
        //判断是否是班主任
        $class_info = $this->user['class_info'][$class_code];
        
        try {
        	import("@.Control.Api.Upgrade.Core.reflectClassInfo");
        	$classUpdateObj = new reflectClassInfo($class_code);
        } catch(Exception $e) {
        	return false;
        }
        
        $need_upgrade = $need_show = false;
        //$is_headteacher = $this->user['client_type'] == CLIENT_TYPE_TEACHER && $class_info['headteacher_account'] == $this->user['client_account'];
        
        //if($is_headteacher && $classUpdateObj->needUpgrade()) {
        if($classUpdateObj->needUpgrade()) {
            $need_upgrade = true;
        }
        
        $need_show = $classUpdateObj->isDoing() || $need_upgrade ? true : false;
        
        return array($need_show, $need_upgrade, $classUpdateObj->getSecretKey());
    }
    
	function homespace(){
		$this->redirect("../Homeuser/Index/spacehome/spaceid/".$this->getCookieAccount());
	}
	
	function headerinfo(){
		$_REQUEST['class_code']=="" ? $class_code = key($this->user['client_class']) : $class_code = $_REQUEST['class_code'];
		
		if($this->user['client_type'] != CLIENT_TYPE_TEACHER){
			$mClassInfo = ClsFactory::Create('Model.mClassInfo');	
			$mClientClass = ClsFactory::Create('Model.mClientClass');	
			$classInfo = $mClassInfo->getClassInfoByclass_code($class_code);
			if(!$classInfo) {
				$class_code = key($this->user['client_class']);
			} else {
				
				$UserClassInfo = $mClientClass->getMyClassCodeInfo($this->getCookieAccount(),$class_code);
				if(!$UserClassInfo) { 
					$class_code = key($this->user['client_class']);
				}
			
			}
		}
		
		
		$school_id = $this->user['class_info'][key($this->user['client_class'])]['school_id'];
		$school_name = $this->user['school_info'][$school_id]['school_name'];
		$this->assign('client_headimg',Pathmanagement_sns::getHeadImg($this->user['client_account']) . $this->user['client_headimg']);
		$this->assign('client_name',$this->user['client_name']);
		$this->assign('log_account',$this->getCookieAccount());
		$this->assign('tpl_class_code',$class_code);
		$this->assign('tpl_school_Name',$school_name);
		$this->assign('tpl_gradeclass_Name',$this->user['class_info'][$class_code]['class_name']);
		$this->assign('tpl_grade_id_name',$this->user['class_info'][$class_code]['grade_id_name']);
		$this->assign('tpl_headteacher_account',$this->user['class_info'][$class_code]['headteacher_account']);
	
		/*用户导航模板*/
		$client_type = ($this->user['client_type']);
		if($client_type == CLIENT_TYPE_TEACHER){
			$this->assign('public_header_link',"/Homeclass/Myclass/index");
			$this->assign('public_left_showId',CLIENT_TYPE_TEACHER);
			//获取当前用户的班级列表信息
			$myclasslist = &$this->user['class_info'];
			$this->assign('myclasslist' , $myclasslist); 

		}elseif($client_type==CLIENT_TYPE_STUDENT){
			$this->assign('public_header_link',"/Homeclass/Account/index");
			$this->assign('public_left_showId',CLIENT_TYPE_STUDENT);
		}elseif($client_type==CLIENT_TYPE_FAMILY){
			$this->assign('public_header_link',"/Homeclass/Account/index");
			$this->assign('public_left_showId',CLIENT_TYPE_FAMILY);
		}
	    //判断当前用户是否是管理员
	    foreach($this->user['client_class'] as $key=>$val) {
	        if($val['class_code'] == $class_code) {
	            $current_class_info = $val;
	        }
	    }

		$var_teacher_class_role =  $current_class_info['teacher_class_role']; //班主任
		$var_class_admin =  $current_class_info['class_admin'];  //班级管理员
		if($var_teacher_class_role == TEACHER_CLASS_ROLE_CLASSADMIN || $var_class_admin == IS_CLASS_ADMIN){
			$falg_albumManager = 0;
		}else{
			$falg_albumManager = 1;
		}		
		$this->assign('falg_albumManager',$falg_albumManager);
		
		//统计班级成员数量 

	}

	
	//用户访问权限验证
	function chkUserJurisdiction($UserType,$stopstate){
		if($UserType==false){
			switch($stopstate){
				case "submit" :
					$this->redirect("../Homepage/Homepage/index");
					exit;
				break;
				case "ajax" :
					exit;
				break;
			}
			
		}
	}
    //说说上传照片方法
    public function stalk_upload_photo(){
        $stalk_upload_tpl = WEB_ROOT_DIR . "/View/Template/Public/talk_photo_upload.html";
        $this->display($stalk_upload_tpl);
    }




}
?>