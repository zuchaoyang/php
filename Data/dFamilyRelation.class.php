<?php
/*todo list
 * 方法提取
 * 涉及到的问题：
 * 1. 将wmw_family_relation和wmw_account_relation进行分离后，C层和M层相应调用的地方的代码调整；
 * 2. 重命名D层的文件名和M层的文件名；
 * 2. 删除的方法有：
 */
class dFamilyRelation extends dBase{
    protected $_tablename = 'wmw_family_relation';
    protected $_fields = array(
        'relation_id',
		'client_account',
		'family_account',
		'family_type',
		'add_account',
		'add_time',
    );
    protected $_pk = 'relation_id';
    protected $_index_list = array(
        'relation_id',
        'client_account',
        'family_account',
    );
    
    public function getCompositeKeys() {
	    return array(
	    	'client_account',
        	'family_account',
	    );
	}
    
    //todolist 数据key导致的问题
    public function getFamilyRelationByUid($uids) {
        return $this->getInfoByFk($uids, 'client_account');
    }
    
    /**
     * 通过家长id获取相关信息,一个家长只对应一个孩子
     * @param $uids
     */
    //todolist 代码调整后导致的数据维度问题
    public function getFamilyRelationByFamilyUid($uids) {
        return $this->getInfoByFk($uids, 'family_account');
    }
    
    public function modifyFamilyRelation($dataarr , $relation_id) {
        return $this->modify($dataarr, $relation_id);
    }
    
    
    /**
     * 数据库family_relation表中插入数据
     * @param $datas
     */
    //todolist 函数实现的逻辑存在明显的问题，批量增加的算法有问题；
    public function addFamilyRelation($dataarr, $is_return_id=false) {
    	if(empty($dataarr) || !is_array($dataarr)) {
    		return false;
    	}
		
        return $this->add($dataarr, $is_return_id);
    }
	
    /**
     * 删除对应的数据
     * @param $uids
     */
    //todolist 函数的定义不符合规范
    public function delFamilyRelation($relation_id) {
        return $this->delete($relation_id);
    }
 
}
