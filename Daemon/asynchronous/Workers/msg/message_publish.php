<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Daemon.inc.php');

class message_publish extends BackGroundController{

    public function run($job, &$log) {
        
        $workload = $job->workload();
        $workload = unserialize($workload);
        $msg_type = $workload['msg_type'];
        $id = $workload['id'];
        
        
        switch ($msg_type){
            case 'notice':
                $mMsgNoticeList = ClsFactory::Create("RModel.Msg.mStringNotice");
                $mMsgNoticeList->publishMsg($id, $msg_type);
            break;
            case 'homework':
                
                $mMsgHomeworkList = ClsFactory::Create("RModel.Msg.mStringHomework");
                $mMsgHomeworkList->publishMsg($id, $msg_type);
            break;
            case 'exam':
                $mMsgExamList = ClsFactory::Create("RModel.Msg.mStringExam");
                $mMsgExamList->publishMsg($id, $msg_type);
            break;
            case 'comments':
                $mMsgCommentskList = ClsFactory::Create("RModel.Msg.mStringComments");
                $mMsgCommentskList->publishMsg($id, $msg_type);
            break;
            case 'req':
                $mMsgRequestList = ClsFactory::Create("RModel.Msg.mStringRequest");
                $mMsgRequestList->publishMsg($id, $msg_type);
            break;
            case 'res':
                $mMsgRequestList = ClsFactory::Create("RModel.Msg.mStringResponse");
                $mMsgRequestList->publishMsg($id, $msg_type);
            break;
            case 'privatemsg':
                $mStringPrivateMsg = ClsFactory::Create("RModel.Msg.mStringPrivateMsg");
                $mStringPrivateMsg->publishMsg($id, $msg_type);
            break;
        }
        
        file_put_contents('/tmp/gearman-test.log', date('Y-m-d H:i:s') .':' . 'msging' . "\n", FILE_APPEND );
    
        $log[] = "Success";
    
        return true;

    }

}


?>