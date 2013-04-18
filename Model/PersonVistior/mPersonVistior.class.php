<?php
class mPersonVistior extends mBase{
    
    private  $_dPersonVistior = null;
    
    public function __construct() {
        $this->_dPersonVistior = ClsFactory::Create("Data.PersonVistior.dPersonVistior");
    }
    
    
    public function addPersonVistior($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        return $this->_dPersonVistior->addPersonVistior($dataarr);
        
    }
    
    //根绝帐号获取该帐号的最近访客列表
    public function getPersonVistiorByUid($uid) {
        if(empty($uid)) {
            return false;
        }
        
        return $this->_dPersonVistior->getInfoByFk($uid,'uid');
    }
    
    public function getPersonVistiorInfo($wheresql,$orderby,$offset,$limit) {
        if(empty($wheresql)) {
            return false;
        }
        
        $PersonVistiorInfo = $this->_dPersonVistior->getPersonVistiorInfo($wheresql,$orderby,$offset,$limit);
        $new_person_vistior = array();
        foreach($PersonVistiorInfo as $id=>$val) {
            $new_person_vistior[$val['vuid']] = $val;
        }
        
        return !empty($new_person_vistior) ? $new_person_vistior : false;
    }
    
    public function modifyPersonVistior($datarr,$id) {
        if(empty($datarr)) {
            return false;
        }
        
        return $this->_dPersonVistior->modifyPersonVistior($datarr,$id);
    }
    
    public function delPersonVistior($id) {
        if(empty($id)) {
            return false;
        }
        
        $resault = $this->_dPersonVistior->delPersonVistior($id);
        
         return !empty($resault) ? $resault : false;
    }
}