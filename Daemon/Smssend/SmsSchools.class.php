<?php
header("Content-Type:text/html; charset=utf-8");
include_once(dirname(dirname(__FILE__)) . '/Daemon.inc.php');
class SmssendautoschoolsAction extends Controller{
    public function sendmultiple() {
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $send_times = time();
        $status = '1';//待发状态
        $orderby = 'id asc limit 0,1';
        $sms_list = $mSendTmp->getSendTmpInfoBySendSendTimeAndStatus($send_times, '1', $orderby);
        //dump($sms_list);die;
        $sms_list = reset($sms_list);
        
        //得到学校school_id
        if(empty($sms_list)) {
            return false;  
        }
        $operation_strategy_arr = explode(',', $sms_list['operation_strategy']);
        
        $i=0;
        $datas_arr = array();
        foreach($operation_strategy_arr as $operation_strategy) {
            $school_ids = $this->getschoolids($operation_strategy);
            if(empty($school_ids)) {
                $this->error_info("not get school_ids by operation_strategy that is $operation_strategy");
                continue;
            }
            $now_date = date('Y-m-d H:i:s', time());
            foreach($school_ids as $keyschools=>$school_id) {
                $datas_arr[$i++] = array(
                    'school_id'            => $school_id,
                    'send_tmp_id'          => $sms_list['id'],
                    'operation_strategy'   => $operation_strategy,
                    'add_date'             => $now_date,
                );
            }
            
        }
        unset($operation_strategy_arr);
        //添加到临时表send_schoolids
        $mSendTmpSchool = ClsFactory::Create('Model.mSendTmpSchool');
        $rs = $mSendTmpSchool->addSendSchoolIds($datas_arr);
        if($rs) {
            $rs = $mSendTmp->modifySendTmpInfoById(array('send_status'=>2),$sms_list['id']);
            if(empty($rs)) {
                $this->error_info("\$mSendTmp->modifySendTmpInfoById({$sms_list['id']}");
                return false;
            }
        }else{
            $this->error_info("\$mSendTmpSchool->addSendSchoolIds(\$datas_arr) \r\n");
            return false;
        }
    }
    
    //得到所有黑龙江的学校的班级id
    private function getschoolids($operation_strategy) {
        $mSchoolInfo = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $SchoolInfoes = $mSchoolInfo->getSchoolInfoByOperationStrategy ( $operation_strategy );
        $schoolids = array ();
        foreach ( $SchoolInfoes as & $SchoolInfo ) {
            $schoolids [] = $SchoolInfo ['school_id'];
        }
        unset ( $mSchoolInfo );
        unset ( $SchoolInfoes );
        return ! empty ( $schoolids ) ? $schoolids : false;
    }
    
    private function error_info($message) {
        error_log(date('Y-m-d H:i:s').'  error_log:' .__FILE__ ."：$message\r\n", 3, dirname(dirname(__FILE__)) . '/Errorlog/smserror.log');
    }
}

$send = new SmssendautoschoolsAction();
$send->sendmultiple();