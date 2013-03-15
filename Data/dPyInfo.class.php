<?php

class dPyInfo extends dBase {
    
    protected $_tablename = 'wmw_py_info';
    protected $_fields = array(
        'py_id',
        'py_content',
        'add_account',
        'add_date',
        'py_type',
        'py_att',
    );
    protected $_pk = 'py_id';
    protected $_index_list = array(
        'py_id',
    );
    
    /**
     * 按评语ID读取评语
     * @param $py_id
     */
	public function getPyInfoById($py_id) {
		return $this->getInfoByPk($py_id);
    }
}