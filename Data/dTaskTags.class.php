<?php
class dTaskTags extends dBase {
	protected $_tablename = 'oa_task_tags';
    protected $_fields = array(
        'tag_id',
        'school_id',
        'tag_name',
        'add_time',
    );
    protected $_pk = 'tag_id';
	protected $_index_list = array(
	    'tag_id',
	    'school_id',
	);
    
    public function _initialize() {
        $this->connectDb('oa', true);
    }
    
    /**
     * 根据标签id 获取标签详情（可能是多条）
     * @param $tag_ids 标签id
     * @return $new_tasktag_list 标签列表;
     * 
     **/
    public function getTaskTagById($tag_ids) {
        return $this->getInfoByPk($tag_ids);
    }

    /**
     * 根据学校id 获取标签详情（可能是多条）
     * @param $school_ids 学校id（外键）
     * @return $new_tasktag_list 标签列表;
     * 
     **/    
    public function getTaskTagBySchoolId($school_ids) {
        return $this->getInfoByFk($school_ids, 'school_id');
    }

    /**
     * 添加标签
     * @param $datas 标签内容
     * @param $is_return_id 是否返回最后插入记录的id
     * @return $effect_rows,$this->getLastInsID 根据$is_return_id 返回;
     * 
     **/  
    public function addTaskTag($datas, $is_return_id=false) {
        return $this->add($datas, $is_return_id);
    }

     /**
     * 修改标签
     * @param $datas 标签内容
     * @param $tag_id 标签
     * @return 成功返回影响记录的行数失败返回 fasle;
     * 
     **/     
    public function modifyTaskTag($datas, $tag_id) {
        return $this->modify($datas, $tag_id);
    }

     /**
     * 删除标签
     * @param $tag_id 标签
     * @return 成功返回影响记录的行数失败返回 fasle;
     * 
     **/      
    public function delTaskTag($tag_id) {
        return $this->delete($tag_id);
    }
}