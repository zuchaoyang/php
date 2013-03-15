<?php
class dTaskType extends dBase {
	protected $_tablename = 'oa_task_type_system';
    protected $_fields = array(
	    'type_id',
	    'type_name',
	    'add_time',
    );
    protected $_pk = 'type_id';
	protected $_index_list = array(
	    'type_id'
	);
    
    public function _initialize() {
        $this->connectDb('oa', true);
    }
    

    /**
     * 根据 id 返回详细的分类信息
     * @param $type_ids 工作分类的id(主键key)
     * return $new_tasktype_list 工作分类列表 （二维数组）
     * **/
    //todolist 有待考虑处理方案
    public function getTaskTypeById($type_ids) {
    	$tasktype_list = $this->getInfoByPk($type_ids);
        
        return !empty($tasktype_list) ? $tasktype_list : false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}