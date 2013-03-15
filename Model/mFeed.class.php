<?php
/**
 * Feed服务模型层
 *
 * @copyright   (c) 2012, wmw.cn All rights reserved.
 * @author      杨益 <yangyi@wmw.cn>
 * @version     1.0 - 2012-03-20
 * @package     Model
 */

class mFeed extends mBase{
	protected $_dFeed = null;
	
	public function __construct() {
		$this->_dFeed = ClsFactory::Create('Data.dFeed');
	}
	
    const FEED_LIST_MAX_NUM = 500;
    /**
     * 增加个人的feed信息
     * @param $uid int
     * @param $res_id int
     * @param $res_type int
     * @param $res_stats int
     * @param $ctime int
     */
    public function addPersonFeed($uid, $res_id, $res_type, $res_stats, $ctime) {
    	if(empty($uid) || empty($res_id) || $res_stats==='' || $res_type==='' || empty($ctime)){
    		return false;
    	}
    	
        return $this->_dFeed->addPersonFeed($uid, $res_id, $res_type, $res_stats, $ctime);
    }

    /**
     * 增加班级的feed信息
     * @param $class_code int
     * @param $uid int
     * @param $res_id int
     * @param $res_type int
     * @param $res_stats int
     * @param $ctime int
     */
    public function addClassFeed($class_code, $uid, $res_id, $res_type, $res_stats, $ctime) {
    	if(empty($class_code) || empty($uid) || empty($res_id) || $res_stats==='' || $res_type==='' || empty($ctime)){
    		return false;
    	}
        return $this->_dFeed->addClassFeed($class_code, $uid, $res_id, $res_type, $res_stats, $ctime);
    }

    /**
     * 根据用户账号获取个人feed信息
     * @param $uids
     * @return array
     */
    private function getPersonFeedContentByUid($uids) {
        if (empty($uids)) {
            return false;
        }
        
       $this->_dFeed->switchToPersonFeed();
       $wheresql = "client_account in (".implode(',' , (array)$uids).")";
       $feedlist = $this->_dFeed->getInfo($wheresql);
       
       $result = array();
       foreach ($feedlist as $feeds) { 
            $result[$feeds['client_account']] = $feeds['feed_content'];
       }
       
       return !empty($result) ? $result : false;
       
    }

    /**
     * 根据用户账号获取班级feed信息
     * @param $class_code
     * @return array
     */
    public function getClassFeedContentByCid($class_code) {
        if (empty($class_code)) {
            return false;
        }
        
        $this->_dFeed->switchToClassFeed();
        $wheresql = "class_code =" . $class_code;
        $feedlist = $this->_dFeed->getInfo($wheresql);
        $result = array();
        foreach ($feedlist as $feeds) { 
            $result[$feeds['client_account']] = $feeds['feed_content'];
        }
        return !empty($result) ? $result : false;
        
    }

    /**
     * feed获取数据后进行Top排序
     * @param   array   $data           从getPersonFeedContentByUid获取的源数据
     * @param   integer $length         Top长度
     * @param   integer $lastUpdTime     进行比对的最小时间
     * @return  array
     */
    public function getTopListRids($data, $length, $lastUpdTime) {
        if (empty($data) || !is_array($data) || !is_int($length) || !is_int($lastUpdTime)) {
            return false;
        }
        
        return $this->_dFeed->getTopListRids($data, $length, $lastUpdTime);
    }

    /**
     * 获取个人feed list
     * @param   array   $uids           
     * @param   int   $lastUpdTime 最后更新时间
     * @param   integer $offset         起始值
     * @param   integer $length         分页长度
     * @return  array
     */
    public function getPersonFeedList($uids, $lastUpdTime, $offset, $length) {
        if (empty($uids) || !is_array($uids) || !is_int($lastUpdTime) || !is_int($offset) || !is_int($length)) {
            return false;
        }
        $allFeeds = $this->getPersonFeedContentByUid($uids);
        $feeds = $this->getTopListRids($allFeeds, self::FEED_LIST_MAX_NUM, $lastUpdTime);
        if (empty($feeds)) {
            return false;
        }

        $result['count'] = count($feeds);
        $result['feed'] = array_slice($feeds, $offset, $length);
        return $result;
    }

    /**
     * 获取班级feed list
     * @param   array   $class_code           
     * @param   int   $lastUpdTime 最后更新时间
     * @param   integer $offset         起始值
     * @param   integer $length         分页长度
     * @return  array
     */
    public function getClassFeedList($class_code, $lastUpdTime, $offset, $limit) {
        if (!is_int($class_code) || !is_int($lastUpdTime) || !is_int($offset) || !is_int($limit)) {
            return false;
        }
        
        $allFeeds = $this->getClassFeedContentByCid($class_code);
        $feeds = $this->getTopListRids($allFeeds, self::FEED_LIST_MAX_NUM, $lastUpdTime);
        if (empty($feeds)) {
            return false;
        }
        $result['count'] = count($feeds);
        $result['feed'] = array_slice($feeds, $offset, $limit);
        return $result;
    }

}
