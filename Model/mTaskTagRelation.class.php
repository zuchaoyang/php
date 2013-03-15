<?php

class mTaskTagRelation extends mBase {
	protected $dTaskTagRelation = null;
	
	public function __construct() {
		$this->dTaskTagRelation = ClsFactory::Create('Data.dTaskTagRelation');
	}
	
    /**
     * 根据 标签$tag_ids(外键) 返回这个标签对应的工作关系
     * 一个标签可以对应多个工作
     * @param $tag_ids 标签id
     * @param $offset limit 开始
     * @param $length 取的记录条数（分页）
     * @param $orderby asc 或者desc
     * @return  关系列表
     **/	
    public function getTaskTagRelationByTagId($tag_ids, $offset = 0, $limit = 10) {
        if (empty($tag_ids)) {
            return false;
        }
        
        return $this->dTaskTagRelation->getTaskTagRelationByTagId($tag_ids, $offset, $limit);
    }

    /**
     * 根据工作id($task_ids外键) 返回这个工作对应的，标签与工作关系
     * 一个工作 可以对应多个标签
     * @param $task_ids 工作id
     *  return $new_relation_list 关系列表
     **/  
    public function getTaskTagRelationByTaskId($task_ids) {
        if (empty($task_ids)) {
            return false;
        }
        
        return $this->dTaskTagRelation->getTaskTagRelationByTaskId($task_ids);
    }

     /**
     * 添加工作标签关系
     * 一个工作 可以对应多个标签
     * @param $datas 关系详情
     * @return $i_num_rows，getLastInsID  默认返回影响记录的条数
     **/  
    public function addTaskTagRelation($datas, $is_return_id=false) {
        if (empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->dTaskTagRelation->addTaskTagRelation($datas, $is_return_id);
    }
    
     /**
     * 删除工作标签关系
     * @param $ttr_id 关系ID
     * @return   返回影响记录的条数
     **/      
    public function delTaskTagRelation($ttr_id) {
        if (empty($ttr_id)) {
            return false;
        }
        
        return $this->dTaskTagRelation->delTaskTagRelation($ttr_id);
    }
}