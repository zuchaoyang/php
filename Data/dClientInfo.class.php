<?php
class dClientInfo extends dBase {
	
	protected $_tablename = 'wmw_client_info';
	protected $_fields = array(
		'client_account',
		//'client_name',
		'client_firstchar',
		//'client_headimg',
		'client_sex',
		'client_birthday',
		'client_phone',
	    'client_email',
		'area_id',
		'client_trade',
		'client_job',
		'client_character',
		'client_interest',
		'client_classrole',
		'like_teacher',
		'like_subject',
		'like_cartoon',
		'like_game',
		'like_movement',
		'add_time',
		'upd_time',
		//'client_type',
		'client_zodiac',
		'client_constellation',
		'client_blood_type',
		'teach_time',
		'client_title',
		'job_address_name',
		//'business_enable',
		//'phone_create_time',
		//'phone_status',
		'client_address',
		//'onread',
	);
	protected $_pk = 'client_account';
	protected $_index_list = array(
	    'client_account',
	);
	
	public function _initialize() {
        $this->connectDb('user', true);
    }
    
    /**
     * 通过主键获取用户信息
     * @param $client_accounts
     */
    //todo 没有C层调用
    public function getClientInfoById($uids) {
        return $this->getInfoByPk($uids);
    }
    
    /**
     * 更新用户资料
     * @param  $datas
     * @param  $client_account
     */
    public function modifyUserClientInfo($datas, $uid) {
        return $this->modify($datas, $uid);
    }
    
    /**
     * 删除用户信息
     * @param $client_account
     */
    public function delUserClientInfo($uid) {
        return $this->delete($uid);
    }
}