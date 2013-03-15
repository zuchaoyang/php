<?php
class mTaskType extends mBase {
	protected $_dTaskType = null;
	
	public function __construct() {
		$this->_dTaskType = ClsFactory::Create('Data.dTaskType');
	}
	
	/**
     * 获取所有的工作分类信息
     * @return $new_tasktype_list 工作分类列表 （二维数组）
     * 
     * **/
    public function getTaskTypeSystemAll() {
    	
    	return $this->_dTaskType->getInfo();
        //return $this->dTaskType->getTaskTypeSystemAll();
    }

    /**
     * 根据 id 返回详细的分类信息
     * @param $type_ids 工作分类的id(主键key)
     * @return $new_tasktype_list 工作分类列表 （二维数组）
     * **/
    public function getTaskTypeById($type_ids) {
        if (empty($type_ids)) {
            return false;
        }

        $tasktype_list = $this->_dTaskType->getTaskTypeById($type_ids);
        if (!empty($tasktype_list)) {
            foreach($tasktype_list as $key=>$val) {
                $tasktype_list[$key]['is_system'] = 1;
            }
        }
        
        return $tasktype_list;
    }
}
