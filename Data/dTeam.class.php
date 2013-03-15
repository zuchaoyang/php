<?php
/*todo list
 * 代码提取
 * 
 */
class dTeam extends dBase {
    
    protected $_tablename = null;
    protected $_index_list = array();
    protected $_pk = null;
    protected $_fields = array();
    
	public function switchToTeamDuties() {
	    $this->_tablename = 'team_duties';
	    $this->_index_list = array(
	        'team_duties_id'
	    );
	    $this->_pk = 'team_duties_id';
	    $this->_fields = array(
	        'team_duties_id',
	        'team_duties_name',
	        'db_createtime',
	        'db_updatetime',
	    );
	}
	
	public function switchToTeamNumberDuties() {
	    $this->_tablename = 'team_number_duties';
	    $this->_index_list = array(
	        'team_number_duties_id',
	        'team_id',
	        'wmw_uid',
	        'team_duties_id'
	    );
	    $this->_pk = 'team_number_duties_id';
	    $this->_fields = array(
	        'team_number_duties_id',
	        'team_id',
	        'wmw_uid',
	        'team_duties_id',
	        'db_createtime',
	        'db_updatetime',
	        'db_delete',
	    );
	}
	
	public function switchToTeam() {
	    $this->_tablename = 'team';
	    $this->_index_list = array(
	        'team_id',
	        'squadron_id',
	    );
	    $this->_pk = 'team_id';
	    $this->_fields = array(
	        'team_id',
	        'squadron_id',
	        'team_name',
	        'db_createtime',
	        'db_updatetime',
	        'db_delete',
	    );
	}
   
    public function _initialize() {
        $this->connectDb('wm_cy', true);
    }
    
    /**
     * 根据小队id 获取小队信息（主键key）
     * @param $team_ids 小队id
     * @return $new_team_list 小队列表（可能多条）
     **/
    public function getTeamById($team_ids) {
        $this->switchToTeam();
        return $this->getInfoByPk($team_ids);
    }

    /**
     * 根据中队id 获取小队信息（外键）
     * @param $squadron_ids 小队id
     * @return $new_team_list 小队列表（可能多条）
     **/    
    public function getTeamBySquadronId($squadron_ids) {
        $this->switchToTeam();
        return $this->getInfoByFk($squadron_ids, 'squadron_id');
    }

    /**
     * 添加小队信息
     * @param $dataarr 小队详细内容（数组形式）
     * @return $new_team_list 小队列表（可能多条）
     **/    
    public function addTeam($dataarr, $is_return_id = false) {
        $this->switchToTeam();
        return $this->add($dataarr, $is_return_id);
    }
    
    /**
     * 修改小队信息
     * @param $dataarr 小队详细内容（数组形式）
     * @param $team_id 小队id
     * @return 成功返回影响行数 失败返回false
     **/     
    public function modifyTeam($dataarr, $team_id) {
        $this->switchToTeam();
        return $this->modify($dataarr, $team_id);
    }
    
    /**
     * 删除小队信息
     * @param $team_id 小队id
     * @return 成功返回影响行数 失败返回false
     **/    
    public function delTeam($team_id) {
        $this->switchToTeam();
        return $this->delete($team_id);
    }
    
     /**
     * 根据小队职务id  获取小队职务信息（主键）
     * @param $team_duties_ids 小队职务id
     * @return $new_team_list 小队职务列表（可能多条）
     **/     
    public function getTeamDutiesById($team_duties_ids) {
        $this->switchToTeamDuties();
        return $this->getInfoByPk($team_duties_ids);
    }

     /**
     * 删除小队职务信息
     * @param $team_duties_ids 小队职务id
     * @return 成功返回影响行数失败返回false
     **/      
    public function delTeamDuties($team_duties_id) {
        $this->switchToTeamDuties();
        return $this->delete($team_duties_id);
    }
    
    /**
     * 根据id 获取小队成员信息（主键key）
     * @param $team_number_duties_ids 不是用户id 只是流水号
     * @return $new_team_list 小队成员（可能多条）
     **/    
    public function getTeamNumberDutiesById($team_number_duties_ids) {
        $this->switchToTeamNumberDuties();
        return $this->getInfoByPk($team_number_duties_ids);
    }

    /**
     * 根据小队id 获取小队成员信息（外键）
     * @param $team_ids 外键
     * @return $new_team_list 小队成员（可能多条）
     **/  
    public function getTeamNumberDutiesByTeamId($team_ids) {
        $this->switchToTeamNumberDuties();
        return $this->getInfoByFk($team_ids, 'team_id');
    }

    /**
     * 根据会员id 获取小队成员信息（外键）
     * @param $wmw_uids 外键
     * @return $new_team_list 小队成员（可能多条）
     **/      
    public function getTeamNumberDutiesByUid($wmw_uids) {
        $this->switchToTeamNumberDuties();
        return $this->getInfoByFk($wmw_uids, 'wmw_uid');
    }

    /**
     * 添加小队成员信息（外键）
     * @param $dataarr 成员信息
     * @param $is_return_id 是否返回最后插入的id
     * @return 根据$is_return_di 判断返回
     **/   
    public function addTeamNumberDuties($dataarr, $is_return_id = false) {
        $this->switchToTeamNumberDuties();
        return $this->add($dataarr, $is_return_id);
    }

    /**
     * 修改小队成员信息
     * @param $dataarr 成员信息
     * @param $team_number_duties_id 成员id
     * @return 成功返回影响的行数 失败返回 false
     **/   
    public function modifyTeamNumberDuties($dataarr, $team_number_duties_id) {
        $this->switchToTeamNumberDuties();
        return $this->modify($dataarr, $team_number_duties_id);
    }

    /**
     * 根据主键删除小队成员
     * @param $id 成员id
     * @return 成功返回影响行数失败返回false
     **/
    public function delTeamNumberDuties($id) {
        $this->switchToTeamNumberDuties();
        return $this->delete($id);
    }
    

    
    
    
}
?>