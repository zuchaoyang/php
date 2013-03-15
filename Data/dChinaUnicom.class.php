<?php
//佣金功能，属于临时文件，当表 chinaunicom的数据为空，此功能将作废
//lnc
class dChinaUnicom extends dBase{

	protected $_tablename = 'china_unicom';
    protected $_fields = array(
      'id',
      'phone_id',
      'sim_time',
      'area_code'
    );
    protected $_pk = 'id';
    protected $_index_list = array();

}