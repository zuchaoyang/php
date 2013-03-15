<?php
class dLogplun extends dBase {
    protected $_tablename = 'wmw_log_plun';
    protected $_fields = array(
    	'plun_id',
    	'log_id',
    	'plun_content',
    	'add_account',
    	'add_date',
    );
    protected $_pk = 'plun_id';
    protected $_index_list = array(
        'plun_id',
    	'log_id'
    );
    
    /*按日志ID获取评论内容
     * @param $logid
     * return $new_logplun_arr 三维维数组
     */
    public function getLogplunByLogid($log_ids) {
        if(empty($log_ids)) {
            return false;
        }
        
        return $this->getInfoByFk($log_ids, 'log_id', 'log_id desc');
    }
	
	/*按评论ID删除评论内容
     * @param $logid
     * return $effect_rows 影响行数
     */
	public function delLogplun($plunId) {
		return  $this->delete($plunId);
	}    
	
	/*添加日志评论
     * @param $logid
     * $param $is_return_inset_id 是否返回插入id
     * return $is_success_add 影响行数或者插入id
     */
    public function addLogplun($plunData, $is_return_insert_id) {
        return $this->add($plunData, $is_return_insert_id);
    }
}
