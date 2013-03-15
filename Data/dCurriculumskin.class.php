<?php

class dCurriculumskin extends dBase {
	protected $_tablename = 'old_wmw_curriculum_skin';
	protected $_fields = array(
		'skin_id', 
		'skin_name', 
		'skin_value',
	);
	protected $_pk = 'skin_id';
	protected $_index_list = array(
	    'skin_id'
	);

	public function getCurriculumSkinById($skin_ids) {
	    return $this->getInfoByPk($skin_ids);
	}
}