<?php

class StudentListAction extends SnsController {
    
    public function index() {
        $class_code = $this->objInput->getStr('class_code');
        
        //校验班级code和用户之间的关系
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->showError('您暂时没有权限管理学生信息!', '/Sns/ClassAdmin/Index/index/class_code/' . $class_code);            
        }
        
        //获取学生的列表信息
        $student_list = $this->getClassStudentDetailsInfo($class_code);
        
        //获取学生的职位列表
        import('@.Common_wmw.Constancearr');
        $classleader_list = Constancearr::classleader();
        
        $this->assign('class_code', $class_code);
        $this->assign('student_list', $student_list);
        $this->assign('classleader_list', $classleader_list);
        
        $this->display('student_admin_list');
    }
    
    /**
     * 导出班级成员的excel文件
     */
    public function exportStudentsExcel() {
        $class_code = $this->objInput->getInt('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            return false;
        }
        
        $student_list = $this->getClassStudentDetailsInfo($class_code);
        if(empty($student_list)) {
            return false;
        }
        
        $sheet_datas = array(
            'cols' => 5,
            'rows' => count($student_list) + 1,
            'title' => '班级学生列表',
            'datas' => array(
                1 => array(
                    '学生姓名',
                    '职务',
                    '学生账号',
                    '家长账号（1）',
                    '家长账号（2）'
                )
            ),
        );
        $rows_id = 2;
        foreach($student_list as $uid=>$student) {
            $sheet_datas['datas'][$rows_id++] = array(
                $student['client_name'],
                $student['client_class_role_name'],
                $student['client_account'],
                $student['family_list'][1]['client_account'],
                $student['family_list'][2]['client_account'],
            );
            unset($student_list[$uid]);
        }
        
        $class_name = $this->user['class_info'][$class_code]['class_name'];
        $export_file_name = (!empty($class_name) ? "($class_name)" : "") . "学生列表.xls";
        //将相关数据直接输出到浏览器
        import('@.Common_wmw.WmwPHPExcel');
        $PHPExcel = new WmwPHPExcel();
        $PHPExcel->outputExcel(array($sheet_datas), $export_file_name);
    }
    
    /**
     * 删除班级学生是以学生client_account为主线的
     */
    public function removeStudentAjax() {
        $class_code      = $this->objInput->getStr('class_code');
        $client_account  = $this->objInput->postStr('client_account');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限删除该班学生!', -1, 'json');
        }
        
        //校验md5_key,该key已经隐含了全部的验证,如何保证用户不能从其他渠道拿到该值
        if(!$this->canRemoveClassStudent($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限删除该班学生!', -1, 'json');
        }
        
        //通过client_class的值获取班级成员的班级关系
        $current_client_class = $this->getUserClientClassByUidAndClassCode($client_account, $class_code);
        if(empty($current_client_class)) {
            $this->ajaxReturn(null, '学生对应的班级关系信息不存在!', -1, 'json');
        }
        //删除用户的班级的关系
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        if(!$mClientClass->delClientClass($current_client_class['client_class_id'])) {
            $this->ajaxReturn(null, '移除班级学生失败，请稍后重试!', -1, 'json');            
        }
        
        //删除学生家长的班级关系
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $FamilyRelation = $mFamilyRelation->getFamilyRelationByUid($client_account);
        
        $family_accouts = array();
        
        foreach($FamilyRelation[$client_account] as $uid => $family) {
            $family_accouts[] = $family['family_account'];
        }        
        
        $clientclass_list = $mClientClass->getClientClassByUid($family_accouts);
        foreach($clientclass_list as $uid => $client_info) {
            $client_class_id = key($client_info);
            $mClientClass->delClientClass($client_class_id);
        }
        
        //更新Redis 班级学生  班级家长  学生班级关系  家长班级关系==============================================
        
        $mHashClientClass = ClsFactory::Create('RModel.Common.mHashClientClass');
        
        //更新学生redis  mHashClientClass
        $mHashClientClass->getClientClassbyUid($client_account, true);
        //更新学生家长帐号，也是 mHashClientClass
        foreach($family_accouts as $uid) {
            $mHashClientClass->getClientClassbyUid($uid, true);
        }
        //更新班级成员，包括 学生和家长.
        $mSetClassStudent = ClsFactory::Create('RModel.Common.mSetClassStudent');
        $mSetClassFamily = ClsFactory::Create('RModel.Common.mSetClassFamily');        
       
        $mSetClassStudent->delClassStudentByMember($class_code, array($client_account));
        $mSetClassFamily->delClassFamilyByMember($class_code, $family_accouts);        
        
        
        $this->ajaxReturn(null, '移除班级学生成功!', 1, 'json');
    }
    
    /**
     * 班级成员的信息的修改是以学生client_account为主线的
     */
    public function editStudentAjax() {
        $class_code        = $this->objInput->getStr('class_code');
        $client_account    = $this->objInput->postStr('client_account');
        $client_name       = $this->objInput->postStr('client_name');
        $client_class_role = $this->objInput->postInt('client_class_role');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限编辑该班学生!', -1, 'json');
        }
        
        //校验md5_key,并校验用户权限
        if(!$this->canEditClassStudent($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限编辑该班学生!', -1, 'json');
        }
        
        //通过学生的账号信息，检索出学生对应的班级关系
        $current_client_class = $this->getUserClientClassByUidAndClassCode($client_account, $class_code);
        if(empty($current_client_class)) {
            $this->ajaxReturn(null, '学生对应的班级关系信息不存在!', -1, 'json');
        }
        
        //修改用户的信息
        $client_class_datas = array(
            'client_class_role' => $client_class_role,
            'upd_account'	    => $this->user['client_account'],
            'upd_time'			=> time(),
        );
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        if($mClientClass->modifyClientClass($client_class_datas, $current_client_class['client_class_id']) === false) {
            $this->ajaxReturn(null, '学生的职务信息修改失败!', -1, 'json');
        }
        
        $client_account_datas = array(
            'client_name' => $client_name,
            'upd_time' => time(),
        );
        $mUser = ClsFactory::Create('Model.mUser');
        if($mUser->modifyUserClientAccount($client_account_datas, $client_account) === false) {
            $this->ajaxReturn(null, '学生的姓名修改失败!', -1, 'json');
        }
        
        $this->ajaxReturn(null, '学生的信息编辑成功!', 1, 'json');
    }
    
    /**
     * 获取班级成员的基本信息
     * @param $class_code
     */
    private function getClassStudentDetailsInfo($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        //获取班级对应的学生列表
        $filters = array(
            'client_type' => CLIENT_TYPE_STUDENT,
        );
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code, $filters);
        $client_class_list = & $client_class_arr[$class_code];
        if(empty($client_class_list)) {
            return false;
        }
        
        //解析学生的班级职务信息
        import ( '@.Common_wmw.Constancearr' );
        foreach($client_class_list as $uid=>$client_class) {
            $client_class_role = intval($client_class['client_class_role']);
            if(empty($client_class_role)) {
                $client_class_role = key(Constancearr::classleader());
                $client_class['client_class_role'] = $client_class_role;
            }
            $client_class ['client_class_role_name'] = Constancearr::classleader($client_class_role);
            $client_class_list[$uid] = $client_class;
        }
        
        //获取班级成员的家长关系(只有家长的账号信息)
        $student_uids = array_keys($client_class_list);
        //获取学生的基本信息,并合并班级成员的client_class的值
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid($student_uids);
        foreach((array)$user_list as $uid=>$user) {
            $user = array_merge($user, (array)$client_class_list[$uid]);
            $user_list[$uid] = $user;
        }
        
        //将家长的信息合并到用户列表中去
        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
        $family_relation_arr = $mFamilyRelation->getFamilyRelationByUid($student_uids);
        foreach($user_list as $uid=>$user) {
            $family_list = array();
            if(isset($family_relation_arr[$uid])) {
                foreach($family_relation_arr[$uid] as $family_relation) {
                    $family_list[$family_relation['family_type']] = $family_relation;
                }
            }
            //家长一的账号信息
            if(empty($family_list[1])) {
                $family_list[1] = array(
                    'client_account' => '--'
                );
            }
            //家长二的账号信息
            if(empty($family_list[2])) {
                $family_list[2] = array(
                    'client_account' => '--'
                );
            }
            $user['family_list'] = $family_list;
            $user_list[$uid] = $user;
        }
        
        //学生按照sort_seq的值排序
        $sort_keys = array();
        foreach($user_list as $uid=>$user) {
            $sort_keys[$uid] = intval($user['sort_seq']);
        }
        array_multisort($sort_keys, SORT_NUMERIC, SORT_ASC, $user_list);
        
        return !empty($user_list) ? $user_list : false;
    }
    
    
    /**
     * 获取用户对应的班级关系
     * @param $client_account
     * @param $class_code
     */
    private function getUserClientClassByUidAndClassCode($client_account, $class_code) {
        if(empty($client_account) || empty($class_code)) {
            return false;
        }
        
        //通过client_class的值获取班级成员的班级关系
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByUid($client_account);
        $client_class_list = & $client_class_arr[$client_account];
        
        //获取用户当前的班级关系
        $current_client_class = array();
        foreach((array)$client_class_list as $client_class) {
            if($client_class['class_code'] == $class_code) {
                $current_client_class = $client_class;
                break;
            }
        }
        
        return !empty($current_client_class) ? $current_client_class : false;
    }
    
    /**
     * 判断用户能否删除班级成员关系
     * @param $class_code
     */
    private function canRemoveClassStudent($class_code) {
       if(empty($class_code)) {
           return false;
       }
       
       return $this->isClassAdminTeacher($class_code) ? true : false;
    }
    
    /**
     * 判断用户是否能够编辑班级成员信息
     */
    private function canEditClassStudent($class_code) {
       if(empty($class_code)) {
           return false;
       }
       
       return $this->isClassAdminTeacher($class_code) ? true : false;
    }
    
}