<?php

/**
 * 虚拟RModel类，作为用户常用业务封装
 * 
 * @author lnczx
 */


class mUserVm {
    
    public function __construct() {

    }
        
    /********************************************************************************
     * 基本业务扩展封装
     ********************************************************************************/
    
	//获取token中的用户名和密码
    public function getCurrentUser() {

        $cookie_info = $this->getCookieTokenInfo(SNS_SESSION_TOKEN);
        $uid = $cookie_info['uid'];
        if (!empty($uid)) {
            $userlist = $this->getUserByUid($uid);
            $user = $userlist[$uid];
        }
        return !empty($user) ? $user : false;
    }    
    
   	/**
	 * 通过用户账号获取用户的基本信息, 
	 * @param $uid
	 * return array()
	 * 
	 */
	public function getClientAccountById($uid) {
	    
	    if(empty($uid)) {
            return false;
        }	    

        //用户基本信息
        $mHashClient = ClsFactory::Create('RModel.Common.mHashClient');
	    $client_base = $mHashClient->getClientbyUid($uid);
        $userlist[$uid] = $client_base;
	    
	    return !empty($userlist) ? $userlist : false;
	}        
	
	
   	/**
	 * 通过用户账号获取用户的基本信息及班级关系, 
	 * @param $uid
	 * return array()
	 * 
	 */
	public function getUserBaseByUid($uid) {
	    
	    if(empty($uid)) {
            return false;
        }	    

        //用户基本信息
        $mHashClient = ClsFactory::Create('RModel.Common.mHashClient');
	    $client_base = $mHashClient->getClientbyUid($uid);

	    //用户与班级关联信息
	    $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
	    $client_class = $mHashClientClass->getClientClassbyUid($uid);
	    
	    //合并数据
	    $userlist[$uid] = $client_base;
	    $userlist[$uid]['client_class'] = $client_class;
	    
	    //转换数据
		if (!empty($userlist)) {
		    $m = ClsFactory::Create('Model.mUser');
	        foreach($userlist as $uid=>$user) {
	            $userlist[$uid] = $m->parseUser($user);
	        }
	    }
	    
	    return !empty($userlist) ? $userlist : false;
	}   	
    
    
   	/**
	 * 通过用户账号获取用户的相关信息, 分别调用 mHashClass  mHashClientClass _mHashClass _mHashSchool
	 * @param $uids
	 * return array()
	 * 
	 */
	public function getUserByUid($uid) {
	    
	    if(empty($uid)) {
            return false;
        }	    

        //用户基本信息
        $mHashClient = ClsFactory::Create('RModel.Common.mHashClient');
	    $client_base = $mHashClient->getClientbyUid($uid);
	    
	    //没有基本信息则直接读取Model.mUser数据库返回
	    if (empty($client_base)) {
	        $m = ClsFactory::Create('Model.mUser');
	        return $m->getUserByUid($uid);
	    }
	    
	    //用户与班级关联信息
	    $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
	    $client_class_datas = $mHashClientClass->getClientClassbyUid($uid);
	    
	    $client_class = array();
	    
	    //班级基本信息
	    
	    $mHashClass = ClsFactory::Create('RModel.Common.mHashClass');
	    $class_infoes = array();
	    foreach($client_class_datas as $key =>$val) {
	        $client_class[$key] = $val;
	        
	        $class_info = array();
	        $class_code = $val['class_code'];
	        $class_info = $mHashClass->getClassById($class_code);

	        $class_infoes[$class_code] = $class_info;
	        
	        //应该没有一个用户所属多个班级，每个班级属于不同学校的情况，这个情况应该是没有底.
	        $school_id = $class_info['school_id'];
	    }
	    

	    
	    //合并数据
	    $userlist[$uid] = $client_base;
	    $userlist[$uid]['client_class'] = $client_class;
	    $userlist[$uid]['class_info'] = $class_infoes;
	    $userlist[$uid]['school_info'] = array();
		    //学校基本信息
	    $mHashSchool = ClsFactory::Create('RModel.Common.mHashSchool');
	    $school_info = array();
	    if (isset($school_id)) {
	        $school_info = $mHashSchool->getSchoolById($school_id);
	        $userlist[$uid]['school_info'][$school_id] = $school_info;
	    }	    
	    
	    //转换数据
		if (!empty($userlist)) {
		    $m = ClsFactory::Create('Model.mUser');
	        foreach($userlist as $uid=>$user) {
	            $userlist[$uid] = $m->parseUser($user);
	        }
	    }

	    return !empty($userlist) ? $userlist : false;
	}    
	
	
   	/**
	 * 通过用户账号获取用户所有关系成员，包括所有班级成员，用户好友,最终结果为去重后的数组
	 * @param $uid
	 * return array()
	 * 
	 */
	public function getUserAllRelations($uid) {
	    if(empty($uid)) {
            return false;
        }	    

        //获取该用户所有班级成员
	    $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
	    $client_class_datas = $mHashClientClass->getClientClassbyUid($uid);
	    $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
	    $mSetClassTeacher = ClsFactory::Create('RModel.Common.mSetClassTeacher');
	    $mSetClientParent = ClsFactory::Create('RModel.Common.mSetClientParent');
	            
	    $all_uids = array();
	    
        foreach($client_class_datas as $key =>$val) {
            $class_code = $val['class_code'];
            $students = $mSetClassStudent->getClassStudentById($class_code);
            
            $teachers = $mSetClassTeacher->getClassTeacherById($class_code);
            
            $parents = $mSetClientParent->getClientParentByUid($class_code);
            
            if (empty($students)) $students = array();
            if (empty($teachers)) $teachers = array();
            if (empty($parents))  $parents = array();

            
            $all_uids = array_merge($all_uids, $students);
            $all_uids = array_merge($all_uids, $teachers);
            $all_uids = array_merge($all_uids, $parents);
        }
        //获取该用户所有好友
	     /*  用户好友	*************************************************************************/ 
	    $mSetClientFriends = ClsFactory::Create('RModel.Common.mSetClientFriends');
	    $friends = $mSetClientFriends->getClientFriendsByUid($uid);      
        
	    if (empty($friends)) $friends = array();
	    
	    $all_uids = array_merge($all_uids, $friends);
	    
	    $all_uids = array_unique($all_uids);
	    
	    return !empty($all_uids) ? $all_uids : false;
	}	
	
   	/**
	 * 用户登录后相关数据的初始化方法，包括如下：
	 * 
	 * 注意：
	 * 1. 有相关数据是公有，如果其他用户初始化后，则不会再次操作,公有数据的过期时间为0
	 * 2. 本身数据如果也在redis存在，则不会再初始化，并且用户私有数据的过期时间为7天.
	 * 
	 * 用户自身数据:
	 * 		用户实体			usr:[client_account]:obj
	 * 		用户好友			usr:[client_account]:friends
	 * 		家长学生			usr:[client_account]:children          //根据类型来生成
	 * 		学生家长			usr:[client_account]:parent			   //根据类型来生成
	 * 		用户与班级关系	usr:[client_account]:client:class
	 * 
	 * 公有数据:
     * 		班级实体			cls:[class_code]:obj
     * 		学校实体			school:[school_id]:obj
     * 		班级学生			cls:[class_code]:student
     * 		班级老师			cls:[class_code]:teacher
	 * 		班级家长			cls:[class_code]:family
	 * @param $uids
	 * return array()
	 * 
	 */	
	
	function  init_user_data($uid) {

	    if(empty($uid)) {
            return false;
        }	  	    
	                
        /*  初始化  用户实体	用户与班级关系  班级实体  学校实体	*************************************/   
	    $userlist = $this->getUserByUid($uid);

	    /*  根据类型来生成 家庭成员	*************************************************************/ 
	    $client_type = $userlist[$uid]['client_type'];

	    Switch($client_type) {
	        case CLIENT_TYPE_STUDENT :
	            $mSetClientParent = ClsFactory::Create('RModel.Common.mSetClientParent');
	            $result = $mSetClientParent->getClientParentByUid($uid);
	            
//    	        print_r("======================mSetClientParent========================= \n");
//    	        print_r($result);	            
//	            
	            break;
	        case CLIENT_TYPE_FAMILY  :
	            $mSetClientChileren = ClsFactory::Create('RModel.Common.mSetClientChildren');
	            $result = $mSetClientChileren->getClientChildrenByUid($uid);
	            
//    	        print_r("======================mSetClientChileren========================= \n");
//    	        print_r($result);	
    	        	            
	            break;
	        default : 
	            break;
	    }
	    
	     /*  用户好友	*************************************************************************/ 
	    $mSetClientFriends = ClsFactory::Create('RModel.Common.mSetClientFriends');
	    $result = $mSetClientFriends->getClientFriendsByUid($uid);
	    
//        print_r("======================mSetClientFriends========================= \n");
//        print_r($result);		    
  
	     /*  班级成员	*************************************************************************/ 
	    $client_class_datas = $userlist[$uid]['client_class'];

	    if (!empty($client_class_datas)) {
	        
	        $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
	        $mSetClassTeacher = ClsFactory::Create('RModel.Common.mSetClassTeacher');
	        $mSetClassFamily = ClsFactory::Create('RModel.Common.mSetClassFamily');
    	    foreach($client_class_datas as $key =>$val) {
    	        $client_class[$key] = $val;
    	        

    	        $class_code = $val['class_code'];
//    	        print_r(" class_code = $class_code \n");
    	        $class_student = $mSetClassStudent->getClassStudentById($class_code);
    	        
//    	        print_r("======================class_student========================= \n");
//    	        print_r($class_student);
    	        	  
    	        $class_teacher = $mSetClassTeacher->getClassTeacherById($class_code);
    	        
//    	        print_r("======================class_teacher========================= \n");
//    	        print_r($class_teacher);    	        
    	        
    	        $class_family = $mSetClassFamily->getClassFamilyById($class_code);
    	        
//    	        print_r("======================class_family========================= \n");
//    	        print_r($class_family);  
//    	        return true;  	         	        
                
    	    }	        
	    }
	    
	    return true;
	}
	
	
    /********************************************************************************
     * 私有辅助函数封装
     ********************************************************************************/	
	
	// 私有辅助函数方法放在这里
	
	
    //获取cookie中的token
	private  function getCookieTokenInfo($token_name) {
		if (empty($token_name)) {
			return false;
		}
		
		$token = $_COOKIE[$token_name];
		
		$token_arr = token_decode($token);
//		list($passwd, $uid) = empty($token_arr) || count($token_arr) < 2 ? array('', '') : $token_arr;
		list($client_id, $username, $access_token, $expires_in, $scope) = $token_arr;
		$passwd = '';
		return array('uid' => $username, 'passwd'=> $passwd);
	}	
	
	
}
