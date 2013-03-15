<?php
class SchoolmanageAction extends WmsController {
    private $_mobj;
    const LENGTH = 10;
    public function _initialize() {
        parent::_initialize();
        
        header ( "Content-Type:text/html; charset=utf-8" );
        import ( "@.Common_wmw.Constancearr" );
        import("@.Common_wmw.WmwString");
    }
    
    //获取cookie中的用户账号
    function getCookieAccount() {
        return $this->user['wms_account'];
    }

    //查出他所录入的已通过和未处理的学校信息
    public function updateschool() {
        $currentpage = $this->objInput->getInt ( 'page' );
        if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $name = $this->objInput->postStr('name');
            $flag = $this->objInput->postInt('flag');
            $pub = $this->objInput->postInt('pub');
            $status = $this->objInput->postInt("status");
        } else {
            $flag = $this->objInput->getInt('flag');
            $pub = $this->objInput->getInt('pub');
            $status = $this->objInput->getInt('status');
        }
        
       
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        if (empty ( $currentpage ) || ! isset ( $currentpage )) {
        $currentpage = 1;
        $page ['prepage'] = 0;
        $page ['nextpage'] = 2;
        } else {
        if ($currentpage <= 1) {
        $page ['prepage'] = 0;
        $page ['nextpage'] = 2;
        } else {
        $page ['prepage'] = $currentpage - 1;
        $page ['nextpage'] = $currentpage + 1;
        }
        }
        
        if (empty ( $flag )) {
            $flag = 0;
        }
        
        $offset = ($currentpage - 1) * self::LENGTH;
        
        if(!empty($pub) || $pub === 0) {
            $schoolInfo = $mSchool->getAllSchoolInfoByPub ( $offset, self::LENGTH + 1, 0, $pub, $status, $name );
        } else{
            $schoolInfo = $mSchool->getAllSchoolInfo ( $offset, self::LENGTH + 1, 0, $flag, $name );
        }
        
        $count = count ( $schoolInfo );
        $num = ($page ['nextpage'] - 2) * 10 + 1;
        if ($count < self::LENGTH + 1) {
        $page ['prepage'] = $currentpage - 1;
        $page ['nextpage'] = 'end';
        }
        if (count ( $schoolInfo ) > 10)
            array_pop ( $schoolInfo );
        if (! empty ( $schoolInfo )) {
        foreach ( $schoolInfo as $key => $school ) {
        if (isset ( $school ['upd_date'] )) {
        list ( $year, $month, $day ) = explode ( "-", $school ['upd_date'] );
        $year = intval ( $year );
        $month = intval ( $month );
        if (empty ( $year ) && empty ( $month )) {
        unset ( $school ['upd_date'] );
        }
        }
        $school ['shownum'] = $num;
        $schoolInfo [$key] = $school;
        $num ++;
        }
        }
        
        $this->assign( 'flag', $flag );
        $this->assign('pub' , $pub);
        $this->assign('status',$status);
        $this->assign( 'name', $name );
        $this->assign( 'currentpage', $currentpage );
        $this->assign( 'schoolInfo', $schoolInfo );
        $this->assign( 'page', $page );
        
        $this->display( 'schoolupdatemanage' );
    }

    //填写修改的学校信息
    public function saveschool() {
        $schoolid = $this->objInput->getInt ( 'schoolid' );
        if (empty ( $schoolid ))
            return false;
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $schoolArr = $mSchool->getSchoolInfoById ( $schoolid );
        if ($schoolArr [$schoolid] ['school_url_new'] == "无")
            $schoolArr [$schoolid] ['school_url_new'] = "";
        $schoolArr [$schoolid] ['school_create_date'] = substr($schoolArr [$schoolid] ['school_create_date'],0,-1)."1";
        $area = getAreaidSelect ( $schoolArr [$schoolid] ['area_id'] );

        $operation_strategy = max(0,intval($schoolArr [$schoolid]['operation_strategy']));
        $this->assign ( 'schooltype', Constancearr::school_type());
        $this->assign ( 'gradetype', Constancearr::grade_type());
        $this->assign ( 'resources', Constancearr::school_resource_advantage() );
        $this->assign("schoolid",$schoolid);
        $this->assign('operation_strategy',$operation_strategy);
        $this->assign ( 'schoolinfo', $schoolArr [$schoolid] );
        $this->assign ( 'area', $area );
        $this->display ( 'showSchoolApp' );
    }

    //修改学校信息
    public function confirmchange() {
        //将用户输入信息组建成信息数组：
        $flag = true;
        $schoolid = $this->objInput->postInt ( 'schoolid' );
        if (empty ( $schoolid )) {
        $flag = false;
        } else {
        $data = array (//学校基本资料
                        'school_name' => $this->objInput->postStr ( 'schoolName' ),
                        'area_id' => $this->objInput->postStr ( 'area_id' ), //省市区
                		'school_address' => $this->objInput->postStr ( 'schoolAddress_Content' ),
                        'post_code' => $this->objInput->postStr ( 'zipCode' ), //邮编有的是以0开头，故用str接收。
                		'school_create_date' => substr ( $this->objInput->postStr ( 'createSchoolDate' ), 0, 7 ) . '-00',
                		'school_type' => $this->objInput->postInt ( 'schoolType' ),
        				'grade_type' => $this->objInput->postInt ( 'gradeType' ),
                		'resource_advantage' => $this->objInput->postInt ( 'schoolGrade' ),
                		'school_master' => $this->objInput->postStr ( 'principal' ),
                		'contact_person' => $this->objInput->postStr ( 'contact' ), //师生情况
                        'class_num' => $this->objInput->postInt ( 'classNum' ),
                        'teacher_num' => $this->objInput->postInt ( 'teachNum' ),
                        'student_num' => $this->objInput->postInt ( 'studentNum' ),//学校网络负责人
                        'net_manager' => $this->objInput->postStr ( 'personInCharge' ),
                        'net_manager_phone' => $this->objInput->postStr ( 'PICcontact' ),
                        'net_manager_email' => $this->objInput->postStr ( 'setMail' ), //校园门户网站申请
                        'school_url_old' => $this->objInput->postStr ( 'oldWebUrl' ),
                        'school_url_new' => strpos($this->objInput->postStr ('newWebUrl' ),'.wmw.cn') ? $this->objInput->postStr ( 'newWebUrl' ):$this->objInput->postStr ( 'newWebUrl').'.wmw.cn',//《教育信息化公共服务平台申请表》扫描件
                        'upd_date' => date ( "Y-m-d H:i:s" ),
                        'operation_strategy'=>max(0,intval($this->objInput->postInt('operation_strategy'))),
                        'upd_account' => $this->getCookieAccount (),
                        'upd_date' => time (),
                        'is_pub' => $this->objInput->postInt('is_publish')       
                     );
        if (! empty ( $data )) { //数据验证：
            $sn_length = WmwString::mbstrlen( $data ['school_name'] );
            if (empty ( $data ['school_name'] ) || $sn_length < 2 || $sn_length > 20) {
            $this->showError("校名不能为空，长度在2-20之间", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //街道地址长度
        $sm_length = WmwString::mbstrlen( $data ['school_address'] );
            if ($sm_length < 2 || $sm_length > 50) {
            $this->showError("街道地址长度在2-30之间", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        // 邮编验证 六位数字
        $preg_postcode = '/\d{6}/';
        if (preg_match ( $preg_postcode, $data ['post_code'] ) == false) {
            $this->showError("邮编格式不正确", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }

        //校长名称长度
        $sm_length = WmwString::mbstrlen( $data ['school_master'] );
        if ($sm_length < 2 || $sm_length > 50) {
            $this->showError("校长名称不能为空，长度在2-30之间", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }

        $sm_length = WmwString::mbstrlen( $data ['contact_person'] );
        if ($sm_length < 8 || $sm_length > 11) {
            $this->showError("电话格式不正确", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //验证纯数字输入
        $preg_num = '/\d/';
        if ((preg_match ( $preg_num, $data ['class_num'] ) == false) || (preg_match ( $preg_num, $data ['teacher_num'] ) == false) || (preg_match ( $preg_num, $data ['student_num'] ) == false)) {
            $this->showError("师生情况请填入数字", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //网络负责人名称长度
        $nm_length = WmwString::mbstrlen( $data ['net_manager'] );
        if ($nm_length < 2 || $nm_length > 30) {
            $this->showError("网络负责人名称长度在2-30之间", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //网络负责人电话验证
        /* if((preg_match($preg_phone,$data['net_manager_phone'])==false)){
                    echo "网络负责人电话格式不正确";
                    return false;
                }*/
        $sm_length = WmwString::mbstrlen( $data ['net_manager_phone'] );
        if ($sm_length < 8 || $sm_length > 11) {
            $this->showError("电话格式不正确", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //验证邮箱格式：
        //$preg_email =  "/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/";
        $preg_email = "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/";
        if (preg_match ( $preg_email, $data ['net_manager_email'] ) == false) {
            $this->showError("网络负责人邮箱格式不正确", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }
        //验证学校原网址
        //$preg_url = "/^(http:\/\/){0,1}w{3}\.[\w-]+\.(com|net|org|gov|cc|biz|info|cn)(\.(cn|hk))*$/";
        $preg_url = "/^(http(s)?:\/\/)?([\w]+\.)+[\w]+([\w-.\/?%&=]*)?$/";
        //^(http[s]?:\\/\\/)?([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$
        if (empty ( $data ['school_url_old'] )) {
            $data ['school_url_old'] = 'www.wmw.cn';
        } elseif (preg_match ( $preg_url, $data ['school_url_old'] ) == false) {
            $this->showError("原校园网址格式不正确", "/Admingroup/Schoolmanage/updateschool");
            $flag = false;
        }

        //新网址申请验证，未填写时跳过
        if ($data ['school_url_new'] == '.wmw.cn') {
            $data ['school_url_new'] = '无';
        } else {
        //$newurl_preg  = '/^(http:\/\/){0,1}[w{3}]\.[\w-]*.*(\.wmw\.(cn))*$/';
            $newurl_preg = "/^([\w-]+\\.)+[\w-]+([\w-.\/?%&=]*)?$/";
            if (preg_match ( $newurl_preg, $data ['school_url_new'] ) == false) {
            	$this->showError("申请的新网址格式不正确", "/Admingroup/Schoolmanage/updateschool");
                $flag = false;
            }
        }
        }
        if($flag){
            $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
            $result = $mSchool->modifySchoolInfo($data,$schoolid);
            if($result){
                
                //更新redis 学校信息
                $mHashSchool = ClsFactory::Create ( 'RModel.Common.mHashSchool' );
                $mHashSchool->getSchoolbyId($schoolid, true);
                
            	$this->showSuccess("修改成功", "/Admingroup/Schoolmanage/updateschool");
            }else{
            	$this->showError("修改失败", "/Admingroup/Schoolmanage/updateschool");
            }
        }else{
        	$this->showError("修改失败", "/Admingroup/Schoolmanage/updateschool");
        }

        }
    }

    //查出他所录入的学校信息
    public function getSchool() {
        $currentpage = $this->objInput->getInt ( 'page' );
        if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
            $uid = $this->objInput->postStr('uid');
            $flag = $this->objInput->postInt('flag');
        } else {
            $uid = $this->objInput->getStr('uid');
            $flag = $this->objInput->getInt('flag');
        }
        
        $uid = !empty($uid) ? $uid : 0;
        $flag = in_array($flag, array(0, 1, 2)) ? $flag : 0;
        $currentpage = max($currentpage, 1);
           
        $offset = ($currentpage - 1) * self::LENGTH;
        
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $schoolInfo = $mSchool->getAllSchoolInfo($offset, self::LENGTH + 1, $uid, $flag);
        
        //获取分页显示信息
        $page = array(
            'prepage' => $currentpage > 1 ? $currentpage - 1 : 0,
            'nextpage' => count($schoolInfo) > self::LENGTH ? $currentpage + 1 : 'end',
        );
        
        $schoolInfo = array_slice($schoolInfo, 0, self::LENGTH, true);
        if (!empty($schoolInfo)) {
            //设置序号的起始值
            $num = $offset + 1;
            foreach($schoolInfo as $key => $school) {
                if(isset($school['upd_date'])) {
                    list($year, $month, $day ) = explode ( "-", $school ['upd_date'] );
                    $year = intval($year);
                    $month = intval($month);
                    if (empty($year) && empty($month )) {
                        unset($school['upd_date']);
                    }
                }
                $school['shownum'] = $num++;
                $schoolInfo[$key] = $school;
            }
        }
        
        $this->assign('flag', $flag);
        $this->assign('uid', $uid);
        $this->assign('currentpage', $currentpage);
        $this->assign('schoolInfo', $schoolInfo);
        $this->assign('page', $page);
        
        $this->display('schoolmanage');
    }

    //显示没有通过审核的原因
    public function showRefuseReason() {
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $schoolInfo = $mSchool->getSchoolInfoById ( $this->objInput->getInt ( 'sid' ) );
        echo $schoolInfo [$this->objInput->getInt ( 'sid' )] ['refuse_reason'];
    }

    //显示学校的扫描件
    public function showScanningCopy() {
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $sid = $this->objInput->getInt ( 'sid' );
        $schoolInfo = $mSchool->getSchoolInfoById ( $sid );
        $imgsrc = $schoolInfo [$sid] ['school_scan'];
        $this->assign ( 'imgsrc', $imgsrc );
        
        $this->display ( 'showschoolscan' );
    }

    //显示学校的详细信息
    public function getSchoolInfo() {
        $mSchool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $sid = $this->objInput->getInt ( 'sid' );
        $schoolInfo = $mSchool->getSchoolInfoById ( $sid );
        $info = array ('school_name' => '', 'school_address' => '', 'post_code' => '', 'school_create_date' => '', 'school_type' => '', 'resource_advantage' => '', 'school_master' => '', 'contact_person' => '', 'class_num' => '', 'teacher_num' => '', 'student_num' => '', 'net_manager' => '', 'net_manager_phone' => '', 'net_manager_email' => '', 'school_url_old' => '', 'school_url_new' => '' );

        $addressList = getAreaNameList( $schoolInfo [$sid] ['area_id'] );
        $info ['school_name'] = $schoolInfo [$sid] ['school_name'];
        $info ['school_address'] = $addressList ['province'] . $addressList ['city'] . $addressList ['county'] . $schoolInfo [$sid] ['school_address'];
        $info ['post_code'] = $schoolInfo [$sid] ['post_code'];
        $info ['school_create_date'] = $schoolInfo [$sid] ['school_create_date'];
        $info ['school_type'] = Constancearr::school_type($schoolInfo [$sid] ['school_type']);
        $info ['grade_type'] = Constancearr::grade_type($schoolInfo [$sid] ['grade_type']);
        $info ['resource_advantage'] = Constancearr::school_resource_advantage($schoolInfo [$sid] ['resource_advantage']);
        $info ['school_master'] = $schoolInfo [$sid] ['school_master'];
        $info ['contact_person'] = $schoolInfo [$sid] ['contact_person'];
        $info ['class_num'] = $schoolInfo [$sid] ['class_num'];
        $info ['teacher_num'] = $schoolInfo [$sid] ['teacher_num'];
        $info ['student_num'] = $schoolInfo [$sid] ['student_num'];
        $info ['net_manager'] = $schoolInfo [$sid] ['net_manager'];
        $info ['net_manager_phone'] = $schoolInfo [$sid] ['net_manager_phone'];
        $info ['net_manager_email'] = $schoolInfo [$sid] ['net_manager_email'];
        $info ['school_url_old'] = $schoolInfo [$sid] ['school_url_old'];
        $info ['school_url_new'] = $schoolInfo [$sid] ['school_url_new'];

        if (! empty ( $info ['school_create_date'] )) {
        $info ['school_create_date'] = date ( "Y-m", strtotime ( $info ['school_create_date'] ) );
        } else {
        $info ['school_create_date'] = "暂无";
        }

        if ($schoolInfo) {
        $schoolInfom = array ('error' => array ('code' => 1, 'message' => '系统繁忙' ), 'data' => $info );
        } else {
        $schoolInfom = array ('error' => array ('code' => - 1, 'message' => '系统繁忙' ) );
        }
        echo json_encode ( $schoolInfom );
    }

    //审核的处理方法和结果
    public function addSchoolInfo() {
        $schoolid = $this->objInput->getStr ( 'schoolid' );
        $mschool = ClsFactory::Create ( 'Model.mSchoolInfo' );
        $schoolInfo = $mschool->getSchoolInfoById ( $schoolid );
        $net_manage = $schoolInfo [$schoolid] ['net_manager'];
        $addaccount = $this->getCookieAccount ();
        if ($this->objInput->getStr ( 'sellid' )) {
            $sellid = $this->objInput->getStr ( 'sellid' );
            
            $mAccountRule = ClsFactory::Create('Model.mAccountRule');
            $user_flag = 1;//使用标志
	        $accountlength = $mAccountRule->getAccountRuleByUseFlag($user_flag);
            $accountlength = array_shift($accountlength);
            $num = $accountlength['account_length'];
            $upd_time = time();
            $mAmsAccount = ClsFactory::Create ( 'Model.mAmsAccount' );

            while(true) {
                $new_account = $this->createAccount($num);
                
                $ams_account = $mAmsAccount->getAmsAccountByUid($new_account);
                if (empty($ams_account)) {
                    break;
                }
            }
            
            $pwd = trim (rand(100000, 999999));

            $school = array (
            	'school_status' => SCHOOL_STATUS_PASS, 
            	'operation_strategy' => $sellid, 
            	'check_date' => date ("Y-m-d H:i:s",$upd_time), 
            	'net_manager_account' => $new_account 
            );

            $accountinfo = array (
            	 'ams_account' => $new_account,
                 'ams_name'=>$schoolInfo [$schoolid] ['school_name'] . "管理员",
                 'ams_password' => md5 ( $pwd ),
                 'ams_email'=> $schoolInfo [$schoolid] ['net_manager_email'],
                 'add_date' => $upd_time 
            );
            
            $school = array ('school_status' => SCHOOL_STATUS_PASS, 'operation_strategy' => $sellid, 'check_date' => date ( "Y-m-d H:i:s" ), 'net_manager_account' => $new_account );

            $email = $schoolInfo [$schoolid] ['net_manager_email'];
            $emailContent = '您好,您的学校' . $schoolInfo [$schoolid] ['school_name'] . '已经通过审核,您的管理员账号为:' . $new_account . "。" . '密码为:' . $pwd . '请您妥善保管。' . "请持登录账号和密码登录ams账号管理系统进行学校相关信息的设置，访问地址为：http://ams.wmw.cn/";
    
            //查询申请该学校的基地账号
            $mSchoolRequest = ClsFactory::Create ( 'Model.mSchoolRequest' );
            $schoolRequestinfo = $mSchoolRequest->getSchoolRequestBySchool_id ( $schoolid );
	        $schoolRequestinfo = reset($schoolRequestinfo[$schoolid]);
            $add_account = $schoolRequestinfo['add_account'];

            //通过账号查询该基地的邮箱
            $mBaseUser = ClsFactory::Create ( 'Model.mBmsAccount' );
            $base_account_info = $mBaseUser->getUserInfoByUid ( $add_account );
            $base_account_info = array_shift($base_account_info);
            $base_email = $base_account_info ['base_email'];
            $mAmsAccount ->addAmsAccount($accountinfo);

        } else {
            $cmt_content = $this->objInput->postStr ( 'cmt_content' );
            $school = Array ('school_status' => SCHOOL_STATUS_REFUSE, 'refuse_reason' => $cmt_content, 'check_date' => date ( "Y-m-d H:i:s" ) );
        }

        $re = $mschool->modifySchoolInfo ( $school, $schoolid );
        
        $emailObj = ClsFactory::Create('@.Common_wmw.WmwEmail');
        
        if(($base_email == $email) || ($base_email == "")){
            $send = $emailObj->send($email,$emailContent);
        }else{
            $send = $emailObj->send($email,$emailContent);
            $base_send = $emailObj->send($base_email,$emailContent);//同时向基地发送重置后的邮件
	    }
        $this->redirect ( '../Admingroup/Schoolmanage/getSchool' );
    }
    
    //检测网址是否已被申请或者使用(不包含其自己) 
    public function checkNewUrl(){
        $newUrl = $this->objInput->postStr('newWebUrl');
        $school_id = $this->objInput->postStr('schoolid');
        if(!strpos($newUrl,'.wmw.cn')){
            $newUrl.=".wmw.cn";
        }
        
        $mSchool = ClsFactory::Create('Model.mSchoolInfo');
        $school_info = $mSchool->getSchoolInfoById($school_id);
        if($school_info[$school_id]['school_url_new'] == $newUrl) { //是否做出修改
            $json = array(
              'result'=>array(
                      'code'=> 2,
                      'message' => '没有更改' 
              ),
              'data'=>''
            );
            echo json_encode($json);
        } else { //若做出修改，检测是否被其他学校占用
            $is_available = 1;
            $school_info_list = $mSchool->checkUrl($newUrl, $limit=1);
    
            if(!empty($school_info_list)) {
                $is_available = -1;
            } else {
                $mOldSchoolUrl = ClsFactory::Create('Model.mOldSchoolUrl');
            	$old_school_list = $mOldSchoolUrl->checkOldSchoolUrlForUrlIsExist($newUrl); 
                if(!empty($old_school_list)) {
                    $is_available = -1;
                }
            }
            
            $message = $is_available ?  '网址已存在' : '网址可用';
            $json = array(
                  'result'=>array(
                          'code'=>$is_available,
                          'message' => $message 
                  ),
                  'data'=>''
            );
            echo json_encode($json);
        }
    }
    
	/* todo 系统发号器-产生随机数
	 * 以后修改系统发号机制时可删除此方法
	 */
	private function createAccount($num1){
				
		if($num1 <= 0) {
			$num = 8;
		} else {
			$num = $num1-1;
		}
		$connt = 0;
		while($connt<$num){
			$a[]=mt_rand(0,9);//产生随机数
			$connt=count($a);
		}
		foreach ($a as $key => $value){
			$val.=$value;			
		}
		$one  = mt_rand(1,9);
		$str = $one.$val;		
		return  $str;
	}

}