<?php
header("Content-Type:text/html; charset=utf-8");
include_once(dirname(dirname(__FILE__)) . '/Daemon.inc.php');
class SmssendautoAction extends Controller {
    public function sendmultiple() {
        $mSendTmpClass = ClsFactory::Create('Model.mSendTmpClass');
        $getOne_list = $mSendTmpClass->getOneSendClassCode();
        
        if(empty($getOne_list)) {
            return false;  
        }
        $getOne_list = reset($getOne_list);
        $send_tmp_id = $getOne_list['send_tmp_id'];
        $operation_strategy = $getOne_list['operation_strategy'];
        $send_class_code_list = $mSendTmpClass->getListByTmpId($send_tmp_id,$operation_strategy);
        
        $class_codes = array();
        $send_class_ids = array_keys($send_class_code_list);
        foreach($send_class_code_list as $key=>$val) {
            $class_codes[$val['class_code']] = $val['class_code'];
        }
        
        $mSendTmp = ClsFactory::Create('Model.mSendTmp');
        $SendTmp_info = $mSendTmp->getSendTmpInfoById($send_tmp_id);
        $SendTmp_info = reset($SendTmp_info);
        
        $uids = $this->getclientaccounts($class_codes,explode(',',$SendTmp_info['send_range']));
        if(empty($uids)) {
            $this->error_info("not get uids by class_code that is ".implode(',', $class_codes)." and send_range is {$SendTmp_info['send_range']}");
            
            foreach($send_class_ids as $key=>$val) {
                $mSendTmpClass->delSendClasstmpById($val);
            }
            return false;
        }
        $phonenum = $this->getallphonenum($uids);
        if(empty($phonenum)) {
            $this->error_info("not get phones by uids");
            foreach($send_class_ids as $key=>$val) {
                $mSendTmpClass->delSendClasstmpById($val);
            }
            
            return false;
        }
        //todolist
        import('@.Control.Api.Smssend.Smssendapi');
        $smssendapi_obj = new Smssendapi();
        //70859265//18620456699
        $result = $smssendapi_obj->send($phonenum, $SendTmp_info['content'], $operation_strategy);
        if($result) {
            foreach($send_class_ids as $key=>$val) {
                $rs = $mSendTmpClass->delSendClasstmpById($val);
                if(empty($rs)) {
                    $this->error_info("\$mSendTmpClass->delSendClasstmpById({$val});");
                    return false;
                }
            }
            
            
        }else{
            $this->error_info("\$smssendapi_obj->send(\$phonenum,\$SendTmp_info['content'],\$send_class_code_list['operation_strategy']);");
            return false;
        }
    }
    
    
    //得到所有制定类型的账号
    private function getclientaccounts($class_codes, $recipient_type) {
        $mClientClass = ClsFactory::Create ( 'Model.mClientClass' );
        $filter = array ('client_type' => $recipient_type );
        $Client_classinfoes = $mClientClass->getClientClassByClassCode ( $class_codes, $filter );
        foreach ( $Client_classinfoes as $classInfo ) {
            foreach ( $classInfo as $client_account => & $clientclassinfo ) {
                if (in_array ( $clientclassinfo ['client_type'], (array)$recipient_type ))
                    $client_accounts [] = $client_account;
            }
        }
        unset ( $Client_classinfoes );
        unset ( $clientclassinfo );
        unset ( $client_account );
        
        return ! empty ( $client_accounts ) ? $client_accounts : false;
    }

//得到所有账号所对应的手机号码
    private function getallphonenum($client_accounts) {
        $mBusinessphone = ClsFactory::Create ( 'Model.mBusinessphone' );
        $phonenums = $mBusinessphone->changeuidtophonenum ( $client_accounts );
        unset ( $mBusinessphone );
        unset ( $client_accounts );
        return ! empty ( $phonenums ) ? $phonenums : false;
    }
    
    private function error_info($message) {
        error_log(date('Y-m-d H:i:s').'  error_log:' .__FILE__ ."：$message\r\n", 3, dirname(dirname(__FILE__)) . '/Errorlog/smserror.log');
    }
}

$send = new SmssendautoAction();
$send->sendmultiple();