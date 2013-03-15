<?php
class dSchoolInfo extends dBase{
    protected $_tablename = 'wmw_school_info';
    protected $_fields = array(
        'school_id',
		'school_name',
        'school_address',
        'school_url_old',
        'school_url_new',
        'net_manager_phone',
        'school_status',
        'area_id',//省市区还需修改
        'refuse_reason',
        'add_account',
        'add_date',
        'upd_account',
        'upd_date',
        'add_time',
        'post_code',
        'school_create_date',
        'school_type',
        'resource_advantage',
        'school_master',
        'contact_person',
        'class_num',
        'teacher_num',
        'check_date',
        'student_num',
        'net_manager',
        'net_manager_email',
        'school_scan',
        'check_date',
        'operation_strategy',
        'net_manager_account',
        'school_logo', 
        'grade_type', 
        'is_pub'
    );
    protected $_pk = 'school_id';
    protected $_index_list = array(
        'school_id',
        'net_manager_account',
    );

    /**
     * 通过学校id获取学校的相关信息
     * @param $schoolids
     */
    function getSchoolInfoById($schoolids) {
        return $this->getInfoByPk($schoolids);
    }
    
    //修改学校信息
    public function modifySchoolInfo($dataarr , $schoolid) {
        return $this->modify($dataarr, $schoolid);
    }
    
    //添加学校信息
    public function addSchoolInfo($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id);
    }

    // 通过学校管理员的账号获取学校的相关信息
    function getSchoolInfoByNetManagerAccount($uids) {
        return $this->getInfoByFk($uids, 'net_manager_account');
    }
    
}
