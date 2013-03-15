<?php

class dPersontalkcomment extends dBase {
	
    protected $_tablename = 'wmw_person_talkcomment';
    protected $_fields = array(
        'plun_id',
        'sign_id',
        'plun_content', 
        'add_account',
        'add_date', 
	 );
	 protected $_pk = 'plun_id';
	 protected $_index_list = array(
	     'plun_id',
	     'sign_id',
	 );
	 
	 /**
	  * 通过话题id获取评论信息
	  * @param $sign_ids
	  */
	 public function getPersonTalkCommentBySignId($sign_ids) {
	     return $this->getInfoByFk($sign_ids, 'sign_id');
	 }
	 
	 /**
	  * 添加评论信息
	  * @param $datas
	  * @param $is_return_id
	  */
	 public function addPersonTalkComment($datas, $is_return_id = false) {
	     return $this->add($datas, $is_return_id);
	 }
	 
	 /**
	  * 删除评论信息
	  * @param $plun_id
	  */
	 public function delPersonTalkComment($plun_id) {
	     return $this->delete($plun_id);
	 }
}
