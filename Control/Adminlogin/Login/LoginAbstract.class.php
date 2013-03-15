<?php
abstract class LoginAbstract {
    
    const USER_PREP_TYPE = 'ENNUM';//用户名命名规则（数字字母下划线）
    const USER_MIN_LENGHT = 5;//用户名最小长度
    const USER_MAX_LENGHT = 30;//用户名最小长度
    
    const PASSWORD_PREP_TYPE = 'ENNUM';//用户名命名规则（数字字母下划线）
    const PASSWORD_MIN_LENGTH = 6;//密码最小长度
    const PASSWORD_MAX_LENGTH = 20;//密码最小长度
    
    protected $app_name = null;
    protected $user = array();
    
    protected $success_url = null;
    
    protected $view = null;
    
    public function setView(& $view) {
        $this->view = $view;
    }
    /**
     * 检测用户是否已经登录
     */
    public function isLogined() {
        $token_name = $this->getTokenName();
        list($uid, $passwd) = $this->getCookieTokenInfo($token_name);  
        return !empty($uid) ? true : false;
	}
    
    
    
    /**
     * login登录时的显示页面
     */
    abstract public function login();
	
    public function loginin($uid, $passwd) {
        //检测用户是否输入了用户名和密码
        if(empty($uid)) {
            $this->showErrMsg('用户名不能为空!');
            exit;
        }
        
        if(empty($passwd)) {
            $this->showErrMsg('密码不能为空!');
            exit;
        }
        
//        //检测用户名格式
//        if(!$this->checkUsernameFormat($uid)) {
//            $this->showErrMsg('用户名格式错误!');
//            exit;
//        }
//        //检测密码格式
//        if(!$this->checkPasswdFormat($passwd)) {    
//            $this->showErrMsg('密码名格式错误!');
//            exit;
//        }
        
        //初始化用户信息,密码和权限的检测需要加载用户信息
        $this->initUserByUid($uid);
        
        //检测用户密码信息
        if(!$this->checkPasswd($passwd)) {
            $this->showErrMsg('用户名或者密码错误!');
            exit;
        }
        //检测用户的权限信息
        if(!$this->checkUserAccess()) {
            $this->showErrMsg('账号无权登录!');
            exit;
        }
        //设置cookie
        $this->setCookie($uid, $passwd);
        //页面跳转
        $this->successSkip();
    }
    
 	/**
     * 退出登录
     */
    public function loginout() {
        $token_name = $this->getTokenName();
        
        ob_end_clean();
        header("Content-Type:text/html; charset=utf-8");
        setcookie(constant($token_name), '', time() - 3600, "/", "");
        $this->login();
    }
    
    /**
     * 通过uid初始化用户信息
     * @param $uid
     */
    abstract protected function initUserByUid($uid);
    
    /**
     * 检测用户的密码是否正确
     * @param $passwd
     */
    protected function checkPasswd($passwd) {
        $app_name = strtolower($this->app_name) == 'bms'? 'base' : $this->app_name;
        return $this->user[strtolower($app_name) . '_password'] == md5($passwd) ? true : false;
    }
    
    /**
	 * 检测用户的权限信息
	 */
    abstract protected function checkUserAccess();
    
    /**
     * 设置cookie的值
     * @param  $uid
     * @param  $password
     */
    protected function setCookie($uid, $passwd) {
        $session_token = token_encode(array($uid,$passwd));
        $token_name = $this->getTokenName();
        
        ob_end_clean();
        header("Content-Type:text/html; charset=utf-8");
        setcookie(constant($token_name), $session_token, time() + COOKIE_TIME_OUT, "/", "");
    }
    
    /**
     * 按照规则获取token_name
     */
    protected function getTokenName() {
        return strtoupper($this->app_name . "_SESSION_TOKEN");
    }
    
    /**
     * 显示错误信息
     * @param $msg
     */
    abstract protected function showErrMsg($msg);
    
    protected function successSkip() {
        ob_end_clean();
        header('Location: http://' . $_SERVER['SERVER_NAME'] . $this->success_url);
    }
    
    /**
     * 检测用户名是否满足规则
     * @param $str
     */
    protected function checkUsernameFormat($str) {
        if(strlen($str) > self::USER_MAX_LENGHT || strlen($str) < self::USER_MIN_LENGHT) {
            return false;
        }
        
        return self::checkStringFormart($str, self::USER_PREP_TYPE);
    }
    
    /**
     * 检测密码信息是否满足规则
     * @param $passwd
     */
    protected function checkPasswdFormat($passwd) {
        if(strlen($passwd) > self::PASSWORD_MAX_LENGTH || strlen($passwd) < self::PASSWORD_MIN_LENGTH) {
            return false;
        }
        
        return self::checkStringFormart($passwd, self::PASSWORD_PREP_TYPE);
    }
    
    /**
     * 检测字符串的格式
     * @param  $str
     * @param  $type
     */
    static protected function checkStringFormart($str, $type) {
        if(empty($type)) {
            return false;
        }
        
        switch($type) {
            case "EN"://纯英文
                return preg_match("/^[a-zA-Z]+$/", $str) ? true : false;
                break;
            case "NUM"://纯数字
                return preg_match("/^[0-9]+$/", $str) ? true : false;
                break;
            case "ENNUM"://英文数字下划线
                return preg_match("/^[_a-zA-Z0-9]+$/", $str) ? true : false;
                break;
            case "ALL":    //允许的符号(|-_字母数字)
                return preg_match("/^[|-_a-zA-Z0-9]+$/", $str) ? true : false;
                break;
        }
        
        return false;
    }
    
    //获取cookie中的token
	private function getCookieTokenInfo($token_name) {
		if (empty($token_name)) {
			return false;
		}
		
		$token = $_COOKIE[constant($token_name)];
		$token_arr = token_decode($token);
		return empty($token_arr) || count($token_arr) < 2 ? array('', '') : $token_arr;
	}
} 