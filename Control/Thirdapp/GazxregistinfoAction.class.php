<?php 
class GazxregistinfoAction extends SnsController {
	public $user;
    public $soap_opt = array ('connection_timeout' => 30);
    public function _initialize() {
        parent::_initialize(); 
	}

	/**
	 * 
	 */
	public function index() {

	    // step 1 判断是否为家长
		if ($this->user['client_type']!= CLIENT_TYPE_FAMILY){
			  $this->redirect("../Homepage/Homepage/index");
			exit;
		}
        
	    // step2 判断是否办理过业务
	    $m = ClsFactory::Create('Model.mGazxRegistInfo');
	    $uid = $this->getCookieAccount();
	    $info = $m->getRegistInfoByParentAccount(array($uid));
	    $handle = false;
	    if ($info == false) {
	        $handle = true;
	    }
	    unset($info);
	    // 显示
	    $this->assign('handle', $handle);
	    $this->assign('gazx_login_url', C('GAZX_LOGIN_URL'));
	    
	    $this->display('carefor_star');
	}
	
	public function viewflow() {
		
	    $this->display('carefor_star_flow');	    
	}
	
	public function viewagreement() {
		
	    $this->display('carefor_star_agreement');	    
	}	

	public function handle() {
	    
	    $info = array();
	    // 获取家长信息
	    
		$uid = $this->getCookieAccount();//获得登陆人账号
	    $mUser = ClsFactory::Create('Model.mUser');
        $userInfo = $mUser->getUserBaseByUid($uid);

        // 家长姓名 手机
        $info['parent_account'] = $uid;
        $info['parent_name'] = $userInfo[$uid]['client_name'];
        $info['parent_phone'] = $userInfo[$uid]['client_phone'];
        $info['parent_id'] = '';
        unset($userInfo);
        
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $childs = $mFamilyRelation->getFamilyRelationByFamilyUid($uid);
       
//        $child_uid = $childs[$uid]['client_account'];
        $account_info = array_shift($childs[$uid]);
        $child_uid = $account_info['client_account'];
        $childInfo = $mUser->getUserBaseByUid($child_uid);
        
        // 孩子姓名 手机
        $info['child_account'] = $child_uid;
        $info['child_name'] = $childInfo[$child_uid]['client_name'];
        $info['child_phone'] = '';
        $info['child_id'] = '';
        unset($childInfo);
        
        $this->assign('info', $info);    
            
	    $this->display('carefor_star_handle');	 	    
	}
	
	public function dohandle() {
	    $info = array();
        $info['parent_account'] = $this->objInput->postInt('parent_account');
        $info['parent_phone'] = $this->objInput->postInt('parent_phone');
        $info['parent_id'] = $this->objInput->postStr('parent_id');	    
        $info['child_account'] = $this->objInput->postInt('child_account');
        $info['child_phone'] = $this->objInput->postInt('child_phone');
        $info['child_id'] = $this->objInput->postStr('child_id');
        $info['add_date'] = time();
        
        //查看是否已经办理过此业务
        
	    $m = ClsFactory::Create('Model.mGazxRegistInfo');
	    $uid = $this->getCookieAccount();
	    $gazxInfo = $m->getRegistInfoByParentAccount(array($uid));
	    $handle = false;
	    if (is_array($gazxInfo) && isset($gazxInfo[0]['regist_id'])) {
	        $handle = true;
	    } else {
    	    $success = $this->sendTogazx($info);
    	    if ($success) {
        	    $m = ClsFactory::Create('Model.mGazxRegistInfo');
        	    if ($m->addRegistInfo($info) != false) {
                    $handle = true;
        	    }
    	    } else {
    	        $handle = false;
    	    }
	    }
	    
	    unset($gazxInfo);       
	    if ($handle) {
	        $login_url = C('GAZX_LOGIN_URL');
	        $this->assign('parent_phone', $info['parent_phone']);
	        $this->assign('gazx_login_url', $login_url);
	        
	        $this->display('carefor_star_success');	        
	    } else {
	        $notice = '办理失败,请联系客服人员!';
	        $this->assign('notice', $notice);
	        
	        $this->display('carefor_star_notice');	        
	    }
	}	
	
	//新加过度页面
	public function childwz(){
		// step 1 判断是否为家长
		if ($this->user['client_type']!= CLIENT_TYPE_FAMILY){
			  $this->redirect("../Homepage/Homepage/index");
			exit;
		}
       
	    // 显示
		$this->display('child_wz');
	}
	
	//关爱之星-办理业务流程文档下载
	public function downBusinessProcessFile(){
		$filename = WEB_ROOT_DIR.'/Public/downfile/carefor_star_flow.doc';
    	if (!file_exists($filename)) {
    		$filename = IMG_SERVER.'/Public/downfile/carefor_star_flow.doc';
        }
       $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
       $down_file->downfile($filename, '用户办理业务流程');
	}
	
	//关爱之星-用户服务协议文档下载
	public function downServiceAgreementFile(){
		$filename = WEB_ROOT_DIR.'/Public/downfile/carefor_star_agreement.doc';
    	if (!file_exists($filename)) {
    		$filename = IMG_SERVER.'/Public/downfile/carefor_star_agreement.doc';
        }
        
       $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
       $down_file->downfile($filename, '用户服务协议');
	}
	
	private function sendTogazx($info = array()) {
	    if(empty($info) || !isset($info['parent_phone']) || !isset($info['child_phone'])) {
	        return false;
	    }
	    
	    import("@.Common_wmw.DesCrypt");
        import("@.Common_wmw.WmwArray");
        import("@.Common_wmw.Vendor.Nusoap.nusoap");
        
        $wsdl = C('GAZX_WSDL');
        $key = C('GAZX_PRIVATE_KEY');
        $username = C('GAZX_USERNAME');
        $password = C('GAZX_PASSWORD');
        $guardian = $info['parent_phone'];
        $ward     = $info['child_phone'];
        $data = array(
            'TABLE'   => 'T_WMW',
            'DESC'	=> '对外服务对接表',
            'USERNAME'=> $username,
            'PASSWORD'=> $password,
            'GUARDIAN'=> $guardian,
            'WARD'	=> $ward
        );        
        
	    $xml = WmwArray::toXml($data, 'WMW');
        $encode = DesCrypt::encrypt(mb_convert_encoding ($xml, "GBK","UTF-8"), $key);                                                
        
        $args= array($encode);
        $success = false;
        try {
            $client = new nusoap_client($wsdl); 
            $result = $client->call('regist', array($args));
            
            if (!$err = $client->getError()) {
               if ($result == 0) {
                  $success = true;
               }
            } else {
               $success = false;
            }
        } catch (Exception $e) {
            //todo log to erro info 
            $success = false;
        }
        return $success;
	}
	
}
