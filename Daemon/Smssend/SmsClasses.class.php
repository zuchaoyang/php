<?php
header("Content-Type:text/html; charset=utf-8");
include_once(dirname(dirname(__FILE__)) . '/Daemon.inc.php');
class SmssendautoclassesAction  extends Controller {
    public function sendmultiple() {
        $mSendTmpSchool = ClsFactory::Create('Model.mSendTmpSchool');
        $schoolids_list = $mSendTmpSchool->getOneSendSchoolid();
        $schoolids_list = reset($schoolids_list);
        if(empty($schoolids_list)) {
            return false;  
        }
        $class_codes = $this->getClass_codes($schoolids_list['school_id']);
        if(empty($class_codes)) {
            $this->error_info("not get ClassCodes by school_id that is {$schoolids_list['school_id']}");
            $mSendTmpSchool->delSendSchoolIdsById($schoolids_list['id']);
            return false;
        }
        $datas_arr = array();
        $now_date = date('Y-m-d H:i:s', time());
        $i=0;
        foreach($class_codes as $keyclass=>$class_code) {
            $datas_arr[$i++] = array(
                'class_code' => $class_code,
                'send_tmp_id' => $schoolids_list['send_tmp_id'],
                'operation_strategy' => $schoolids_list['operation_strategy'],
                'add_date' => $now_date,
            );
        }
        
        $mSendClassCode = ClsFactory::Create('Model.mSendTmpClass');
        $rs = $mSendClassCode->addSendClassCodes($datas_arr);
        
        if($rs) {
            $rs = $mSendTmpSchool->delSendSchoolIdsById($schoolids_list['id']);
            if(empty($rs)) {
                $this->error_info("\$mSendTmpSchool->delSendSchoolIdsById({$schoolids_list['id']});");
                return false;
            }
            
        }else{
            $this->error_info("\$mSendClassCode->addSendClassCodes(\$datas_arr)");
            return false;
        }
    }
    
    
    //通过学校id得到班级id
    protected function getClass_codes($school_id) {
        $mClassInfo = ClsFactory::Create ( 'Model.mClassInfo' );
        $Class_Infoes = $mClassInfo->getClassInfoBySchoolId ( $school_id );
        $class_codes = array ();
        foreach ( $Class_Infoes[$school_id] as $class_id => & $classinfo ) {
                $class_codes [] = $class_id;
        }
        unset ( $Class_Infoes );
        unset ( $classinfo );
        return ! empty ( $class_codes ) ? $class_codes : false;
    }
    private function error_info($message) {
        error_log(date('Y-m-d H:i:s').'  error_log:' .__FILE__ ."：$message\r\n", 3, dirname(dirname(__FILE__)) . '/Errorlog/smserror.log');
    }

}

$send = new SmssendautoclassesAction();
$send->sendmultiple();