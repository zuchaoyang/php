<?php
define('VENDOR_EMAIL_DIR', dirname(__FILE__));
include_once VENDOR_EMAIL_DIR . "/phpmailer/phpmailer.class.php";

class Email implements EmailInterface {
    protected $mail = null;
    
    public function __construct() {
        $mail = new PHPMailer();
        //指定邮件内容类型为'text/html'
		$mail->ContentType = 'text/html';
		// 设置PHPMailer使用SMTP服务器发送Email
		$mail->IsSMTP();
		// 设置邮件的字符编码，若不指定，则为'UTF-8'
		$mail->CharSet='UTF-8';
		
		// 设置邮件头的From字段。
		// 对于网易的SMTP服务，这部分必须和你的实际账号相同，否则会验证出错。
		$mail->From='service@wmw.cn';
		
		// 设置发件人名字
		$mail->FromName='我们网用户中心';
		
		// 设置邮件标题
		$mail->Subject='我们网用户服务邮件';
		
		// 设置SMTP服务器。这里使用网易的SMTP服务器。
		$mail->Host='smtp.exmail.qq.com';
		
		// 设置为"需要验证"
		$mail->SMTPAuth = true;
		
		// 设置用户名和密码，即网易邮件的用户名和密码。
		$mail->Username='service@wmw.cn';
		$mail->Password='zhonghai';
		
		$this->mail = & $mail;
    }
    
    public function setFrom($from) {
        $this->mail->From = $from;
    }
    
    public function setFromName($from_name) {
        $this->mail->FromName = $from_name;
    }
    
    public function setSubject($subject_name) {
        $this->mail->Subject = $subject_name;
    }
    
    public function setHost($host) {
        $this->mail->Host = $host;
    }
    
    public function setUsername($username) {
        $this->mail->Username = $username;
    }
    
    public function setPassword($password) {
        $this->mail->Password = $password;
    }
    
    public function send($email_address, $email_content) {
        // 添加收件人地址，可以多次使用来添加多个收件人
		$this->mail->AddAddress($email_address);
		// 设置邮件正文
		$this->mail->Body = $email_content;
		// 发送邮件
		$send = $this->mail->Send();
		
		return $send ? true : false;
    }
}