<?php
class PublishAction extends SnsController{

    public function __construct() {
        parent::__construct();
        $this->assign('uid', $this->user['client_account']);  //todo 屏蔽头部js 错误
    }

    public function checkFunc() {

        //家长绑定手机号的学生账号 51142722 ,14591376,71140188,15063828
        //$this->getParentPhoneByStudetAccount(array(99608848, 66215814,51142722 ,14591376,71140188,15063828));   //获取学生家长关系并且获取到家长对应的手机

        //获取班级学生列表
//        import('@.Control.Api.Class.MemberApi');
//        $member_obj = new MemberApi();
//        $student_list = $member_obj->getStuList($class_code);
//     
        
        //下载成绩发布模板
        //$this->downExamExcelTemplate();

        //导入学生成绩
        //$this->uploadExcelTemplateAjax();

        //获取考试信息
//        $mClassExam = ClsFactory::Create('Model.mClassExam');
//        $exam_info = $mClassExam->getClassExam(null, 'exam_id desc', 0, 1);
//        dump($exam_info);

        //获取成绩信息
//        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
//        $score_list = $mClassExamScore->getClassExamScoreByExamId(61);
//        dump($score_list);
        exit;
    }

   /*
     * 添加班级成绩显示模板
     * 1. 要兼容发布时的空白页
     * 2. 要兼容草稿的渲染页
     * 3. 要兼容修改页
     */
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $exam_id    = $this->objInput->getInt('exam_id');
        $is_draft   = $this->objInput->getInt('is_draft');
        
        //判断 是否是草稿读取并获取考试基本信息 和class_code
        if (!empty($is_draft) && !empty($exam_id)) {
            //验证用户是否具有修改或者读取草稿的权限
            $mClassExam = ClsFactory::Create('Model.mClassExam');
            $exam_list = $mClassExam->getClassExamById($exam_id);
            $exam_info = & $exam_list[$exam_id];
            $class_code = $this->checkoutClassCode($exam_info['class_code']);
            if(empty($exam_info) || $exam_info['add_account']!=$this->user['client_account'] || $exam_info['is_published'] != NO_PUBLISHED) {
                $this->showError('您读取的草稿不存在或者已被删除', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
            }
            
            $exam_info['exam_time'] = !empty($exam_info['exam_time']) ? date('Y-m-d', $exam_info['exam_time']) :  '';
        }
        
        //检查班级code
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }

        //判断用户是否有发布成绩的权限
        $can_publish = $this->canPublishExam($class_code);
        if(empty($can_publish)) {
            $this->showError('您没有权限发布成绩', '/Sns/ClassExam/Exam/index/class_code/' . $class_code);
        }
        
        //获取科目信息
        $subject_list = $this->getUserSubjectList($class_code);
        //获取班级学生列表
        import('@.Control.Api.Class.MemberApi');
        $member_obj = new MemberApi();
        $student_list = $member_obj->getStuList($class_code);
        
        //如果是超过读取或者修改 追加上成绩信息和考试信息
        if (!empty($exam_info)) {
            $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
            $score_list = $mClassExamScore->getClassExamScoreByExamId($exam_info['exam_id']);
            $score_list = & $score_list[$exam_id];
            //给学生列表附加上成绩信息（附加过程相当于校验了，这里不不在验证学生成绩了）
            if(!empty($score_list) && !empty($student_list)) {
                //格式化成绩数据 键变成用户账户方便后续操作
                $new_score_list = array();
                foreach ($score_list as $key=>$score) {
                    $new_score_list[$score['client_account']] = $score;
                }
                unset($score_list);
                
                foreach ($student_list as $account=>$student_info) {
                   if(!isset($new_score_list[$account])) {
                       continue;
                   }
                   //$is_join = $new_score_list[$account]['is_join']=== "" ? '' : intval($new_score_list[$account]['is_join']);
                   $student_info['exam_score'] = $new_score_list[$account]['exam_score'];
                   $student_info['score_py']   = $new_score_list[$account]['score_py'];
                   $student_info['is_join']    = $new_score_list[$account]['is_join'];

                   $student_list[$account] = $student_info;
                }
            }
        }
        
        //追加序号
        $num_id = 1;
        foreach($student_list as $account=>$student) {
            $student['num_id'] = $num_id++;
            $student_list[$account] = $student;
        }
        
        $class_info = $this->user['class_info'][$class_code];
        if(!empty($class_info)) {
            $class_name = $class_info['grade_id_name'].$class_info['class_name'];
        } else {
            $class_name = '暂无';
        }

        $this->assign('class_code', $class_code);
        $this->assign('class_name', $class_name);

        $this->assign('subject_list', $subject_list);
        $this->assign('student_list', $student_list);
        $this->assign('exam_info',    $exam_info);
        $this->assign('is_draft', $is_draft); //是否是草稿

        $this->display('exam_publish');
    }

    /**
     * 考试信息的发布
     */
    public function publish() {
        //接收参数 考试信息
        $class_code = $this->objInput->postStr('class_code');
        $subject_id = $this->objInput->postInt('subject_id');
        $exam_name  = $this->objInput->postStr('exam_name');
        $exam_time  = $this->objInput->postStr('exam_time');
        $exam_well  = $this->objInput->postInt('exam_well');
        $exam_good  = $this->objInput->postInt('exam_good');
        $exam_bad   = $this->objInput->postInt('exam_bad');
        $is_sms     = $this->objInput->postInt('is_sms');
        
        //接收成绩信息
        $exam_score_list = $this->objInput->postArr('exam_score_list');
        
        $is_sms    = empty($is_sms) ? 0 : 1;
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }
        //权限判断
        if(!$this->canPublishExam($class_code)){
            //没有权限发布跳转到成绩列表页面
            $this->showError('没有权限发布成绩', '/Sns/ClassExam/Exam/index/class_code/'. $class_code);
        }
        //参数校验
        if ($subject_id <= 0 || empty($exam_name) || empty($exam_well) || empty($exam_good) || empty($exam_bad)) {
            //参数错误跳回添加页码 ，以前填写的数据丢失
            $this->showError('数据填写不完整，请填写完整后重试!', '/Sns/ClassExam/Publish/index/class_code/'. $class_code);
        }
        //1. 判断接收到的对象是改班级的成员信息(涉及到集合求交集)
         /**
         * 学生姓名不从前台传过来
         * array(
         *     'uid1' =>array('uid'=>102445, 'exam_score' => 90,'score_py' => '学习认真，能很好的完成老师布置的作业','is_join' = 1),
         *     'uid2' =>array(.....),
         *     'uid3' =>array(.....),
         *     'uid4' =>array(.....),
         * )
         */
        $exam_score_list = $this->filterExamScoreList($exam_score_list, $class_code);
        if(empty($exam_score_list)) {
            $this->showError('没有学生不能发布成绩', '/Sns/ClassExam/Exam/index');
        }

        //保存考试基本信息
        $exam_datas = array(
            'class_code'   => $class_code,
            'subject_id'   => $subject_id,
            'exam_name'    => $exam_name,
            'exam_time'    => !empty($exam_time) ? strtotime($exam_time) : 0,
            'add_account'  => $this->user['client_account'],
            'add_time'     => time(),
            'exam_well'    => $exam_well,
            'exam_good'    => $exam_good,
            'exam_bad'     => $exam_bad,
            'is_published' => IS_PUBLISHED,                 //发布 1 草稿 0
            'is_sms'       => NO_SMS                        //发送玩短信才把 这个值设成 1
        );
        
        $exam_id = $this->saveExamInfo($exam_datas);
        if(empty($exam_id)) {
            $this->showError('成绩添加失败，请稍后重试', '');
        }

        //保存成绩的基本信息
        foreach ($exam_score_list as $uid => $score_info) {
            $exam_score_datas[] = array(
                'client_account' => $uid,
                'exam_id'        => $exam_id,
                'exam_score'     => $score_info['exam_score'],
                'score_py'       => $score_info['score_py'],
                'add_time'       => time(),
                'add_account'    => $this->user['client_account'],
                'is_join'        => !empty($score_info['exam_score']) ? 1 : 0,
                'is_sms'         => NO_SMS  //发送玩短信才把 这个值设成 1

            );
        }

        $mClassExam = ClsFactory::Create('Model.mClassExam');
        
        if(!$this->saveExamScoreInfo($exam_score_datas)) {
            //删除考试信息 保持数据的完整性
            $mClassExam->delClassExam($exam_id);
            $this->showError('成绩添加失败，请稍后重试', '');
        }

        //判断是否发送短息 (发送短信，改变短信发送状态包括成绩表和考试表)
        if($is_sms == IS_SMS) {
            $send_success = $this->sendExamSmsAll($exam_id);
            if(empty($send_success)){
                $this->showError("短信通知家长失败!你可以选择补发短信处理。", '/Sns/ClassExam/Exam/index/class_code/'.$class_code);
            }
        }
        
        $mMsgExamList = ClsFactory::Create("RModel.Msg.mStringExam");
        $mMsgExamList->publishMsg($exam_id, 'exam'); 

        $this->showSuccess("成绩发布成功，查看已发布成绩", '/Sns/ClassExam/Exam/index/class_code/'.$class_code);
    }

    /**
     * 保存草稿
     */
    public function publishDraft() {
        
        //接收参数  考试信息
        $class_code = $this->objInput->postStr('class_code');
        $subject_id = $this->objInput->postInt('subject_id');
        $exam_name = $this->objInput->postStr('exam_name');
        $exam_time = $this->objInput->postStr('exam_time');
        $exam_well = $this->objInput->postInt('exam_well');
        $exam_good = $this->objInput->postInt('exam_good');
        $exam_bad  = $this->objInput->postInt('exam_bad');
        
        //接收成绩信息
        /**
         * 学生姓名不从前台传过来
         * array(
         *     'uid1' =>array('uid'=>102445, 'exam_score' => 90,'score_py' => '学习认真，能很好的完成老师布置的作业','is_join' = 1),
         *     'uid2' =>array(.....),
         *     'uid3' =>array(.....),
         *     'uid4' =>array(.....),
         * )
         */
        $exam_score_list = $this->objInput->postArr('exam_score_list');
        
        //参数校验 （保存草稿的时候考试信息必须填写完整）
        if ($subject_id <= 0 || empty($exam_name) || empty($exam_well) || empty($exam_good) || empty($exam_bad)) {
            $this->showError('考试信息填写不完整，请填写完整后重试!', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
        }
        //权限判断(添加草稿权限 )
        $can_get_draft = $this->can_add_draft($class_code);
        if (empty($can_get_draft)){
            $this->showError('没有保存草稿权限 !', '/Sns/ClassExam/Exam/index/class_code/' . $class_code);
        }
        //参数校验 （保存草稿的时候考试信息必须填写完整）
        if ($subject_id <= 0 || empty($exam_name) || empty($exam_well) || empty($exam_good) || empty($exam_bad)) {
            //参数错误 Ajax 提示
            $this->showError('考试信息填写不完整，请填写完整后重试!', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
        }

        //保存考试基本信息
        $exam_datas = array(
            'class_code'   => $class_code,
            'subject_id'   => $subject_id,
            'exam_name'    => $exam_name,
            'exam_time'    => !empty($exam_time) ? strtotime($exam_time) : 0,
            'add_account'  => $this->user['client_account'],
            'add_time'     => time(),
            'exam_well'    => $exam_well,
            'exam_good'    => $exam_good,
            'exam_bad'     => $exam_bad,
            'is_published' => NO_PUBLISHED,                 //发布 1 草稿 0
            'is_sms'       => NO_SMS
        );
        $exam_id = $this->saveExamInfo($exam_datas);
        if (empty($exam_id)) {
             $this->showError('系统繁忙，请稍后重试!', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
        }

        //保存成绩的基本信息
            //1. 判断接收到的对象是改班级的成员信息(涉及到集合求交集)
        $exam_score_list = $this->filterExamScoreList($exam_score_list, $class_code);
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        if (empty($exam_score_list)) {
            //删除考试信息
            $mClassExam->delClassExam($exam_id);
            $this->showError('班级没有学生不能保存草稿!', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
        }
        
        foreach ($exam_score_list as $uid => $score_info) {
            $exam_score_datas[] = array(
                'client_account' => $uid,
                'exam_id'        => $exam_id,
                'exam_score'     => $score_info['exam_score'],
                'score_py'       => $score_info['score_py'],
                'add_time'       => time(),
                'add_account'    => $this->user['client_account'],
                'is_join'        => !empty($score_info['exam_score']) ? 1 : 0,
                'is_sms'         => NO_SMS

            );
        }
        $is_success = $this->saveExamScoreInfo($exam_score_datas);
        if(empty($is_success)) {
            //删除考试信息 保持数据的完整性
            $mClassExam->delClassExam($exam_id);
            $this->showError('保存失败，请稍后重试!', '/Sns/ClassExam/Publish/index/class_code/' . $class_code);
        }
        
        $this->showSuccess('保存成功!', '/Sns/ClassExam/Publish/index/is_draft/1/exam_id/' . $exam_id . '/class_code/' . $class_code);
    }

    /**
     * ajax获取用户的草稿列表信息
     * 注明：1. 只拿考试草稿的列表信息；
     *       2. 草稿在点击的是否重新渲染index页面
     */
    public function getDraftListAjax() {
        //接收参数  考试信息
        $page         = $this->objInput->getInt('page');
        $class_code   = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }
        
        $page = max(1, $page);
        $perpage = 10;
        $offset  = ($page-1) * $perpage;

        //权限判断(获取草稿权限 )
        $can_get_draft = $this->can_get_draft($class_code);
        if (empty($can_get_draft)){
            $this->ajaxReturn(null, '没有获取草稿权限 !', -1, 'JSON');
        }

        $wherearr = array(
        	"add_account={$this->user['client_account']}",
        	"is_published=".NO_PUBLISHED,
            "class_code=$class_code"
        );
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_list = $mClassExam->getClassExam($wherearr, 'exam_id desc', $offset, $perpage + 1);
        if(empty($exam_list)) {
            $this->ajaxReturn(null, '没有更多的草稿信息!', 1, 'json');
        }
        
        $has_nextpage = count($exam_list) > $perpage ? 1 : 0;
        if (count($exam_list) > $perpage) {
            $exam_list = array_slice($exam_list, 0, $perpage, true);
        }

        //格式化时间戳
        foreach($exam_list as $exma_id=>$exam_info) {
            $exam_info['add_time'] = !empty($exam_info['add_time'])? date('Y-m-d H:i', $exam_info['add_time']) : '--';
            $exam_list[$exma_id] = $exam_info;
        }
        $datas = array(
            'current_page' => $page,
            'has_nextpage' => $has_nextpage, 
            'draft_list'   => & $exam_list
        );
        
        $this->ajaxReturn($datas, '草稿获取成功!', 1, 'json');
    }

    /**
     * 远程删除草稿信息
     */
    public function deleteDraftAjax() {
        $exam_id = $this->objInput->getInt('exam_id');
        
        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_arr = $mClassExam->getClassExamById($exam_id);
        $exam_arr = & $exam_arr[$exam_id];
        
        //验证用户是否能够删除(该考试信息是否是当前用户发布的)
        if (empty($exam_arr) || $exam_arr['add_account'] != $this->user['client_account'] || $exam_arr['is_published'] != NO_PUBLISHED) {
            $this->ajaxReturn(null, '您没有权限删除该草稿或该草稿已被删除!', -1, 'json');
        }

        //先删除成绩
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $del_score = $mClassExamScore->delBatClassExamScoreByExamId($exam_id);
        if (empty($del_score)) {
            $this->ajaxReturn(null, '草稿删除失败!', -1, 'json');
        }
        
        //再删除考试信息
        $is_success = $mClassExam->delClassExam($exam_id);
        if(empty($is_success)) {
             $this->ajaxReturn(null, '草稿删除失败!', -1, 'json');
        }

        //成功为1, 失败为-1
        $this->ajaxReturn(null, '草稿删除成功!', 1, 'json');
    }
    
    /**
     * 下载考试的excel模板
     */
    public function downExamExcelTemplate() {
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }

        //验证是否具有权限
        $can_pulish = $this->canPublishExam($class_code);
        if(empty($can_pulish)) {
            $this->showError('您没有权限下载模板!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }

        //临时文件的命名规则和class_code相关: exam_excel_{$class_code}
        import("@.Common_wmw.Pathmanagement_sns");
        $excel_path = Pathmanagement_sns::getExamTplPath();
        $excel_name = $excel_path . EXAM_EXCEL_NAME_PREFIX . $class_code . '.xls';
        
        $file_exists = file_exists($excel_name);
        //如果文件不存在或者文件已过期（ 过期时间 EXAM_EXCEL_EXPIRY_TIME） 重新生成成绩导入模板文件
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        
        //如果文件不存在或者文件已经过期
        if(!$file_exists || ($file_exists && filectime($excel_name) + EXAM_EXCEL_EXPIRY_TIME < time())) {
            //如果文件存在先删除(避免服务器不允许文件覆盖导致的问题)
            if($file_exists) {
                @ unlink($excel_name);
                //clearstatcache();   //清楚文件缓存
            }

            //获取班级对应的学生列表
            import('@.Control.Api.Class.MemberApi');
            $member_obj = new MemberApi();
            $student_list = $member_obj->getStuList($class_code);
             
            //保存数据到临时Excel文件（生成excel）
            $index = 1;
            $exam_score = $score_py = '';

            $excel_datas[$index++] = array ('账号','姓名','成绩','评语');
	        foreach($student_list as $student_account=>$student) {
	            $excel_datas[$index++] = array(
	                $student['client_account'],
	                $student['client_name'],
	                $exam_score,
	                $score_py
	            );
	        }

            $datas[0] = array(
                'title'=> '学生成绩导入模板',
	            'cols' => 4,
	            'rows' => count($excel_datas),
	            'datas'=> $excel_datas,
            );

        	$HandlePHPExcel->saveToExcelFile($datas, $excel_name);
	        unset($student_list);
        }

        //获取班级名称
        $class_name = $this->user['class_info'][$class_code]['class_name'];
        //下载成绩导入模板
        $HandlePHPExcel->export($excel_name, "学生成绩导入模板($class_name)");
        exit;
    }

    /**
     * 远程上传excel文件
     */
    public function uploadExcelTemplateAjax() {
        $class_code = $this->objInput->postStr('class_code');

        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }

        //验证是否具有权限
        $can_pulish = $this->canPublishExam($class_code);
        if(empty($can_pulish)) {
            $this->ajaxReturn(null, '您没有权限导入成绩', -1, 'json');
        }
        
        //上传域中的文件名字: excel_template_file
        $file_attrs = $this->uploadExcelFile('excel_template_file');
                                  
        //解析excel文件
        $pFileName = $file_attrs['filename'];
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        $sheet_datas = $HandlePHPExcel->getSheetDatasByIndex($pFileName, 0);
        $head_settings = $this->getSheetHeadSettings(& $sheet_datas);
        
        $exam_score_list = $this->importBySheet($sheet_datas['datas']); //对excel 里面的数据进行过滤处理
        
        //过滤excel文件中的数据
        $score_list = $this->filterExamScoreList($exam_score_list, $class_code);
        //班级成员的补全
        import('@.Control.Api.Class.MemberApi');
        $member_obj = new MemberApi();
        $student_list = $member_obj->getStuList($class_code);
       
        //保证输出的顺序一致
        $new_score_list = array();
        $num_id = 1;
        if(!empty($student_list)) {
            foreach($student_list as $uid=>$student) {
                if(isset($score_list[$uid])) {
                    $score_datas = $score_list[$uid];
                } else {
                    $score_datas = array(
                        'client_account' => $uid,
                    	'exam_score'     => '',
                    	'score_py'       => '',
                    	'is_join'        => 0
                    );
                }
                
                $score_datas['num_id'] = $num_id ++;
                $score_datas['client_name']= $student['client_name'];  //已数据库中的用户名为准
                $new_score_list[$uid] = $score_datas;
            }
        }
        
        $this->ajaxReturn($new_score_list, '导入成功!', 1, 'json');
    }

    /*************************************************************************************
    * public 辅助函数
    ************************************************************************************/

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

	/**
	 * 获取用户在对应班级担任的班级角色
	 * @param $class_code
	 */
	private function getUserClientClass($class_code) {
	    if(empty($class_code)) {
	        return false;
	    }

	    $current_client_class = array();

	    $client_class_list = $this->user['client_class'];
	    foreach($client_class_list as $client_class) {
	        if($client_class['class_code'] == $class_code) {
	            $current_client_class = $client_class;
	            break;
	        }
	    }

	    return !empty($current_client_class) ? $current_client_class : false;
	}

	/**
     * 获取用户当前的科目信息
     * @param $class_code
     */
    private function getUserSubjectList($class_code) {
        if(empty($class_code)) {
            return false;
        }

        if($this->isClassTeacher($class_code)) {
           list($subject_list, $class_teacher_list) = $this->getSubjectAll($class_code);
        } else {
           list($subject_list, $class_teacher_list) = $this->getSubjectByTeacher($class_code, $this->user['client_account']);
        }
        
        if(empty($subject_list)) {
            return false;
        }

        //建立科目到老师的对应关系
        foreach($class_teacher_list as $key=>$class_teacher) {
            $teacher_uids[$class_teacher['subject_id']] = $class_teacher['client_account'];
        }

        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid(array_unique($teacher_uids));

        //数据拼装 老师名称 并且过滤掉没有任课老师的科目
        foreach ($subject_list as $subject_id=>$subject) {
            if(!isset($teacher_uids[$subject_id])) {
                continue;
            }
            
            $subject['teacher_name'] = $user_list[$teacher_uids[$subject_id]]['client_name'];
            $new_subject_list[$subject_id] = $subject;
        }

        return !empty($new_subject_list) ? $new_subject_list : false;
    }

    /*
     * 获取本班所有科目
     * 并格式好数据添加上教师名称
     */
    private function getSubjectAll($class_code) {
        if(empty($class_code)) {
            return false;
        }

        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $class_teacher_arr = $mClassTeacher->getClassTeacherByClassCode($class_code);
        $class_teacher_list = & $class_teacher_arr[$class_code];

        $school_id = key($this->user['school_info']);
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $school_subject_arr = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
        $subject_list = & $school_subject_arr[$school_id];

        return array($subject_list, $class_teacher_list);
    }

    /*
     *	获取当前老师在当前班级所教的所有科目
     */
    private function getSubjectByTeacher($class_code, $client_account) {
        if(empty($class_code) || empty($client_account)) {
            return false;
        }

        $mClassTeacher = ClsFactory::Create('Model.mClassTeacher');
        $class_teacher_arr = $mClassTeacher->getClassTeacherByUid($client_account);
        $class_teacher_list = & $class_teacher_arr[$client_account];

        if (empty($class_teacher_list)) {
            return false;
        }

        //过滤出当前班级老师所教科目,并以subject_id作为key重组
        $new_teacher_list = array();
        foreach($class_teacher_list as $key=>$class_teacher) {
            if($class_code != $class_teacher['class_code']) {
                continue;
            }
            $new_teacher_list[$class_teacher['subject_id']] = $class_teacher;
        }

        $subject_ids = array_keys($new_teacher_list);
        if(!empty($subject_ids)) {
            $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
            $subject_list = $mSubjectInfo->getSubjectInfoById($subject_ids);
        }

        return array($subject_list, $class_teacher_list);
    }

 	/**
     * 判断用户是否有权限发布成绩信息
     * @param $class_code
     * @return boolean true表示可以发布，false表示不能发布
     */
    private function canPublishExam($class_code) {
        $client_class = $this->getUserClientClass($class_code);

        return ($client_class['client_type'] == CLIENT_TYPE_TEACHER) ? true : false;
    }
    

    /*************************************************************************************
     * 考试发布部分的辅助函数
     ************************************************************************************/
	/**
     * 保存考试的基本信息
     * @param $exam_datas
     */
    private function saveExamInfo($exam_datas) {
        if (empty($exam_datas)) {
            return false;
        }

        $mClassExam = ClsFactory::Create('Model.mClassExam');
        $exam_id = $mClassExam->addClassExam($exam_datas, true);

        return !empty($exam_id) ? $exam_id : false;
    }

    /**
     * 过滤页面接收到的成绩列表是否是该班级对应的成员列表信息
     * @param $exam_score_list array('client_account1'=>array(成绩信息)，'client_account2'=>array(成绩信息)...)
     * @param $class_code
     */
    private function filterExamScoreList($exam_score_list, $class_code) {
        if (empty($exam_score_list) || !is_array($exam_score_list) || empty($class_code)) {
            return false;
        }

        //获取班级成员列表
        import('@.Control.Api.Class.MemberApi');
        $member_obj = new MemberApi();
        $class_student_list = $member_obj->getStuList($class_code);
        if (empty($class_student_list)) {
            return false;
        }
        
        foreach ($exam_score_list as $client_account => $exam_score_info) {
            if(!isset($class_student_list[$client_account])) {
                unset($exam_score_list[$client_account]);
            }
        }

        return $exam_score_list;
    }

    /**
     * 保存成绩的基本信息
     * @param $exam_score_list
     * @param $exam_id
     */
    private function saveExamScoreInfo($exam_score_datas) {
        if(empty($exam_score_datas) || !is_array($exam_score_datas)) {
            return false;
        }
        $mClassExamScore = ClsFactory::Create('Model.mClassExamScore');
        $is_success = $mClassExamScore->addBatClassExamScore($exam_score_datas);

        return !empty($is_success) ? true : false;
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
  
   /*************************************************************************************
    * excel 导入成绩部分辅助函数
    ************************************************************************************/

    /**
     * 上传对应的Excel文件
     */
    private function uploadExcelFile($inputfilename = null) {
        if(empty($inputfilename) || !isset($_FILES[$inputfilename])) {
            return false;
        }
        
        import("@.Common_wmw.Pathmanagement_wms");
        $up_init = array(
            'max_size' => 1024 * 5,
            'attachmentspath' => Pathmanagement_wms::UploadExcel(),
        	'renamed' => true,
            'allow_type' => array('xls', 'xlsx')
        );
  
        $uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');
        $uploadObj->_set_options($up_init);
        $up_rs = $uploadObj->upfile($inputfilename);

        return !empty($up_rs) ? $up_rs : false;
    }

    /**
     * 获取excel的头部信息设置
     * @param $sheet_datas
     */
    private function getSheetHeadSettings($sheet_datas) {
        if(empty($sheet_datas)) {
            return false;
        }

        $head_settings = array(
            'title' => $sheet_datas['title'],
            'cols' => $sheet_datas['cols'],
            'rows' => $sheet_datas['rows'],
            'first_rows' => array_shift($sheet_datas['datas'])
        );

        return $head_settings;
    }

    /**
     *
     * 导入Excel的相关数据 进行处理（验证合法性）
     * @param $resource_list
     * @param $product_id
     */
    private function importBySheet($resource_list) {
        if (empty($resource_list)) {
            return false;
        }

        $datas = array();
        foreach($resource_list as $key=>$data) {
            list($client_account, $client_name, $exam_score, $score_py) = $data;
            $client_account = trim($client_account);
            $client_name    = trim($client_name);
            $exam_score     = ($exam_score === "") ? "" : floatval(trim($exam_score));
            $score_py       = trim($score_py);
            if(empty($client_account)) {
                continue;
            }
            
            $datas[$client_account] = array(
                'client_account' => $client_account,
            	'client_name'    => $client_name,
            	'exam_score'     => $exam_score,
            	'score_py'       => $score_py,
            	'is_join'        => ($exam_score === "") ? 0 : 1
            );
        }

        return !empty($datas) ? $datas : false;
    }

    /*************************************************************************************
    * 草稿部分的辅助函数
    ************************************************************************************/
    
    /**
     * 获取用户是否有草稿读取权限
     * @param $class_code 班级id
     */
    private function can_get_draft($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //现在是用户有发布成绩权限就有读取自己草稿的权限
        $can_get_draft = $this->canPublishExam($class_code);
        return $can_get_draft;
    }
    
    /**
     * 获取用户是否有添加草稿权限
     * @param $class_code 班级id
     */
    private function can_add_draft($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //现在是用户有发布成绩权限就有读取自己草稿的权限
        $can_get_draft = $this->canPublishExam($class_code);
        return $can_get_draft;
    }





}