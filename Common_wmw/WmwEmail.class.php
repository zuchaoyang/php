<?php
define("WMW_EMAIL_DIR", dirname(__FILE__));
include_once WMW_EMAIL_DIR . "/Vendor/Email/EmailInterface.php";
include_once WMW_EMAIL_DIR . "/Vendor/WmwAutoLoader.class.php";

class WmwEmail implements EmailInterface {
    private $objEmail = null;
    
    public function __construct() {
        $this->objEmail = new Email();
    }
    
	/**
	 * 设置邮件头的From字段。
	 * 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
 	 *@param $from,如:'service@wmw.cn';
	*/
    public function setFrom($from) {
        if(method_exists($this->objEmail, 'setFrom')) {
            $this->objEmail->setFrom($from);
        }
    }
    
    /**
     * 设置发件人名字
     * @param $from_name,如: "WMW"
     */
    public function setFromName($from_name) {
        if(method_exists($this->objEmail, 'setFromName')) {
            $this->objEmail->setFromName($from_name);
        }
    }
    
    /**
     * 设置邮件标题
     * @param $subject_name,如:'WMW为您服务'
     */
    public function setSubject($subject_name) {
        if(method_exists($this->objEmail, 'setSubject')) {
            $this->objEmail->setSubject($subject_name);
        }
    }
    
    /**
     * 设置SMTP服务器
     * @param $host,如网易的SMTP服务器 'smtp.exmail.qq.com'
     */
    public function setHost($host) {
        if(method_exists($this->objEmail, 'setHost')) {
            $this->objEmail->setHost($host);
        }
    }
    
    /**
     * 设置用户名，即使用的邮件工具
     * @param $username
     */
    public function setUsername($username) {
        if(method_exists($this->objEmail, 'setUsername')) {
            $this->objEmail->setUsername($username);
        }
    }
    
    /**
     * 设置密码，即使用的邮件工具的账号密码
     * @param $password
     */
    public function setPassword($password) {
        if(method_exists($this->objEmail, 'setPassword')) {
            $this->objEmail->setPassword($password);
        }
    }
    
    /**
     * 发送邮件信息
     * @param string $email_address 邮件地址 
     * @param $email_content		邮件内容
     */
    public function send($email_address, $email_content) {
        return $this->objEmail->send($email_address, $email_content);
    }
}