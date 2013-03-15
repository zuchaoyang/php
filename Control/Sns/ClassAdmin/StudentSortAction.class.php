<?php


class StudentSortAction extends SnsController {
    
    /**
     * 已经排序的学生列表的显示
     */
    public function index() {
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            //$this->showError('您暂时不能管理该班级的学生排序!', '/Sns/ClassAdmin/StudentList/index/class_code/' . $class_code);
        }
        
        //获取班级成员的列表信息
        $filters = array(
            'client_type' => CLIENT_TYPE_STUDENT,
        );
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code, $filters);
        $client_class_list = & $client_class_arr[$class_code];
        
        //获取用户的姓名等信息
        $student_uids = array_keys($client_class_list);
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getClientAccountById($student_uids);
        foreach($client_class_list as $uid=>$client_class) {
            if(isset($user_list[$uid])) {
                $client_class = array_merge($client_class, (array)$user_list[$uid]);
            }
            $client_class_list[$uid] = $client_class;
        }
        
        //将班级成员的信息按照sort_seq排序
        if(!empty($client_class_list)) {
            $sort_keys = array();
            foreach($client_class_list as $client_account=>$client_class) {
                $sort_keys[$client_account] = intval($client_class['sort_seq']);
            }
            array_multisort($sort_keys, SORT_NUMERIC, SORT_ASC, $client_class_list);
        }
        
        //显示学生成员列表信息
        $this->assign('class_code', $class_code);
        $this->assign('client_class_list', $client_class_list);
        
        $this->display('student_sort');
    }
    
    /**
     * 保存排序后的结果
     * 注明：建立的是对应关系是:学生账号->sort_seq的值
     */
    public function saveSortedStudent() {
        $class_code = $this->objInput->getStr('class_code');
        $sorted_list = $this->objInput->postArr('sorted_list');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code) || !$this->isClassAdminTeacher($class_code)) {
            $this->showError('您暂时不能管理该班级的学生排序!', '/Sns/ClassAdmin/StudentSort/index/class_code/' . $class_code);
        }
        
        //获取班级对应的班级成员列表信息，并以client_account为key重组数据
        $filters = array(
            'client_type' => CLIENT_TYPE_STUDENT,
        );
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_arr = $mClientClass->getClientClassByClassCode($class_code, $filters);
        $client_class_list = & $client_class_arr[$class_code];
        
        foreach((array)$sorted_list as $client_account=>$sort_seq) {
            //排出非班级的学生信息
            if(!isset($client_class_list[$client_account])) {
                unset($sorted_list[$client_account]);
                continue;
            }
            
            $client_class = $client_class_list[$client_account];
            
            //校验sort_seq的值，范围为:0~9999
            $sort_seq = intval($sort_seq);
            $sort_seq = $sort_seq > 0 && $sort_seq < 9999 ? $sort_seq : 0;
            
            //排出sort_seq值未发生变化的学生
            $old_sort_seq = $client_class['sort_seq'];
            if($old_sort_seq == $sort_seq) {
                unset($sorted_list[$client_account]);
                 continue;
            }
            //保存新的sort_seq值到数据
            $client_class_datas = array(
                'sort_seq'    => $sort_seq,
                'upd_account' => $this->user['client_account'],
                'upd_time'	  => time(),
            );
            $mClientClass->modifyClientClass($client_class_datas, $client_class['client_class_id']);
        }
        
        $this->showSuccess('学生排序设置成功!', '/Sns/ClassAdmin/StudentList/index/class_code/' . $class_code);
    }
}