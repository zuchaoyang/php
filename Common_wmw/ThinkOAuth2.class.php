<?php

/** 
 * @category ORG 
 * @package ORG 
 * @author Leyteris 
 * @version 2012.3.16 
 * OAUTH2_DB_DSN  数据库连接DSN  
 * OAUTH2_CODES_TABLE 服务器表名称  
 * OAUTH2_CLIENTS_TABLE 客户端表名称  
 * OAUTH2_TOKEN_TABLE 验证码表名称  
 */  

if (!defined('WMW_COMMON')) {
    define('WMW_COMMON', dirname(__FILE__));
}
include_once WMW_COMMON . "/Vendor/OAuth2/OAuth2.php";

class ThinkOAuth2 extends OAuth2 {

    private $db;
    private $table;  
    /**
    * Overrides OAuth2::__construct().
    */
    public function __construct() {
        parent::__construct();
        try {
//          $this -> db = Db::getInstance(C('OAUTH2_DB_DSN'));  
          $this->db = Db::getInstance('user');  
          $this ->table = array(  
            'auth_codes' => 'uc_oauth_codes',  
            'clients'    => 'uc_oauth_clients',  
            'tokens'     => 'uc_oauth_tokens'  
          );  
        } catch (Exception $e) {
          throw new Exception('Connection failed: ' . $e->getMessage());
        }
    }
    
    /** 
     * 析构 
     */  
    function __destruct() {  
        $this->db = NULL; // Release db connection  
    }  
    
    private function handleException($e) {  
        echo "Database error: " . $e->getMessage();  
        exit;  
    }  
    
    /** 
     *  
     * 增加client 
     * @param string $client_id 
     * @param string $client_secret 
     * @param string $redirect_uri 
     */  
    public function addClient($client_id, $client_secret, $redirect_uri) {  
          
//        $time = time();  
//        $sql = "INSERT INTO {$this -> table['clients']} ".  
//            "(client_id, client_secret, redirect_uri, create_time) VALUES (\"{$client_id}\", \"{$client_secret}\", \"{$redirect_uri}\",\"{$time}\")";  
//        $this->db ->execute($sql);          
        $datas = array('client_id' => $client_id,
                       'client_secret'	=> $client_secret,
                       'redirect_uri'	=> $redirect_uri,
                       'create_time'	=> time()
                      );
        
        $m = ClsFactory::Create("Model.mOauthClient");
        return $m->addOauthClient($datas);

          
    }  
    
    /** 
     * Implements OAuth2::checkClientCredentials() 
     * @see OAuth2::checkClientCredentials() 
     */  
    protected function checkClientCredentials($client_id, $client_secret = NULL) {  
          
//        $sql = "SELECT client_secret FROM {$this -> table['clients']} ".  
//            "WHERE client_id = \"{$client_id}\"";  
//          
//        $result = $this->db->query($sql);  
//        if ($client_secret === NULL) {  
//            return $result !== FALSE;  
//        }  
          
        //Log::write("checkClientCredentials : ".$result);  
        //Log::write("checkClientCredentials : ".$result[0]);  
        //Log::write("checkClientCredentials : ".$result[0]["client_secret"]);  
        
//        return $result[0]["client_secret"] == $client_secret;          
        
        $m = ClsFactory::Create("Model.mOauthClient");
        $result = $m->getOauthClientById($client_id);
        if (empty($result)) {
            return false;
        }
        
        if ($client_secret === NULL) {  
            return  $result[$client_id]['client_secret'] !== FALSE;
        }          
        
        return isset($result[$client_id]['client_secret']) ? $result[$client_id]['client_secret'] == $client_secret : NULL;  
    }  
    
    /** 
     * Implements OAuth2::getRedirectUri(). 
     * @see OAuth2::getRedirectUri() 
     */  
    protected function getRedirectUri($client_id) {  
          
//        $sql = "SELECT redirect_uri FROM {$this -> table['clients']} ".  
//            "WHERE client_id = \"{$client_id}\"";  
//          
//        $result = $this->db->query($sql);  
//          
//        if ($result === FALSE) {  
//            return FALSE;  
//        }  
          
        //Log::write("getRedirectUri : ".$result);  
        //Log::write("getRedirectUri : ".$result[0]);  
        //Log::write("getRedirectUri : ".$result[0]["redirect_uri"]);  
          
//        return isset($result[0]["redirect_uri"]) && $result[0]["redirect_uri"] ? $result[0]["redirect_uri"] : NULL;  

        $m = ClsFactory::Create("Model.mOauthClient");
        $result = $m->getOauthClientById($client_id);
        if (empty($result)) {
            return false;
        }
        
        return isset($result[$client_id]['redirect_uri']) ? $result[$client_id]['redirect_uri'] : NULL;          
    }  
    
    /** 
     * Implements OAuth2::getAccessToken(). 
     * @see OAuth2::getAccessToken() 
     */  
    protected function getAccessToken($access_token) {  
          
//        $sql = "SELECT client_id, expires, scope FROM {$this -> table['tokens']} ".  
//            "WHERE access_token = \"{$access_token}\"";  
          
//        $result = $this->db->query($sql);  
          
        //Log::write("getAccessToken : ".$result);  
        //Log::write("getAccessToken : ".$result[0]);  
          
//        return $result !== FALSE ? $result : NULL;  

        $m = ClsFactory::Create("Model.mOauthToken");
        $result = $m->getOauthTokenByAccessToken($access_token);
        return !empty($result) && isset($result[0]) ? $result[0] : NULL;
    }
    /** 
     * Implements OAuth2::setAccessToken(). 
     * @see OAuth2::setAccessToken() 
     */  
    protected function setAccessToken($access_token, $client_id, $expires, $scope = NULL) {  
          
//        $sql = "INSERT INTO {$this -> table['tokens']} ".  
//            "(id, access_token, client_id, expires, scope) ".  
//            "VALUES (0, \"{$access_token}\", \"{$client_id}\", \"{$expires}\", \"{$scope}\")";  
//          
//        $this->db ->execute($sql);  
        $datas = array( 'id'             => 0,
                      'access_token'   => $access_token,
                      'client_id'	   => $client_id,
                      'expires'		   => $expires,
                      'scope'		   => $scope
                     );
        $m = ClsFactory::Create("Model.mOauthToken");
        return $m->addOauthToken($datas);          
    }  
    
    /** 
     * Overrides OAuth2::getSupportedGrantTypes(). 
     * @see OAuth2::getSupportedGrantTypes() 
     */  
    protected function getSupportedGrantTypes() {  
        return array(  
            OAUTH2_GRANT_TYPE_USER_CREDENTIALS
        );  
    }  
    
    /** 
     * Overrides OAuth2::getAuthCode(). 
     * @see OAuth2::getAuthCode() 
     */  
    protected function getAuthCode($code) {  
          
//        $sql = "SELECT code, client_id, redirect_uri, expires, scope ".  
//            "FROM {$this -> table['auth_codes']} WHERE code = \"{$code}\"";
//        $result = $this->db->query($sql);  
//        return $result !== FALSE ? $result[0] : NULL;  

        $m = ClsFactory::Create("Model.mOauthCode");
        $result = $m->getOauthCodeById($code);
        return !empty($result) ? $result[$code] : NULL;
    }  
    
    /** 
     * Overrides OAuth2::setAuthCode(). 
     * @see OAuth2::setAuthCode() 
     */  
    protected function setAuthCode($code, $client_id, $redirect_uri, $expires, $scope = NULL) {  
          
//        $time = time();  
//        $sql = "INSERT INTO {$this -> table['auth_codes']} ".  
//            "(code, client_id, redirect_uri, expires, scope) ".  
//            "VALUES (\"${code}\", \"${client_id}\", \"${redirect_uri}\", \"${expires}\", \"${scope}\")";  
//          
//        $result = $this->db ->execute($sql);  

        $datas = array( 'code'             => $code,
                      'client_id'	   => $client_id,
        			  'redirect_uri'	=> $redirect_uri,
                      'expires'		   => $expires,
                      'scope'		   => $scope
                     );
        
        $m = ClsFactory::Create("Model.mOauthCode");
        return $m->addOauthClient($datas);        
        
    }  
    
    
      /**
    * Grant or deny a requested access token.
    *
    * This would be called from the "/token" endpoint as defined in the spec.
    * Obviously, you can call your endpoint whatever you want.
    *
    * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-10#section-4
    *
    * @ingroup oauth2_section_4
    * 
    */
    public function grantAccessToken() {
        $filters = array(
          "grant_type" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => OAUTH2_GRANT_TYPE_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
          "scope" => array("flags" => FILTER_REQUIRE_SCALAR),
          "code" => array("flags" => FILTER_REQUIRE_SCALAR),
          "redirect_uri" => array("filter" => FILTER_SANITIZE_URL),
          "username" => array("flags" => FILTER_REQUIRE_SCALAR),
          "password" => array("flags" => FILTER_REQUIRE_SCALAR),
          "assertion_type" => array("flags" => FILTER_REQUIRE_SCALAR),
          "assertion" => array("flags" => FILTER_REQUIRE_SCALAR),
          "refresh_token" => array("flags" => FILTER_REQUIRE_SCALAR),
        );
        
        $input = filter_input_array(INPUT_POST, $filters);
        
        // Grant Type must be specified.
        if (!$input["grant_type"])
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
        
        // Make sure we've implemented the requested grant type
        if (!in_array($input["grant_type"], $this->getSupportedGrantTypes()))
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_UNSUPPORTED_GRANT_TYPE);
        
        // Authorize the client
        $client = $this->getClientCredentials();
        
        if ($this->checkClientCredentials($client[0], $client[1]) === FALSE)
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_CLIENT);
        
        if (!$this->checkRestrictedGrantType($client[0], $input["grant_type"]))
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_UNAUTHORIZED_CLIENT);
        
        // Do the granting
        switch ($input["grant_type"]) {
          case OAUTH2_GRANT_TYPE_USER_CREDENTIALS:
//            if (!$input["username"] || !$input["password"])
              if (!$input["username"])
              $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        
            $stored = $this->checkUserCredentials($client[0], $input["username"], $input["password"]);
        
            if ($stored === FALSE)
              $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_GRANT);
        
            break;
            
            //todo add getSupportedGrantTypes, see OAuth2.class.php function grantAccessToken
          
        }
        
        // Check scope, if provided
        if ($input["scope"] && (!is_array($stored) || !isset($stored["scope"]) || !$this->checkScope($input["scope"], $stored["scope"])))
          $this->errorJsonResponse(OAUTH2_HTTP_BAD_REQUEST, OAUTH2_ERROR_INVALID_SCOPE);
        
        if (!$input["scope"])
          $input["scope"] = NULL;
        
          
        switch ($input["grant_type"]) {
            case OAUTH2_GRANT_TYPE_USER_CREDENTIALS:
                $token = $this->createAccessTokenWithUserName($client[0], $input["username"], $input["scope"]);
            break;
            //todo add getSupportedGrantTypes, see OAuth2.class.php function grantAccessToken
        }
        
        
        $this->sendJsonHeaders();
        return json_encode($token);
    }    
    
    
    /** 
    * Overrides OAuth2::checkUserCredentials(). 
    * @see OAuth2::checkUserCredentials() 
    */  
    protected function checkUserCredentials($client_id, $username, $password){  
        return TRUE;  
    }
    
    /**
    * Handle the creation of access token, also issue refresh token if support.
    *
    * This belongs in a separate factory, but to keep it simple, I'm just
    * keeping it here.
    *
    * @param $client_id
    *   Client identifier related to the access token.
    * @param $scope
    *   (optional) Scopes to be stored in space-separated string.
    *
    * @ingroup oauth2_section_4
    */
    protected function createAccessTokenWithUserName($client_id, $username, $scope = NULL) {
        $token = array(
          "client_id"    => $client_id,
          "username"	 => $username,
          "access_token" => $this->genAccessToken(),
          "expires_in"	 => $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME),
          "scope"		 => $scope
        );
        
        $this->setAccessToken($token["access_token"], $client_id, time() + $this->getVariable('access_token_lifetime', OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME), $scope);
        
        // Issue a refresh token also, if we support them
        if (in_array(OAUTH2_GRANT_TYPE_REFRESH_TOKEN, $this->getSupportedGrantTypes())) {
          $token["refresh_token"] = $this->genAccessToken();
          $this->setRefreshToken($token["refresh_token"], $client_id, time() + $this->getVariable('refresh_token_lifetime', OAUTH2_DEFAULT_REFRESH_TOKEN_LIFETIME), $scope);
          // If we've granted a new refresh token, expire the old one
          if ($this->getVariable('_old_refresh_token'))
            $this->unsetRefreshToken($this->getVariable('_old_refresh_token'));
        }
        
        return $token;
    }
}
