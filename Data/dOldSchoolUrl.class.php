<?php
class dOldSchoolUrl extends dBase {

    //表名
	protected $_tablename = 'wmw_old_school_url';
	protected $_fields = array(
        'id',
        'school_name',
        'school_url',
	);
	protected $_pk = 'id';
	protected $_index_list = array(
	    'id',
	);

}