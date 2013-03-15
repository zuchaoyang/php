<?php
class StudentImportAction extends SnsController {
    /**
     * 通过姓名导入
     */
    public function studentCreateAccount() {
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->showError('您暂时没有权限导入学生信息', '/Sns/ClassAdmin/StudentList/index/class_code/' . $class_code);
        }
        
        $this->assign('class_code', $class_code);
        
        $this->display('student_create_account');
    }
    
    /**
     * 已有账号的导入
     */
    public function studentImportAccount() {
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->showError('您暂时没有权限导入学生信息', '/Sns/ClassAdmin/StudentList/index/class_code/' . $class_code);
        }
        
        $this->assign('class_code', $class_code);
        $this->display('student_import_account');
    }
    
    /**
     * 导入保存函数
     */
    public function execStudentCreateAccountAjax() {
        $class_code         = $this->objInput->getStr('class_code');
        $student_name_list  = $this->objInput->postArr('student_name_list');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->ajaxReturn(null, '您暂时没有权限使用通过账号导入学生信息!', -1, 'json');
        }
        
        //名字的整理：去掉2端空格，名字的长度在20字符内
        import('@.Common_wmw.WmwString');
        foreach((array)$student_name_list as $key=>$name) {
            $name = trim($name);
            if(empty($name)) {
                unset($student_name_list[$key]);
                continue;
            }
            $name = WmwString::mbstrcut($name, 0, 20, 1);
            $student_name_list[$key] = $name;
        }
        
       if(empty($student_name_list)) {
           $this->ajaxReturn(null, '请输入要导入的学生姓名!', -1, 'json');
       } 
        
        //生成账号
        $mUser = ClsFactory::Create('Model.mUser');
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        
        //生成姓名和账号的数据关系数据
        list($map_datas, $fail_student_names) = $this->buildUserNameDatabaseMap($student_name_list, $class_code);
        if(empty($map_datas)) {
            $this->ajaxReturn(null, '系统繁忙，请稍后重试!', -1, 'json');
        }
        
        list($client_accounts_dataarr, $client_info_dataarr, $client_class_dataarr, $family_relation_dataarr, $account_family_relations) = $map_datas;
        //添加用户的基本关系数据
        if(!$mUser->addUserClientAccountBat($client_accounts_dataarr)) {
            $this->ajaxReturn(null, '系统繁忙，请稍后重试!', -1, 'json');
        }
        //添加用户的扩展关系数据
        if(!$mUser->addUserClientInfoBat($client_info_dataarr)) {
            //回滚
            $this->rollbackClientAccount(array_keys($client_accounts_dataarr));
            $this->ajaxReturn(null, '系统繁忙，请稍后重试!', -1, 'json');
        }
        //添加用户的班级关系数据
        if(!$mClientClass->addClientClassBat($client_class_dataarr)) {
            //回滚
            $this->rollbackClientAccount(array_keys($client_accounts_dataarr));
            $this->rollbackClientInfo(array_keys($client_info_dataarr));
            $this->ajaxReturn(null, '系统繁忙，请稍后重试!', -1, 'json');
        }
        //添加用户的家长关系数据
        if(!$mFamilyRelation->addFamilyRelationBat($family_relation_dataarr)) {
            $this->rollbackClientAccount(array_keys($client_accounts_dataarr));
            $this->rollbackClientInfo(array_keys($client_info_dataarr));
            $this->rollbackClientClass(array_keys($client_class_dataarr), $class_code);
            $this->ajaxReturn(null, '系统繁忙，请稍后重试!', -1, 'json');
        }
        
        //更新Redis  班级学生和家长
        $this->updateRedis($class_code);
        //跳转到该班级对应的学生列表页面
        $this->ajaxReturn(null, '导入成功!', 1, 'json');
    }
    
    /**
     * 按账户导入保存函数
     */
    public function execStudentImportAccountAjax() {
        $class_code  = $this->objInput->getStr('class_code');
        $client_account_list  = $this->objInput->postArr('client_account_list');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->ajaxReturn(null, '您暂时没有权限使用通过账号导入学生信息!', -1, 'json');
        }
        
        //检测出账号格式不正确的信息
        if(($error_account_list = $this->checkoutErrorFormatAccount($client_account_list)) != false) {
            $this->ajaxReturn($error_account_list, '账号格式有误,账号只能是数字!', -1, 'json');
        }
        
        //检测出账号不存在
        if(($error_account_list = $this->checkoutNotExistAccount($client_account_list)) != false) {
            $this->ajaxReturn($error_account_list, '账号信息不存在!', -1, 'json');
        }
        
        //检测出重复的账号信息,把账号分组后统计分组后元素个数大于1的账号信息
        if(($error_account_list = $this->checkoutRepeatAccount($client_account_list)) != false) {
            $this->ajaxReturn($error_account_list, '账号重复!', -1, 'json');
        }
        
        //通过数据的账号，尝试获取账号对应的班级关系
        if(($error_account_list = $this->checkoutUnlinkClientClassAccount($client_account_list)) != false) {
            $this->ajaxReturn($error_account_list, '账号班级关系未解除!!', -1, 'json');
        }
        
        //将对应的账号绑定和该班级进行班级对应关系的绑定
        $client_class_dataarr = array();
        foreach($client_account_list as $client_account) {
            $client_class_dataarr[$client_account] = array(
                'client_account'    => $client_account,
                'class_code'	    => $class_code,
                'client_class_role' => CLIENT_CLASS_ROLE_PT,
                'add_time'			=> time(),
                'add_account'		=> $this->user['client_account'],
                'upd_account'		=> $this->user['client_account'],
                'upd_time'			=> time(),
                'client_type'		=> CLIENT_TYPE_STUDENT
            );
        }
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        if(!$mClientClass->addClientClassBat($client_class_dataarr)) {
            $this->ajaxReturn(null, '关系导入失败,请稍后重试!', -1, 'json');
        }
        
        //更新Redis  班级学生和家长
        $this->updateRedis($class_code);        
        
        $this->ajaxReturn(null, '关系导入成功!', 1, 'json');
    }
    
    /**
     * 创建学生账号到数据库之间的映射关系
     * @param $student_names
     * @param $class_code
     */
    private function buildUserNameDatabaseMap($student_names, $class_code) {
        if(empty($student_names) || empty($class_code)) {
            return false;
        }
        
        $need_client_nums = count($student_names) * 3;
        $mUser = ClsFactory::Create('Model.mUser');
        $new_client_accounts = $mUser->getClientCountBat($need_client_nums);
        //判断生成的账号数是否和请求的数量一致
        $test_times = 1;
        while(count($new_client_accounts) < $need_client_nums && $test_times++ < 10) {
            $append_nums = $need_client_nums - count($new_client_accounts);
            //尝试生成足够的账号信息
            $append_client_accounts = $mUser->getClientCountBat($append_nums);
            $new_client_accounts = array_merge($new_client_accounts, (array)$append_client_accounts);
        }
        
        //将生成的账号信息3个一组
        $chunk_accounts_list = array_chunk($new_client_accounts, 3);
        //用户默认密码为6个0
        $default_password = md5('000000');
        
        //绑定账号和姓名之间的关系，绑定学生和家长之间的关系
        $client_accounts_dataarr = $client_info_dataarr = $client_class_dataarr = $family_relation_dataarr = $account_family_relations = array();
        foreach($student_names as $key=>$name) {
            list($client_account, $family1_account, $family2_account) = array_shift($chunk_accounts_list);
            if(!isset($client_account, $family1_account, $family2_account)) {
                break;
            }
            //导入用户的基本信息
            $client_accounts_dataarr[$client_account] = array(
                'client_account'  => $client_account,
                'client_password' => $default_password,
                'client_type'	  => CLIENT_TYPE_STUDENT,
                'client_name'	  => $name,
                'status'		  => CLIENT_STOP_FLAG,
                'add_time'		  => time(),
                'upd_time'		  => time(),
            );
            $client_accounts_dataarr[$family1_account] = array(
            	'client_account'  => $family1_account,
                'client_password' => $default_password,
                'client_type'	  => CLIENT_TYPE_STUDENT,
                'client_name'	  => '父亲',
                'status'		  => CLIENT_STOP_FLAG,
                'add_time'		  => time(),
                'upd_time'		  => time(),
            );
            $client_accounts_dataarr[$family2_account] = array(
            	'client_account'  => $family2_account,
                'client_password' => $default_password,
                'client_type'	  => CLIENT_TYPE_STUDENT,
                'client_name'	  => '母亲',
                'status'		  => CLIENT_STOP_FLAG,
                'add_time'		  => time(),
                'upd_time'		  => time(),
            );
            
            //用户的基本信息
            $client_info_dataarr[$client_account] = array(
                'client_account'   => $client_account,
                'client_firstchar' => WmwString::getfirstchar($name),
                'add_time'	       => time(),
                'upd_time'	       => time(),
            );
            $client_info_dataarr[$family1_account] = array(
            	'client_account'   => $family1_account,
                'client_firstchar' => WmwString::getfirstchar('父亲'),
                'add_time'	       => time(),
                'upd_time'	       => time(),
            );
            $client_info_dataarr[$family2_account] = array(
                'client_account'   => $family2_account,
                'client_firstchar' => WmwString::getfirstchar('母亲'),
                'add_time'	       => time(),
                'upd_time'	       => time(),
            );
            
            //导入班级的基本关系
            $client_class_dataarr[$client_account] = array(
                'client_account'    => $client_account,
                'class_code'	    => $class_code,
                'client_class_role' => CLIENT_CLASS_ROLE_PT,
                'add_time'			=> time(),
                'add_account'		=> $this->user['client_account'],
                'upd_account'		=> $this->user['client_account'],
                'upd_time'			=> time(),
                'client_type'		=> CLIENT_TYPE_STUDENT
            );
            
            $client_class_dataarr[$family1_account] = array(
                'client_account'    => $family1_account,
                'class_code'	    => $class_code,
                'add_time'			=> time(),
                'add_account'		=> $this->user['client_account'],
                'upd_account'		=> $this->user['client_account'],
                'upd_time'			=> time(),
                'client_type'		=> CLIENT_TYPE_FAMILY
            );
            
            $client_class_dataarr[$family2_account] = array(
                'client_account'    => $family2_account,
                'class_code'	    => $class_code,
                'add_time'			=> time(),
                'add_account'		=> $this->user['client_account'],
                'upd_account'		=> $this->user['client_account'],
                'upd_time'			=> time(),
                'client_type'		=> CLIENT_TYPE_FAMILY
            );
            
            //导入用户的家庭关系
            $family_relation_dataarr[$family1_account] = array(
                'client_account' => $client_account,
                'family_account' => $family1_account,
                'family_type'	 => 1,
                'add_account'	 => $this->user['client_account'],
                'add_time'		 => time(),
            );
            //导入用户的家庭关系
            $family_relation_dataarr[$family2_account] = array(
            	'client_account' => $client_account,
                'family_account' => $family2_account,
                'family_type'	 => 2,
                'add_account'	 => $this->user['client_account'],
                'add_time'		 => time(),
            );
            
            //建立用户账号到家长账号之间的映射，用于失败时的数据回滚
            $account_family_relations[$client_account] = array(
                $family1_account,
                $family2_account,
            );
            
            unset($student_names[$key]);
        }
        
        return array(
            array(
                $client_accounts_dataarr,
                $client_info_dataarr,
                $client_class_dataarr,
                $family_relation_dataarr,
                $account_family_relations,
            ),
            $student_names,
        );
    }
    
    /**
     * 回滚用户的基本数据
     * @param $client_accounts
     */
    private function rollbackClientAccount($client_accounts) {
        if(empty($client_accounts)) {
            return false;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        
        $delete_nums = 0;
        foreach($client_accounts as $uid) {
            if($mUser->delUserClientAccount($uid)) {
                $delete_nums++;
            }
        }
        
        return $delete_nums ? $delete_nums : false;
    }
    
    /**
     * 回滚用户的扩展信息数据
     * @param $client_accounts
     */
    private function rollbackClientInfo($client_accounts) {
        if(empty($client_accounts)) {
            return false;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        
        $delete_nums = 0;
        foreach($client_accounts as $uid) {
            if($mUser->delUserClientInfo($uid)) {
                $delete_nums++;
            }
        }
        
        return $delete_nums ? $delete_nums : false;
    }
    
    /**
     * 回滚用户对应的班级关系信息
     * @param  $client_accounts
     * @param  $class_code
     */
    private function rollbackClientClass($client_accounts, $class_code) {
        if(empty($client_accounts) || empty($class_code)) {
            return false;
        }
        
        $client_accounts = (array)$client_accounts;
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code);
        $client_class_list = & $client_class_arr[$class_code];
        if(empty($client_class_list)) {
            return false;
        }
        
        $delete_nums = 0;
        foreach($client_class_list as $client_class) {
            $client_class_id = $client_class['client_class_id'];
            $uid = $client_class['client_account'];
            if(in_array($uid, $client_accounts)) {
                if($mClientClass->delClientClass($client_class_id)) {
                    $delete_nums++;
                }
            }
        }
        
        return $delete_nums ? $delete_nums : false;
    }
    
    /**
     * 回滚用户的家庭关系数据
     * @param $account_family_relations 用户账号和家长账号之间的对应关系
     */
    private function rollbackFamilyRelation($account_family_relations) {
        if(empty($account_family_relations)) {
            return false;
        }
        
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $family_relation_arr = $mFamilyRelation->getFamilyRelationByUid(array_keys($account_family_relations));
        if(empty($family_relation_arr)) {
            return false;
        }
        
        $delete_nums = 0;
        foreach($family_relation_arr as $client_account=>$relation_list) {
            $family_accounts = $account_family_relations[$client_account];
            foreach($relation_list as $client_class_id=>$family_relation) {
                $family_uid = $family_relation['family_account'];
                if(in_array($family_uid, $family_accounts) && $mFamilyRelation->delFamilyRelation($client_class_id)) {
                   $delete_nums++;
                }
            }
        }
        
        return $delete_nums ? $delete_nums : false;
    }
    
    /**
     * 检测数据的账号的格式是否正确
     * @param $client_account_list
     */
    private function checkoutErrorFormatAccount($client_account_list) {
        if(empty($client_account_list)) {
            return false;
        }
        
        $error_account_list = array();
        //账号整理:去掉账号2端的空格，有下划线、字母的，中间有空格的账号作为错误数据返回
        $pattern = "/^(\d+)$/";
        foreach($client_account_list as $key=>$client_account) {
            $uid = trim($uid);
            if(!preg_match($pattern, $client_account)) {
                $error_account_list[$client_account] = $client_account;
                unset($client_account_list[$key]);
            }
        }
        
        return !empty($error_account_list) ? $error_account_list : false;
    }
    
    /**
     * 检测用户对应的账号信息是否存在
     * @param $client_account_list
     */
    private function checkoutNotExistAccount($client_account_list) {
        if(empty($client_account_list)) {
            return false;
        }
        
        $error_account_list = array();
        //判断账号是否存在，如果账号不存在提示错误信息
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid($client_account_list);
        foreach($client_account_list as $key=>$client_account) {
            if(!isset($user_list[$client_account])) {
                $error_account_list[$client_account] = $client_account;
                unset($client_account_list[$key]);
            }
        }
        
        return !empty($error_account_list) ? $error_account_list : false;
    }
    
    /**
     * 检测对应账号的班级关系是否解除
     * @param $client_account_list
     */
    private function checkoutUnlinkClientClassAccount($client_account_list) {
        if(empty($client_account_list)) {
            return false;
        }
        
        $error_account_list = array();
        
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByUid($client_account_list);
        if(!empty($client_class_arr)) {
            foreach($client_account_list as $key=>$client_account) {
                if(!empty($client_class_arr)) {
                    $error_account_list[$client_account] = $client_account;
                }
            }
        }
        
        return !empty($error_account_list) ? $error_account_list : false;
    }
    
    /**
     * 检测重复账号信息
     * @param $client_account_list
     */
    private function checkoutRepeatAccount($client_account_list) {
        if(empty($client_account_list)) {
            return false;
        }
        
        $error_account_list = array();
         //检测出重复的账号信息,把账号分组后统计分组后元素个数大于1的账号信息
        $grouped_client_list = array();
        foreach($client_account_list as $key=>$client_account) {
            $grouped_client_list[$client_account][] = $client_account;
        }
        foreach($grouped_client_list as $client_account=>$list) {
            if(count($list) > 1) {
                $error_account_list[$client_account] = $client_account; 
            }
        }
        
        return !empty($error_account_list) ? $error_account_list : false;
    }
    
    /**
     * 更新Redis 班级学生和班级家长成员信息
     * @param $class_code
     */    
    private function updateRedis($class_code) {
        if(empty($class_code)) {
            return false;
        }        
        
        //===========================更新redis============================================================
        $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
        $mSetClassFamily = ClsFactory::Create('RModel.Common.mSetClassFamily');
        $class_student = $mSetClassStudent->getClassStudentById($class_code, true);
        $class_family = $mSetClassFamily->getClassFamilyById($class_code, true);        
        return true;
    }
}