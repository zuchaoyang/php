<?php
class dLogStationery extends dBase {
    
    protected $_tablename = "wmw_log_stationery";
    protected $_fields = array (
		'id', 
		'sty_type', 
		'sty_name', 
		'sty_url'
    );
    protected $_pk = 'id';
    protected $_index_list = array(
        'id'
    );
    
}
