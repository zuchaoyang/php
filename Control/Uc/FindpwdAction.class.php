<?php
class FindpwdAction extends UcController {
	public $_isLoginCheck = false;
	public function _initialize() {
 		parent::_initialize();
    }
    
    // 找回密码首页
	public function index() {
		 $this->display("find_pwd_index");
	}
	
	 // 找回密码首页 处理
	public function findpwdByUsername() {
		$username = $this->objInput->postStr('username');
		
	 	//校验1：参数空校验
	    if (empty($username)) {
	        $this->showError('用户名不能为空', '/Uc/Findpwd/index');
	    }
	    
		//用户输入数据处理 都统一转换成账号
		$client_account = $this->convertAccount($username);
		//检测用户是否存在
	    $mUser = ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getClientAccountById($client_account);
        if (empty($userInfo)) {
            $this->showError('该用户不存在', '/Uc/Findpwd/index');
        }
        
        //用户存在  根据用户获取用户设置的手机号 邮箱情况显示 用户找回密码页面
        $user_binding_list = $this->getEmailBinding($client_account);

        $this->assign('user_binding_list', $user_binding_list);
        $this->assign('username', $client_account);
        $this->display('find_pwd_mode');
	}
	
	/**
	 * ajax 是否为空，用户是否存在
	 * 
	 */
	public function checkUsernameAjax() {
		$username = $this->objInput->postStr('username');
		
	 	//校验1：参数空校验
	    if (empty($username)) {
	        $this->ajaxReturn(null, '用户名不能为空', -1, 'json');
	    }
		//用户输入数据处理 都统一转换成账号
		$client_account = $this->convertAccount($username);
		//检测用户是否存在
	    $mUser=ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getClientAccountById($client_account);
        if (empty($userInfo)) {
            $this->ajaxReturn(null, '该用户不存在', -1, 'json');     
        }
        
        $this->ajaxReturn(null, null, 1, 'json');
	}
	
	/**
	 * 手机 找回密码 
	 */
	public function findPhoneShow() {
		 $client_account = $this->objInput->getStr('username'); 
		 
		 // 根据用户提交的账号 取出用户设置的手机号
		 $user_binding_list = $this->getEmailBinding($client_account);
		 
		 if (empty($user_binding_list['phone'])) {
		 	$msg = "您还没有设置手机号，请尝试其它方式";
    		$url = '/Uc/Findpwd?username='.$client_account;
    		$this->showSuccess($msg, $url);
		 }
		 
		 $this->assign('user_binding_list', $user_binding_list);
         $this->assign('username', $client_account);
            
         $this->display('find_pwd_phone');
	}
	
	/**
	 * 手机 找回密码 处理
	 *
	 */
	public function findpwdByPhone() {
		$phone = $this->objInput->postInt('phone');
		$scode = $this->objInput->postInt('scode');
		$client_account = $this->objInput->postStr('username');

		// 根据用户提交的账号 取出用户设置的手机号 	验证手机号是否匹配
		$user_binding_list = $this->getEmailBinding($client_account);
		
		// 检测手机号是否匹配
		if ($user_binding_list['phone'] != $phone) {
			$this->showError('手机号错误', '/Uc/Findpwd/findPhoneShow?username='.$client_account);
		}
		 
		//验证验证码是否正确是否过期
    	$last_user_scode = $this->getLastUserScode($client_account);
    	if (time() > $last_user_scode['end_time']) {
    		$this->showError('验证码已过期，请重新发送', '/Uc/Findpwd/findPhoneShow/username/'.$client_account);
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$this->showError('验证码错误，请重试', '/Uc/Findpwd/findPhoneShow/username/'.$client_account);
    	}
    	

    	// 加密手机号 账号 跳转到修改密码页面、
    	$user_info = array(
    		'key' =>mt_rand(1, 100000),   //干扰码没有意义
    		'phone'=> $phone,
    		'username'=>$client_account,
    		'scode'=>$scode
    		
    	);
    	
    	$this->assign('tokey', token_encode($user_info));
    	$this->display('find_pwd_reset');
	}
	
	/**
	 * email 找回密码 
	 *
	 */
	public function findEmailShow() {
		 $client_account = $this->objInput->getStr('username'); 
		 
		 // 根据用户提交的账号 取出用户设置的邮箱
		 $user_binding_list = $this->getEmailBinding($client_account);
		 
		 if (empty($user_binding_list['email'])) {
		 	$msg = "您还没有设置邮箱，请尝试其它方式";
    		$url = '/Uc/Findpwd?username='.$client_account;
    		$this->showSuccess($msg, $url);
		 }
		 
		 $this->assign('user_binding_list', $user_binding_list);
         $this->assign('username', $client_account);
            
         $this->display(find_pwd_email);
	}
	
	/**
	 * email 找回密码 处理
	 *
	 */
	public function findpwdByEmail() {
		$email = $this->objInput->postStr('email');
		$scode = $this->objInput->postInt('scode');
		$client_account = $this->objInput->postStr('username');
		
		// 根据用户提交的账号 取出用户设置的手机号 	验证手机号是否匹配
		$user_binding_list = $this->getEmailBinding($client_account);
		
		// 检测邮箱是否匹配
		if ($user_binding_list['email'] != $email) {
			$this->showError('邮箱错误', '/Uc/Findpwd/findEmailShow/username/'.$client_account);
		}
		 
		//验证验证码是否正确是否过期
    	$last_user_scode = $this->getLastUserScode($client_account);
	    if (time() > $last_user_scode['end_time']) {
    		$this->showError('验证码已过期，请重新发送', '/Uc/Findpwd/findEmailShow/username/'.$client_account);
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$this->showError('验证码错误，请重试', '/Uc/Findpwd/findEmailShow/username/'.$client_account);
    	}

    	// 加密手机号 账号 跳转到修改密码页面、
    	$user_info = array(
    		'key' =>mt_rand(1, 100000),   //干扰码没有意义
    		'email'=> $email,
    		'username'=>$client_account,
    		'scode'=>$scode
    	);
    	
    	$this->assign('tokey', token_encode($user_info));
    	$this->display('find_pwd_reset');
	}
	
	/**
	 * 验证通过修改密码
	 *
	 */
	public function upPwdByPhoneAndEmail() {
		$tokey = $this->objInput->postStr('tokey');
		$new_pwd = $this->objInput->postStr('new_pwd');
		$re_enter  = $this->objInput->postStr('re_enter');
		$user_info = token_decode($tokey);  // 解密 后关联数组变成了索引数组 
		list($key, $phone_email, $client_account, $scode) = $user_info;
		
		//为空判断
		if (empty($new_pwd) || empty($re_enter)) {
			$this->showError('非法操作', '/Uc/Findpwd');
		}
		if($new_pwd != $re_enter){
			$this->showError('非法操作', '/Uc/Findpwd');
		}

		// 检查验证码和（手机号或邮箱）是否正确
		$user_binding_list = $this->getEmailBinding($client_account);
		
		//根据 $phone_email 判断是email 还是手机号找回密码 做不同的处理
		//从而达到 两种找回密码公用一个修改密码 action 的目的
		$isEmail = $this->isEmail($phone_email);
		if ($isEmail) {
			//检测邮箱是否匹配
			$link = 'findEmailShow/username/'.$client_account;
			if ($user_binding_list['email'] != $phone_email) {
				$this->showError("邮箱错误，请重试", '/Uc/Findpwd/'.$link);
			}
		} else {
			// 检测手机号是否匹配
			$link = 'findPhoneShow/username/'.$client_account;
			if ($user_binding_list['phone'] != $phone_email) {
				$this->showError("手机号错误，请重试", '/Uc/Findpwd/'.$link);
			}
		}

		//验证验证码是否正确是否过期
    	$last_user_scode = $this->getLastUserScode($client_account);
    	if (time() > $last_user_scode['end_time']) {
    		$this->showError("验证码已过期，请重新发送", '/Uc/Findpwd/'.$link);
    	} else if ($last_user_scode['security_code'] != $scode){
	    	$this->showError("验证码错误，请重试", '/Uc/Findpwd/'.$link);
    	}
    	
		$mUser = ClsFactory::Create('Model.mUser'); 
		$account_info = $mUser->getClientAccountById($client_account);
		$old_password = $account_info[$client_account]['client_password'];
		//如果新密码与老密码相同
		if(md5($new_pwd) == $old_password){
			$this->ajaxReturn(null, '新密码不能和旧密码相同', 0, 'json');
		}
		
		// 更新密码
		$data = array(
			'client_password'=>md5($new_pwd),
			'upd_time'=>time(),
		);
		$upd_rs = $mUser->modifyUserClientAccount($data, $client_account); 
		if(!empty($upd_rs)) {
			//更新是验证码表 设为过期
    		$mUserScode = ClsFactory::Create('Model.mUserScode');
    		$data = array('end_time'=>time());
    		$is_success = $mUserScode->modifyUserScodeById($data, $client_account);
			// 跳转到成功页面
			
    		$this->assign('username', $client_account);
			$this->display('find_pwd_tips');
		}else{
			$this->assign('tokey', token_encode($tokey));
			$this->showError("系统繁忙请重试", '/Uc/Findpwd/'.$link);
		}
	}
	
	/**
    * ajax 发送手机验证码
    */
	public function sendPhoneAjax() {
   		$client_account = $this->objInput->postStr('username');
   		$phone = $this->objInput->postStr('phone');
   		
    	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode($client_account);
    	
       	//验证手机号是否正确且跟账号匹配
    	if (!$this->checkPhone($client_account, $phone)) {
    		$this->ajaxReturn(null, "手机号错误", -1, 'json');
    	}
    	//计算是否超过，最短间隔发送时间 超过返回true
    	$is_out = (time() - ($last_user_scode['end_time']- FINDPWD_PHONE_TIME_OUT)) < SORT_TIME_OUT;
    	if (!empty($last_user_scode) && $is_out) {
    		$this->ajaxReturn(null, '发送过于频繁，请' .SORT_TIME_OUT. '秒后重新发送', -1, 'json');
    	}
    	
    	//更新验证码表 wmw_user_scode
    	$socde = mt_rand(100000,999999);
    	$end_time = time() + FINDPWD_PHONE_TIME_OUT;
    	$is_success = $this->upUserScode($client_account, $phone, $socde, $end_time);
    	if(empty($is_success)) {
    		$this->ajaxReturn(null, "发送失败，请重试", -1, 'json');
    	}
    	
   		//判断更新验证码表是否成功     成功后发送短信验证码 给用户
    	$operationStrategy = $this->getOperationStrategyByPhone($phone);  //根据手机号获取运营策略
    	$sms_send_content = "您在我们网使用手机找回密码的验证码为：".$socde."，请尽快完成相关操作。";
    	$addData = array(
                'sms_send_mphone'     		=>	$phone,
                'sms_send_content'    		=>	$sms_send_content,
                'sms_send_mphone_num' 		=>	1, 
                'sms_send_type'       		=>	0,
                'db_createtime'       		=>	date("Y-m-d H:i:s"),
            	'sms_send_bussiness_type' 	=> $operationStrategy
        );
            
        $mSmsSend = ClsFactory::Create('Model.mSmsSend');
        $rs = $mSmsSend->sendSingleMsg($addData);
    	if (empty($rs)) {
    		$this->ajaxReturn(null, "系统繁忙，请重试", -1, 'json');
    	}
    	
    	$this->ajaxReturn(null, "验证短信已发送，如未收到，请稍后重发", 1, 'json');
	}
	
    /**
     * ajax 验证并发送邮件
     */
    public function sendEmailAjax() {
   		$email = $this->objInput->postStr('email');
   		$client_account =  $this->objInput->postStr('username');
   		
    	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode($client_account);
    	
    	//计算是否超过，最短间隔发送时间 超过返回true
    	$interval = (time() - ($last_user_scode['end_time']- FINDPWD_EMAIL_TIME_OUT)); //发送间隔时间
    	$is_out =  $interval < SORT_TIME_OUT;
    	if (!empty($last_user_scode) && $is_out && $interval > 0) {
    		$this->ajaxReturn(null, '发送过于频繁，请' .SORT_TIME_OUT. '秒后重新发送', -1, 'JSON');
    	}
    	
    	//验证邮箱是否 正确且没有被使用
    	if (!$this->checkEmail($client_account, $email)) {
	    	$this->ajaxReturn(null, '邮箱错误', -1, 'json');
	    }
	    
    	//更新是数据库
    	$scode = mt_rand(100000,999999);
    	$end_time = time() + FINDPWD_EMAIL_TIME_OUT;
    	$is_success = $this->upUserScode($client_account, $email, $scode, $end_time);
    	//判断更新数据库是否成功 成功后发送邮件
    	if(empty($is_success)) {
    		$this->ajaxReturn(null, "系统繁忙，请重试", -1, 'json');
    	}
    	
    	$email_content = "感谢您使用我们网服务。<br/>您正在使用邮箱修改密码功能，请在验证码输入框中输入此次验证码：$scode 按提示完成密码修改。"
    					 . "<br/>如非本人操作，请忽略此邮件，由此给您带来的不便请谅解<br/> 感谢您对我们网的支持。";
    	$emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
    	
    	//重新设置邮件标题
    	$emailObj->setSubject("我们网邮箱找回密码验证码邮件");
		$is_send = $emailObj->send($email, $email_content);
    	
    	$this->ajaxReturn(null, "验证码邮件已发送，如未收到，请查找垃圾邮箱或稍后重发", 1, 'json');
   	}
	

   	/**
   	 * Ajax 验证手机找回密码表单
   	 */
	public function phoneFromAjax() {
		$client_account = $this->objInput->postStr('username');
		$phone = $this->objInput->postInt('phone');
		$scode = $this->objInput->postInt('scode');

		// 根据用户提交的账号 取出用户设置的手机号 	验证手机号是否匹配
		$user_binding_list = $this->getEmailBinding($client_account);
		$data = array();  //$data 格式说明 array('phone'=>array(错误代码,错误提示),'scode'=>array(错误代码,错误提示))
		
		// 检测手机号是否匹配
		if ($user_binding_list['phone'] != $phone) {
			$data['phone_info'] = '手机号码错误';
		}
		//验证验证码是否正确是否过期
    	$last_user_scode = $this->getLastUserScode($client_account);
    	if (time() > $last_user_scode['end_time']) {
    		$data['scode_info'] = '验证码已过期，请重新发送';
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$data['scode_info'] = '验证码错误，请重试';
    	}

    	empty($data) ? $this->ajaxReturn(null, "正确", 1, 'json') : $this->ajaxReturn($data, "错误", -1, 'json');
	}

   	/**
   	 * Ajax 验证Email找回密码表单
   	 */
	public function emailFromAjax() {
		$client_account = $this->objInput->postStr('username');
		$email = $this->objInput->postStr('email');
		$scode = $this->objInput->postInt('scode');

		// 根据用户提交的账号 取出用户设置的手机号 	验证手机号是否匹配
		$user_binding_list = $this->getEmailBinding($client_account);
		$data = array();  //$data 格式说明 array('phone'=>array(错误代码,错误提示),'scode'=>array(错误代码,错误提示))
		
		// 检测手机号是否匹配
		if ($user_binding_list['email'] != $email) {
			$data['email_info'] = '邮箱错误';
		}
		//验证验证码是否正确是否过期
    	$last_user_scode = $this->getLastUserScode($client_account);
    	if (time() > $last_user_scode['end_time']) {
    		$data['scode_info'] = '验证码已过期，请重新发送';
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$data['scode_info'] = '验证码错误，请重试';
    	}

    	empty($data) ? $this->ajaxReturn(null, "正确", 1, 'json') : $this->ajaxReturn($data, "错误", -1, 'json');
	}
	
	
   	/**
   	 * Ajax 验证手机找回密码表单
   	 */
	public function modifyFromAjax() {
		$tokey = $this->objInput->postStr('tokey');
		$new_pwd = $this->objInput->postStr('new_pwd');
		$pwd_length = strlen($new_pwd);
		
		//为空判断
		if (empty($new_pwd)) {
			$this->ajaxReturn(null, '新密码不能为空', -1, 'json');
		} else if ($pwd_length >20 || $pwd_length < 6) {
			$this->ajaxReturn(null, '密码长度为6~20个字符', -1, 'json');
		}
		
		$user_info = token_decode($tokey);  // 解密 后关联数组变成了索引数组 
		list($key, $phone_email, $client_account, $scode) = $user_info;
		$mUser = ClsFactory::Create('Model.mUser'); 
		$account_info = $mUser->getClientAccountById($client_account);
		$old_password = $account_info[$client_account]['client_password'];
		//如果新密码与老密码相同
		if(md5($new_pwd) == $old_password){
			$this->ajaxReturn(null, '新密码不能和原密码相同', -1, 'json');
		}
		
    	$this->ajaxReturn(null, "正确", 1, 'json');
	}
	
   /**
     * 将用户输入的账号信息映射到实际的账号
     */
    protected function convertAccount($input) {
        if(empty($input)) {
            return false;
        }
        
        //先尝试从手机绑定中获取账号信息，如果手机中找到了该账号信息，则返回；
        if($this->isPhonenum($input)) {
            $mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
            $business_phone_list = $mBusinessphone->getbusinessphonebyalias_id($input);
            $business_phone = & $business_phone_list[$input];
            if(!empty($business_phone['uid'])) {
                return $business_phone['uid'];
            }
        }
        
        if($this->isUid($input)) {
            return $input;
        }
        
        if($this->isEmail($input)) {
            $time33_key = time33($input);
            //获取email映射到的账号信息
            $mEmailBinding = ClsFactory::Create('Model.mEmailBinding');
            $email_bind_arr = $mEmailBinding->getEmailBindingByTime33Key($time33_key);
            $email_bind_list = $email_bind_arr[$time33_key];
            //查找对应的账号信息
            foreach((array)$email_bind_list as $bind) {
                if($bind['email'] == $input) {
                    return $bind['client_account'];
                }
            }
            return false;
        }        
        
        return false;
    }
    
    /**
	 * 根据用户账号获取对应邮箱 手机号 
	 * 并作相应隐藏处理
	 */
    private function getEmailBinding($client_account){
    	
    	if(empty($client_account)) {
    		return false;
    	}
    	//根据账号取得手机号 和邮箱并作部分隐藏处理
    	$email_binding_info = array();
		
		$mEmailBinding = ClsFactory::Create('Model.mEmailBinding');
		$email_binding_list = $mEmailBinding->getEmailBindingByClientAccount($client_account);
		$email_binding_info = reset($email_binding_list[$client_account]);
		
		$mBusinessphon = ClsFactory::Create('Model.mBusinessphone');
		//$phone_list = $mBusinessphon->getBusinessPhone($client_account); // 没有手机号码的详细信息
		$phone_list = $mBusinessphon->getbusinessphonebyalias_id($client_account);   //原来取用户绑定时候号码
		$phone_info = $phone_list[$client_account];
		
		if (!empty($email_binding_info['email'])) {
			$befstr = substr($email_binding_info['email'], 0, strrpos($email_binding_info['email'], "@"));
			$endstr = substr($email_binding_info['email'], strrpos($email_binding_info['email'], "@"));
			$email_binding_info['sort_email'] = substr($befstr,0 ,3) . '****' . $endstr;
		}
		
		if (!empty($phone_info['phone_id'])) {
			$email_binding_info['phone'] = $phone_info['phone_id'];
			$email_binding_info['sort_phone'] = substr_replace($phone_info['phone_id'], '****', 3, 4);
		}
		
		return $email_binding_info;
    }
    
    /**
     * 获取最后一次发送的记录(一个用户只能有一条记录) 
     * 
     * @return 如果没有返回 false
     */
    private function getLastUserScode($client_account) {
    	if (empty($client_account)) {
    		return false;
    	}
    	
    	$mUserScode = ClsFactory::Create('Model.mUserScode');
		$user_code_last = $mUserScode->getUserScodeById($client_account);
		$user_code = & $user_code_last[$client_account];
		
		return !empty($user_code) ? $user_code : false;
    }
    
    /**
     * 验证手机号 是否正确 且和账号匹配 
     * 
     * @return 如果错误返回 false
     */
    private function checkPhone($client_account, $phone) {
    	if (empty($client_account) || empty($phone)) {
    		return false;
    	}
    	
    	// 获取账号绑定记录
    	$binding_info = $this->getEmailBinding($client_account);

    	return ($binding_info['phone'] != $phone) ? false : true;
    }
    
        /**
     * 验证手机号 是否正确 且和账号匹配 
     * 
     * @return 如果错误返回 false
     */
    
    private function checkEmail($client_account, $email) {
    	if (empty($client_account) || empty($email)) {
    		return false;
    	}
    	
    	// 获取账号绑定记录
    	$binding_info = $this->getEmailBinding($client_account);
    	
    	return isset($binding_info['email']) && ($binding_info['email'] != $email) ? false : true;
    }
    
    /**
     * 更新 验证码发送表
     * @param $clent_email 收邮件的email地址  也可能存手机号码 
     * @param $scode 验证码
     * @return 是否更新成功 
     */
    private function upUserScode($client_account, $clent_email, $scode, $end_time) {
    	if (empty($client_account) || empty($clent_email) || empty($scode) || empty($end_time)) {
    		return false;
    	}
    	
    	$is_success = false; //是否成功
    	
    	$data = array(
    		'client_account' => $client_account,
    		'client_email'   => $clent_email,
    		'security_code'  => $scode,
    		'end_time' => $end_time
    	);
    	
    	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode($client_account);
    	$mUserScode = ClsFactory::Create('Model.mUserScode');
    	if (!empty($last_user_scode)) {
    		unset($data['client_account']);
    		$is_success = $mUserScode->modifyUserScodeById($data, $client_account);
    	} else {
    		$is_success = $mUserScode->addUserScode($data);
    	}
    	
    	return $is_success;
    }
    
    /**
     * 通过业务手机号获取发送短信时所需的学校运营策略
     */
	private function getOperationStrategyByPhone($phone){
	    if(empty($phone)){
	        return false;
	    }
	    
        //将手机号转换成用户账号
        $Businessphone = ClsFactory::Create ( 'Model.mBusinessphone' );
        $phoneinfo = $Businessphone->getbusinessphonebyalias_id($phone);  
        $account = $phoneinfo[$phone]['uid'];
	    //获取当前用户所在学校的运营策略
	    $mUser = ClsFactory::Create('Model.mUser');
	    $userBaseInfo = $mUser->getUserByUid($account); 
	    $schoolinfo = array_shift($userBaseInfo[$account]['school_info']);
	    $schoolid = $schoolinfo['school_id'];
		$mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
		$schoolInfo = $mSchoolInfo->getSchoolInfoById($schoolid);
		$operationStrategy = intval($schoolInfo[$schoolid]['operation_strategy']);//获取该学校的运营策略
		
		return $operationStrategy;
	}
	
	 /**
     * 判断是否是邮箱
     * @param $account
     */
    protected function isEmail($email) {
        if(empty($email) || stripos($email, '@') === false) {
            return false;
        }
        return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $email) ? true : false;
    }
    
    /**
     * 判断是否是手机号
     * @param $phone
     */
    protected function isPhonenum($phone) {
        if(empty($phone) || strlen(strval($phone)) != 11) {
            return false;
        }
        
        return preg_match("/^1[0-9]{10}$/", $phone) ? true : false;
    }
    
    /**
     * 判断是否是账号
     * @param $uid
     */
    protected function isUid($uid) {
        if(empty($uid)) {
            return false;
        }
        
        return preg_match("/^[1-9](\d)+$/", $uid) ? true : false;
    }
    
    
    public function showAppeal() {
        $this->display('pwd_appeal');
    }
    
    public function addAppeal() {
        $client_name = $this->objInput->postStr("username");
        $client_account = $this->objInput->postStr("uid");
        $client_email = $this->objInput->postStr("email");
        $client_phone = $this->objInput->postStr("phone");
        $school_name = $this->objInput->postStr("school_name");
        $class_name = $this->objInput->postStr("class_name");
        $area_id = $this->objInput->postInt("area_id_div");
        $question_description = $this->objInput->postStr("problem_description");
        $err_msg = array();
        import("@.Common_wmw.WmwString");
        if(empty($client_name)) {
            $err_msg[] = "姓名不能为空";
        }else if(WmwString::mbstrlen($client_name) < 2 || WmwString::mbstrlen($client_name) > 20) {
            $err_msg[] = "姓名长度为2~20";
        }else if(!empty($client_account)) {
            if(WmwString::mbstrlen($client_account) < 5 || WmwString::mbstrlen($client_account) > 60) {
                $err_msg[] = "账号长度为5~60";
            }
        }else if(empty($client_phone)) {
            $err_msg[] = "手机号不能为空";
        }else if(!empty($client_email) && !preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/', $client_email)){
            $err_msg[] = "邮箱格式错误";
        }else if(WmwString::mbstrlen($school_name) < 4 || WmwString::mbstrlen($school_name) > 60){
            $err_msg[] = "学校名称长度为4~60";
        }else if(WmwString::mbstrlen($class_name) < 4 || WmwString::mbstrlen($class_name) > 20){
            $err_msg[] = "班级名称长度为4~20";
        }else if(WmwString::mbstrlen($question_description) < 1 || WmwString::mbstrlen($question_description) > 100){
            $err_msg[] = "问题说明长度为100以内";
        }else if(empty($question_description)){
            $err_msg[] = "问题说明不能为空";
        }
        
        if(!empty($err_msg)) {
            $this->showError(array_shift($err_msg), "/Uc/Findpwd/showAppeal");
            exit;
        }
        
        $dataarr = array(
            'client_name' => $client_name,
            'client_account' => $client_account,
            'client_phone' => $client_phone,
            'client_email' => $client_email,
            'area_id' => $area_id,
            'school_name' => $school_name,
            'class_name' => $class_name,
            'question_description' => $question_description,
            'add_time' => time(),
        );
        
        $mPasswordAppeal = ClsFactory::create("Model.mPasswordAppeal");
        $resault = $mPasswordAppeal->addPasswordAppeal($dataarr);
        if(!empty($resault)) {
        $address_arr = getAreaNameList($area_id);
        $address_str = $address_arr['province'] . $address_arr['city'] . $address_arr['county'];
$email_content = <<<EOF
<div style="width:400px;align:200px;">
姓名：{$client_name}<br>
账号：{$client_account}<br>
联系电话：{$client_phone}<br>
邮箱：{$client_email}<br>
省市区：{$address_str}<br>
学校名称：{$school_name}<br>
班级名称：{$class_name}<br>
问题描述：{$question_description}<br>
</div>
EOF
        	;
        	$emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
        	
        	//新设置邮件标题
    		$emailObj->setSubject("我们网账号申诉邮件");
		    $is_send = $emailObj->send(WMW_APPEAL_EMAIL, $email_content);
            $this->display("pwd_appeal_tips");
        }else{
            $this->showError("添加申诉信息失败", "/Uc/Findpwd/showAppeal");
        }
    }
}