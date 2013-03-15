<?php

class dClasstalk extends dBase {
	
	protected $_tablename = 'wmw_class_talk';
    protected $_fields = array(
        'talk_id',
        'talk_content',
        'class_code',
        'add_account', 
        'add_date', 
        'comment_nums', 
	 );
	 protected $_pk = 'talk_id';
	 protected $_index_list = array(
	     'talk_id',
	     'class_code',
	 	 'add_account',
	 );

     /**
     * 个人第一次发布说说插入一条数据 @sign_content
     * @param $arrTalkData $add_account
     */
	public function addClassTalk($datas, $is_return_id = false) {
	    return $this->add($datas, $is_return_id);
    }
    
	/**
     * 按用id态信息
     * @param $account
     */
    public function getTalkcontentinfoById($talk_ids) {
        return $this->getInfoByPk($talk_ids);
	}
	
	/**
     * 最新一条数据 @sign_content
     * @param $account
     */	


    public function getClassTalkcontentinfoByaccount($account) {
    	return $this->getInfoByFk($account,'add_account');
	}
}
