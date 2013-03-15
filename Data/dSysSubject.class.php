<?php
class dSysSubject extends dBase{
    protected $_tablename = 'wmw_sys_subject';
    protected $_fields = array(
		'subject_id',
		'subject_name',
		'subject_type',
		'add_account',
		'add_time'
    );
    protected $_pk = 'subject_id';
    protected $_index_list = array(
        'subject_id',
    );
    /*
     * note：
     *   学校在首次进入课程管理时，将获取对应的系统科目数据，
     *   插入到表subject_info中，初始化后不需要再查询系统科目表 
     */

}
