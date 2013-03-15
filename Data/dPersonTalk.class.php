<?php

class dPersonTalk extends dBase {
	protected $_tablename = 'wmw_person_talk';
    protected $_fields = array(
		'sign_id',
		'sign_content',
		'add_account',
		'add_date',
		'comment_nums', 
	 );
	 protected $_pk = 'sign_id';
	 protected $_index_list = array(
	    'sign_id',
	    'add_account'
	 );

    /**
     * 按用户账号获取动态信息 (外键)
     * @param $account
     */
    public function getPersonTalkByAddAccount($addAccount) {
    	return $this->getInfoByFk($addAccount, 'add_account');
    	
	}

	/**
     * 按用id获取动态信息
     * @param $account
     */
    public function getPersonTalkById($signId) {
		return $this->getInfoByPk($signId);
	}

   	/**
     * 个人第一次发布说说插入一条数据 @sign_content
     * @param $arrTalkData 
     */
	//todolist 函数命名不规范
	public function addPersonTalk($dataarr, $is_return_id = false) {
        return $this->add($dataarr, $is_return_id); 
    }

	/**
     * 删除我的新鲜事
     * @param $sign_id
     * @return $arrMySqlData 成功返回影响的记录数 失败返回 false
     */
    //todolist 函数命名不规范
	public function delPersonTalk($sign_id) {
		return $this->delete($sign_id);
	}

	/**
     * 个人动态评论数据更新
     * @param $sign_id 		信息id
     * @return $i_num_rows 成功返回影响的记录数 失败返回 false
     */	
	//todolist 需要根据新的方式进行抽取
    public function modifyPersonTalk($data, $sign_id) {
        if (empty($sign_id)) { 
            return false;
        } 
		return $this->modify($data, $sign_id);
	}
}
