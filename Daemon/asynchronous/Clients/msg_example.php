<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');

$uids = array('56067742', '11070004', '95469975');

print_r($uids);
//初始化在线用户

$mUserVm = ClsFactory::Create('RModel.mUserVm');
$mLiveUsr = ClsFactory::Create('RModel.Common.mSetLiveUser');

    foreach ($uids as $uid) {
        $mUserVm->init_user_data($uid);
        $mLiveUsr->ping($uid);
    }
    
    $notice_id = get_test_data_notice(); 
    $msg_type = 'notice';    
    $mMsgNoticeList = ClsFactory::Create("RModel.Msg.mStringNotice");
    $mMsgNoticeList->publishMsg($notice_id, $msg_type);
    del_test_data_notice($notice_id);
        
    
    $homework_id = get_test_data_homework();
    $msg_type = 'homework';
    $mMsgHomeworkList = ClsFactory::Create("RModel.Msg.mStringHomework");
    $mMsgHomeworkList->publishMsg($homework_id, $msg_type);
    del_test_data_homework($homework_id);
    
    $exam_id = get_test_data_exam();
    $msg_type = 'exam';  
    $mMsgExamList = ClsFactory::Create("RModel.Msg.mStringExam");
    $mMsgExamList->publishMsg($exam_id, $msg_type); 
    del_test_data_exam($exam_id);

    $comments_id = get_test_data_comments();
    $msg_type = 'comments';
    $mMsgCommentskList = ClsFactory::Create("RModel.Msg.mStringComments");
    $mMsgCommentskList->publishMsg($comments_id, $msg_type); 
    del_test_data_comments($comments_id);
    
    $req_id = get_test_data_req();
    $msg_type = 'req';
    $mMsgRequestList = ClsFactory::Create("RModel.Msg.mStringRequest");
    $mMsgRequestList->publishMsg($req_id, $msg_type); 
    del_test_data_req($req_id);
    
    $res_id = get_test_data_res();
    $msg_type = 'res';
    $mMsgRequestList = ClsFactory::Create("RModel.Msg.mStringResponse");
    $mMsgRequestList->publishMsg($res_id, $msg_type);    
    del_test_data_res($res_id);    
        

//发消息


//获取测试数据:

function get_test_data_notice() {
    
    $datas = array( 'notice_id'		 => 0,
                    'class_code'     => '146',
                    'notice_title'   => 'test_data_notic',
                    'notice_content' => 'test_data_notice content',
                   	'add_account'	 => '56067742',
                    'add_time'		 => time(),
                    'is_sms'		 => 0
                   );
     $Notice_m = ClsFactory::Create('Model.ClassNotice.mClassNotice');
     $notice_id = $Notice_m->addClassNotice($datas, true);
     return $notice_id;
}

function del_test_data_notice($notice_id) {
     $Notice_m = ClsFactory::Create('Model.ClassNotice.mClassNotice');
     return $Notice_m->delClassNotice($notice_id);    
}


function get_test_data_homework() {
    
    $class_code = 146;
    $datas = array( 'homework_id'		 => 0,
                    'class_code'         => $class_code,
                    'subject_id'  	     => 897,
                    'add_account'        => 56067742,
                   	'add_time'	         => time(),
                    'upd_account'		 => time(),
                    'upd_time'		     => time(),
                    'end_time'		     => time(),
                    'attachment'		 => '1111',
                    'content'		     => 'test_data_time',
    				'is_sms'		     => 0,
    				'accepters'		     => '全班同学'
                   );

    $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
    $homework_id = $mClassHomework->addHomework($datas,true);
     
    $Class_m = ClsFactory::Create("Model.mClientClass");
    $client_class_list = $Class_m->getClientClassByClassCode($class_code);
    
    $client_class_list = $client_class_list[$class_code];
    $to_account = array();
    if(!empty($client_class_list)){
        foreach($client_class_list as $client_class_info) {
            $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
        }
    }
    
    $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
    $dataarr = array();
    foreach($to_account as $val) {
        
        $dataarr[]= array(
            'homework_id' => $homework_id,
            'client_account' => $val,
            'add_time' => time()
        );
     }      

        
    $resault_classhomework_send = $mClassHomeworkSend->addHomeworkSend($dataarr);
      
    return $homework_id;
}

function del_test_data_homework($homework_id) {
     $mClassHomework = ClsFactory::Create('Model.ClassHomework.mClassHomework');
     $mClassHomework->delHomework($homework_id);
     
     
     $mClassHomeworkSend = ClsFactory::Create('Model.ClassHomework.mClassHomeworkSend');
     
     $datas = $mClassHomeworkSend->getHomeworkSendByhomeworkid($homework_id);
     
     $datas = reset($datas);
     foreach ($datas as $key => $val) {
          $mClassHomeworkSend->delHomeworkSend($val['id']);
     }     

     return true; 
}


function get_test_data_exam() {
    
    $class_code = 146;
    
    
    $datas= array(
                    'class_code'=>146,
            		'subject_id'=>900,
            		'exam_name'=>'测试考试',
            		'exam_time'=>time(),
            		'add_account'=>11070004,
            		'add_time'=>time(),
            		'upd_account'=>11070004,
            		'upd_time'=>time(),
            		'exam_well'=>90,
            		'exam_good'=>70,
            		'exam_bad'=>40,
            		'is_published'=>1,
            		'is_sms'=>0,
                   );
    $Exam_m = ClsFactory::Create('Model.mClassExam');
    $exam_id = $Exam_m->addClassExam($datas, true);
    
    $Class_m = ClsFactory::Create("Model.mClientClass");
    $client_class_list = $Class_m->getClientClassByClassCode($class_code);
    
    $client_class_list = $client_class_list[$class_code];
    $to_account = array();
    if(!empty($client_class_list)){
        foreach($client_class_list as $client_class_info) {
            $to_account[$client_class_info['client_account']] = $client_class_info['client_account'];
        }
    }
    
    $datas = array();
    foreach($to_account as $uid){
        $datas[] = array(
            'client_account'=>$uid,
            'exam_id'=>$exam_id,
            'exam_score'=>rand(20,100),
            'score_py'=>'',
            'add_time'=>time(),
            'add_account'=>11070004,
            'upd_time'=>time(),
            'upd_account'=>11070004,
            'is_join'=>1,
        	'is_sms'=>0,
        );
    }
    
    $mClassExamScore = ClsFactory::Create("Model.mClassExamScore");
    $mClassExamScore->addBatClassExamScore($datas);
    
    return $exam_id;
}

function del_test_data_exam($exam_id) {
     $Exam_m = ClsFactory::Create('Model.mClassExam');
     $Exam_m->delClassExam($exam_id);   

     $mClassExamScore = ClsFactory::Create("Model.mClassExamScore");
     $mClassExamScore->delBatClassExamScoreByExamId($exam_id);
     return true;
}



function get_test_data_comments(){
    $uid = 95469975;//当前登录用户
    $dataarr = array(
        'up_id'=>5,
        'feed_id'=>1,
        'content'=>'测试评论',
        'client_account'=>11070004,
        'add_time'=>time(),
    	'level'=>2,
    );
    
    $mFeedComments = ClsFactory::Create("Model.Feed.mFeedComments");
    $comments_id = $mFeedComments->addFeedComments($dataarr,true);
    return $comments_id;
}

function del_test_data_comments($comments_id){
    $mFeedComments = ClsFactory::Create("Model.Feed.mFeedComments");
    return $mFeedComments->delFeedComments($comments_id);
}


function get_test_data_req(){
    $dataarr = array(
		'content' =>'加好友测试',
		'to_account'=>95469975,
		'add_account'=>11070004,
		'add_time'=>time(),
    );
    
    $mMsgRequire = ClsFactory::Create("Model.Message.mMsgRequire");
    return $mMsgRequire->addMsgRequire($dataarr,true);
    
}

function del_test_data_req($req_id){
    $mMsgRequire = ClsFactory::Create("Model.Message.mMsgRequire");
    return $mMsgRequire->delMsgRequire($req_id);
}

function get_test_data_res(){
    $dataarr = array(
		'content' =>'回复加好友测试',
		'to_account'=>95469975,
		'add_account'=>11070004,
		'add_time'=>time(),
    );
    
    $mMsgResponse = ClsFactory::Create("Model.Message.mMsgResponse");
    return $mMsgResponse->addMsgResponse($dataarr,true);
    
}

function del_test_data_res($req_id){
    $mMsgResponse = ClsFactory::Create("Model.Message.mMsgResponse");
    return $mMsgResponse->delMsgResponse($req_id);
}



    

