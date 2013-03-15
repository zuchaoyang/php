<?php
//todolist 存在无用索引
class dGuestbookInfo extends dBase{
	protected $_tablename = 'wmw_guestbook_info';
    protected $_fields = array(
		'guestbook_id',
		'to_account',
		'guestbook_content',
		'upid',
		'add_account',
		'add_date',
    );
    protected $_pk = 'guestbook_id';
    protected $_index_list = array(
        'guestbook_id',
        'to_account',
        'add_account',
    );

    /**
     * 通过对应的留言id获取留言信息
     * @param $guestbook_ids
     */
    public function getGuestbookInfoById($guestbook_ids) {
        return $this->getInfoByPk($guestbook_ids);
    }

    /**
     * 根据个人账号获取当前给自己的留言
     * @param $uids
     */
    public function getGuestbookInfoByToUid($uids) {
        return $this->getInfoByFk($uids, 'to_account');
    }

    /**
     * 增加留言信息
     * @param $dataarr
     */
    public function addGuestbookInfo($datas , $is_return_id = false) {
        return $this->add($datas, $is_return_id);
    }

    /**
     * 删除对应的留言信息
     * @param $guestbook_ids
     */
    public function delGuestbookInfo($guestbook_ids) {
        return $this->delete($guestbook_ids);
    }


	//最新一条留言
    public function getGuestbookInfoByToAccount($account) {
        if(empty($account)) { 
            return false;
        } 
        
		$arrMySqlData = $this->getInfoByFk($account, 'to_account', 'guestbook_id desc');
        return !empty($arrMySqlData) ? $arrMySqlData : false;
	}
}

?>