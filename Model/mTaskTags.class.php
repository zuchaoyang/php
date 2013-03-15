<?php
class mTaskTags extends mBase {
	protected $_dTaskTags = null;
	
	public function __construct() {
		$this->_dTaskTags = ClsFactory::Create('Data.dTaskTags');	
	}
	
    /**
     * 根据标签id 获取标签详情（可能是多条）
     * @param $tag_ids 标签id
     * @return $new_tasktag_list 标签列表;
     * 
     **/	
    public function getTaskTagById($tag_ids) {
        if (empty($tag_ids)) {
            return false;
        }
        
        return $this->_dTaskTags->getTaskTagById($tag_ids);
    }

    /**
     * 根据学校id 获取标签详情（可能是多条）
     * @param $school_ids 学校id（外键）
     * @return $new_tasktag_list 标签列表;
     * 
     **/      
    public function getTaskTagBySchoolId($school_ids) {
        if (empty($school_ids)) {
            return false;
        }
 
        return $this->_dTaskTags->getTaskTagBySchoolId($school_ids);
    }

    /**
     * 根据标签名字和学校id 获取标签详情（不是模糊查询）
     * @param $tag_names 标签名字
     * @param $school_ids 学校id（外键）
     * @return $new_tasktag_list 标签列表;
     * 
     **/     
    public function getTaskTagByNamesWithSchoolId($tag_names, $school_id) {
        if (empty($school_id) || empty($tag_names)) {
            return false;
        }
        
        $school_id = is_array($school_id) ? array_shift($school_id) : $school_id;
        $tag_names = array_unique((array)$tag_names);
        $wherearr = array(
        	"school_id='$school_id'",
        	"tag_name in('" . implode("','", $tag_names) . "')"
        );
        
        return $this->_dTaskTags->getInfo($wherearr);
        //return $this->_dTaskTags->getTaskTagByNamesWithSchoolId($tag_names, $school_id);
    }

    /**
     * 添加标签
     * @param $datas 标签内容
     * @param $is_return_id 是否返回最后插入记录的id
     * @return $effect_rows,$this->getLastInsID 根据$is_return_id 返回;
     * 
     **/      
    public function addTaskTag($datas, $is_return_id = false) {
        if (empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dTaskTags->addTaskTag($datas, $is_return_id);
    }

     /**
     * 修改标签
     * @param $datas 标签内容
     * @param $tag_id 标签
     * @return 成功返回影响记录的行数失败返回 fasle;
     * 
     **/      
    public function modifyTaskTag($datas, $tag_id) {
        if (empty($datas) || !is_array($datas) || empty($tag_id)) {
            return false;
        }
        
        return $this->_dTaskTags->modifyTaskTag($datas, $tag_id);
    }

     /**
     * 删除标签
     * @param $tag_id 标签
     * @return 成功返回影响记录的行数失败返回 fasle;
     * 
     **/       
    public function delTaskTag($tag_id) {
        if (empty($tag_id)) {
            return false;
        }
        
        return $this->_dTaskTags->delTaskTag($tag_id);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}