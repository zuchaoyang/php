<?php
require_once(LIBRARIES_DIR . '/Request.class.php');
import("@.Common_wmw.RequestDecorate");
session_start();

/**
 * 控制层基类
 * 功能说明:
 * 1. 实现页面输入对象的过滤和初始化处理；
 * 2. 统一页面的成功提示和错误提示函数；
 * 3. 规范子类行为;
 * @author Administrator
 * 
 */
abstract class Controller extends Action {
    //用户输入对象
    protected $objInput;
    
    protected $commandobjarr = array();
    
    protected $user;

    public function __construct() {
    	$this->objInput =  Request::getInstance();
        parent::__construct();
        
    }
    
    public function _initialize() {
    	header("Content-Type:text/html; charset=utf-8");
    }
    
    /**
     * 获取当前登录用户名信息
     */
	protected function getCookieAccount() {
	    
	}
	
	/**
	 * 获取统一注销地址
	 */
	protected function getLogoutUrl() {
	    
	}

    /**
     * 成功提示函数
     * @param $message
     * @param $backurl
     */
    protected function showSuccess($message, $backurl = null) {
        ob_clean();
        
        if($this->isAjax()) {
            $this->ajaxReturn(array('back_url' => $backurl), $message, 1, 'json');
        }
        
        $timeout = 5;
        if(is_null($backurl)) {
            header("refresh:{$timeout}");
            $backurl = "javascript:;";
        } else {
            header("refresh:{$timeout};url={$backurl}");
        }
        //提示标题
        $this->assign('msgTitle', '成功!');
        //页面提示内容
        $this->assign('message', $message);
        //页面等待时间
        $this->assign('timeout', $timeout);
        //跳转的url
        $this->assign('backurl', $backurl);
        //追加参数钩子
        if(method_exists($this, 'append_success_assign')) {
            $this->append_success_assign();
        }
        //使用钩子，减少函数的参数
        $this->display($this->getSuccessTplFile());
        exit;
    }
    
    /**
     * 显示失败信息
     * @param $message
     * @param $backurl
     */
    protected function showError($message, $backurl = null) {
        ob_clean();
        
        if($this->isAjax()) {
            $this->ajaxReturn(array('backurl' => $backurl), $message, -1, 'json');
        }
        
        $timeout = 5;
        if(is_null($backurl)) {
            header("refresh:{$timeout}");
            $backurl = "javascript:;";
        } else {
            header("refresh:{$timeout};url={$backurl}");
        }
        //提示标题
        $this->assign('msgTitle', '失败!');
        //页面提示内容
        $this->assign('message', $message);
        //页面等待时间
        $this->assign('timeout', $timeout);
        //跳转的url
        $this->assign('backurl', $backurl);
        //追加参数钩子
        if(method_exists($this, 'append_error_assign')) {
            $this->append_error_assign();
        }
         //使用钩子，减少函数的参数
        $this->display($this->getErrorTplFile());
        exit;
    }
    
    /*****************************************************************************************************************
     * 说明:
     * 1. 如果子类存在自己的提示页模板风格，则重写getSuccessTplFile()和getErrorTplFile()方法;
     * 2. 如果相应的模板需要追加其他的变量信息，则重写append_success_assign()和append_error_assign()方法;
     ***************************************************************************************************************/
 	/**
     * 获取成功提示模板
     */
    protected function getSuccessTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_success_tips.html";
    }
    
    /**
     * 获取失败提示页模板
     */
    protected function getErrorTplFile() {
        return WEB_ROOT_DIR . "/View/Template/Public/wmw_error_tips.html";
    }
    
  	/**
     * 追加成功信息变量
     */
    protected function append_success_assign() {
        
    }
    
    /**
     * 追加失败页面的参数设置
     */
    protected function append_error_assign() {
        
    }
}
