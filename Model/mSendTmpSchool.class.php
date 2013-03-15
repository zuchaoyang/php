<?php
class mSendTmpSchool extends mBase {
    protected $_dSendTmpSchool = null;
    
    public function __construct() {
        $this->_dSendTmpSchool = ClsFactory::Create('Data.dSendTmpSchool');
    }
    
    //得到一条数据
    public function getOneSendSchoolid() {
        
        $orderby = "id asc limit 0,1";
        return $this->_dSendTmpSchool->getInfo('', $orderby);
    }
    
    
    //批量添加添加
    public function addSendSchoolIds($datas_arr) {
        if(empty($datas_arr)) {
            return false;
        }
        
        return $this->_dSendTmpSchool->addBat($datas_arr);
    }
    //删除记录
    public function delSendSchoolIdsById($datas_arr) {
        if(empty($datas_arr)) {
            return false;
        }
        
        return $this->_dSendTmpSchool->delSendSchoolIdsById($datas_arr);
    }
}