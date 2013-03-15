<?php


    /**
     +----------------------------------------------------------
     * 向开通联通手机业务的学生家长手机发送短信
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content 短信内容 字符型
     * @param array  $phone  多手机号时使用数字索引数组
     +----------------------------------------------------------
     */

//测试：
class SmssendAction extends Controller {
    public function _initialize(){
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Constancearr");
    }
    public function sendMessage(){$this->display("sendMessage");}//显示文本输入页面。
    
    public function send(){//信息处理
        $content = $this->objInput->postStr('content'); 
        $phone = trim($this->objInput->postStr('mobile'));
        $phone = explode(',',$phone);//接受的是以空格分隔的电话字符串
            
        $mSmsSend = ClsFactory::Create('Model.mSmsSend');
        $result = $mSmsSend->addSmsSend($phone , $content);
        if($result){   //添加数据库是否成功
            echo "添加数据成功,请查收信息。";
            return true;
        }else{    
            echo "<br>"."添加数据失败，请检查数据是否正常入库";  
            return false;
        } 
    }
    
 //短信发送状态说明
    function checkstatus($num) {
        $num = intval($num);
        $statusstr = "";
        switch ($num) {
            case 100 :
                $statusstr = "发送成功";
                break;
            case 101 :
                $statusstr = "验证失败";
                break;
            case 102 :
                $statusstr = "短信不足";
                break;
            case 103 :
                $statusstr = "操作失败";
                break;
            case 104 :
                $statusstr = "非法字符";
                break;
            case 105 :
                $statusstr = "内容过多";
                break;
            case 106 :
                $statusstr = "号码过多";
                break;
            case 107 :
                $statusstr = "频率过快";
                break;
            case 108 :
                $statusstr = "号码内容空";
                break;
            case 109 :
                $statusstr = "账号冻结";
                break;
            case 110 :
                $statusstr = "禁止频繁单条发送";
                break;
            case 111 :
                $statusstr = "系统暂定发送";
                break;
            case 112 :
                $statusstr = "号码不正确";
                break;
            case 113 :
                $statusstr = "连接失败";
                break;
            case 120 :
                $statusstr = "系统升级";
                break;
            case 444:
                $statusstr = "更新数据库失败";
                break;
            default:
                $statusstr = "未知错误";
                break;
        }
        return $statusstr;
    }
}
