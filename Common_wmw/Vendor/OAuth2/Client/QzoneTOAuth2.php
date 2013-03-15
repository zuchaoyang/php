<?php
/**
 * 腾讯OAuth 认证类(OAuth2)
 *
 * 授权机制说明请大家参考腾讯开放平台文档：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E7%BD%91%E7%AB%99%E6%8E%A5%E5%85%A5}
 *
 * @package Common_wmw/Vendor/OAuth2
 * @author lnczx
 * @version 1.0
 */
class QzoneTOAuth2 {
	/**
	 * @ignore
	 */
	public $client_id;
	/**
	 * @ignore
	 */
	public $client_secret;
	/**
	 * @ignore
	 */
	public $access_token;
	/**
	 * @ignore
	 */
	public $refresh_token;
	
	/**
	 * @ignore  针对qq登录
	 */
	public $open_id;	
	/**
	 * Contains the last HTTP status code returned. 
	 *
	 * @ignore
	 */
	public $http_code;
	/**
	 * Contains the last API call.
	 *
	 * @ignore
	 */
	public $url;
	/**
	 * Set up the API root URL.
	 *
	 * @ignore
	 */
	public $host = "https://graph.qq.com/";
	/**
	 * Set timeout default.
	 *
	 * @ignore
	 */
	public $timeout = 30;
	/**
	 * Set connect timeout.
	 *
	 * @ignore
	 */
	public $connecttimeout = 30;
	/**
	 * Verify SSL Cert.
	 *
	 * @ignore
	 */
	public $ssl_verifypeer = FALSE;
	/**
	 * Respons format.
	 *
	 * @ignore
	 */
	public $format = 'json';
	/**
	 * Decode returned json data.
	 *
	 * @ignore
	 */
	public $decode_json = TRUE;
	/**
	 * Contains the last HTTP headers returned.
	 *
	 * @ignore
	 */
	public $http_info;
	/**
	 * Set the useragnet.
	 *
	 * @ignore
	 */
	public $useragent = 'QQ T OAuth2 v0.1';

	/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug = FALSE;

	/**
	 * boundary of multipart
	 * @ignore
	 */
	public static $boundary = '';
    
	/**
	 * available_api_list
     * 'get_user_info',    //获取用户在QQ空间的个人资料
     * 'add_topic',        //发表一条说说到QQ空间
     * 'add_one_blog',     //发表一篇日志到QQ空间
     * 'add_album',        //创建一个QQ空间相册
     * 'upload_pic',       //上传一张照片到QQ空间相册
     * 'list_album',       //获取用户QQ空间相册列表
     * 'add_share',        //同步分享到QQ空间、朋友网、腾讯微博
     * 'check_page_fans',  //验证是否认证空间粉丝
     * 'add_t',        //发表一条微博信息到腾讯微博
     * 'add_pic_t',        //上传图片并发表消息到腾讯微博
     * 'del_t',        //删除一条微博信息
     * 'get_repost_list',  //获取一条微博的转播或评论信息列表
     * 'get_info',     //获取登录用户自己的详细信息
     * 'get_other_info',   //获取其他用户的详细信息
     * 'get_fanslist',     //获取登录用户的听众列表
     * 'get_idollist',     //获取登录用户的收听列表
     * 'add_idol',     //收听腾讯微博上的用户
     * 'del_idol',     //取消收听腾讯微博上的用户

     'get_tenpay_addr',  //获取用户在财付通的收货地址
	 */
    private $api = array(
     	'get_user_info',    //获取用户在QQ空间的个人资料
    );	
    
	/**
	 * Set API URLS
	 */
	/**
	 * @ignore
	 */
	function accessTokenURL()  { return 'https://graph.qq.com/oauth2.0/token'; }
	/**
	 * @ignore
	 */
	function authorizeURL()    { return 'https://graph.qq.com/oauth2.0/authorize'; }

	/**
	 * @ignore
	 */
	function openidURL()    { return 'https://graph.qq.com/oauth2.0/me'; }	
	
	/**
	 * construct QQOAuth object
	 */
	function __construct($client_id, $client_secret, $open_id = NULL, $access_token = NULL, $refresh_token = NULL) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->open_id = $open_id;
		$this->access_token = $access_token;
		$this->refresh_token = $refresh_token;
	}
    
	/**
	 * 
	 * 统一登录接口
	 * @param string $callback  成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
	 * @param unknown_type $state client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
	 */
	
	function login($callback, $state = NULL) {
	    $url = $this->getAuthorizeURL($callback, $state);
	    header('Location:'.$url);
	}
	
	
	/**
	 * 
	 * 统一回调接口
	 * @param string $callback 成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
	 * @param string $state  client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
	 */
	
	function callback($callback, $state = NULL) {
	    $code = $_GET["code"];
	    $state = $_GET["state"];
	    
	    if (empty($code) || empty($state)) {
	        return array();
	    }
	    
	    $access_token = $this->getAccessToken($callback, $state);
	    if ($access_token == false ) return array();
	    
	    $openid = $this->getOpenid($access_token);
	    if ($openid == false)  return array();
	    
	    $result = array('access_token' => $access_token,
	                    'openid'	   => $openid
	                    );
	    return $result;
	}
	
	/**
	 * access_token接口
	 *
	 * 对应API：{@link http://open.weibo.com/wiki/OAuth2/access_token OAuth2/access_token}
	 *
	 */
	function getAccessToken($callback, $state = NULL) {

	    $url = $this->getAccessTokenURL($callback, $state);
	    $content = $this->get($url);

	    if ($content == false) return false;
	    
        $temp = explode('&',$content);
        $temp2 = explode('=',$temp[0]);
        $access_token = $temp2[1];
		return $access_token;
	}
	
	/**
	 * openid接口
	 *
	 * 对应API：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E4%BD%BF%E7%94%A8Authorization_Code%E8%8E%B7%E5%8F%96Access_Token}
	 *
	 */
	function getOpenid($access_token = NULL) {
	    $url = $this->getOpenidURL($access_token);
	    $content = $this->get($url);
	    if ($content == false) return false;
	    
        preg_match('/callback\(\s+(.*?)\s+\)/i', $content,$temp);
        $result = json_decode($temp[1],true);	    
        $openid = $result['openid'];
		return $openid;
	}

	/**
	 * authorize接口URL
	 *
	 * 对应API：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E4%BD%BF%E7%94%A8Authorization_Code%E8%8E%B7%E5%8F%96Access_Token}
	 *
	 * @param string $url 成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
	 * @param string $response_type 授权类型，此值固定为“code”。
	 * @param string $state client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
	 * @param string $display 仅PC网站接入时使用。
     *  - 用于展示的样式。不传则默认展示为PC下的样式。
     *  - 如果传入“mobile”，则展示为mobile端下的样式。
	 * @return String
	 */
	private function getAuthorizeURL( $url, $state = NULL, $response_type = 'code', $display = NULL ) {
		$params = array();
		$params['response_type'] = $response_type;
		$params['client_id'] = $this->client_id;
		$params['redirect_uri'] = $url;
	    foreach($this->api as $v){
            $scope[] = $v;
        }		
		$params['scope'] = join(",", $scope);
		$params['state'] = $state;
		$params['display'] = $display;
		
		return $this->authorizeURL() . "?" . http_build_query($params);
	}
	
	/**
	 * openid接口URL
	 *
	 * 对应API：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91Qzone_OAuth2.0%E7%AE%80%E4%BB%8B#Step2.EF.BC.9A.E6.A0.B9.E6.8D.AEaccess_token.E8.8E.B7.E5.BE.97.E5.AF.B9.E5.BA.94.E7.94.A8.E6.88.B7.E8.BA.AB.E4.BB.BD.E7.9A.84openid}
	 *
	 * @param string $access_token 成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
	 * @return String
	 */
	private function getOpenidURL( $access_token = NULL ) {
		$params = array();
		$params['access_token'] = $access_token;		
		return $this->openidURL() . "?" . http_build_query($params);
	}	
	
	
	/**
	 * access_tokenURL
	 *
	 * 对应API：{@link http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E4%BD%BF%E7%94%A8Authorization_Code%E8%8E%B7%E5%8F%96Access_Token}
	 *
	 * @param string $url 成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
	 * @param string $response_type 授权类型，此值固定为“code”。
	 * @param string $state client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
	 * @return String
	 */
	private function getAccessTokenURL( $url, $state = NULL, $grant_type = 'authorization_code') {
		$params = array();
		$params = array(
         	"grant_type"    =>    $grant_type,
         	"client_id"     =>    $this->client_id,
         	"client_secret" =>    $this->client_secret,
         	"code"          =>    $_GET["code"],
         	"state"         =>    $_GET["state"],
         	"redirect_uri"  =>    $url
        );
		
		return $this->accessTokenURL() . "?" . http_build_query($params);
	}		
	
    public function get($url) {
        $curl = curl_init();
        if (stripos($url,"https://") !== FALSE) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if( (int)$status['http_code'] == 200 ) {
            return $content;
        } else {
            return false;
        }
    }
    
	/**
	 * 
	 * 获取APi ,估计加入必需参数
	 * @param String $url            Api url路径
	 * @param array() $params        调用APi所需参数
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public  function getAPI( $url, $params) {
	    
	    if (empty($url)) {
	        return false;
	    }
	    
		if (empty($params)) {
		    $params = array();
		}
		
		$params['access_token'] = $this->access_token;
		$params['oauth_consumer_key'] = $this->client_id;
		$params['openid'] = $this->open_id;
        $params['format'] = $this->format;
		if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
		    $url = "{$this->host}{$url}";
		}		
		$url =  $url . "?" . http_build_query($params);

		$response = $this->get($url);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}    
    
}