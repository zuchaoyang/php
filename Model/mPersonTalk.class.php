<?php
//mTalkcontentinfo
class mPersonTalk extends mBase {
    protected $_dPersonTalk = null;

    public function __construct() {
    	$this->_dPersonTalk = ClsFactory::Create('Data.dPersonTalk');
    }

	/**
     * 根据添加人账号($account) 获取添加的总记录数
     * @param $account 		 添加信息人的账号
     * @return $arrMySqlData 成功返回影响的记录数 失败返回 false
     */
	public function getDataRows($account) {
		if (empty($account)) {
			return false;
		}
		
		$account = array_unique((array)$account);
		$wherearr = 'add_account in(' . implode(',', $account) . ')';
		return $this->_dPersonTalk->getCount($wherearr);
	}


	 /**
     * 按用户账号获取动态信息
     * @param $account
     *
     * getPersonTalkByAddAccount($account,$Llimit,$Rlimit) 改成getPersonTalkByAddAccount($account)
     * D层没有这个方法也不需要 $Llimit,$Rlimit
     */
    public function getPersonTalkByAddAccount($account) {
    	if (empty($account)) {
			return false;
		}

		//去掉 limit  没有用 D层和 C层都没用这两个参数
		$talkcontentinfo_list = $this->_dPersonTalk->getPersonTalkByAddAccount($account);
		return !empty($talkcontentinfo_list) ? $talkcontentinfo_list : false;
    }

 	/**
     * 按用id获取动态信息
     * @param $id
     */
 	public function getPersonTalkById($id) {
 		if (empty($id)) {
 			return false;
 		}

		$talkcontentinfo_list = $this->_dPersonTalk->getPersonTalkById($id);
		return !empty($talkcontentinfo_list) ? $talkcontentinfo_list : false;
    }



	/*获取最新发布一条信息*/
    public function getNewTalkcontentinfoByAddAccount($account) {
    	if (empty($account)) {
			return false;
		}

		//$talkcontentinfo_list = $this->_dPersonTalk->getNewTalkcontentinfoByAddAccount($account);
		$wherearr = "add_account=$account";
		$orderby = "sign_id desc";
		//取最后一条
		$talkcontentinfo_list = $this->_dPersonTalk->getInfo($wherearr, $orderby, 0, 1);
		return current($talkcontentinfo_list);
    }

	//保存
    public function addPersonTalk($arrTalkData, $insert_id=false) {
        if (empty($arrTalkData)  || !is_array($arrTalkData)) {
			return false;
		}

		$InfoArr = $this->_dPersonTalk->addPersonTalk($arrTalkData, $insert_id);

		return  $InfoArr;
    }

	/*删除评论*/
	public function delPersonTalk($RecId) {
		if (empty($RecId)) {
		   return false;
		}

		$InfoArr = $this->_dPersonTalk->delPersonTalk($RecId);

		return $InfoArr;
    }


	/*更新评论数量*/
    // todo 没有C层调用
	public function modifyPersonTalk($clentTalkSaveData) {
        if (empty($clentTalkSaveData)) {
            return false;
        }

		$data = array(
			'comment_nums' => "%comment_nums+1%"
		);
		
		$i_num_rows = $this->_dPersonTalk->modifyPersonTalk($data, $clentTalkSaveData);
		return $i_num_rows;
    }
}
