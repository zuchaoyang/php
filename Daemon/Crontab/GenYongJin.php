<?php
/**
 * 
 * Author: lnc<lnczx0915@gmail.com>
 * 功能:提取酬金的用户数据，并可输出到ftp等;
 * 日期:	2012-05-10
 * 说明:		提取酬金的用户数据，格式如下：
 * create table TI_B_USER_WMW  (
 *   EPARCHY_CODE  VARCHAR2(8),     --地市编码（必须提供）
 *   SERIAL_NUMBER VARCHAR2(40),    --手机号码（必须提供）
 *   IN_DATE       DATE,            --激活时间（必须提供）
 *   DESTROY_TIME  DATE,            --销户时间（可以不提供）
 *   CYCLE_ID      VARCHAR2(6)      --账期（必须提供）
 * )
 * 流程：
 *   1. 提取佣金省市循环
 *   2. 提取单个省市账号
 *   
 *   1) 根据wmw_school_info 找出运营策略operation_strategy 对应的省市学校列表
 *   2) 根据1得到的学校列表，找出对应的班级列表 wmw_class_info   class_code, 
 *   3) 根据2得到的班级列表，找出对应的账号列表wmw_client_class client_account
 *   4) 根据3得到的账号列表，找出对应的手机号列表 wmw_business_phone where seq_type = 0 
 *   5) 根据3得到的账号列表，找出账号的激活信息.
 *   6) 学校省市关联得出区号  
*/

header("Content-Type:text/html; charset=utf-8");
include_once(dirname(dirname(__FILE__)) . '/Daemon.inc.php');
include_once(CONFIGE_DIR.'/area.php');
define("YONGJIN_DIR", "/root/ftp");
ini_set('error_reporting', E_ALL);



class GenYongJinAction extends Controller {
    
    private $_cities = array(2 =>'黑龙江省');
    
    // 输出临时文件的名称;
    private $_outFileName;
    
    //账期
    private $_CYCLE_ID;
    
    //班级分批处理
    private $_class_pagesize = 300;
    
    //账号分批处理
    private $_client_pagesize = 1000;
    
    //全局老师账号存储数组
    private $_client_teach_result;
    
    //todo  定义输出的目的，可以扩展，格式为 '协议'=>'执行函数'  ,要注意必须有相应的配置，比如ftp配置等.
    private $_outActions = array('ftp'=>'doSendToFtp', 'mail'=>'doSendToMail');
        
    
    public function _initialize() {
        //
    }

    public function before_exec() {
        
        //删除存在的文件
        date_default_timezone_set('Etc/GMT'); 
        $this->_CYCLE_ID = $this->GetPurMonth(date("Y-m-d"));
        mk_dir(YONGJIN_DIR);
    }
    
    public function after_exec() {
        //todo;
        
    }

    public function exec() {
        debug_start('run');
        $this->before_exec();
        //处理多个省市，根据配置$_cities来循环
        if (!empty($this->_cities)) {
            foreach ($this->_cities as $city=>$val) {
                $this->_client_teach_result = array();
                //filename = wmw_2_201204
                $this->_outFileName = YONGJIN_DIR. '/wmw_'. $city . '_' . $this->_CYCLE_ID . '.txt';
                if(file_exists($this->_outFileName)) {
                    unlink($this->_outFileName);
                    //rename($this->_outFileName, $this->_outFileName . '.bak');
                }
                
    
                //数据处理
                $this->gendata($city);
                unset($this->_client_teach_result);
            }
        }
        
        // 处理表china_unionc 的数据，写入目标文件
        $this->execByChinaUnicom();
        
        $this->after_exec();
        debug_end('run');
    }
    
    public function gendata($city) {
        
        if (empty($city)) {
            return;
        }
       
        //1) 根据wmw_school_info 找出运营策略operation_strategy 对应的省市
        $schoolIds = array();
        $schools = array();        
        $schoolInfo = $this->getSchoolInfoByOperationStrategy($city);
        foreach($schoolInfo as $schoolId=>$val) {

            $schoolIds[] = $schoolId;
            $schools[$schoolId] = $this->getAreaCode($city, $val['area_id']);
        }

        unset($schoolInfo);
        //2) 根据1得到的学校列表，找出对应的班级列表 wmw_class_info   class_code
         
        $s = $this->getClassInfoBySchoolIds($schoolIds);      
        $class_codes = array();
        $classes = array();  
        if (!empty($s)) {
        	foreach($s as $shcool_key=>$class_list) {
                foreach ($class_list as $class_code=>$class_info) {
                    $class_codes[] = $class_code;
                    $classes[$class_code] = $schools[$class_info['school_id']];
                }
            }
        } else {
            return false;
        }

        // 班级分页处理,每两百个进行处理
        while(!empty($class_codes)) { 
             $class_code_sub = array_splice($class_codes, 0, $this->_class_pagesize);   
             $this->execByClassCode($class_code_sub, $classes);
        }
        
        return true;
    }
    
    private function execByClassCode($class_codes = array(), $classes = array()) {
        
        if (empty($class_codes)) {
            return false;
        }

        //3) 根据2得到的班级列表，找出对应的账号列表wmw_client_class client_account
        
        $clientInfo = $this->getClientClassByClassCode($class_codes);
        $uids = array();
        $accounts = array();
        
        foreach ($clientInfo as $schoolId=>$client_info) {
            foreach($client_info as $key=>$val){  //获取班级所有会员账号，包括学生家长和老师
                $uids[] = $uid = $val['client_account'];
                $accounts[$uid] = $classes[$val['class_code']];    
            }
        }

       
        while(!empty($uids)) { 
             $uid_sub = array_splice($uids, 0, $this->_client_pagesize);   
             $this->execByClientInfo($uid_sub, $accounts);
        }
        
        //清除临时缓存数据
        unset($clientInfo);
        unset($classes);
        unset($uids);
        unset($accounts);
        
        return true;
    }
    

    private function execByClientInfo($uids = array(), $accounts = array()) {
                
        //4) 根据3得到的账号列表，找出对应的手机号列表 wmw_business_phone where seq_type = 0 
        
        $phoneInfo = $this->getBusinessPhone($uids);
        
        //最后合并为一个数组:
        $result = array();
        foreach($phoneInfo as $key=>$val) {
            
            $account_phone_id2 = $val['account_phone_id2'];
            $user = $userInfo[$key];
            $activeData = '';
            if (isset($val['dbcreatetime'])) {
                //$activeData = date( 'Y-m-d',strtotime($user['active_date']));
                $activeData = substr($val['dbcreatetime'], 0, 10);
            }
            $item = array($accounts[$key],
               				  $val['account_phone_id2'],
               				  $activeData,
                              '',
                              $this->_CYCLE_ID
                              );
            
            if (!isset($this->_client_teach_result[$account_phone_id2])) {
                    $this->_client_teach_result[$account_phone_id2] = $item;
                     $result[$account_phone_id2] = $item;
            }
        }
        $this->fputCsv($this->_outFileName, $result);
        
        unset($phoneInfo);
        unset($accounts);
        unset($result);        
    }    
    
    
    //输出到文件
    private function fputCsv($filename, $result = array(), $filemode = 'a') {
        
        if (empty($filename) || empty($result)) {
            return false;    
        }

        $file = fopen($filename, $filemode);
        foreach ($result as $line) {
          fputcsv($file, $line);
        }
        fclose($file);
        
        return true;        
    }
    
    //通过学校的业务类型得到学校的信息
    public function getSchoolInfoByOperationStrategy($city) {
        
        if (empty($city)) {
             return array();   
        }
        
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        
        return $mSchoolInfo->getSchoolInfoByOperationStrategy($city);
    }
    
    //通过学校ids得到班级信息
    public function getClassInfoBySchoolIds($schoolIds = array()) {
        
        if (empty($schoolIds)) {
             return array();   
        }
        
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    
	    return $mClassInfo->getClassInfoBySchoolId($schoolIds);
    }
    
    //通过班级ids得到会员账号
    public function getClientClassByClassCode($class_codes = array()) {
        
        if (empty($class_codes)) {
            return array();   
        }
        
	    $mClassInfo = ClsFactory::Create('Model.mClientClass');
	    
	    return  $mClassInfo->getClientClassByClassCode($class_codes);
    }
    
    //通过会员账号转换为手机号
    public function getBusinessPhone($uids = array()) {
        
        if (empty($uids)) {
            return array();   
        }
        
	    $mClassInfo = ClsFactory::Create('Model.mBusinessphone');
	    
	    return  $mClassInfo->getBusinessPhone($uids);
    }
    
    //通过会员账号获取账号信息
    public function getClientAccountById($uids = array()) {
        
        if (empty($uids)) {
            return array();   
        }
        
	    $mClassInfo = ClsFactory::Create('Model.mUser');
	    
	    return  $mClassInfo->getClientAccountById($uids);
    }    
    
    //获取联通手机号，并追加到outputfile
    public function execByChinaUnicom() {
        
	    $mChinaUnicom = ClsFactory::Create('Model.mChinaUnicom');
	    
//	    print_r('<pre>');
//	    print_r($mChinaUnicom->getPhoneInfo());	    
	    
	    $phoneInfo = $mChinaUnicom->getPhoneInfoAll();
        $result = array();
        if (empty($phoneInfo)) {
            return false;
        }
        foreach($phoneInfo as $key=>$val) {
            
            $phone_id = $val['phone_id'];
            $activeData = '';
            if (isset($val['sim_time'])) {
                //$activeData = date( 'Y-m-d',strtotime($user['active_date']));
                $activeData = substr($val['sim_time'], 0, 10);
            }
            $item = array($val['area_code'],
               				  $phone_id,
               				  $activeData,
                              '',
                              $this->_CYCLE_ID
                              );
            
            $result[$phone_id] = $item;

        }
        $this->fputCsv($this->_outFileName, $result);	    
	    
	    return true;

    }    
    
    //查询获取区号
    public function getAreaCode($province_id, $area_id) {
        
        if (empty($province_id) || empty($area_id)) {
            return '';   
        }
        
        $de_area_code = decodeAreaId($area_id);
        if (is_array($de_area_code))   $city = $de_area_code[1];
        if (isset($city)) {
            global $CONF_AREA_CODE;
            if (isset($CONF_AREA_CODE[$province_id])) {
                if (isset($CONF_AREA_CODE[$province_id][$city]))
                return $CONF_AREA_CODE[$province_id][$city]['code'];
            }
        }
        
        return '';
    }
    
    // todo 可以放到公有日期类函数
    //获取指定日期上个月的第一天
    private function GetPurMonth($date) {
        $time = strtotime($date);
        $firstday=date('Ym',strtotime(date('Y',$time).'-'.(date('m',$time)-1).'-01'));
        
        return $firstday;
    }
}
$yj = new GenYongJinAction();
$yj->exec();