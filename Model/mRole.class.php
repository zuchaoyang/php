<?php

define('SYSTEM_MAX_ROLE_ID', 10000);    //系统的角色id的范围

class mRole extends mBase{
    protected $_dRole = null;
    
    public function __construct() {
        $this->_dRole = ClsFactory::Create('Data.dRole');
    }
    
    //获取所有系统定义角色
    public function getRoleSystemAll(){
        $this->_dRole->switchToRoleSystem();
        $sys_role_list = $this->_dRole->getInfo();
        if(!empty($sys_role_list)) {
            $sys_role_list = $this->decodeQueryList($sys_role_list);
        }
        
        return !empty($sys_role_list) ? $sys_role_list : false;
    }

    //通过主键获取角色信息（包括系统及自定义角色）
    public function getRoleById($role_ids){
        if(empty($role_ids)){
            return false;
        }
        $sys_role_ids = $custom_role_ids = array();
        foreach((array)$role_ids  as $key=> $role_id) {//区分系统id和自定义id
            if($role_id <= constant('SYSTEM_MAX_ROLE_ID')) {
                $sys_role_ids[] = $role_id;
            } else {
                $custom_role_ids[] = $role_id;
            }
        }
        $list_sys = $list_custom = array(); //变量使用前必须先声明
        if(!empty($sys_role_ids)) {
            $list_sys = $this->_dRole->getRoleSystemById($sys_role_ids); 
        }
        if(!empty($custom_role_ids)) {
            $list_custom = $this->_dRole->getRoleById($custom_role_ids);
        }
        $list = array_merge((array)$list_sys, (array)$list_custom); //合并
        
        if(!empty($list)) {
            foreach($list as $key => $role) {
                $new_list[$role['role_id']] = $role;
                unset($list[$key]);
            }
        }
        if(!empty($new_list)) {//权限解码
            $new_list = $this->decodeQueryList($new_list);
        }
        return !empty($new_list) ? $new_list : false;
    }
   
    //通过角色名称获取角色信息（包括系统及学校自定义角色）
    public function getRoleByRoleNameAndSchoolId($role_name, $school_id) {
        if(empty($role_name) || empty($school_id)){
            return false;
        }
        
        $this->_dRole->switchToRoleSystem(); //表oa_role_system
        $whereSql = array(
        	"role_name ='$role_name'",
        );
        $sys_role_list = $this->_dRole->getInfo($whereSql);
        
        $this->_dRole->switchToRole();//表oa_role
        $school_id = is_array($school_id) ? array_shift($school_id) : intval($school_id);
        $wheresql = array(
            "school_id='$school_id'",
          	"role_name='$role_name'"  
        );
        
        $custom_role_list = $this->_dRole->getInfo($wheresql);
        
        if(empty($sys_role_list)) { //合并数组
            $role_list = $custom_role_list;
        } else if(empty($custom_role_list)) {
            $role_list = $sys_role_list;
        } else {
            array_merge($sys_role_list, $custom_role_list);
        }
        
        if(!empty($role_list)) {
            $role_list = $this->decodeQueryList($role_list);
        }
        
        return !empty($role_list) ? $role_list : false;
    }
    
    //通过school_id获取学校自定义角色 (可附加系统角色)
    public function getRoleBySchoolId($school_id, $withSys = false) {
        if(empty($school_id)){
            return false;
        }
        
        $return_role_list = $sys_role_list = $role_list = array();
        
        if($withSys){   //同时查询系统定义角色
            $sys_role_list = $this->getRoleSystemAll(); 
            if(!empty($sys_role_list)) {
                foreach($sys_role_list as $role_id=>$sys_role) {
                    $sys_role['is_system'] = 1;
                    $sys_role_list[$role_id] = $sys_role;
                }
                $return_role_list = & $sys_role_list;
            }
        }
        
        $role_list = $this->_dRole->getRoleBySchoolId($school_id);
        $role_list = & $role_list[$school_id];
    
        //转换角色信息
        if(!empty($role_list)) {
            $role_list = $this->decodeQueryList($role_list);
        }
        if(!empty($role_list)) {
            foreach($role_list as $role_id=>$role) {
                $return_role_list[$role_id] = $role;
            }
        }
        return !empty($return_role_list) ? $return_role_list : false;
    }

    //修改用户自定义角色
    public function modifyRole($datas, $role_id){
        if(empty($datas) || empty($role_id)){
            return false;
        }
        $datas['role_access'] = $this->encode_access($datas['role_access_arr']); //按规则转换role_access值
        $rs = $this->_dRole->modifyRole($datas, $role_id);
        return !empty($rs) ? $rs : false;
    }
    
    //添加用户自定义角色
    public function addRole($datas, $is_return_id=false){
        if(empty($datas)){
            return false;
        }
        
        $datas['role_access'] = $this->encode_access($datas['role_access_arr']); //按规则转换role_access值
        $rs = $this->_dRole->addRole($datas, $is_return_id);
        
        return !empty($rs) ? $rs : false;
    }
    
    //删除用户自定义角色
    public function delRoleById($role_id){
        if(empty($role_id)){
            return false;
        }
        $rs = $this->_dRole->delRoleById($role_id);
        return !empty($rs) ? $rs : false;
    }
    
    /**
     * 将二进制字符串加密成十进制
     * @param $access_arr :
     */
    public function encode_access( $access_arr) {
        if(empty($access_arr)) {
            return 0;
        }
        $access_arr = array_reverse($access_arr);
        $bin_str = join('', $access_arr);
        
        return bindec($bin_str);
    }
    
    /**
     * @param $query_list data层查询出的数组（主键=>info）
     */
    private function decodeQueryList($query_list){
        if(empty($query_list)) {
            return false;
        }
        
        foreach($query_list as $role_id=>$role) {
            $access_info = $this->decode_access($role['role_access']);
            $role = array_merge((array)$role, (array)$access_info);
            $query_list[$role_id] = $role;
        }
        
        return !empty($query_list) ? $query_list : false;
    }
    
    /**
     * 将相应的角色权限解析成相应的权限名称
     * @param $access_dec 字段role_access 中存储的十进制数
     */
    private function decode_access($access_dec) {
        
        $access_info = array();
        import("@.Common_wmw.Constancearr");
        $oaRoleAccessModel = Constancearr::oaRoleAccessModel();

        $model_num = count($oaRoleAccessModel);
        $access_bin = decbin($access_dec); 
        for($i=strlen($access_bin)-1; $i>-1; $i--){ //将权限转为2进制然后按位存入数组
            $access_info['access_bin_arr'][] = $access_bin[$i];
        }
        for($i=strlen($access_bin);$i<$model_num; $i++){//补全高位，null值补充为0
            $access_info['access_bin_arr'][$i] = 0;
        }
        for($i=0; $i<$model_num; $i++){
            if($access_info['access_bin_arr'][$i] == 1){
                $access_info['access_name_arr'][$i] = $oaRoleAccessModel[$i];
            }
        }
        return $access_info;
    }
 
}