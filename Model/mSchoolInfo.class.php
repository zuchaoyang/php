<?php
class mSchoolInfo extends mBase {
    protected $_dSchoolInfo = null;
    
    public function __construct() {
        $this->_dSchoolInfo = ClsFactory::Create ( 'Data.dSchoolInfo' );
    }
    
    //获取cookie中的用户账号
    function getCookieAccount() {
        $mBmsAccount = ClsFactory::Create ( 'Model.mBmsAccount' );
        return $mBmsAccount->getBaseCookieAccount ();
    }
    
    //通过学校账号查询学校信息
    public function getSchoolInfoById($schoolids) {
        $schoollist = $this->_dSchoolInfo->getSchoolInfoById ( $schoolids );
        if (! empty ( $schoollist )) {
            foreach ( $schoollist as $key => $school ) {
                $schoollist [$key] = $this->parseSchool ( $school );
            }
        }
        
        return ! empty ( $schoollist ) ? $schoollist : false;
    }

    //通过学校的业务类型得到学校的信息
    function getSchoolInfoByOperationStrategy($operation_strategy){
        if(empty($operation_strategy)){
            return false;
        }
        $operation_strategy = implode(',' , (array)$operation_strategy);
        $wheresql = array (
            "operation_strategy in ($operation_strategy)"
        );

        return $this->_dSchoolInfo->getInfo($wheresql);
    }

    //根据学校账号修改学校信息
    public function modifySchoolInfo($schoolinfo, $schoolids) {
        return $this->_dSchoolInfo->modifySchoolInfo ( $schoolinfo, $schoolids );
    }

    public function addSchoolInfo($schoolinfo, $is_return_id = false) {
        return $this->_dSchoolInfo->addSchoolInfo ( $schoolinfo, $is_return_id);
    }
    //根据账号删除学校信息
    //  更改函数名delSchoolInfoById 为 delSchoolInfo --Luan 2011-08-16
    public function delSchoolInfo($schoolids) {
        return $this->_dSchoolInfo->delSchoolInfo ( $schoolids );
    }

    //得到学校分页的所有信息
    public function getAllSchoolInfo($offset = 0, $limit = 10, $uid, $flag, $name="") {

        $flag = (array)$flag;
        $flag = implode(',', $flag);
        $wheresql = array(
            "school_status in ($flag)"
        );
	    if(!empty($uid)){
	        array_push($wheresql, "add_account = '$uid'");
	    }
	    if(!empty($name)){
	        array_push($wheresql, "school_name like '$name%'");
	    }
	    
        return $this->_dSchoolInfo->getInfo($wheresql, "add_time desc", $offset, $limit);
    }
    
    
    //得到学校分页的所有信息
    public function getAllSchoolInfoByPub($offset = 0, $limit = 10, $uid, $flag, $status, $name="") {
        $flag = (array)$flag;
        $flag = implode(',', $flag);
        $status = (array)$status;
        $status = implode(',',$status);
        $wheresql = array(
            "is_pub in ($flag)",
            "school_status in ($status)"
        );
        
	    if(!empty($uid)){
	        array_push($wheresql, "add_account = '$uid'");
	    }
	    if(!empty($name)){
	        array_push($wheresql, "school_name like '$name%'");
	    }
	    
        return $this->_dSchoolInfo->getInfo($wheresql, "add_time desc", $offset, $limit);
    }
    
    //得到学校分页的所有信息
    public function getSchoolInfo() {
        return $this->_dSchoolInfo->getInfo();
    }
    
    //得到学校分页的所有信息,倒序
    public function getSchoolInfoOrderByIdDesc($where, $orderby) {
        return $this->_dSchoolInfo->getInfo($where, $orderby);
    }    
    
    //通过管理员账号得到学校信息
    Public function getSchoolInfoByNetManagerAccount($adminUids) {
        if(empty($adminUids)) {
            return false;
        }
        
        return $this->_dSchoolInfo->getSchoolInfoByNetManagerAccount ( $adminUids );
    }
    
    //检测网址是否已被申请或者使用 
    public function checkUrl($url, $limit){
        if(empty($url)) {
            return false;
        }
        $is_exist = 0 ;
        
        $wheresql = array(
            "school_url_new='$url'"
        );
        $list = $this->_dSchoolInfo->getInfo($wheresql, null, 0, $limit); //school_url_new
        
        if(empty($list)) {
            $wheresql = array(
                "school_url_old='$url'" 
            );
            $list = $this->_dSchoolInfo->getInfo($wheresql, null, 0, $limit);//school_url_old
        }
        
        return !empty($list) ? $list : false;
    }

    /**
     * 转换学校中的部分数据信息
     * @param $school_info
     */
    private function parseSchool($school_info) {
        if (empty ( $school_info )) {
            return false;
        }
        
        import ( "@.Common_wmw.Constancearr" );
        if (isset ( $school_info ['school_type'] )) {
            $schooltype = intval ( $school_info ['school_type'] );
            $school_info ['school_type_name'] = Constancearr::school_type( $schooltype );
        }
        
        if (isset ( $school_info ['resource_advantage'] )) {
            $resource_advantage = intval ( $school_info ['resource_advantage'] );
            $school_info ['resource_advantage_name'] = Constancearr::school_resource_advantage ( $resource_advantage );
        }
        //用户地区信息
        if (isset ( $school_info ['area_id'] ) && ! empty ( $school_info ['area_id'] )) {
            $namearr = getAreaNameList ( $school_info ['area_id'] );
            if (! empty ( $namearr )) {
                $school_info ['area_id_namearr'] = $namearr;
                $school_info ['area_id_name'] = implode ( "", $namearr );
            } else {
                $school_info ['area_id_name'] = "暂无";
            }
        } else {
            $school_info ['area_id_name'] = "暂无";
        }
        
        if(isset($school_info['school_logo'])) {
            import("@.Common_wmw.Pathmanagement_ams");

            $school_info['school_logo_url'] = Pathmanagement_ams::getSchoolLogo() . $school_info['school_logo'];
            $pathinfo = pathinfo($school_info['school_logo']);
            $school_info['school_logo_small_url'] = Pathmanagement_ams::getSchoolLogo() . $pathinfo['filename'].'_small.'.$pathinfo['extension'];

        }
        return $school_info;
    }
    
    /**
     * 
     * 根据省市获取学校列表
     * @param int $area_id 地区代码   
     * @param int $city_id 市区代码
     * @param int $offset  
     * @param int $limit
     */
    public function getSchoolInfoByAreaId($area_id, $city_id, $is_pub =  1,$offset = 0, $limit = 10) {
        
       //如果参数为空则查询全部
       $wheres = array();
       $wheres[] = ' school_status = 1 ';
       $wheres[] = " is_pub = $is_pub ";
       $area_id = strval($area_id);
       if(empty($city_id)) {
           $up_limit = $area_id . '999999';
           $down_limit = $area_id . '000000';
       } else {
           $city_id = str_pad(strval($city_id), 3, '0', STR_PAD_LEFT);
           $up_limit = $area_id . $city_id . '999';
           $down_limit = $area_id . $city_id . '000';
       }
       $wheres[] = " area_id >= $down_limit AND area_id <= $up_limit ";
       
       $wheresql = implode('AND', $wheres);
//       echo $wheresql;
       $schoolinfo_list = $this->_dSchoolInfo->getInfo($wheresql, 'school_id desc', $offset, $limit);
       if(!empty($schoolinfo_list)) {
           foreach($schoolinfo_list as $school_id=>$school_info) {
               $schoolinfo_list[$school_id] = $this->parseSchool($school_info);
           }
       }
       return !empty($schoolinfo_list) ? $schoolinfo_list : false;
    }
    
    
}