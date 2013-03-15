<?php
class mSendTmpClass extends mBase {
    protected $_dSendTmpClass = null;
    
    public function __construct() {
        $this->_dSendTmpClass = ClsFactory::Create('Data.dSendTmpClass');
    }
    
    //得到一条数据
    public function getOneSendClassCode() {
        
        $orderby = "id asc limit 0,1";
        
        return $this->_dSendTmpClass->getInfo(null, $orderby);
    }
    //得到一条数据
    public function getListByTmpId($tmp_id,$operation_strategy) {
        $wherearr[] = 'send_tmp_id='.$tmp_id;
        $wherearr[] = 'operation_strategy='.$operation_strategy;
        $orderby = 'id asc'; 
        $offset = 0;
        $limit = 10;
        return $this->_dSendTmpClass->getInfo($wherearr, $orderby, $offset, $limit);
    }
   
    
    //批量添加添加
    public function addSendClassCodes($datas_arr) {
        
        if(empty($datas_arr)) {
            return false;
        }
        
        return $this->_dSendTmpClass->addBat($datas_arr);
    }
    
    //删除记录
    public function delSendClasstmpById($ids) {
        
        if(empty($ids)) {
            return false;
        }
        
        return $this->_dSendTmpClass->delSendClasstmpById($ids);
    }
}