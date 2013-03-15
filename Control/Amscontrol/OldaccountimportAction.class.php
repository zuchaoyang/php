<?php
define("EXCEL_FILE_DIR", WEB_ROOT_DIR . "/attachment");    //Excel文件的上传目录
define("EXCEL_FILE_NAME_PRE", 'old_');            //上传的Excel文件的命名前缀
define('AUTO_CLEAR', true);                       //是否开启自动清理上传的Excel文件(true|false)

define("DEFAULT_PASSWORD_STUDENT", "000000");    //学生默认密码
define("DEFAULT_PASSWORD_FAMILY", "000000");     //家长默认密码
define("DEFAULT_PASSWORD_TEACHER", "123456");    //教师默认密码
define("DEFAULT_CLIENT_SEX", 1);                 //用户默认性别

class OldaccountimportAction extends AmsController {
    protected $is_school = true;
    private $_DEFAULT_SUBJECT_NAME = '语文';
    protected $_stack = array();     //用户处理回滚的栈
    protected $_stack_attrs = array();
    protected $_stack_format = array(
        'client_account' => 'uid',
        'client_class' => 'uid:class_code',
        'family_relation' => 'uid',
    );

    protected $_config = array(
        CLIENT_TYPE_STUDENT => array(
            'fields' => array(
                1 => array(
                	'client_account',
                	'int',
                    true,
                ),
                2 => array(
                	'client_name',
                	'string',
                  ),
                3 => array(
                    'client_sex',
                    'string'
                ),
            ),
            'bind_fields' => array(
                'client_sex' => array(
                    1 => '男',
                    2 => '女',
                ),
            ),
        ),
        CLIENT_TYPE_TEACHER => array(
            'fields' => array(
                0 => array(
                    'client_account',
                    'int',
                    true,
                ),
                1 => array(
                    'client_name',
                    'string',
                ),
            ),
        ),

    );


    public function _initialize(){
        // todolist
        parent::_initialize();
        header("Content-Type:text/html; charset=utf-8");
        import("@.Common_wmw.Constancearr");
        import("@.Common_wmw.Pathmanagement_ams");
		
		if(IS_SET_OLDACCOUNT_IMPORT == -1){
		    $this->showError("当前功能未开启，请联系管理员", "/Amscontrol/Index/index");
		}
        $this->assign('username', $this->user['client_name']);
    }

    //获取cookie中的用户账号todolist
	function getCookieAccount(){
	    $mUser = ClsFactory::Create('Model.mUser');
        return $mUser->getHomeCookieAccount();
	}

	function showimportaccount(){
	    $schoolid = $this->user['schoolinfo']['school_id'];
	    $this->assign('schoolid',$schoolid);
	    $this->display('old_teacher_import');
	}

    public function teacherUploadExcelFile() {
	    //上传excel文件
	    $file = $this->upload_excel('file_excel');
	    $schoolid = $this->objInput->postInt('schoolid');
	    $file_name =  $file['getfilename'];
	    if(empty($file)) {
	        $this->showError("文件上传失败", "/Amscontrol/Oldaccountimport/showimportaccount");
	    }
	    $this->redirect("Oldaccountimport/showImportInfo", array("schoolid"=>$schoolid, 'pFileName'=>base64_encode($file_name)));
	}

	//导入数据回显
	function showImportInfo(){
	    $page = $this->objInput->getInt('page');
	    $perpage = 50;
	    $page = max(1, $page);
	    $pFile_name = base64_decode($this->objInput->getStr('pFileName'));
	    $schoolid = $this->user['schoolinfo']['school_id'];
	    $data_list = $this->toArray(EXCEL_FILE_DIR.$pFile_name);
	    $import_data = array();
	    foreach($data_list as & $Sheet){
    	    array_shift($Sheet['datas']);
    	    $import_data = array_merge($import_data, $Sheet['datas']);
	    }
	    foreach($import_data as $key=>$val){
	        $flag = true;
	        foreach($val as $val_1){
    	        if(!empty($val_1)){
    	            $flag = false;
    	            break;
    	        }
	        }
	        if($flag){
	            unset($import_data[$key]);
	        }
	    }
	    unset($data_list);
	    if(empty($import_data)){
	        $this->showError("Excel文件为空请重新上传", "/Amscontrol/Oldaccountimport/showimportaccount");
	    }
	    $totalnum = count($import_data);
	    $totalpage = ceil($totalnum/$perpage);
	    if($page>=$totalpage){
	        $flag = true;
	    }
	    $import_data_num = count($import_data);
	    $new_import_data = array_splice($import_data, ($page-1)*$perpage, $perpage);
	    $this->assign('import_data', $new_import_data);
	    $this->assign('flag', $flag);
	    $this->assign('perpage',$perpage);
	    $this->assign('page', $page);
	    $this->assign('excel_path',base64_encode($pFile_name));
	    $this->assign('totalpage', $totalpage);
	    $this->assign('import_data_num', $import_data_num);
	    $this->assign('schoolid', $schoolid);
	    $this->display('old_teacher_import_show');
	}

    //导入账号入库
	function impostTeacherAccount(){
	    $schoolid= $this->objInput->postInt('schoolid');
	    $current_uid = $this->getCookieAccount();
	    $excel_path = base64_decode($this->objInput->postStr('excel_path'));
	    $school_id = $this->objInput->postInt('schoolid');
	    if(empty($school_id)){
	        $this->showError("没有学校信息", "/Amscontrol/Oldaccountimport/showimportaccount");
	        exit;
	    }

	    $file_extension = pathinfo(EXCEL_FILE_DIR.$excel_path, PATHINFO_EXTENSION);
	    $data_list = $this->toArray(EXCEL_FILE_DIR.$excel_path);
	    $import_data = array();
	    foreach($data_list as & $Sheet){
	        if(!empty($Sheet['datas'])){
        	    array_shift($Sheet['datas']);
        	    $import_data = array_merge($import_data, $Sheet['datas']);
	        }
	    }
	    unset($data_list);
        $total_fail_list = array();
    	foreach($import_data as $key=>$val){
	        $flag = true;
	        foreach($val as $val_1){
    	        if(!empty($val_1)){
    	            $flag = false;
    	        }
	        }
	        if($flag){
	            unset($import_data[$key]);
	        }
	    }
        $impost_short_data = array_chunk($import_data, 1000, true);
        import("@.Common_wmw.WmwString");

        $default_password = !constant('DEFAULT_PASSWORD_TEACHER') ? DEFAULT_PASSWORD_TEACHER : "123456";
	    $default_client_sex = !constant('DEFAULT_CLIENT_SEX') ? DEFAULT_CLIENT_SEX : 1;
	    $default_password_md5 = md5($default_password);
        foreach($impost_short_data as $short_data){
            list($success_arr, $fail_list) = $this->parseToAssocArr($short_data, CLIENT_TYPE_TEACHER);
            $base_arr = $success_arr;
    	    $total_fail_list = $this->array_merge_keep_keys($total_fail_list, $fail_list);
    	    list($success_arr, $fail_list) = $this->filterDatas($success_arr);
    	    $success_arr = $this->filter_with_keys($success_arr, array_keys($fail_list));
    	    $total_fail_list = $this->array_merge_keep_keys($total_fail_list, $fail_list);
    	    foreach($success_arr as $uid=>$val) {
                $account_datas = array(
                    'client_account' => $uid,
                	'client_name' => $val['client_name'],
                    'client_password' => $default_password_md5,
                    'client_type' => CLIENT_TYPE_TEACHER,
                    'add_time' => time(),
                	'upd_time' => time(),
                    'status' => CLIENT_STOP_FLAG,
                );
                $info_datas = array(
                    'client_account' => $uid,
                    'client_firstchar' => WmwString::getfirstchar($val['client_name']),
                    'client_sex' => isset($val['client_sex']) ? $val['client_sex'] : $default_client_sex,
                    'add_time' => time(),
                    'upd_time' => time(),
                );
                $student_account_list[$uid] = $account_datas;
                $student_info_list[$uid] = $info_datas;
    	    }
	        $fail_list = $this->import_client_account($student_account_list, $student_info_list);
	        $success_arr = $this->filter_with_keys($success_arr, array_keys($fail_list));
	        unset($student_account_list, $student_info_list);
            if(!empty($fail_list)) {
    	        foreach($fail_list as $uid=>$msg) {
    	            $total_fail_list[$uid] = array_merge((array)$base_arr[$uid], array('msg' => $msg));
    	        }
    	    }
    	    $subject_id = $this->getsubjectinfo($school_id, $this->_DEFAULT_SUBJECT_NAME);
    	    $school_teacher_list = array();
            foreach($success_arr as $uid=>$info){
    	        $school_teacher = array(
    	            'client_account'=>$uid,
    	            'school_id'=>$school_id,
    	            'subject_id'=>$subject_id,
    	            'add_time'=>time(),
    	            'add_account'=>$current_uid,
    	            'upd_account'=>$current_uid,
    	            'upd_time'=>time(),
    	        );
    	        $school_teacher_list[$uid] = $school_teacher;
    	    }
    	    $fail_list = $this->import_school_teacher($school_teacher_list);
            if(!empty($fail_list)) {
    	        foreach($fail_list as $uid=>$msg) {
    	            $total_fail_list[$uid] = array_merge((array)$base_arr[$uid], array('msg' => $msg));
    	        }
    	    }
        }
	    unlink(EXCEL_FILE_DIR.$excel_path);
	    $new_fail_list = array();
	    $flag = false;
	    if(!empty($total_fail_list)) {
	        $index = 1;
	        $new_fail_list[$index++] = array(
                0 => '账号',
                1 => '姓名',
                2 => '错误信息',
	        );
	        foreach($total_fail_list as $key=>$data) {
	            $new_fail_list[$index++] = array(
	                0 => $data['client_account'],
	                1 => $data['client_name'],
	                2 => $data['msg'],
	            );
	        }

	        $excel_datas[0] = array(
	            'title' => '失败账号信息',
	            'cols' => 3,
	            'rows' => count($new_fail_list),
	            'datas' => $new_fail_list,
	        );
	        $fail_path = EXCEL_FILE_DIR.$excel_path;
	        $success = $this->saveToExcelFile($excel_datas, $fail_path);
	        $flag = true;
	    }
	    $success_num = count($import_data) - count($total_fail_list);
	    $fail_num = count($total_fail_list);
	    
	    $this->assign('success_num',$success_num);
	    $this->assign('fail_num',$fail_num);
	    $this->assign('path',$excel_path);
	    $this->assign('flag', $flag);
	    $this->assign('fail_filename', base64_encode($excel_path));
	    $this->assign('file_name', date('YmdHis'). '.' . $file_extension);
	    $this->assign('schoolid', $schoolid);
	    if(empty($total_fail_list)){
	        $this->assign('flag',true);
	    }
	    unset($total_fail_list);
	    $this->display('old_teacher_export_fail');
	}

	public function export_fail_excel(){
	    $pathstt = $this->objInput->postStr('fail_filename');
	    $file_name = $this->objInput->postStr('file_name');

	    $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
	    
	    $pFileName = EXCEL_FILE_DIR . base64_decode($pathstt);
	    $HandlePHPExcel->export($pFileName, $file_name);
	}

	public function oldStudentUploadExcel() {
	    $uid = $this->getCookieAccount();
	    $class_code = $this->objInput->getInt('class_code');
	    $grade_id = $this->objInput->getInt('grade_id');
	    $school_id = $this->objInput->getInt('school_id');

	    $this->assign('school_id', $school_id);
	    $this->assign('grade_id', $grade_id);
	    $this->assign('class_code', $class_code);
	    $this->assign('uid', $uid);
	    $this->display('oldStudentUploadExcel');
	}

	public function studentUploadExcelFile() {
	    //上传excel文件
	    $uid = $this->getCookieAccount();

	    $school_id = $this->objInput->postInt('school_id');
	    $grade_id = $this->objInput->postInt('grade_id');
	    $class_code = $this->objInput->postInt('class_code');

	    try {
	        $file = $this->upload_excel('file_excel');
	    } catch(Exception $e) {
	        $this->showError("$e->getMessage()", "/Amscontrol/Oldaccountimport/oldStudentUploadExcel");
	    }

	    if(empty($file)) {
	        $this->showError("文件上传失败", "/Amscontrol/Oldaccountimport/oldStudentUploadExcel");
	    } else {
	        $this->redirect("Oldaccountimport/oldStudentPreview", array('filename'=>base64_encode($file['getfilename']),'class_code'=>$class_code,'school_id'=>$school_id, 'gradeid'=>$grade_id));
	    }
	}

	public function oldStudentPreview() {
	    $uid = $this->getCookieAccount();
	    $class_code = $this->objInput->getInt('class_code');
	    $grade_id = $this->objInput->getInt('grade_id');
	    $school_id = $this->objInput->getInt('school_id');

	    $page = $this->objInput->getInt('page');
	    $filename_base64 = $this->objInput->getStr('filename');
	    $filename = base64_decode($filename_base64);
	    //实际文件位置
	    $pFileName = EXCEL_FILE_DIR . $filename;

	    $page = max(1, $page);
	    $perpage = 50;

	    $offset = ($page - 1) * $perpage;

	    $excel_datas = $this->toArray($pFileName);
	    $sheet_datas = array_shift($excel_datas);
	    unset($excel_datas);

	    $student_datas = $sheet_datas['datas'];
	    list($student_datas, $fail_list, $total_nums) = $this->parseToAssocArr($student_datas, CLIENT_TYPE_STUDENT);
	    if(empty($student_datas)) {
	    	$this->showError("导入文件为空", "/Amscontrol/Oldaccountimport/oldStudentUploadExcel/school_id/$school_id/grade_id/$grade_id/class_code/$class_code");
	    }

	    $has_next = count($student_datas) > $perpage * $page + 1 ? true : false;

	    $params_str = "filename/$filename_base64/school_id/$school_id/grade_id/$grade_id/class_code/$class_code";
	    $page_list = array(
	        'pre_page' => $page > 1 ? "/Amscontrol/Oldaccountimport/oldStudentPreview/page/" . ($page - 1) . "/" . $params_str: false,
	        'next_page' => $has_next ? "/Amscontrol/Oldaccountimport/oldStudentPreview/page/" . ($page + 1) . "/" . $params_str : false,
	    );

	    $student_datas = array_slice($student_datas, $offset, $perpage);
	    $index_id = $offset + 1;
	    if(!empty($student_datas)) {
	        foreach($student_datas as $key=>$data) {
	            $data['index_id'] = $index_id++;
	            $student_datas[$key] = $data;
	        }
	    }
	    $fail_nums = !empty($fail_list) ? count($fail_list) : 0;

	    //公共部分参数
	    $this->assign('uid', $uid);
	    $this->assign('school_id', $school_id);
	    $this->assign('grade_id', $grade_id);
	    $this->assign('class_code', $class_code);

	    $this->assign('student_datas', $student_datas);
	    $this->assign('fail_nums', $fail_nums);
	    $this->assign('total_nums', $total_nums);
	    $this->assign('pFileName', base64_encode($pFileName));
	    $this->assign('md5_key', $this->get_md5key($pFileName, $class_code));
	    $this->assign('page_list', $page_list);

	    $this->display('oldStudentPreview');
	}

	protected function get_md5key($pFileName, $class_code) {
	    return md5($class_code . $pFileName);
	}

	public function oldStudentExportAccount() {
	    $school_id = $this->objInput->getInt('school_id');
	    $grade_id = $this->objInput->getInt('grade_id');
	    $class_code = $this->objInput->getInt('class_code');

	    $pFileName_base64 = $this->objInput->postStr('pFileName');
	    $md5_key = $this->objInput->postStr('md5_key');

	    $pFileName = base64_decode($pFileName_base64);
	    //权限检测
	    if($md5_key !== $this->get_md5key($pFileName, $class_code)) {
	        $this->showError("非法操作", "/Amscontrol/Oldaccountimport/oldStudentUploadExcel");
	    } elseif(empty($class_code)) {
	        $this->showError("班级信息不存在", "/Amscontrol/Oldaccountimport/oldStudentUploadExcel");
	    }

	    $uid = $this->getCookieAccount();

        $file_extension = pathinfo($pFileName, PATHINFO_EXTENSION);
	    list($fail_list, $total_nums) = $this->oldStudentInitDatabase($pFileName, $class_code);

	    $new_fail_list = array();
	    if(!empty($fail_list)) {
	        $index = 1;
	        $new_fail_list[$index++] = array(
                0 => '账号',
                1 => '姓名',
                2 => '性别',
                3 => '错误信息',
	        );
	        foreach($fail_list as $key=>$data) {
	            $new_fail_list[$index++] = array(
	                0 => $data['client_account'],
	                1 => $data['client_name'],
	                2 => $data['client_sex'],
	                3 => $data['msg'],
	            );
	            unset($fail_list[$key]);
	        }

	        $excel_datas[0] = array(
	            'title' => '失败账号信息',
	            'cols' => 4,
	            'rows' => count($new_fail_list),
	            'datas' => $new_fail_list,
	        );

	        $fail_filename = $this->getExcelFilePath('student_fail', $file_extension);
	        $success = $this->saveToExcelFile($excel_datas, $fail_filename);
	    }

	    //要减去添加的标题项
	    $fail_nums = max(0, count($new_fail_list) - 1);
	    $success_nums = max(0, $total_nums - $fail_nums);

	    $file_attrs = array(
	        'fail_file_path' => $fail_filename,
	        'export_file_name' => '学生导入失败列表' . "." . $file_extension,
	    );

	    //公共部分参数
	    $this->assign('uid', $uid);
	    $this->assign('school_id', $school_id);
	    $this->assign('grade_id', $grade_id);
	    $this->assign('class_code', $class_code);

	    $this->assign('success_nums', $success_nums);
	    $this->assign('fail_nums', $fail_nums);
	    $this->assign('file_attrs', json_encode($file_attrs));
	    $this->assign('can_export', file_exists($fail_filename));

	    $this->display('oldStudentExportAccount');
	}

	public function exportStudentFailFile() {
	    $file_attrs = $this->objInput->postStr('file_attrs');
	    $file_attrs = htmlspecialchars_decode($file_attrs);
	    $file_attrs = json_decode($file_attrs, true);

	    $pFileName = $file_attrs['fail_file_path'];
        $export_file_name = $file_attrs['export_file_name'];

        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        $HandlePHPExcel->export($pFileName, $export_file_name);
	}

	public function oldStudentInitDatabase($pFileName, $class_code) {
	    if(empty($class_code) || empty($pFileName)) {
	        return false;
	    }

	    $excel_datas = $this->toArray($pFileName);
	    $sheet_datas = array_shift($excel_datas);
	    unset($excel_datas);

	    $total_fail_list = $original_datas = array();

	    list($datas, $fail_list, $total_nums) = $this->parseToAssocArr($sheet_datas['datas']);
	    $total_fail_list = array_merge((array)$total_fail_list, (array)$fail_list);
	    //获取转换成关联数组后的原始数据
	    $original_datas = $datas;

	    list($datas, $fail_list) = $this->filterDatas($datas);
	    $total_fail_list = array_merge((array)$total_fail_list, (array)$fail_list);

	    //绑定数据的字符串到实际值的映射
	    list($datas, $fail_list) = $this->bindDatasAttrs($datas, CLIENT_TYPE_STUDENT);
	    $total_fail_list = array_merge((array)$total_fail_list, (array)$fail_list);

	    //导入相关的数据库表关系
	    $this->set_stack_attrs(array("class_code" => $class_code));
	    //导入学生关系
	    $fail_msg_list = $this->database_import_student($datas, $class_code);
	    $datas = $this->filter_with_keys($datas, array_keys($fail_msg_list));
	    if(!empty($fail_msg_list)) {
	        foreach($fail_msg_list as $uid=>$msg) {
	            $total_fail_list[] = array_merge((array)$original_datas[$uid], array('msg' => $msg));
	        }
	    }
	    //添加家长的信息
	    $family_datas = array();
	    if(!empty($datas)) {
    	    $family_nums = 2 * count($datas);
    	    try {
    	        $family_uids = $this->getFamilyAccounts($family_nums);
    	    } catch(Exception $e) {

    	    }

    	    $build_relations_uids = array();
    	    if(!empty($family_uids)) {
        	    foreach($datas as $uid=>$student) {
        	        for($i = 1; $i <= 2; $i++) {
            	        $family_account = array_shift($family_uids);
            	        if(empty($family_account)) {
            	            break 2;
            	        }
            	        $family_datas[$family_account] = array(
            	            'client_account' => $uid,
            	            'client_name' => ($i == 1) ? "家长一" : "家长二",
            	            'family_account' => $family_account,
            	            'family_type' => $i,
            	        );
        	        }
        	        $build_relations_uids[$uid] = $uid;
        	    }
    	    }

    	    //需要处理家长账号生成不全时的问题
    	    $diff_arr = array_diff(array_keys($datas), $build_relations_uids);
    	    if(!empty($diff_arr)) {
    	        $this->build_rollback_stack("", $diff_arr);
    	    }

	    }
	    //导入家长关系数据
	    $fail_msg_list = $this->database_import_family($family_datas, $class_code);
	    if(!empty($fail_msg_list)) {
	        foreach($fail_msg_list as $uid=>$msg) {
	            $total_fail_list[] = array_merge((array)$original_datas[$uid], array('msg' => $msg));
	        }
	    }
	    //执行回滚
	    $this->run_rollback();

	    return array($total_fail_list, $total_nums);
	}

	protected function database_import_student($datas, $class_code) {
	    if(empty($datas) || empty($class_code)) {
	        return false;
	    }

	    import("@.Common_wmw.WmwString");

	    $total_fail_list = array();
	    //导入学生关系数据
	    $default_password = !constant('DEFAULT_PASSWORD_STUDENT') ? DEFAULT_PASSWORD_STUDENT : "000000";
	    $default_client_sex = !constant('DEFAULT_CLIENT_SEX') ? DEFAULT_CLIENT_SEX : 1;
	    $default_password_md5 = md5($default_password);

	    $student_account_list = $student_info_list = array();
	    foreach($datas as $uid=>$val) {
            $student_account_list[$uid] = array(
                'client_account' => $uid,
            	'client_name' => $val['client_name'],
                'client_password' => $default_password_md5,
                'client_type' => CLIENT_TYPE_STUDENT,
                'add_time' => time(),
            	'upd_time' => time(),
                'status' => CLIENT_STOP_FLAG,
            );
            $student_info_list[$uid] = array(
                'client_account' => $uid,
                'client_firstchar' => WmwString::getfirstchar($val['client_name']),
                'client_sex' => isset($val['client_sex']) ? $val['client_sex'] : $default_client_sex,
                'add_time' => time(),
                'upd_time' => time(),
            );
	    }
	    $fail_list = $this->import_client_account($student_account_list, $student_info_list);
	    $total_fail_list = $this->array_merge_keep_keys($total_fail_list, $fail_list);
	    $datas = $this->filter_with_keys($datas, array_keys($fail_list));
	    unset($student_account_list, $student_info_list);

	    if(!empty($datas)) {
    	    $student_clientclass_list = array();
    	    foreach($datas as $uid => $val) {
    	        $student_clientclass_list[$uid] = array(
    	            'client_account' => $uid,
    	            'class_code' => $class_code,
    	            'client_class_role' => intval(CLIENT_CLASS_ROLE_PT),
    	            'class_admin' => NO_CLASS_ADMIN,
    	            'add_time' => time(),
    	            'add_account' => $this->user['client_account'],
    	            'upd_account' => $this->user['client_account'],
    	            'upd_time' => time(),
    	            'client_type' => CLIENT_TYPE_STUDENT,
    	        );
    	    }
    	    $fail_list = $this->import_client_class($student_clientclass_list);
    	    $total_fail_list = $this->array_merge_keep_keys($total_fail_list, $fail_list);
    	    unset($student_clientclass_list);
	    }

	    return !empty($total_fail_list) ? $total_fail_list : false;
	}

	protected function database_import_family($family_datas, $class_code) {
	    if(empty($family_datas) || empty($class_code)) {
	        return false;
	    }
	    
	    import("@.Common_wmw.WmwString");
	    
	    $return_fail_list = array();

	    $default_password = !constant('DEFAULT_PASSWORD_FAMILY') ? DEFAULT_PASSWORD_FAMILY : "000000";
	    $default_client_sex = !constant('DEFAULT_CLIENT_SEX') ? DEFAULT_CLIENT_SEX : 1;
	    $default_password_md5 = md5($default_password);

	    $family_account_list = $family_info_list = array();
	    foreach($family_datas as $family_account => $data) {
	        $family_account_list[$family_account] = array(
	        	'client_account' => $family_account,
	        	'client_name' => $data['client_name'],
                'client_password' => $default_password_md5,
                'client_type' => CLIENT_TYPE_FAMILY,
                'add_time' => time(),
            	'upd_time' => time(),
                'status' => CLIENT_STOP_FLAG,
	        );

	        $family_info_list[$family_account] = array(
	        	'client_account' => $family_account,
                'client_firstchar' => WmwString::getfirstchar($data['client_name']),
                'client_sex' => isset($data['client_sex']) ? $data['client_sex'] : $default_client_sex,
                'add_time' => time(),
                'upd_time' => time(),
	        );
	    }
	    $fail_list = $this->import_client_account($family_account_list, $family_info_list);
	    $family_datas = $this->filter_with_keys($family_datas, array_keys($fail_list));
	    unset($family_account_list, $family_info_list);

	    if(!empty($family_datas)) {
    	    $family_clientclass_list = array();
    	    foreach($family_datas as $family_account=>$data) {
    	        $family_clientclass_list[$family_account] = array(
    	        	'client_account' => $family_account,
    	            'class_code' => $class_code,
    	            'class_admin' => NO_CLASS_ADMIN,
    	            'add_time' => time(),
    	            'add_account' => $this->user['client_account'],
    	            'upd_account' => $this->user['client_account'],
    	            'upd_time' => time(),
    	            'client_type' => CLIENT_TYPE_FAMILY,
    	        );
    	    }
    	    $fail_list = $this->import_client_class($family_clientclass_list);
    	    $family_datas = $this->filter_with_keys($family_datas, array_keys($fail_list));
    	    unset($family_clientclass_list);
	    }

	    if(!empty($family_datas)) {
    	    $student_family_relation_list = array();
    	    foreach($family_datas as $family_account=>$data) {
    	        $student_family_relation_list[$family_account] = array(
    	            'client_account' => $data['client_account'],
    	            'family_account' => $data['family_account'],
    	            'family_type' => $data['family_type'],
    	            'add_account' => $this->user['client_account'],
    	            'add_time' => time(),
    	        );
    	    }

    	    //对返回的导入错误记录信息有影响
    	    $fail_list = $this->import_family_relation($student_family_relation_list);
    	    $return_fail_list = & $fail_list;

    	    unset($student_family_relation_list);
	    }

	    return !empty($return_fail_list) ? $return_fail_list : false;
	}

	/**
	 * 通过主键过滤数据
	 * @param  $datas
	 * @param  $keys
	 */
	protected function filter_with_keys($datas, $keys) {
	    if(empty($datas)) {
	        return false;
	    } elseif(empty($keys)) {
	        return $datas;
	    }

	    foreach((array)$keys as $key) {
	        unset($datas[$key]);
	    }

	    return !empty($datas) ? $datas : false;
	}

	/**
	 * 以保留键值的方式合并数组
	 * @param  $arr1
	 * @param  $arr2
	 */
	protected function array_merge_keep_keys($arr1, $arr2) {
	    if(empty($arr1)) {
	        return (array)$arr2;
	    } elseif(empty($arr2)) {
	        return (array)$arr1;
	    }

	    $arr1 = (array)$arr1;
	    foreach($arr2 as $key=>$val) {
	        $arr1[$key] = $val;
	    }

	    return $arr1;
	}


	/**
	 * 获取家长账号信息
	 * @param $nums
	 */
	protected function getFamilyAccounts($nums) {
	    if(empty($nums) || $nums <= 0) {
	        return false;
	    }

	    $max_test_times = 3;

	    $mUser = ClsFactory::Create('Model.mUser');
	    $return_family_uids = array();
	    $test_times = 0;
	    do {

	        $family_uids = $mUser->getClientCountBat($nums);
	        $return_family_uids = array_merge((array)$return_family_uids, (array)$family_uids);

	    } while(count($return_family_uids) < $nums && $test_times++ < $max_test_times);

	    if($test_times >= $max_test_times && count($return_family_uids) < $nums) {
	        throw new Exception("账号生成系统繁忙!请稍后再试!", -1);
	    }

	    return array_slice($return_family_uids, 0, $nums, true);
	}

	public function exportfail(){
	    $up_path =  Pathmanagement_ams::uploadExcel();
	    
	    $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
	    $result = $HandlePHPExcel->saveToExcelFile('','falie_excel');
	    $HandlePHPExcel->export($up_path.falie_excel, date('YmdHis').rand(10000,99999));
	}


	/**
	 * 文件上传
	 * @param  $filename
	 * @param  $up_path
	 */
	protected function upload_excel($filename) {
	    if(empty($filename) || !isset($_FILES[$filename]['name'])) {
	        return false;
	    }

	    //允许上传文件类型
	    $allow_type = array('xls', 'xlsx');

        import('Libraries.uploadfile'); //引入上传类

        $up_path = defined('EXCEL_FILE_DIR') && EXCEL_FILE_DIR ? EXCEL_FILE_DIR : false;
        if(empty($up_path) || !is_dir($up_path)) {
            throw new Exception("您需要指定Excel文件的上传目录!", -1);
        }

        $ext = pathinfo($_FILES[$filename]['name'], PATHINFO_EXTENSION);
        if(empty($ext) || !in_array($ext, $allow_type)) {
            throw new Exception('文件类型不正确,允许上传的文件类型:' . implode(',', $allow_type) . "!", -2);
        }

        $new_name = $this->excelname_encode($filename);    //文件重命名
        $up_init = array(
            'allow_type' => $allow_type, //支持格式
            'attachmentspath' => $up_path,
            'renamed' => true,
            'newname' => $new_name,
        );

        $uploadfile = new uploadfile($up_init);
        return $uploadfile->upfile($filename);
	}


	/**
	 * 转换成关联数组
	 * @param $pFilename
	 */
	protected function toArray($pFilename) {
	    if(empty($pFilename)) {
	        return false;
	    }
	    
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        return $HandlePHPExcel->toArray($pFilename);
	}

	protected function saveToExcelFile($datas, $filename) {
	    if(empty($datas) || empty($filename)) {
	        return false;
	    }
	    
	    $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');

	    return $HandlePHPExcel->saveToExcelFile($datas, $filename);
	}

	/**
	 * 将excel数据转换成关联数组
	 * @param $datas
	 */
	protected function parseToAssocArr($datas, $client_type) {
	    if(empty($datas)) {
	        return false;
	    }
	    $client_type = intval($client_type);

	    $assoc_arr = $fail_list = array();
	    $fields = $this->_config[$client_type]['fields'];

	    $total_nums = 0;
	    foreach($datas as $record) {
	        $assoc_data = array();
	        $assoc_key = "";
	        foreach($record as $i=>$val) {
	            if(!isset($fields[$i])) {
	                continue;
	            }
	            list($field, $type, $is_key) = $fields[$i];
	            if($type == 'int') {
	                $val = intval($val);
	            } elseif($type == 'string') {
	                $val = addslashes(trim(strval($val)));
	            }
	            $is_key && $assoc_key = $val;

	            $assoc_data[$field] = $val;
	        }

	        //空行数据不做处理
	        if(self::is_empty($assoc_data)) {
	            continue;
	        }

	        if(!empty($assoc_key) && !isset($assoc_arr[$assoc_key])) {
	            $assoc_arr[$assoc_key] = $assoc_data;
	        } else {
	            $msg = !empty($assoc_key) ? "账号重复 " : "账号信息不全";
	            $assoc_data['msg'] = $msg;
	            $fail_list[] = $assoc_data;
	        }
	        $total_nums++;
	    }

	    return array($assoc_arr, $fail_list, $total_nums);
	}

	/**
	 * 判断数据是否为空
	 * @param $datas
	 */
	protected static function is_empty($datas) {
	    if(empty($datas)) {
	        return true;
	    }
	    foreach((array)$datas as $val) {
	        if(!empty($val)) {
	            return false;
	        }
	    }
	    return true;
	}

	/**
	 * 过滤数据
	 * @param $datas
	 */
	protected function filterDatas($datas) {
	    if(empty($datas)) {
	        return false;
	    }

	    $fail_list = array();
	    $uids = array_keys($datas);
	    //过滤锁定的账号
	    $mAccountLock = ClsFactory::Create('Model.mAccountLock');
	    $lock_list = $mAccountLock->getAccountLockById($uids);
	    if(!empty($lock_list)) {
	        $lock_uids = array_keys($lock_list);
	        unset($lock_list);
	        $msg = "账号锁定";
	        foreach($lock_uids as $uid) {
	            $fail_msg = $datas[$uid];
	            $fail_msg['msg'] = $msg;
	            $fail_list[$uid] = $fail_msg;
	            unset($datas[$uid]);
	        }
	    }

	    $mUser = ClsFactory::Create('Model.mUser');
	    //账号的格式检测，位数
	    if(!empty($datas)) {
	        $length_min = 5;
	        
    	    $mAccountRule = ClsFactory::Create('Model.mAccountRule');
            $user_flag = 1;//使用标志
	        $current_length_list = $mAccountRule->getAccountRuleByUseFlag($user_flag);
    	    
    	    $current_length = reset($current_length_list);
    	    $length_max = $current_length['account_length'];

    	    $length_max = $current_length;
    	    foreach($datas as $uid=>$val) {
    	        $len = strlen($uid);
    	        if($len < $length_min || $len > $length_max) {
    	            $val['msg'] = "账号位数不正确";
    	            $fail_list[$uid] = $val;
    	            unset($datas[$uid]);
    	        }
    	    }
	    }
	    return array($datas, $fail_list);
	}

	/**
	 * 绑定相关的值信息
	 * @param $datas
	 */
	protected function bindDatasAttrs($datas, $client_type) {
	    if(empty($datas)) {
	        return false;
	    }

	    $client_type = intval($client_type);
	    $fail_list = array();
	    $bind_fields = $this->_config[$client_type]['bind_fields'];

	    if(!empty($bind_fields)) {
    	    foreach($datas as $key=>$val) {
    	        foreach($bind_fields as $field=>$attrs) {
    	            if(isset($val[$field])) {
    	                $bind_val = array_search($val[$field], $attrs);
    	                if(is_null($bind_val) || $bind_val === false) {
    	                    $val['msg'] = "参数值不合法!";
    	                    $fail_list[] = $val;
    	                    unset($datas[$key]);
    	                    continue;
    	                } else {
    	                    $val[$field] = $bind_val;
    	                }
    	            }
    	        }
    	        $datas[$key] = $val;
    	    }
	    }

	    return array($datas, $fail_list);
	}

	/**
	 * 批量导入到账号基本信息表中
	 */
	protected function import_client_account($account_list, $info_list) {
	    if(empty($account_list) || empty($info_list)) {
	        return false;
	    }

	    $fail_list = $rollback_list = $recall_list = array();
	    $uids = array_keys($account_list);

	    //检测数据库中是否存在该账号信息
	    $mUser = ClsFactory::Create('Model.mUser');
	    $user_list = $mUser->getUserBaseByUid($uids);
	    if(!empty($user_list)) {
	        $exist_uids = array_keys($user_list);
	        unset($user_list);

	        foreach($exist_uids as $uid) {
	            $fail_list[$uid] = '账号信息已经存在!';
	            unset($account_list[$uid], $info_list[$uid]);
	        }
	    }

	    //批量导入数据
	    if(!empty($account_list) && !empty($info_list)) {
	        $chunck_accout_arr = array_chunk($account_list, 100, true);
	        foreach($chunck_accout_arr as $chunck_account_list) {
	            $mUser->addUserClientAccountBat($chunck_account_list);
	        }

	        $chunck_info_arr = array_chunk($info_list, 100, true);
	        foreach($chunck_info_arr as $chunck_info_list) {
	            $mUser->addUserClientInfoBat($chunck_info_list);
	        }

	        $import_uids = array_keys($account_list);
	        unset($account_list, $info_list);

	        //检测导入是否成功
    	    $imported_user_list = $mUser->getUserBaseByUid($import_uids);
    	    if(!empty($imported_user_list)) {
    	        $imported_uids = array_keys($imported_user_list);
    	        unset($imported_user_list);

    	        $diff_arr = array_diff((array)$import_uids, (array)$imported_uids);
    	        foreach($diff_arr as $uid) {
    	            $fail_list[$uid] = "账号导入失败!";
    	        }
    	        //获取需要回滚的账号信息
    	        $rollback_list = $recall_list = $diff_arr;
    	    }
    	    $this->rollback_client_account($rollback_list);

	        $this->build_rollback_stack('client_account', $recall_list);
	    }
	    return !empty($fail_list) ? $fail_list : false;
	}

	/**
	 * 回滚用户的基本信息表
	 * @param $uids
	 */
	protected function rollback_client_account($uids) {
	    if(empty($uids)) {
	        return false;
	    }

	    $mUser = ClsFactory::Create('Model.mUser');
	    return $mUser->delUserAllInfo($uids);
	}

	protected function import_client_class($clientclass_list) {
	    if(empty($clientclass_list)) {
	        return false;
	    }

	    $fail_list = $rollback_list = $recall_list = array();

	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    //导入相关数据
	    $chunk_clientclass_arr = array_chunk($clientclass_list, 500, true);
	    foreach($chunk_clientclass_arr as $chunk_clientclass_list) {
	        $mClientClass->addClientClassBat($chunk_clientclass_list);
	    }
	    unset($chunk_clientclass_arr);
	    
	    //todolist
//	    import('@.Common_wmw.functions', null, '.php');
//	    moniter_control($this->user, __METHOD__ . ":addClientClassBat", count($clientclass_list));

	    $relation_clientclass_list = $imported_relations = $uids = array();
	    foreach($clientclass_list as $clientclass) {
	        $relation_clientclass_list[] = $clientclass['client_account'] . "_" . $clientclass['class_code'];
	        $uids[] = $clientclass['client_account'];
	    }
	    unset($clientclass_list);

	    //检测数据是否全部导入成功
	    if(!empty($uids)) {
	        $uids = array_unique($uids);
	        $client_class_arr = $mClientClass->getClientClassByUid($uids);
	        
	        $imported_clientclass_list = array();
		    if ( !empty($client_class_arr) ) {
		    	foreach ( $client_class_arr as $key=>$list ) {
		    		foreach ($list as $key1=>$val) {
		    			$imported_clientclass_list[$val['client_account']][$val['class_code']] =$val; 
		    		}
		    		
		    	}	
		    }
		    unset($client_class_arr);
	        
	        
	        if(!empty($imported_clientclass_list)) {
	            foreach($imported_clientclass_list as $uid=>$list) {
	                foreach($list as $clientclass) {
	                    $imported_relations[] = $uid . "_" . $clientclass['class_code'];
	                }
	                unset($imported_clientclass_list[$uid]);
	            }
	            //比对关系集合
	            $diff_arr = array_diff((array)$relation_clientclass_list, (array)$imported_relations);
	            if(!empty($diff_arr)) {
	                foreach($diff_arr as $val) {
	                    list($uid, $class_code) = explode('_', $val);
	                    $fail_list[$uid] = "班级关系导入失败!";
	                    $recall_list[$uid] = $uid;
	                }
	                $rollback_list = & $diff_arr;
	            }
	        }
	    }
	    $this->rollback_client_class($rollback_list);
	    $this->build_rollback_stack('client_class', $recall_list);

	    return !empty($fail_list) ? $fail_list : false;
	}

	/**
	 * 回滚clientclass相关数据
	 * @param $clientclass_relation = array(0=>"client_account" + "_" + "class_code");
	 */
	protected function rollback_client_class($clientclass_relations) {
	    if(empty($clientclass_relations)) {
	        return false;
	    }

	    $uids = array();
	    foreach($clientclass_relations as $relation) {
	        list($uid, $class_code) = explode('_', $relation);
	        $uids[$uid] = $uid;
	    }

	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $client_class_arr = $mClientClass->getClientClassByUid($uids);

	    $clientclass_list = array();
	    if ( !empty($client_class_arr) ) {
	    	foreach ( $client_class_arr as $key=>$list ) {
	    		foreach ($list as $key1=>$val) {
	    			$clientclass_list[$val['client_account']][$val['class_code']] =$val; 
	    		}
	    		
	    	}	
	    }
	    unset($client_class_arr);
	    
	    $del_nums = 0;
	    if(!empty($clientclass_list)) {
	        foreach($clientclass_list as $uid=>$list) {
	            foreach($list as $clientclass) {
	                $pramary_key = intval($clientclass['client_class_id']);
	                $search_val = $clientclass['client_account'] . "_" . $clientclass['class_code'];

	                $search_key = array_search($search_val, $clientclass_relations);
	                if(is_null($search_key) || $search_key === false) {
	                    continue;
	                }
	                $mClientClass->delClientClass($pramary_key);
	                $del_nums++;
	            }
	            unset($clientclass_list[$uid]);
	        }
	    }

	    return $del_nums;
	}

	/**
	 * 导入家庭关系，并自动建立回滚栈信息
	 * @param $relation_datas
	 * @return array(导入失败的主线id=>错误类型信息);
	 */
	protected function import_family_relation($relation_datas) {
	    if(empty($relation_datas)) {
	        return false;
	    }

	    $fail_list = $rollback_list = $recall_list = array(); //当前回滚和回溯影响
	    //导入数据
	    $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	    $chunk_relation_arr = array_chunk($relation_datas, 500, true);
	    foreach($chunk_relation_arr as $chunk_relation_datas) {
	        $mFamilyRelation->addFamilyRelationBat($chunk_relation_datas);
	    }
	    unset($chunk_relation_arr);
	    
	    //todolist
//	    import('@.Common_wmw.functions', null, '.php');
//	    moniter_control($this, __METHOD__ . ":addFamilyRelationBat", count($relation_datas));

	    //建立数据的关系
	    $student_family_relation = $uids = array();
	    foreach($relation_datas as $data) {
	        $client_account = $data['client_account'];
	        $family_account = $data['family_account'];

	        $student_family_relation[$client_account][$family_account] = $family_account;
	        $uids[$client_account] = $client_account;
	    }
	    unset($relation_datas);

	    $uids = array_unique($uids);
	    //检测数据是否导入成功
	    $error_msg = "家庭关系导入失败!";
	    $tmp_familyrelation_list = $mFamilyRelation->getFamilyRelationByUid($uids);
	    $familyrelation_list = array();
	    if(!empty($tmp_familyrelation_list)) {
    	    foreach($tmp_familyrelation_list as $uid => $relation_list) {
    	        foreach($relation_list as $relation_info) {
    	            $familyrelation_list[$uid][$relation_info['family_account']] = $relation_info;
    	        }
    	        
    	    }
	    }
	    

        foreach($student_family_relation as $uid=>$family_accounts) {
            $need_rollback = true;
            if(isset($familyrelation_list[$uid])) {
                $imported_family_accounts = array_keys($familyrelation_list[$uid]);
                $diff_arr = array_diff((array)$family_accounts, (array)$imported_family_accounts);
                if(empty($diff_arr)) {
                    $need_rollback = false;
                }
            }
            //关系导入不全,或者相应的关系不存在
            if($need_rollback){
                $fail_list[$uid] = $error_msg;
                $recall_list = array_merge((array)$recall_list, (array)$family_accounts, array($uid=>$uid));
                $rollback_list[$uid] = $uid;
            }

            unset($familyrelation_list[$uid]);
        }
	    $this->rollback_family_relation($rollback_list);
	    //建立相应的回滚堆栈
	    $this->build_rollback_stack('family_relation', $recall_list);

	    return !empty($fail_list) ? $fail_list : false;
	}

    protected function rollback_family_relation($uids) {
	    if(empty($uids)) {
	        return false;
	    }

	    $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	    return $mFamilyRelation->delFamilyRelationByCompositeKeys($uids);
	}

	protected function import_school_teacher($datas){
	    if(empty($datas)){
	        return false;
	    }

	    $import_uid = array_keys($datas);
	    $fail_list = $recall_list = $rollback_list = array();
	    $mSchoolTeacher = ClsFactory::Create('Model.mSchoolTeacher');
	    $SchoolTeacher_info = $mSchoolTeacher->getSchoolTeacherByTeacherUid($import_uid);
	    if(!empty($SchoolTeacher_info)) {
	        $exist_uids = array_keys($SchoolTeacher_info);
	        unset($SchoolTeacher_info);

	        foreach($exist_uids as $uid) {
	            $fail_list[$uid] = '账号信息已经存在!';
	            unset($datas[$uid]);
	        }
	    }
	    if(!empty($datas)) {
	        $teach_datas  = array_chunk($datas, 100, true);
	        foreach($teach_datas as $teach_data ){
	            $mSchoolTeacher->addSchoolTeacherBat($teach_data);
	        }
	        $import_uid = array_keys($datas);
	        $SchoolTeacher_info = $mSchoolTeacher->getSchoolTeacherByTeacherUid($import_uid);
	        if(!empty($SchoolTeacher_info)){
	            $imported_list = array_keys($SchoolTeacher_info);
    	        $diff_arr = array_diff((array)$import_uid, (array)$imported_list);
    	        foreach($diff_arr as $uid) {
        	            $fail_list[$uid] = "账号导入失败!";
        	        }
    	        $rollback_list = $recall_list = $diff_arr;
	        }
    	    $this->rollback_client_account($rollback_list);

	        $this->build_rollback_stack('school_teacher', $recall_list);
	    }

	    return !empty($fail_list) ? $fail_list : false;
	}

	protected function getsubjectinfo($school_id, $default_subject_name){
	    if(empty($school_id)){
	        return false;
	    }
	    $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	    $school_subject_info = $mSubjectInfo->getSubjectInfoBySchoolid($school_id);
        $school_subject_info = & $school_subject_info[$school_id];
	    
	    if(empty($default_subject_name)) {
	        $subject_list = array_shift($school_subject_info);
	        unset($school_subject_info);
	        $subject_id = $subject_list['subject_id'];
	    } else {
	        foreach($school_subject_info as $subjectid => $subject_info){
	            if(trim($subject_info['subject_name']) == trim($default_subject_name)){
	                $subject_id = $subjectid;
	                break;
	            }
	        }
	        unset($school_subject_info);
	    }

	    return !empty($subject_id) ? $subject_id : false;

	}

	/**
	 * 建立回滚需要的栈信息
	 * @param  $table_name
	 * @param $uids 账号id信息
	 */
	protected function build_rollback_stack($table_name, $uids) {
	    //只要调用了则相应的表对应的记录要占位
	    $is_first = false;
	    if(!empty($table_name) && !isset($this->_stack[$table_name])) {
	        $this->_stack[$table_name] = array();
	        $is_first = true;
	    }

	    if(empty($uids)) {
	        return;
	    }

        $keys = array_keys($this->_stack);
        foreach($keys as $key) {
            $merge_arr = array();
            $format = $this->_stack_format[$key];

            if(is_null($format) || $format === false) {
                throw new Exception('配置有误!', 1);
            }
            list($comma, $field) = explode(':', $format);
            if(!empty($field)) {
                $field_val = $this->get_stack_attr($field);
                if($field_val === false) {
                    throw new Exception('需要预先设定替换值!', -2);
                }
                foreach($uids as $k=>$uid) {
                    $uid = $uid . "_" . $field_val;
                    $merge_arr[$k] = $uid;
                }
            } else {
                $merge_arr = $uids;
            }
            //只有第一次初始化的时候不对数据进行追加
            if($key == $table_name && $is_first) {
                $merge_arr = array();
            }
            $this->_stack[$key] = array_unique(array_merge((array)$this->_stack[$key], (array)$merge_arr));

            if($key == $table_name) {
                break;
            }
        }
	}

	/**
	 * 执行回滚操作
	 */
	protected function run_rollback() {
	    if(empty($this->_stack)) {
	        return false;
	    }
	    foreach($this->_stack as $tab_name=>$params) {
	        $func_name = "rollback_" . $tab_name;
	        if(!method_exists($this, $func_name)) {
	            throw new Exception('调用的函数不存在!', -3);
	        }
	        call_user_func_array(array($this, $func_name), array($params));
	    }

	    return true;
	}

	protected function set_stack_attrs($arr) {
	    if(!empty($arr) && is_array($arr)) {
	        $this->_stack_attrs = array_merge((array)$this->_stack_attrs, (array)$arr);
	    }
	    return $this;
	}

	protected function get_stack_attr($field) {
	    if(empty($field)) {
	        return false;
	    }
	    return isset($this->_stack_attrs[$field]) ? $this->_stack_attrs[$field] : false;
	}

	protected function get_excelfile_pre() {
	    return defined("EXCEL_FILE_NAME_PRE") && EXCEL_FILE_NAME_PRE ? EXCEL_FILE_NAME_PRE : "old_";
	}

	protected function excelname_encode($name) {
	    if(empty($name)) {
	        return false;
	    }

	    $name_pre = $this->get_excelfile_pre();
	    $name_suffix = time();
	    $encode_name = $name_pre . $name . "_" . $name_suffix;

	    return $encode_name;
	}

	protected function getExcelFilePath($name, $extension = null) {
	    if(empty($name)) {
	        return false;
	    }

	    $extension = $extension && in_array($extension, array('xls', 'xlsx')) ? $extension : "xls";
	    $name_encode = $this->excelname_encode($name);

	    return EXCEL_FILE_DIR . "/" . $name_encode . "." . $extension;
	}

	protected function autoClear() {
	    if(!constant('AUTO_CLEAR')) {
	        return false;
	    }

	    //文件过期时间设置,单位小时
	    $expiration_time = 24;
	    //每次清理的最大文件数
	    $filenums_limit = 10;

	    $dir_name = defined('EXCEL_FILE_DIR') && EXCEL_FILE_DIR ? EXCEL_FILE_DIR : "";
	    if(empty($dir_name) || !is_dir($dir_name)) {
	        return false;
	    }

	    $excelfile_pre = $this->get_excelfile_pre();
	    $time_now = time();

	    $counter = 0;
	    $dir = dir($dir_name);
	    while(($file = $dir->read()) !== false) {
	        if(in_array($file, array('.', '..')) || !in_array(pathinfo($file, PATHINFO_EXTENSION), array('xls', 'xlsx'))) {
	            continue;
	        }
	        //查找满足当前自动清理的excel文件
	        if(stripos($file, $excelfile_pre) !== false) {
	            $filename = $dir_name . "/" . $file;
	            $filectime = filectime($filename);
	            if($time_now - $filectime >= $expiration_time * 3600) {
	                @unlink($filename);
	            }
	            if($counter++ >= $filenums_limit) {
	                break;
	            }
	        }
	    }

	    return $counter;
	}

	//析构函数，清理系统不必要的文件
	public function __destruct() {
	    $this->autoClear();
	}
}