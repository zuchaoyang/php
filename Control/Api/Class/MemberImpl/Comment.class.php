<?php
class Comment{
    public function __construct() {
        
       // echo '我是个构造方法';
    }    
    
    /**
     * 根据用户类型获取班级成员
     * @param $class_code 班级id
     * @param $client_type 用户类型
     * 
     * @return $user_list 返回班级成员列表（用户详情）
     */
    public function getClassMember($class_code, $client_type = false) {
        if(empty($class_code)) {
            return false;
        }
        
        $where = array("class_code=$class_code");
        if (empty($client_type)) {
            $where[] = "client_type=$client_type";
        }
        //通过班级成员关系表获取学生列表
        $mClientClass = ClsFactory::Create('Model.mClientClass');
        $client_class_list = $mClientClass->getClientClassInfo($where, "client_type asc,sort_seq asc", null, null);
        if(empty($client_class_list)) {
            return false;
        }
       
        //数据格式化用账号作为键 方便请前台数据  （一个账号不可能有在一个班级中不能有两种关系 保证数据更改键后完整）
        $uids = $new_client_class_list = array();
        foreach ($client_class_list as $key=>$client_class) {
            $client_account = $client_class['client_account'];
            $client_account_arr[] = $client_account;
            
            $new_client_class_list[$client_account] = $client_class;
        }
        unset($client_class_list);
        
        //提取账号
        $mUser = ClsFactory::Create('Model.mUser');
        $member_list = $mUser->getUserBaseByUid($client_account_arr);
        if(empty($new_client_class_list)) {
            return false;
        }

        //循环班级会员关系数据 保证排序id ,过滤掉姓名为空的学生，追加账号详情
        foreach ($new_client_class_list as $client_account=>$client_class) {
            if (!isset($member_list[$client_account]) || empty($member_list[$client_account]['client_name'])) {
                continue;
            }
            
            $new_client_class_list[$client_account] = array_merge($member_list[$client_account], $client_class);
        }

        return !empty($new_client_class_list) ? $new_client_class_list : false;
    }
    
    
}