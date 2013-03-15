<?php

class dPersonspaceskin extends dBase {

    protected $_tablename = 'wmw_person_space_skin';
	protected $_fields = array(
	    'skin_id', 
		'use_type', 
		'skin_name', 
		'skin_value',
	);
	protected $_pk = 'skin_id';
    protected $_index_list = array(
        'skin_id',
    );

     /**
     * 按皮肤ID读取皮肤信息
     * @param $skin_id
     */	
    public function getPersonSpaceSkinById($skin_id) {
		return $this->getInfoByPk($skin_id);
	}
}