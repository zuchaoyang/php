<?php
class dFunc extends dBase {
	protected $_tablename = 'wmw_func';
	protected $_fields = array(
		//权限功能表
		'func_code',
        'func_type',
        'super_func_code',
        'func_name',
		'func_url',
		'is_showflag',
		'func_num',
	);
	protected $_pk = 'func_code';
	protected $_index_list = array(
	    'func_code'
	);

}