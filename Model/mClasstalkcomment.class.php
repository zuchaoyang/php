<?php

class mClasstalkcomment extends mBase {
    
	protected $_dClasstalkcomment = null;
	
	public function __construct() {
		$this->_dClasstalkcomment = ClsFactory::Create('Data.dClasstalkcomment');
	}
	
    /*保存说说评论内容*/
	public function addClassTalkcomment($clentTalkSaveData) {
		if (empty($clentTalkSaveData)) {
			return false;
		}
		
		$InfoArr = $this->_dClasstalkcomment->addClassTalkcomment($clentTalkSaveData);
		
		return !empty($InfoArr) ? $InfoArr : false;
		
    }


	/*更新评论数量*/
	public function mTalkUpdateCommentNums($clentTalkSaveData) {
		if (empty($clentTalkSaveData)) {
			return false;
		}
		
		$InfoArr = $this->_dClasstalkcomment->dTalkUpdateCommentNums($clentTalkSaveData);
		
		return !empty($InfoArr) ? $InfoArr : false;
    }
    
    public function getCommentListByTalkId($talk_ids) {
		if (empty($talk_ids)) {
			return false;
		}
		$InfoArr = $this->_dClasstalkcomment->getCommentListByTalkId($talk_ids);
		
		return !empty($InfoArr) ? $InfoArr : false;
    }

	public function delCommentById($id) {
		return $this->_dClasstalkcomment->delCommentById($id);
	}
	
	public function modifySpaceSkin($Uid,$skinValue) {
		if (empty($Uid) || empty($skinValue)) {
			return false;
		}
		
		$InfoArr = $this->_dClasstalkcomment->modifySpaceSkin($Uid,$skinValue);
		
		return !empty($InfoArr) ? $InfoArr : false;
    }





	












}
