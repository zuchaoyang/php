<?php
//todolist 短信部分需要集中整理
class ViewAction extends SnsController {
    //成绩加密密钥
    protected $rand_secret_key = "class_exam_score_key_5381";
    
    public function __construct() {
        parent::__construct ();
    }
    
    public function checkFunc() {
        //dump($this->sendExamSmsSingle(30587)); //修改成绩
    }
    
    public function index() {
        $exam_id  = $this->objInput->getStr('exam_id');
        
        if(empty($exam_id)) {
            $this->showError('考试信息不存在!', '/Sns/ClassExam/Exam/index');   
        }
        
        //获取考试的基本信息
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_list = $mClassExam->getClassExamById($exam_id);
        $class_exam = & $class_exam_list[$exam_id];
        //考试不存在跳转到列表页面
        if(empty($class_exam)) {
            $this->showError('考试信息不存在!', '/Sns/ClassExam/Exam/index');
        }
        
        //判断用户是否有权限查看
        $class_code_list = array_keys($this->user['class_info']);
        if(!in_array($class_exam['class_code'], (array)$class_code_list)) {
            //todolist
            $this->showError('您暂时没有权限查看该考试信息!', '/Sns/ClassExam/Exam/index/class_code/' . reset($class_code_list));
        }
        
        //转换和解析考试的相关信息
        $class_exam = $this->parseExamInfo($class_exam);
   
        //获取考试的成绩信息
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $exam_score_arr = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $exam_score_list = & $exam_score_arr[$exam_id];
        //获取班级学生列表（按排序id 排序）
        import('@.Control.Api.Class.MemberApi');
        $member_obj = new MemberApi();
        $student_list = $member_obj->getStuList($class_exam['class_code']);
         //整理考试成绩的相关信息,包括成绩的编辑权限
        $exam_score_list = $this->formatExamScore($student_list, $exam_score_list, $class_exam['can_edit']);
        //统计学生的信息
        $stat = $this->statClassExamScore($exam_score_list, $class_exam['exam_good'], $class_exam['exam_bad']);
        
        $this->assign('exam_id', $exam_id);
        $this->assign('class_exam', $class_exam);
        $this->assign('exam_score_list', $exam_score_list);
        $this->assign('stat', $stat);
        
        $this->display('exam_view_edit');
    }
    
    /**
     * 远程修改考试成绩信息
     */
    public function updateExamScoreAjax() {
        $score_id   = $this->objInput->postStr('score_id');
        $score_py   = $this->objInput->postStr('score_py');
        $exam_score = $this->objInput->postStr('exam_score');
        $is_sms     = $this->objInput->postInt('is_sms');
        $secret_key = $this->objInput->postStr('secret_key');
        
        //权限判断，避免非法操作，通过MD5加密机制
        if($secret_key != $this->getSecretKey($score_id)) {
            $this->ajaxReturn(null, '您暂时没有权限修改改成绩信息!', -1, 'json');
        }
        
        //成绩不填表示未参加
        $is_join = ($exam_score === '') ? 0 : 1;
        //分数不能小于0
        $exam_score = max(intval($exam_score), 0);
        
        $score_datas = array(
            'exam_score'  => $exam_score,
            'score_py'	  => $score_py,
            'upd_time'	  => time(),
            'upd_account' => $this->user['client_account'],
            'is_join'	  => $is_join,
            'is_sms'	  => empty($is_sms) ? 0 : 1
        );
        
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        if(!$mClassExamScore->modifyClassExamScore($score_datas, $score_id)) {
            $this->ajaxReturn(null, '考试成绩信息修改失败!', -1, 'json');
        }
        
        //根据 is_sms 判断是否发送短信
        if(!empty($is_sms)) {
            $is_success = $this->sendExamSmsSingle($score_id);
            if(empty($is_success)) {
                 $this->ajaxReturn(null, '短信通知家长失败!您可以选择补发短信处理。', -1, 'json');
            }
        } else {
            //只修改成绩不发送短信的时候 ，更新考试表让用户可以补发短信
            //获取成绩信息
            $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
            $score_arr = $mClassExamScore->getClassExamScoreById($score_id);
            $score_info = & $score_arr[$score_id];
            //获取考试信息
            $mClassExam = ClsFactory::Create('Model.mClassExam');
            $class_exam_arr = $mClassExam->getClassExamById($score_info['exam_id']);
            $class_exam_info = & $class_exam_arr[$score_info['exam_id']];
            if($class_exam_info['is_sms'] != PORTION_SMS) {
                $data = array("is_sms" => PORTION_SMS); //改为补发短信（原来是未发状态也没有影响）
                $mClassExam->modifyClassExam($data, $class_exam_info['exam_id']);
            }
        }
        
        $this->ajaxReturn(null, '考试成绩信息修改成功!', 1, 'json');
    }
    
    /**
     * ajax 补发成绩短信
     */
    public function examSmsReissueAjax() {
        $exam_id = $this->objInput->getStr('exam_id');
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExamById($exam_id); 
        $exam_info = & $exam_list[$exam_id];
        
        if (empty($exam_info)) {
            $this->ajaxReturn(null, '考试信息不存在!', -1, 'json');
        }
        
        //判断权限是否可以补发短信
        $can_send_sms = $this->canSendSms($exam_id, $exam_info);
        if (empty($can_send_sms)) {
            $this->ajaxReturn(null, '您暂时没有权限补发短信!', -1, 'json');
        }
        
        $is_success = $this->sendExamSmsReissue($exam_id);
        if (empty($is_success)) {
             $this->ajaxReturn(null, '系统繁忙请稍后重试!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '恭喜，短信补发完成!', 1, 'json');
    }

        /**
     * ajax 发送成绩短信
     */
    public function examSmsAllAjax() {
        $exam_id = $this->objInput->getStr('exam_id');
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExamById($exam_id); 
        $exam_info = & $exam_list[$exam_id];
        
        if (empty($exam_info)) {
            $this->ajaxReturn(null, '考试信息不存在!', -1, 'json');
        }
        
        //判断权限是否可以补发短信
        $can_send_sms = $this->canSendSms($exam_id, $exam_info);
        if (empty($can_send_sms)) {
            $this->ajaxReturn(null, '您暂时没有权限发送短信!', -1, 'json');
        }
        
        $is_success = $this->sendExamSmsAll($exam_id);
        if (empty($is_success)) {
             $this->ajaxReturn(null, '系统繁忙请稍后重试!', -2, 'json');
        }
        
        $this->ajaxReturn(null, '短信发送成功！', 1, 'json');
    } 
    
    /**
     * 获取考试的统计信息
     */
    public function getExamStatAjax() {
        $exam_id = $this->objInput->getStr('exam_id');
        
         //获取考试的基本信息
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_list = $mClassExam->getClassExamById($exam_id);
        $class_exam = & $class_exam_list[$exam_id];
        if(empty($class_exam)) {
            $this->ajaxReturn(null, '考试信息不存在!', -1, 'json');
        }
        
         //获取考试的成绩信息
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $exam_score_arr = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $exam_score_list = & $exam_score_arr[$exam_id];
        
        $stat = $this->statClassExamScore($exam_score_list, $class_exam['exam_good'], $class_exam['exam_bad']);
        
        $this->ajaxReturn($stat, '获取成功!', 1, 'json');
    }
    
    /**
     * 发送短信
     */
    public function sendExamSmsAllAjax() {
        $exam_id = $this->objInput->getStr('exam_id');
        //获取考试的基本信息
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_list = $mClassExam->getClassExamById($exam_id);
        $class_exam = & $class_exam_list[$exam_id];
        if(empty($class_exam)) {
            $this->ajaxReturn(null, '考试信息不存在!', -1, 'json');
        }
        
        $class_code = $class_exam['class_code'];
        //判断当前用户是否能够编辑成绩信息
        $current_client_class = $this->getUserClientClass($class_code);
        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN, 
            TEACHER_CLASS_ROLE_CLASSBOTH
        );
        if($class_exam['add_account'] != $this->user['client_account'] && in_array($current_client_class['teacher_class_role'], $class_admin_list)) {
            $this->showError('您暂时没有权限发送短信!', '/Sns/ClassExam/Exam/index/class_code/' . $class_code);
        }
        
         //获取考试的成绩信息
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $exam_score_arr = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $exam_score_list = & $exam_score_arr[$exam_id];
        
        $stat = $this->statClassExamScore($exam_score_list, $class_exam['exam_good'], $class_exam['exam_bad']);
        
        $this->ajaxReturn($stat, '获取成功!', 1, 'json');
    }
    
   /**
     * 转换考试相关的信息
     * @param $exam_info
     */
    private function parseExamInfo($class_exam) {
        if(empty($class_exam)) {
            return false;
        }
        
        //考试相关的数据转换
        $class_exam['exam_time_format'] = date('Y-m-d', $class_exam['exam_time']);
        
        $class_code = $class_exam['class_code'];
        //判断当前用户是否能够编辑成绩信息
        $can_edit = false;
        $current_client_class = $this->getUserClientClass($class_code);

        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN, 
            TEACHER_CLASS_ROLE_CLASSBOTH
        );
        if($class_exam['add_account'] == $this->user['client_account'] || in_array($current_client_class['teacher_class_role'], $class_admin_list)) {
            $can_edit = true;
        }
        $class_exam['can_edit'] = $can_edit;
        
        //获取班级的基本信息
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $class_info_list = $mClassInfo->getClassInfoBaseById($class_code);
        $class_info = & $class_info_list[$class_code];
        
        $class_exam['class_name'] = $class_info['grade_id_name'] . $class_info['class_name'];
        
        //获取科目的基本信息
        $subject_id = $class_exam['subject_id'];
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subject_info_list = $mSubjectInfo->getSubjectInfoById($subject_id);
        $subject_info = & $subject_info_list[$subject_id];
        
        $class_exam['subject_name'] = isset($subject_info['subject_name']) ? $subject_info['subject_name'] : '暂无';
        
        return $class_exam;
    }
    
    /**
     * 补全和格式化考试信息
     * @param $exam_score_list
     */
    private function formatExamScore($student_list, $exam_score_list, $can_edit = false) {
        if(empty($exam_score_list)) {
            return false;
        }
         //追加学生的姓名信息
        $student_uids = array();
        foreach($exam_score_list as $score) {
            $student_uids[] = $score['client_account'];
        }

        //拼装用户姓名,并加密成绩信息
        $num_id = 1;
        foreach($exam_score_list as $score_id=>$score) {
            $client_account = $score['client_account'];
            if(isset($student_list[$client_account])) {
                $score['client_name'] = $student_list[$client_account]['client_name'];
            } else {
                $score['client_name'] = '--';
            }
            $score['num_id'] = $num_id++;
            //成绩加密
            if($can_edit) {
                $score['secret_key'] = $this->getSecretKey($score_id);
            }
            $exam_score_list[$score_id] = $score;
        }
        
        return $exam_score_list;
    }
    
    /**
     * 获取用户当前班级的情况
     * @param $class_code
     */
    private function getUserClientClass($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $client_class_list = (array)$this->user['client_class'];
        foreach($client_class_list as $client_class) {
            if($client_class['class_code'] == $class_code) {
                return $client_class;
            }
        }
        
        return false;
    }
    
    /**
     * 考试成绩加密函数
     * @param $score_id
     */
    private function getSecretKey($score_id) {
        return md5($score_id . $this->user['client_account'] . $this->rand_secret_key);
    }

    /*************************************************************************************
    * public 辅助函数
    ************************************************************************************/
    
    /**
     * 单条发送成绩短信 主要是修改的时候
     * 发送失败注意修改考试表
     * 
     */
    private function sendExamSmsSingle($score_id) {
        if(empty($score_id)) {
            return false;
        }
        
        //获取成绩信息
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $score_arr = $mClassExamScore->getClassExamScoreById($score_id);
        $score_info = & $score_arr[$score_id];
        if (empty($score_info)) {
            return false;
        }
        
        //获取考试信息
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_arr = $mClassExam->getClassExamById($score_info['exam_id']);
        $class_exam_info = & $class_exam_arr[$score_info['exam_id']];
        if(empty($class_exam_info)) {
            return false;
        }
        
        //获取科目的基本信息
        $subject_id = $class_exam_info['subject_id'];
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $subject_info_list = $mSubjectInfo->getSubjectInfoById($subject_id);
        $subject_info = & $subject_info_list[$subject_id];
        $class_exam_info['subject_name'] = $subject_info['subject_name'];  //考试详情追加是科目名称用于发短信
        
        // 考试的成绩列表
        $exam_score_list = $mClassExamScore->getClassExamScoreByExamId($class_exam_info['exam_id']);
        $exam_score_list = & $exam_score_list[$class_exam_info['exam_id']];
        
        $exam_stat = $this->statClassExamScore($exam_score_list, $class_exam_info['exam_good'], $class_exam_info['exam_bad']);
        
        //正式开始发送短信
        list($failure_arr, $sucesss_arr) = $this->sendExamSms(array($score_info['score_id']=>$score_info), $class_exam_info, $exam_stat);
       
        if (!empty($failure_arr)) {
            //回滚成绩数据
            $data = array("is_sms" => NO_SMS);
            $mClassExamScore->modifyClassExamScore($data, $score_id);
            
            //回滚考试表短信发送状态 使用户可以通过补发短信来修正失败操作
            if($class_exam_info['is_sms'] != PORTION_SMS) {
                $data = array("is_sms" => PORTION_SMS);
                $mClassExam->modifyClassExam($data, $class_exam_info['exam_id']);
            }
            
            return false;
        }

        //成功 考试发送信息 只在状态为 0 的时候把状态改为 2 其他时候不变
        if($class_exam_info['is_sms'] == NO_SMS) {
            $data = array("is_sms" => PORTION_SMS);
           $mClassExam->modifyClassExam($data, $class_exam_info['exam_id']);
        }
            
        return true;  //修改成功
    }
    
    /**
     * 补发短信修改过的数据进行短信补发
     * 注意根据考试表和成绩表的is_sms 字段判断是否需要补发短信
     * @param $exam_id
     * @return boolean 是否成功 对成功和失败的数据自动进行处理
     * 1 成功，失败的更改状态 
     * 2 根据实际情况对 考试表数据进行更新
     */
    private function sendExamSmsReissue($exam_id) {
        if(empty($exam_id)) {
            return false;
        }

        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_arr = $mClassExam->getClassExamById($exam_id);
        $class_exam_info = & $class_exam_arr[$exam_id];
        
        // 验证是否需要补发短信
        if (empty($class_exam_info) || $class_exam_info['is_sms'] != PORTION_SMS) {
            return false;
        }
        
        //获取需要补发的成绩列表
        $reissue_score_list = $modify_datas = array();
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $exam_score_list = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $exam_score_list = & $exam_score_list[$exam_id];
        if(!empty($exam_score_list)) {
            foreach ($exam_score_list as $score_id=>$score_info) {
               //过滤掉已经发送过短信的成绩信息 
               if ($score_info['is_sms'] != NO_SMS) {
                   continue;
               }
               //需要补发的成绩
               $reissue_score_list[$score_id] = $score_info;
               //拼装批量修改短信发送状态的数组
               $modify_datas[$score_id] = array(
                   'is_sms'     => IS_SMS,
                   'upd_time'   => time(),
                   'upd_account' => $this->user['client_account']
                   
               );
            }
        }
       
        // 如果没有要补发的短信 认为补发成功更改考试表数据
        if(empty($exam_score_list) || empty($reissue_score_list)) {
            $mClassExam->modifyClassExam(array('is_sms'=>IS_SMS), $exam_id);
            return true;
        }

        //考试成绩统计
        $exam_stat = $this->statClassExamScore($exam_score_list, $class_exam_info['exam_good'], $class_exam_info['exam_bad']);
        //发送短信
        list($failure_arr, $sucesss_arr) = $this->sendExamSms($reissue_score_list, $class_exam_info, $exam_stat);

        //先全部默认发送成功，在对失败的数据进行处理
        if (!$mClassExamScore->modeifyBatExamScore($modify_datas, $exam_id)) {
            return false;
        }
        //对失败数据进行处理
        if (!empty($failure_arr)) {
            foreach ($failure_arr as $account=>$failure_info) {
                $failure_datas[$failure_info['score_id']] = array(
                   'is_sms'     => NO_SMS,
                   'upd_time'   => time(),
                   'upd_accoun' => $this->user['client_account']
                );
            }
            $mClassExamScore->modeifyBatExamScore($failure_datas, $exam_id);
            //修改考试信息
            $mClassExam->modifyClassExam(array('is_sms'=> PORTION_SMS), $exam_id);
            return false;
        }
        
        //成功修改考试信息
        $mClassExam->modifyClassExam(array('is_sms'=> IS_SMS), $exam_id);
        
        return true;
    }
    
    /**
     * 发送短信
     * 
     * @param $exam_id
     * @return boolean 成功ture 失败false
     */
    private function sendExamSmsAll($exam_id) {
        if (empty($exam_id)) {
            return false;
        }

        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $class_exam_arr = $mClassExam->getClassExamById($exam_id);
        $class_exam_info = & $class_exam_arr[$exam_id];
        // 验证是否可以发短信
        if (empty($class_exam_info) || $class_exam_info['is_sms'] != NO_SMS) {
            return false;
        }
        
        //获取需要发送的成绩列表
        $new_score_list = $modify_datas = array();
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $score_list = $mClassExamScore->getClassExamScoreByExamId($exam_id);
        $score_list = & $score_list[$exam_id];
        if(!empty($score_list)) {
            foreach ($score_list as $score_id=>$score_info) {
               //过滤掉已经发送过数据 垃圾数据
               if ($score_info['is_sms'] != NO_SMS) {
                   continue;
               }
               //需要发送的成绩
               $new_score_list[$score_id] = $score_info;
               //拼装批量修改短信发送状态的数组
               $modify_datas[$score_id] = array(
                   'is_sms'     => IS_SMS,
                   'upd_time'   => time(),
                   'upd_accoun' => $this->user['client_account']
               );
            }
        }
        
        // 如果没有要发送的短信 认为补发成功更改考试表数据
        if(empty($score_list) || empty($new_score_list)) {
            $mClassExam->modifyClassExam(array('is_sms'=>IS_SMS), $exam_id);
            return true;
        }
        
        //考试成绩统计
        $exam_stat = $this->statClassExamScore($score_list, $class_exam_info['exam_good'], $class_exam_info['exam_bad']);
        //发送短信
        list($failure_arr, $sucesss_arr) = $this->sendExamSms($new_score_list, $class_exam_info, $exam_stat);
        
        //先全部默认发送成功，在对失败的数据进行处理
        if (!$mClassExamScore->modeifyBatExamScore($modify_datas, $exam_id)) {
            return false;
        }
        //对失败数据进行处理
        if (!empty($failure_arr)) {
            foreach ($failure_arr as $account=>$failure_info) {
                $failure_datas[$failure_info['score_id']] = array(
                   'client_account' => $account,
                   'is_sms'     => NO_SMS,
                   'upd_time'   => time(),
                   'upd_accoun' => $this->user['client_account']
                );
            }
            $mClassExamScore->modeifyBatExamScore($failure_datas, $exam_id);
            //修改考试信息
            $mClassExam->modifyClassExam(array('is_sms'=> PORTION_SMS), $exam_id);
            return false;
        }
        
        //成功修改考试信息
        $mClassExam->modifyClassExam(array('is_sms'=> IS_SMS), $exam_id);
        
        return true;
    }
    
    /**
     * 发送短信信息
     *
     * @param $exam_score_datas 待发送的学生成绩
     * @param $exam_info 考试信息
     * @param $exam_stat 考试统计
     *
     * @return array($failure_arr, $sucesss_arr);
     * $failure_arr = array('学生账号1'=>array('client_name'=>张三,'score_id'=>2856), '学生账号2'...);
     * $sucesss_arr = array('学生账号1'=>array('client_name'=>张三,'score_id'=>2856), '学生账号2'...);
     */
    private function sendExamSms($exam_score_datas, $exam_info, $exam_stat) {
        $failure_arr = $sucesss_arr = array();
        if (empty($exam_score_datas) || empty($exam_info) || empty($exam_stat)) {
            return array($failure_arr, $sucesss_arr);
        }

        //格式化成绩信息方便取出数据
        foreach ($exam_score_datas as $key=>$exam_score_info) {
            $new_score_list[$exam_score_info['client_account']] = $exam_score_info;
        }

        //获取班级学生列表（主要是为了获取学生姓名）;
        import('@.Control.Api.Class.MemberApi');
        $member_obj = new MemberApi();
        $student_list = $member_obj->getStuList($exam_info['class_code']);
        //获取学生家长关系和家长对应手机号
        $parent_phone_list = $this->getParentPhoneByStudetAccount(array_keys($new_score_list));
        //根据学生成绩和考试统计信息拼装短信内容数据并发送短信
        if(empty($parent_phone_list)) {
            return array($failure_arr, $sucesss_arr);  //全部没有绑定手机号 默认全部发送成功
        }

        //循环发送短信
        $school_info = reset($this->user['school_info']);
        import('@.Control.Api.Smssend.Smssendapi');
        $smssendapi_obj = new Smssendapi();
        $operationStrategy = $school_info['operation_strategy'];

        foreach($parent_phone_list as $student_account=>$phone_arr) {
            $student_name = $student_list[$student_account]['client_name'];
            
            $score_id     = $new_score_list[$student_account]['score_id'];
            $score_py     = $new_score_list[$student_account]['score_py'];
            $subject_name = $exam_info['subject_name'];
            $exam_name    = $exam_info['exam_name'];
            $ave_score    = round($exam_stat['avg_score'], 2);  //保证不会出现 12.00 或者 12.30 的情况
            $max_score    = round($exam_stat['top_score'], 2);
            $min_score    = round($exam_stat['lower_score'], 2);
            
            //成绩处理 为参加的成绩设成未参加
            $exam_score   = round($new_score_list[$student_account]['exam_score'], 2);
            $exam_score   = $new_score_list[$student_account]['is_join'] == 1 ? $exam_score : '未参加';
            
            $message = sprintf(EXAM_SMS_TEMPLET, $student_name, $subject_name, $exam_name, $exam_score, $score_py, $ave_score, $max_score, $min_score);
			$addSmsSendResult = $smssendapi_obj->send($phone_arr, $message, $operationStrategy);
			if (empty($addSmsSendResult)) {
			    $failure_arr[$student_account] = array('client_name'=>$student_name, 'score_id'=>$score_id);
			} else {
			    $sucesss_arr[$student_account] = array('client_name'=>$student_name, 'score_id'=>$score_id);
			}
        }

        return array($failure_arr, $sucesss_arr);
    }

    /**
     * 通过学生账号获取学生家长和家长绑定的手机
     * 并且过滤掉没有绑定手机的学生账号
     * @param $student_account_arr 学生账号 格式 array('学生账号1'，'学生账号2'....)
     * @return 学生家长关系和家长绑定的手机
     * array (
     * 	'学生账号1'=> array(
     * 				  		'家长账号1'=>绑定手机号1，
     * 						'家长账号2'=>绑定手机号2
     * 				  )
     * '学生账号2'=> array(
     * 				  		'家长账号3'=>绑定手机号3，
     * 						'家长账号4'=>绑定手机号4
     * 				  );
     * )
     */
    private function getParentPhoneByStudetAccount($student_arr) {
        if (empty($student_arr) || !is_array($student_arr)) {
            return false;
        }

        //通过family_relation表获得家长的账号信息。 有学生就一定有家长不在验证$familyRelations 是否空
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
		$familyRelations = $mFamilyRelation->getFamilyRelationByUid($student_arr);
		$parentAccounts = $family_list = array();
		foreach($familyRelations as $student_account=>$parent_arr) {
		    foreach($parent_arr as $key=>$parent_info) {
		        $parentAccounts[] = $parent_info['family_account'];
		        $family_list[$student_account][] = $parent_info['family_account'];
		    }
		}

		$mBusinesphone  = ClsFactory::Create('Model.mBusinessphone');
		$phone_list = $mBusinesphone->getbusinessphonebyalias_id($parentAccounts);//通过家长账号获得business_phones
        if(empty($phone_list)) {
		    return false;
		}

		//过滤掉 没有业务的手机号码
        foreach ($phone_list as $uid=>$phoneInfo) {
    		if ($phoneInfo['business_enable'] != BUSINESS_ENABLE_YES) {
    			unset($phone_list[$uid]);
    	   }
    	}

    	//拼装家长手机号，过滤掉家长没有绑定手机号的学生
    	$parent_phone_list = array();
        if(!empty($phone_list)) {
        	foreach($student_arr as $student_account) {
        	    $parent_account1 = $family_list[$student_account][0];
        	    $parent_account2 = $family_list[$student_account][1];

                if (isset($phone_list[$parent_account1])) {
            	    $parent_phone_list[$student_account][$parent_account1] = $phone_list[$parent_account1]['phone_id'];
                }
        	    if (isset($phone_list[$parent_account2])) {
            	    $parent_phone_list[$student_account][$parent_account2] = $phone_list[$parent_account2]['phone_id'];
                }
        	}
		}

        return !empty($parent_phone_list) ? $parent_phone_list : false;
    }
    
   /**
     * 统计分析班级的成员列表信息
     * @param $exam_score_list 成绩列表
     * @param $exam_good 优秀分
     * @param $exam_bad  及格分
     * 
     * @return array  统计结果
     */
    private function statClassExamScore($exam_score_list, $exam_good, $exam_bad) {
        if(empty($exam_score_list)) {
            return false;
        }
        
        $join_nums = $unjoin_nums = $excellent_nums = $pass_nums = $total_score = 0;
        $score_list = array();
        foreach($exam_score_list as $exam_score) {
            if(!$exam_score['is_join']) {
                $unjoin_nums += 1;    //未参加人数
                continue;
            }
            
            $join_nums += 1;  //参加人数
            
            $score = max(0, intval($exam_score['exam_score'])); 
            
            $score_list[] = $score;    
            $total_score += $score;
            
            //优秀人数
            if($score >= $exam_good) {
               $excellent_nums += 1; 
            }
            //及格人数
            if($score >= $exam_bad) {
                $pass_nums += 1;
            }
        }

        return array(
            'join_nums' => $join_nums,
            'unjoin_nums' => $unjoin_nums,
            'avg_score' => number_format(floatval(1.0 * $total_score / $join_nums), 2),
            'top_score' => max($score_list),
            'lower_score' => min($score_list),
            'excellent_percent' => number_format(floatval(100.0 * $excellent_nums / $join_nums), 2) . '%',
            'pass_percent' => number_format(floatval(100.0 * $pass_nums / $join_nums), 2) . '%',
        );
    }
    
    /**
     * 判断当前用户是否具成绩短信的操作权限（发布，补发）
     *  1 班主任  2 谁添加的谁就有权限
     * @param $exam_id
     * @return boolean true 可以发送 false 不可以发送
     */
    private function canSendSms($exam_id, $exam_info = false) {
        if(empty($exam_id) && empty($exam_info)) {
            return false;
        }
        //跟据考试id 获取考试信息
        if (empty($exam_info)) {
            $mClassExam = ClsFactory::Create('Model.mClassExam');
            $exam_list = $mClassExam->getClassExamById($exam_id); 
            $exam_info = & $exam_list[$exam_id];
        }

        if(empty($exam_info)) {
            return false;
        }
        //是否是班主任
        $is_class_teacher = $this->isClassTeacher($exam_info['class_code']);
        
        return !empty($is_class_teacher) || ($this->user['client_account'] == $exam_info['add_account']) ? true : false;
    }
    
    
    /*
     * 判断老师是否是班主任
     */
    private function isClassTeacher($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $client_class = $this->getUserClientClass($class_code);
        $class_admin_list = array(
            TEACHER_CLASS_ROLE_CLASSADMIN,
            TEACHER_CLASS_ROLE_CLASSBOTH    
        );
        
        //$teacher_class_role 1 班主任 3班级主任兼老师
        return in_array($client_class['teacher_class_role'], $class_admin_list) ? true : false;
    }
    
}