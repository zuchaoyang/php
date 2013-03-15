<?php

class mPersontalkcomment extends mBase {
    
    protected $_dPersontalkcomment = null;
    
    public function __construct() {
        $this->_dPersontalkcomment = ClsFactory::Create('Data.dPersontalkcomment');
    }
    
    /**
     * 通过话题id获取评论信息
     * @param $sign_ids
     */
    public function getPersonTalkCommentBySignId($sign_ids) {
        if(empty($sign_ids)) {
            return false;
        }
        
        return $this->_dPersontalkcomment->getPersonTalkCommentBySignId($sign_ids);
    }
    
    /**
     * 添加评论信息
     * @param $datas
     * @param $is_return_id
     */
    public function addPersonTalkComment($datas, $is_return_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dPersontalkcomment->addPersonTalkComment($datas, $is_return_id);
    }
    
    /**
     * 通过主键删除评论信息
     * @param $plun_id
     */
    public function delPersonTalkComment($plun_id) {
        if(empty($plun_id)) {
            return false;
        }
        
        return $this->_dPersontalkcomment->delPersonTalkComment($plun_id);
    }
}