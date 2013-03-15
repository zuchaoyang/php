<?php
/**
 * 腾讯操作类V2
 */

class QzoneTClientApi extends ApiController {

	/**
	 * 我们网帐号
	 */    
    public $client_account;
    
	/**
	 * OauthType 第三方登录类型 qzone  , sina
	 */    
    public $social_type = 'qzone';    
    
	/**
	 * Oauth Server Api Object
	 */    
    public $qzoneOauth;

	/**
	 * 构造函数
	 * 
	 * @access public
	 * @param mixed $client_account 腾讯开放平台应用APP KEY
	 * @return void
	 */    
    
    public function __construct($client_account) {
        $this->client_account = $client_account;
        parent::__construct();
        
        
    }
    
	public function index() {

	}
	
    public function _initialize(){
        
		parent::_initialize();        
		
        $m = ClsFactory::Create('Model.mOauthBind');
        
        $params = array();
        $params[] = "client_account = '$this->client_account'";
        $params[] = "social_type = '$this->social_type'";          
        $data = $m->getOauthBindByClientAccountAndType($params);	
    
        if (!empty($data)) {
            import('@.Common_wmw.Vendor.OAuth2.Client.QzoneTOAuth2', null, '.php');
            $oauth_bind_info = reset($data);
            
            $supported_oauth2_type = C('SUPPORTED_OAUTH2_TYPE');
            $client_id = $supported_oauth2_type[$this->social_type]['client_id'];
            $client_secret = $supported_oauth2_type[$this->social_type]['client_secret'];
            $open_id =  $oauth_bind_info['social_account'];
            $access_token = $oauth_bind_info['access_token'];

            $this->qzoneOauth = new QzoneTOAuth2($client_id, $client_secret, $open_id, $access_token);           
        }        
        
    }
    
	/**
	 * 获取用户基本信息
	 *
	 * 对应API：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91get_user_info}
	 * @access public
	 * @return array
	 */
	function get_user_info() {
	    
	    if (empty($this->qzoneOauth)) {
	        return array();
	    }
	    $params = array();
		return $this->qzoneOauth->getAPI('user/get_user_info', $params);
	}    
}