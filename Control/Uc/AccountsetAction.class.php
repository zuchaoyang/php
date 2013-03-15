<?php
class AccountsetAction extends UcController {
    public function _initialize() {
 		parent::_initialize();
    }

    //账号设置首页
    public function index(){
		$email_binding_info = $this->getEmailBinding();

		$this->assign('email_binding_info', $email_binding_info);
		
		$client_account = $this->getCookieAccount();
        //qzone 设置
        $uc_domain = C('UC_DOMAIN');
        $redirect_url = 'http://'.$uc_domain . '/Uc/Accountset';
        $qzone_user_info = $this->uc_client->get_oauth_bind_qq($client_account, $redirect_url);
                
        $this->assign('qzone_user_info', $qzone_user_info);		
		
        $this->display("set_account_index");
    }

    /**
     * 设置email 显示模板
     */
    public function setEmail() {
    	//判断是否已经设置过了
    	$email_binding_info  = $this->getEmailBinding();
    	if (!empty($email_binding_info['email'])) {
    		$this->showSuccess('邮箱已设置，请重试', '/Uc/Accountset');
    	}

   		$this->display("set_email");
    }

    public function setEmailFromAjax(){
		$email = $this->objInput->postStr('email');
		$scode = $this->objInput->postInt('scode');

		//检测邮箱是否可用
		$data = array();
		$check_email = $this->checkSetEmail($email);
    	if ($check_email == 1) {
    		$data['email_span'] = '邮箱格式错误，请重试';
    	} else if ($check_email == 2) {
    		$data['email_span'] =  '邮箱已经被使用，请重试';
    	} else if($check_email == 3) {
    		$data['email_span'] = '输入的邮箱和接收验证码的邮箱不一致，请重试';
    	}

    	//检测 验证码是否过期 是否正确
    	$last_user_scode = $this->getLastUserScode();
    	if (time() > $last_user_scode['end_time']) {
    		$data['scode_span'] = '验证码已过期，请重新发送验证码';
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$data['scode_span'] = '输入的验证码错误';
    	}

    	empty($data) ? $this->ajaxReturn(null, '正确。', 1, 'JSON') : $this->ajaxReturn($data, '错误', -1, 'JSON');
    }

    /**
     * 设置邮箱设置处理 （验证用户数据，完成邮箱设置）
     */
    public function setEmaildo() {
        //判断是否已经设置过了
    	$email_binding_info  = $this->getEmailBinding();
    	if (!empty($email_binding_info['email'])) {
    		$this->showSuccess('邮箱已设置，请重试', '/Uc/Accountset');
    	}

    	//检测用户输入数据  验证数据 验证码是否正确 新邮箱格式是否正确
    	$scode = $this->objInput->postInt("scode");
    	$email = $this->objInput->postStr("email");
    	$client_account = $this->getCookieAccount();

        //检查 要绑定的邮箱 是否正确
    	$check_email = $this->checkSetEmail($email);  // 返回值说明 ：0 可以使用 1 邮箱格式错误 2邮箱已被使用 3输入的和接收验证码的邮箱不一致

    	if ($check_email == 1) {
    		$this->showError('邮箱格式错误，请重试。', '/Uc/Accountset/setEmail');
    	} else if ($check_email == 2) {
    		$this->showError('邮箱已经被使用，请重试', '/Uc/Accountset/setEmail');
    	} else if($check_email == 3) {
    		$this->showError('输入邮箱和接收验证码的邮箱不一致，请重试', '/Uc/Accountset/setEmail');
    	}

    	//检测 验证码是否过期 是否正确
    	$last_user_scode = $this->getLastUserScode();
    	if (time() > $last_user_scode['end_time']) {
    		$this->showError('验证码已过期，请重新发送验证码', '/Uc/Accountset/setEmail');
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$this->showError('输入的验证码错误', '/Uc/Accountset/setEmail');
    	}

    	//绑定邮箱 更新两个表
    	$is_send = $this->upEmailbinding($email);
    	//更新是验证码表 设为过期
    	$mUserScode = ClsFactory::Create('Model.mUserScode');
    	$data = array('end_time'=>time());
    	$is_success = $mUserScode->modifyUserScodeById($data, $client_account);

    	if (empty($is_send)) {
    		$this->showError("邮箱设置失败，请重试", '/Uc/Accountset/setemail');
    	}

    	//成功跳转页面
    	$this->showSuccess('邮箱设置成功，您可以使用邮箱登录或找回密码', '/Uc/Accountset');
    }

    //修改邮箱账号第一步（显示模板）
    public function modifyEmailOne() {
    	$email_binding_info = $this->getEmailBinding();
    	if (empty($email_binding_info['email'])) {
    		//没有设置邮箱提示并跳转
    		$msg = "没有设置邮箱";
    		$url = '/Uc/Accountset/setEmail';
    		$this->showSuccess($msg, $url);
    	}

    	$this->assign('email_binding_info', $email_binding_info);
        $this->display("modify_email_step_1");
    }

    /**
     * Ajax 验证旧邮箱是否正确
     */
    public function modifyEmailOneAjax() {
    	$oldemail = $this->objInput->postStr('oldemail');
    	// 非空验证
    	if(empty($oldemail)) {
    		$this->ajaxReturn(null, '邮箱不能为空', -1, 'json');
    	}
    	//验证旧邮箱是否正确
    	$email_binding_info = $this->getEmailBinding();
    	if ($oldemail != $email_binding_info['email']) {
    		$this->ajaxReturn(null, '邮箱和已设置邮箱不一致', -1, 'json');
    	}

    	$this->ajaxReturn(null, '正确', 1, 'json');
    }
    /**
     * 修改邮箱第二部
     * 验证旧邮箱 正确就跳转到设置新邮箱页面
     */
    public function modifyEmailTwo() {
    	$oldemail = $this->objInput->postStr('oldemail');
    	$email_binding_info = $this->getEmailBinding();
    	if (empty($email_binding_info['email'])) {
    		//没有设置邮箱提示并跳转
    		$msg = "没有设置邮箱";
    		$url = '/Uc/Accountset/setEmail';
    		$this->showSuccess($msg, $url);
    	}

    	//验证旧邮箱是否正确
    	if($oldemail != $email_binding_info['email']) {
    		$this->showSuccess('旧邮箱错误，请重试', '/Uc/Accountset/modifyEmailOne');
    	}

    	$this->assign('oldemail', $oldemail);
        $this->display("modify_email_step_2");
    }

    /**
     * Ajax 修改邮箱第二步表单数据 是否正确
     */
    public function modifyFromTwoAjax() {
    	$oldemail = $this->objInput->postStr('oldemail');
    	$newemail = $this->objInput->postStr('newemail');
    	$scode = $this->objInput->postStr('scode');
    	//$dataarray('oldeamil_span'=>'val','newemail_span'=>'val')只有在错误的时候 $data才不为空
    	$data = array();
    	//验证旧邮箱是否正确
    	$email_binding_info = $this->getEmailBinding();
    	if ($oldemail != $email_binding_info['email']) {
    		$data['oldemail_span'] =  '输入邮箱和已设置邮箱不一致, 请返回第一步重试';
    	}
    	//验证新邮箱
    	$check_newemail = $this->checkSetEmail($newemail);
    	if ($check_newemail == 1) {
    		$data['newemail_span'] = '新邮箱格式错误，请重试。';
    	} else if ($check_newemail == 2) {
    		$data['newemail_span'] = '新邮箱已经被使用，请重试';
    	} else if ($check_newemail == 3) {
    		$data['newemail_span'] = '输入的新邮箱和已设置邮箱不一致，请重试';
    	}
    	//验证 邮箱验证码
    	$last_user_scode = $this->getLastUserScode();
    	if (time() > $last_user_scode['end_time']) {
    		$data['scode_span'] = '验证码已过期，请重新发送验证码';
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$data['scode_span'] = '输入的验证码错误';
    	}

    	empty($data) ? $this->ajaxReturn(null, '正确', 1, 'json') : $this->ajaxReturn($data, '错误', -1, 'json');
    }

    //修改邮箱账号处理页面
    public function modifyEmailEnd(){
    	$scode = $this->objInput->postInt("scode");
    	$oldemail = $this->objInput->postStr("oldemail");
    	$newemail = $this->objInput->postStr("newemail");
    	$client_account = $this->getCookieAccount();

    	$email_binding_info = $this->getEmailBinding();
    	if (empty($email_binding_info['email'])) {
    		//todo 没有设置邮箱提示并跳转
    		$this->showError('没有设置邮箱', '/Uc/Accountset');
    	}

    	// 检测新邮箱是否可用  新邮箱、旧邮箱是否正确（邮箱是否可用可以屏蔽新旧邮箱一致的问题）
    	$check_oldemail = $this->checkOldemail($oldemail);
    	$check_newemail = $this->checkSetEmail($newemail);
    	if (!$check_oldemail) {
    		$this->showError('旧邮箱格式错误或与设置邮箱不一致', '/Uc/Accountset/modifyEmailOne');
    	}

    	if ($check_newemail == 1) {
    		$this->showError('新邮箱格式错误，请重试', '/Uc/Accountset/modifyEmailOne');
    	} else if ($check_newemail == 2) {
    		$this->showError('新邮箱已经被使用，请重试', '/Uc/Accountset/modifyEmailOne');
    	} else if($check_newemail == 3) {
    		$this->showError('新邮箱和接收验证码的邮箱不一致，请重试', '/Uc/Accountset/modifyEmailOne');
    	}

        //检测 验证码是否过期 是否正确
    	$last_user_scode = $this->getLastUserScode();
    	if (time() > $last_user_scode['end_time']) {
    		$this->showError('验证码已过期，请重新发送验证码', '/Uc/Accountset/modifyEmailOne');
    	} else if ($last_user_scode['security_code'] != $scode) {
	    	$this->showError('验证码错误', '/Uc/Accountset/modifyEmailOne');
    	}

        //更新两个表
    	$is_send = $this->upEmailbinding($newemail);

    	//不管更新失败、成功都把验证码设为过期
    	$mUserScode = ClsFactory::Create('Model.mUserScode');
    	$data = array('end_time'=>time());
    	$is_success = $mUserScode->modifyUserScodeById($data, $client_account);

    	//更新数据表是否成功
    	if(empty($is_send)) {
    		$this->showError("邮箱修改失败，请重试", '/Uc/Accountset/modifyEmailOne');
    	}

    	//成功跳转页面
    	$this->showSuccess("邮箱修改成功", '/Uc/Accountset');
    }


   /**
    * 修改邮箱发送验证码 ajax 方式
    */
   public function modifyEmailSendScodeAjax() {
   		$oldemail = $this->objInput->postStr('oldemail');
   		$newemail = $this->objInput->postStr('newemail');

   		// 验证旧邮箱是否正确
   		if (!$this->checkOldemail($oldemail)) {
   			$this->ajaxReturn(null, '旧邮箱错误，请返回第一步重新验证', -1, 'JSON');
   		}
   		
       	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode();
    	//计算是否超过，最短间隔发送时间 超过返回true
    	$is_out = (time() - ($last_user_scode['end_time']- SETEMAIL_TIME_OUT)) < SORT_TIME_OUT;
    	if (!empty($last_user_scode) && $is_out) {
    		$info = '您发送的太频繁，请' .SORT_TIME_OUT. '秒后重新发送';
    		$this->ajaxReturn(null, $info, -1, 'JSON');
    	}
   		
   		// 验证新邮箱
   		$newemail_status = $this->checkSetEmail($newemail);
   		if ($newemail_status == 1) {
   			$this->ajaxReturn(null, '新邮箱格式错误', -1, 'JSON');
   		} else if($newemail_status == 2) {
   			$this->ajaxReturn(null, '新邮箱已经被使用，请重试', -1, 'JSON');
   		}
		// 修改邮箱 发送验证码的时候，不能验证邮箱是否和当前账号最后一次发送验证码的邮箱一致
		//   		else if($newemail_status == 3) {
		//   			$this->ajaxReturn(null, '新邮箱和接收验证码的邮箱不一致，请重试', -1, 'JSON');
		//   		}

    	//更新是数据库
    	$user_account = $this->getCookieAccount();
    	$socde = mt_rand(100000,999999);
    	$is_success = $this->upUserScode($newemail, $socde);

   		//判断更新数据库是否成功 成功后发送邮件
   		if(empty($is_success)) {
   			$this->ajaxReturn(null, '邮件发送失败，请重试', -1, 'JSON');
   		}
    	$email_content = "感谢您使用我们网服务。<br/>您正在进行邮箱验证，请在验证码输入框中输入此次验证码：$socde 按提示完成邮箱设置。"
    					."<br/>如非本人操作，请忽略此邮件，由此给您带来的不便请谅解<br/> 感谢您对我们网的支持。";
    	$emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
    	//重新设置邮件标题
    	$emailObj->setSubject("我们网设置邮箱验证码邮件");
		$is_send = $emailObj->send($newemail, $email_content);

    	$this->ajaxReturn(null, "验证码邮件已发送，如未收到，请查找垃圾邮箱或稍等重发", 1, 'JSON');
   }

	/**
     * 设置邮箱验证并发送邮件 Ajax 方式
     */
	public function setEmailSendScodeAjax() {
   		$email = $this->objInput->postStr('email');

       	//验证邮箱是否 正确且没有被使用
    	$email_status = $this->checkSetEmail($email);
       	if ($email_status == 1) {
    		$this->ajaxReturn(null, '邮箱格式错误', -1, 'JSON');
	    } else if ($email_status == 2) {
	    	$this->ajaxReturn(null, '邮箱已被绑定，请换一个邮箱重试', -1, 'JSON');
	    }

    	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode();
    	//计算是否超过，最短间隔发送时间 超过返回true
    	$is_out = (time() - ($last_user_scode['end_time']- SETEMAIL_TIME_OUT)) < SORT_TIME_OUT;

    	if (!empty($last_user_scode) && $is_out) {
    		$info = '您发送的太频繁，请' . SORT_TIME_OUT . '秒后重新发送';
    		$this->ajaxReturn(null, $info, -1, 'JSON');
    	}

    	//更新是数据库
    	$user_account = $this->getCookieAccount();
    	$socde = mt_rand(100000,999999);
    	$is_success = $this->upUserScode($email, $socde);

   		//判断更新数据库是否成功 成功后发送邮件
   		if (empty($is_success)) {
   			$this->ajaxReturn(null, "邮件发送失败，请重试", -1, 'JSON');
   		}
    	$email_content = "感谢您使用我们网服务。<br/>您正在进行邮箱验证，请在验证码输入框中输入此次验证码：$socde 按提示完成邮箱设置。"
    		.			 "<br/>如非本人操作，请忽略此邮件，由此给您带来的不便请谅解<br/> 感谢您对我们网的支持。";
    	$emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
    	//重新设置邮件标题
    	$emailObj->setSubject("我们网设置邮箱验证码邮件");
		$is_send = $emailObj->send($email, $email_content);

    	$this->ajaxReturn(null, "验证码邮件已发送，如未收到，请查找垃圾邮箱或稍等重发", 1, 'JSON');
	}

    /**
     * 绑定邮箱更新两个表 wmw_client_email  wmw_email_binding
     * 有就修改没有就添加 兼容修改邮箱
     * @return 返回是否更新成功 之后两个表都成功才返回 true
     */
    private function upEmailbinding($client_email) {
    	$is_client = $is_binding = false;
    	$client_account = $this->getCookieAccount();
    	$mUser = ClsFactory::Create('Model.mUser');

    	$data = array(
    		'client_email'=>$client_email,
    		'upd_time'=>time(),
    	);

    	$is_client = $mUser->modifyUserClientInfo($data, $client_account);
    	if ($is_client) {
    		$user_binding_info = $this->getEmailBinding();  // 获取用户已经设置的邮箱
    		$mEmailBinding = ClsFactory::Create('Model.mEmailBinding');

    		//更新数据
	    	$data = array(
	    		'email' => $client_email,
	    		'time33_key' => time33($client_email)
	    	);

	    	if (empty($user_binding_info['email'])) {
	    		$data['add_time'] = time();
	    		$data['client_account'] = $client_account;
	    		$is_binding = $mEmailBinding->addEmailBinding($data);
	    	} else {
	    		$is_binding = $mEmailBinding->modifyEmailBinding($data, $user_binding_info['bind_id']);
	    	}

	    	//更新邮箱绑定表失败 回滚用户详细信息表 保持数据的一致
	    	if (!$is_binding) {
	    		$data = array(
	    			'client_email'=>$user_binding_info['email'],
	    			'upd_time'=>time(),
	    		);
	    		$mUser->modifyUserClientInfo($data, $client_account);
	    	}
    	}

    	return ($is_client && $is_binding) ? true : false;
    }

    /**
     * 把秒数换算成 x 年 x 个月 x星期 x 天 x 小时  这样的写法
     * @param $time
     */
	private function formatDate($time){
	    $arr=array(
	        '31536000'=>'年',
	        '2592000'=>'个月',
	        '604800'=>'星期',
	        '86400'=>'天',
	        '3600'=>'小时',
	        '60'=>'分钟',
	        '1'=>'秒'
	    );

	    foreach ($arr as $k=>$v) {
	        $k = intval($k);
	        $c = floor($time/$k);
	        if ($c != 0) {
	            $str .= $c.$v.'&nbsp;';
	            $time = $time%$k;
	        }
	    }

    	return $str;
	}

    /**
     * 获取最后一次发送的记录(一个用户只能有一条记录)
     *
     * @return 如果没有返回 false
     */
    private function getLastUserScode() {
    	$user_code_last = array();
    	$user_account = $this->getCookieAccount();

    	$mUserScode = ClsFactory::Create('Model.mUserScode');
		$user_code_last = $mUserScode->getUserScodeById($user_account);

		return empty($user_code_last) ? false : reset($user_code_last);
    }

    /**
     * 更新 验证码发送表
     * @param $clent_email 收邮件的email地址  也可能存手机号码
     * @param $scode 验证码
     * @return 是否更新成功
     */
    private function upUserScode($clent_email, $scode) {
    	$is_success = false; //是否成功
    	$user_account = $this->getCookieAccount();
    	$end_time = time() + SETEMAIL_TIME_OUT;


    	$data = array(
    		'client_account' => $user_account,
    		'security_code'  => $scode,
    		'client_email'   => $clent_email,
    		'end_time' => $end_time
    	);
    	//最后一次发送的记录
    	$last_user_scode = $this->getLastUserScode();

    	$mUserScode = ClsFactory::Create('Model.mUserScode');
    	if (!empty($last_user_scode)) {
    		unset($data['client_account']);
    		$is_success = $mUserScode->modifyUserScodeById($data, $user_account);
    	} else {
    		$is_success = $mUserScode->addUserScode($data);
    	}

    	return $is_success;
    }

    /**
     * 验证输入的旧邮箱是否正确
     * @param $oldemail
     * @return true,false
     */
    private  function checkOldemail($oldemail) {
    	$email_binding_info = $this->getEmailBinding();  // 获取用户已经设置的邮箱
    	// 正则验证邮箱格式是否正确
    	if (empty($oldemail) || empty($email_binding_info['email']) || !$this->isEmail($oldemail)) {
    		return false;
    	}

    	// 旧邮箱和设置的邮箱是否一致
	    return ($email_binding_info['email'] != $oldemail) ? false : true;
	}

    /**
     * 验证输入的新邮箱是否可用，格式是否正确
     * @param $set_email 要设置的email
     * @return 返回明确的错误信息 0 正确 ，1 Emial格式错误 ，2 Emial已经被使用  3 设置的邮箱和接受验证码的邮箱不一致
     */
    private function checkSetEmail($set_email) {
    	// 正则验证邮箱格式是否正确
    	if (empty($set_email) || !$this->isEmail($set_email)) {
    		return 1;
    	}
    	
    	// 验证新邮箱是否已被使用
    	$mEmailBinding = ClsFactory::Create('Model.mEmailBinding');
    	$email_list = $mEmailBinding->getEmailBindingByEmail($set_email);
    	if (!empty($email_list)) {
    		return 2;
    	} 
    	
    	//验证要设置的邮箱和接受验证码的邮箱是否是一个
    	$last_user_scode = $this->getLastUserScode();
    	if (!empty($last_user_scode) && $last_user_scode['client_email'] != $set_email) {
    		return 3;
    	}
    	
    	return 0;
	}

	/**
	 * 生成验证码方法
	 **/
	public function verify(){
		import('ORG.Util.Image');
		Image::buildImageVerify();
	}

	/**
	 * 根据当前用户账号获取对应邮箱 手机号
	 * 并作相应隐藏处理
	 */
    private function getEmailBinding(){
    	$client_account = $this->getCookieAccount();

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
     * 验证邮箱格式是否正确
     * @return boolean
     */
    private function isEmail($email_str) {
    	if (empty($email_str)) {
    		return false;
    	}

    	$preg_email = "/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/";

    	return preg_match($preg_email, $email_str) ? true : false;
    }


    public function changePwdShow() {
        if($this->user['client_type'] == 1) {
            $str = '老师';
        }else if($this->user['client_type'] == 2) {
            $str = '家长';
        }else{
            $str = '同学';
        }
        $this->assign('username', $this->user['client_name'].$str);
        $this->display("modify_pwd");
    }

    /**
     * 用户密码修改
     */
    public function changePwd() {
        $old_client_pwd = $this->objInput->postStr('old_client_pwd');
        $client_pwd = $this->objInput->postStr('new_client_pwd');
        $re_client_pwd = $this->objInput->postStr('re_client_pwd');
        $err_msg = array();
        import("@.Common_wmw.WmwString");
        if(empty($old_client_pwd) || empty($client_pwd) || empty($re_client_pwd)) {
            $err_msg[] = "表单元素不能为空";
        }else if(WmwString::mbstrlen($client_pwd) < 6 || WmwString::mbstrlen($client_pwd) > 20) {
            $err_msg[] = "密码长度为6~20";
        }else if($client_pwd != $re_client_pwd) {
            $err_msg[] = "两次密码输入的不一致";
        }else if($this->user['client_password'] != md5($old_client_pwd)) {
            $err_msg[] = "原密码错误";
        }else if(md5($client_pwd) == $this->user['client_password']){
            $err_msg[] = "新密码与旧密码一致，不用修改";
       }
        if(!empty($err_msg)) {
            $this->showError(array_shift($err_msg),"/Uc/Accountset/changePwdShow");
            exit;
        }

        $dataarr = array(
            'client_password'=>md5($client_pwd),
        	'upd_time'=>time(),
        );
        $mUser = ClsFactory::Create("Model.mUser");
        $resault = $mUser->modifyUserClientAccount($dataarr, $this->user['client_account']);
        if(!empty($resault)) {
            $this->showSuccess("密码修改成功","/Uc/Index/index");
        }else{
            $this->showError("密码修改失败","/Uc/Accountset/changePwdShow");
        }
    }
}