<?php 
/**
 * Feed服务数据层
 *
 * @copyright   (c) 2012, wmw.cn All rights reserved.
 * @author      杨益 <yangyi@wmw.cn>
 * @version     1.0 - 2012-03-20
 * @package     Data
 */
//todolist
class dFeed extends dBase{
	protected $_tablename = null;
	protected $_index_list = array();
	protected $_pk = null;
	protected $_fields = array();
	
	public function switchToPersonFeed() {
	    $this->_tablename = 'wmw_person_feed';
	    $this->_index_list = array(
	        'feed_id',
	        'client_account'
	    );
	    $this->_pk = 'feed_id';
	    $this->_fields = array(
	        'feed_id',
			'feed_content',
			'client_account',
			'upd_time',
	    );
	}
	
	public function switchToClassFeed() {
	    $this->_tablename = 'wmw_class_feed';
	    $this->_index_list = array(
	        'client_account',
	        'feed_id',
	        'class_code',
	    );
	    $this->_pk = 'feed_id';
	    $this->_fields = array(
	        'feed_id',
			'feed_content',
			'client_account',
			'upd_time',
			'class_code',
	    );
	}
	
    /**
     * 增加个人的feed信息
     * @param $uid int
     * @param $res_id int
     * @param $res_type int
     * @param $res_stats int
     * @param $ctime int
     * @return int
     */
	//todolist 特殊业务
    public function addPersonFeed($uid, $res_id, $res_type, $res_stats, $ctime) {
        //$uid,$res_id,$res_type,$ctime必须为int
        if (!is_int($uid) || !is_int($res_id) || !is_int($res_type) || !is_int($res_stats) || !is_int($ctime)) {
        	return false;
        } 
        
        $this->switchToPersonFeed();
        $pack = mysql_escape_string(pack('L4', $res_id, $res_type, $res_stats, $ctime));

        $sql = "update $this->_tablename set feed_content=CONCAT('".$pack."', feed_content),upd_time=".$ctime.' where client_account='.$uid;
        //echo $sql;
        $rowCount = $this->execute($sql);
        if ($rowCount < 1) {
            $sql = "insert into $this->_tablename (client_account,feed_content,upd_time) values (".$uid.",'".$pack."',".$ctime.")";
            $rowCount = $this->execute($sql);
        }
        return $rowCount;
    }

    /**
     * 增加班级的feed信息
     * @param $class_code int
     * @param $uid int
     * @param $res_id int
     * @param $res_type int
     * @param $res_stats int
     * @param $ctime int
     * @return int
     */
    //todolist 特殊业务
    public function addClassFeed($class_code, $uid, $res_id, $res_type, $res_stats, $ctime) {
        //$uid,$res_id,$res_type,$ctime必须为int
        if (!is_int($class_code) || !is_int($uid) || !is_int($res_id) || !is_int($res_type) || !is_int($res_stats) || !is_int($ctime)) {
        	return false;
        } 
        $this->switchToClassFeed();
        
        $pack = mysql_escape_string(pack('L4', $res_id, $res_type, $res_stats, $ctime));

        $sql = "update $this->_tablename set feed_content=CONCAT('".$pack."', feed_content),upd_time=".$ctime.' where client_account='.$uid." and class_code=".$class_code;
        $rowCount = $this->execute($sql);
        if ($rowCount < 1) {
            $sql = "insert into $this->_tablename (client_account,feed_content,upd_time,class_code) values (".$uid.",'".$pack."',".$ctime.",".$class_code.")";
            $rowCount = $this->execute($sql);
        }

        return $rowCount;
    }

    /**
     * feed获取数据后进行Top排序
     * @param   array   $data           从getPersonFeedContentByUid获取的源数据
     * @param   integer $length         Top长度
     * @param   integer $lastUpdTime     进行比对的最小时间
     * @return  array
     */
    public function getTopListRids($data, $limit, $lastUpdTime) {
        if (empty($data) || !is_array($data) || !is_int($limit) || !is_int($lastUpdTime)) {
        	return false;
        } 
        
        $result = array();
        foreach ($data as $uid => $v) {
            $ar = unpack('L*', $v);
            unset($data[$uid]);
            $count = count($ar);
            $i = 1;
            $rids_del = array();
            while ($i < $count) {
                //过滤不符合时间条件的记录
                if ($ar[$i+3] < $lastUpdTime) break;
                //如果该条feed逻辑删除，则跳过，并记录该删除feed信息
                if ($ar[$i+2] == FEED_DEL) {
                    $rids_del[$ar[$i].'_'.$ar[$i+1]] = $ar[$i].'_'.$ar[$i+1];
                    //continue;
                }
                //取得没有删除的feed
                if (!isset($result[$ar[$i].'_'.$ar[$i+1].'_'.$ar[$i+2]]) && !isset($rids_del[$ar[$i].'_'.$ar[$i+1]])) {
                    $result[$ar[$i].'_'.$ar[$i+1].'_'.$ar[$i+2]] = $ar[$i+3];
                }
                unset($ar[$i], $ar[$i+1], $ar[$i+2], $ar[$i+3]);
                $i += 4;
            }
        }
        arsort($result);
        $result = array_slice(array_keys($result), 0, $limit);

        //数据重组
        foreach ($result as &$rid) {
            list($res_id, $res_type, $res_stats) = explode('_', $rid);
            $rid = array();
            $rid['res_id'] = $res_id;
            $rid['res_type'] = $res_type;
            $rid['res_stats'] = $res_stats;
        }
        return $result;
    }

}
