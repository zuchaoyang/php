<?php
class mLogplun extends mBase {

	protected $_dLogplun = null;
	
    public function __construct() {
        $this->_dLogplun = ClsFactory::Create('Data.dLogplun');
    }


	/*按日志ID获取评论内容
     * @param $logid
     * return $new_logplun_arr 三维维数组
     */
	public function getLogplunByLogid($logid) {
	    if(empty($logid)) {
	        return false;
	    }
	    
	    return $this->_dLogplun->getLogplunByLogid($logid);
	}
	
	/*按评论ID删除评论内容
     * @param $logid
     * return $effect_rows 影响行数
     */
	public function delLogplun($plun_id) {
	    if(empty($plun_id)) {
	        return false;
	    }
	    
		return  $this->_dLogplun->delLogplun($plun_id);
	}

    /*添加日志评论
     * @param $logid
     * $param $is_return_inset_id 是否返回插入id
     * return $is_success_add 影响行数或者插入id
     */
    public function addLogplun($datas, $is_return_insert_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }

        return $this->_dLogplun->addLogplun($datas, $is_return_insert_id);
    }
    

    
    /***************************************************************************
     * 特殊业务函数
     **************************************************************************/
    /**
     * 日志评论数量  
     * @param $logid	日志id值
     * @return 			对应日志的评论数
     */
	public function getLogplunCountByLogid($log_id) {
	    if(empty($log_id)) {
	        return false;
	    }
	    
	    $log_id = is_array($log_id) ? array_shift($log_id) : $log_id;
	    $wheresql = "log_id='$log_id'";
	    
	    return $this->_dLogplun->getCount($wheresql);
	}
    
	/*按日志ID删除日志评论内容
     * @param $logid
     * return $effect_rows 影响行数
     */
	public function delLogplunByLogId($logid) {
	    if(empty($logid)) {
	        return false;
	    }
	    
	    $logplun_arr = $this->_dLogplun->getLogplunByLogid($logid);
	    $logplun_list = & $logplun_arr[$logid];
	    $plun_ids = array_keys($logplun_list);
	    
	    $effect_row = 0;
	    if(!empty($plun_ids)) {
	        foreach($plun_ids as $plun_id) {
	           $this->delLogplun($plun_id) && $effect_row++;
	        }
	    }
	    
		return $effect_row;
	}
    
}
