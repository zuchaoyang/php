<?php
class dTaskTagRelation extends dBase {
	protected $_tablename = 'oa_task_tag_relation';
    protected $_fields = array(
	    'ttr_id',
	    'tag_id',
	    'task_id',
	    'add_time',
    );
    protected $_pk = 'ttr_id';
	protected $_index_list = array(
	    'ttr_id',
	    'tag_id',
	    'task_id'
	);
    
    public function _initialize() {
        $this->connectDb('oa', true);
    }
    
    /**
     * 
     * 根据 标签$tag_ids(外键) 返回这个标签对应的工作关系
     * 一个标签可以对应多个工作
     * @param $tag_ids 标签id
     * @param $offset limit 开始
     * @param $length 取的记录条数（分页）
     * @param $orderby asc 或者desc
     * @return $new_relation_list 关系列表
     **/
    public function getTaskTagRelationByTagId($tag_ids, $offset = 0, $limit = 10) {
    	$orderby = "ttr_id desc";
    	
    	return $this->getInfoByFk($tag_ids, 'tag_id', $orderby, $offset, $limit);
    }
    
    /**
     * 根据工作id($task_ids外键) 返回这个工作对应的，标签与工作关系
     * 一个工作 可以对应多个标签
     * @param $task_ids 工作id
     * @return $new_relation_list 关系列表
     **/    
    public function getTaskTagRelationByTaskId($task_ids) {
        return $this->getInfoByFk($task_ids, 'task_id');
    }
    
     /**
     * 添加工作标签关系
     * 一个工作 可以对应多个标签
     * @param $datas 关系详情
     * @return $i_num_rows，getLastInsID  默认返回影响记录的条数
     **/    
    public function addTaskTagRelation($datas, $is_return_id=false) {
        return $this->add($datas, $is_return_id);
    }
    
     /**
     * 删除工作标签关系
     * @param $ttr_id 关系ID
     * @return   返回影响记录的条数
     **/    
    public function delTaskTagRelation($ttr_id) {
        return $this->delete($ttr_id);
    }
}
