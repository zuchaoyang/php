<?php
class RequestAction extends Controller {
	
	public function _initialize() {
        header("Content-Type:text/html;charset=utf-8");
    }

    //根据手机号码判断手机是否停机
    public function sendxml($phoneNum) {
        $Unicom = ClsFactory::Create ( 'Model.mUnicominterface' );
        //解析返回的xml并取得相应的值
        $rs = $Unicom->getPhoneStatus ( $phoneNum );
        if ($rs === false) {
            //做点什么呢
            switch ($rs) {
                case - 1 :
                    echo '错误';
                    break;
                case 0 :
                    echo '未停机';
                    break;
                case 1 :
                    echo '停机';
                    break;
                default :
                    echo '未知';
                    break;
            }
        } else {
            //做点什么呢
            echo '无效数据（格式错误或请求失败）';
        }
    }

    /*根据手机号码判断手机是否停机
     *错误号：
     *wmw_-1:手机停机
     *wmw_0:手机正常
     *wmw_1:非正常手机号码
     *wmw_2:创建socket失败
     *wmw_3：socket连接失败
     *wmw_4：socket发送请求失败
     *wmw_5：连接服务器失败
     *wmw_6:未知手机号
     *wmw_7:外围接口通用输入参数校验错误
     *wmw_8:ID号不一致
     */
    public function checkphone($phone_num) {

        if (empty ( $phone_num )) {
        	return false;
        }
            
        if (! preg_match ( '/^1[1-9]{1}[0-9]{9}/', $phone_num )) {
            error_log(date("Y-m-d H:i:s")."\tbusiness_v1:手机号码格式错误！错误手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
            return "wmw_1";
        }
        
        global $socket_data;

        //socket监听端口号
        $service_port = 28582;
        //socket ip_address
        $address = '125.211.218.24';
        
        $socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
        if ($socket < 0) {
            $socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );
            if ($socket < 0) {
                error_log(date("Y-m-d H:i:s")."\tbusiness_v1:创建socket失败！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
                return "wmw_2";
            }
        }
        $result = socket_connect ( $socket, $address, $service_port );
        if ($result < 0) {
            $result = socket_connect ( $socket, $address, $service_port );
            if ($result < 0) {
               error_log(date("Y-m-d H:i:s")."\tbusiness_v1:连接socket失败！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
                return "wmw_3";
            }
        }
        $id = time () . rand ( 10000, 99999 ) . rand ( 10000, 99999 );
        $phonenum = "$phone_num         ";
        $ch = chr('26');
        $in = '1089   ' . $id . ' GUSERSTATE  ' . $phonenum . '0WMW00001WMW000001    1     '.$ch;
        $input = iconv ( 'utf-8', 'iso-8859-1', $in );
        $out = '';
        if (! socket_write ( $socket, $input, strlen ( $input ) )) {
            error_log(date("Y-m-d H:i:s")."\tbusiness_v1:请求服务器失败！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
            return "wmw_4";
        }
        try {
            while ( $out = socket_read ( $socket, 400) ) {
                $socket_data .= $out;
            }
            $socket_data = iconv('GBK','UTF-8',$socket_data);
        } catch ( Exception $e ) {
            error_log(date("Y-m-d H:i:s")."\tbusiness_v1:服务器连接失败！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
            return "wmw_5";
        }
        socket_close ( $socket );
        $A_3 = $this->checkA_3 ( substr ( $socket_data, 0, 88 ) );
        if ($A_3) {
            $A_11 = $this->checkA_11 ( substr ( $socket_data, 0, 88 ) );
            if ($A_11 === true) {
                $serial_number = $this->check_serial_number ( substr ( $socket_data, 0, 88 ), $id );
                if ($serial_number) {
                    $phone_status = $this->check_phone_status ( substr ( $socket_data, 88 ) );
                    //echo $phone_status? "没有停机":"已经停机了";
                    return $phone_status ? "wmw_0" : "wmw_-1";
                } else {
                    error_log(date("Y-m-d H:i:s")."\tbusiness_v11:ID号不一致！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
                    return "wmw_8";
                }
            } else {
                error_log(date("Y-m-d H:i:s")."\tbusiness_v11:错误编号：$A_11; 错误信息：".substr ( $socket_data, 88 )."！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
                return "wmw_7";
            }
        } else {
            error_log(date("Y-m-d H:i:s")."\tbusiness_v13:未知手机号；".substr ( $socket_data, 88 )."！手机号码：$phone_num\n",3,WEB_ROOT_DIR."\\Logs\\PhoneError\\".date('y_m_d').'.log');
            return "wmw_6";
        }

    }

    //判断是否请求成功：A3
    private function checkA_3($socket_datas) {
        $f = substr ( $socket_datas, 27, 1 );
        return ($f == 1) ? true : false;
    }
    //判断是否A11成功
    private function checkA_11($socket_datas) {
        $f = substr ( $socket_datas, 83, 5 );
        return (trim ( $f ) == "00000") ? true : $f;
    }
    //判断是不是请求时的流水号：
    private function check_serial_number($socket_datas, $id) {
        $serial_number = substr ( $socket_datas, 7, 20 );
        return (trim ( $serial_number ) == $id) ? true : false;
    }
    //查看请求手机号码是否停机：
    private function check_phone_status($socket_datas) {
        $flag = false;
        $net_type = substr ( $socket_datas, 0, 2 );
        $phone_status = substr ( $socket_datas, 2, 2 );
        if($net_type == 10 || $net_type == 33){
            if(trim($phone_status) == 0)
                $flag =true;
        }elseif($net_type == 16 || $net_type == 17){
            if(trim($phone_status) == 2)
                $flag =true;
        }
        return $flag? $flag : false;
    }
    
    
    //测试方法
    public function testcheckphone(){
        $phonenum = $this->objInput->getInt("phonenum");
        echo $this->checkphone($phonenum);
    }
}
