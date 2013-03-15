<?php

class dClassCourseSkin extends dBase {
	protected $_tablename = 'wmw_class_course_skin';
	protected $_fields = array(
		'skin_id', 
		'name', 
		'url',
	);
	protected $_pk = 'skin_id';
	protected $_index_list = array(
	    'skin_id'
	);

	public function dClassCourseSkinById($skin_ids) {
	    
	    return $this->getInfoByPk($skin_ids);
	}
}