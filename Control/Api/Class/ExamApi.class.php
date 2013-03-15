<?php
/*
 * 关于班级成绩功能的api
 * 调用路径 /Api/Class/Exam/{sendExamSmsAll,sendExamSmsReissue, sendExamSmsSingle }/class_code/696
 */
class ExamApi extends ApiController {
    /**
     * 固定函数
     */
    public function __construct() {
        parent::__construct();
    }    
    public function _initialize(){
		parent::_initialize();        
    }	

    public function checkFunc() {
       dump( $this->sendExamSmsAll(856));
    
    }
    /**
     * 发送考试成绩信息（sms）
     * @param $exam_id 考试id
     * @return 返回发送结果详情
     * 
     * data = array(
     * 	  'data'   => array($failure_arr, $success_arr),  //包含错误账号列表和失败账号列表
     * 	  'info'   => 提示信息（错误，失败）,
     *    'status' => 1 成功 -1 失败   //（失败包括 部分短信发送失败 参数错误等 具体错误信息 参考info）				
     * 	  
     * );
     * 
     * array($failure_arr, $sucesss_arr);  成功的用户id列表 和失败的用户id列表
     */
    public function sendExamSmsAll($exam_id) {
        $failure_arr = $success_arr = array();    //成功和失败都为空也算发送成功 默认发送成功
        if (empty($exam_id)) {
            return array('data'=>null, 'info'=>'考试不能为空', 'status'=>-1);
        }
        
        //获取考试信息并且验证是否需要发送短信
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_arr   = $mClassExam->getClassExamById($exam_id);
        $exam_info  = & $exam_arr[$exam_id];
        
        if (empty($exam_info)) {
            return array('data'=>null, 'info'=>'考试不存在或者已被删除！', 'status'=>-1);
        }
        //验证是否发送过短信了
        if ($exam_info['is_sms'] != NO_SMS) {
            return array('data'=>null, 'info'=>'该考试已经发送过短信了！', 'status'=>-1);
        }
        
        //获取考试成绩信息为发送短信做数据准备
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $score_list = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $score_list = & $score_list[$exam_id];

        //考试成绩统计为发送短信做准备
        import('@.Control.Api.Class.ExamImpl.ExamComment');
        $comObj = new ExamComment();
        $stat_arr = $comObj->statClassExamScore($score_list, $exam_info['exam_good'], $exam_info['exam_bad']);
        //拼装上考试信息
        $exam_info['stat'] =  $stat_arr;
        //发送短信返回发送结果
        list($failure_arr, $success_arr) = $comObj->sendExamSms($score_list, $exam_info);
        
        //处理失败的数据（ 发送短信时默认都是成功，成功的不在做处理）
        if(!empty($failure_arr)) {
                $student_names_str = implode(',', $failure_arr);
                //更新数据为部分发送成功为列表页面补发失败短信做准备
                foreach($failure_arr as $account=>$name) {
                    $score_datas[] = array(
                        'client_account' => $account,
                        'upd_account'    => $this->user['client_account'],
                    	'upd_time'       => time(),
                        'is_sms'		 => NO_SMS
                    );
                }
                // 修改成绩表
                $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
                $mClassExamScore->modeifyBatExamScore($score_datas, $exam_id);
                
                //修改考试信息表
                $exam_data = array(
                    'is_sms'      => 2,
                    'upd_account' => $this->user['client_account'],
                    'upd_time'    => time()
                );
                $mClassExam->modifyClassExam($exam_data, $exam_id);
                return array('data'=>null, 'info' => "学生 ($student_names_str)的家长短信通知家长失败!你可以选择补发短信处理。",'status' => -1);
        }
        
        return array('data'=>array($failure_arr, $success_arr), 'info'=>'发送成功', 'status'=>1);;
    }
    
    
    /**
     * 补发短信对与修改后的成绩进行补发 
     * @param $exam_id
     * @return 返回发送结果详情
     * 
     * data = array(
     * 	  'data'   => array($failure_arr, $success_arr),  //包含错误账号列表和失败账号列表
     * 	  'info'   => 提示信息（错误，失败）,
     *    'status' => 1 成功 -1 失败   //（失败包括 部分短信发送失败 参数错误等 具体错误信息 参考info）				
     * 	  
     * );
     * 
     * array($failure_arr, $sucesss_arr);  成功的用户id列表 和失败的用户id列表
     */
    private function sendExamSmsReissue($exam_id) {
    
    }
}