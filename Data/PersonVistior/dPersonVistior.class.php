<?php
class dPersonVistior extends dBase{
    protected $_tablename = 'wmw_person_vistior';
    protected $_pk='id';
    protected $_fields = array(
        'id',
        'uid',
        'vuid',
        'timeline',
    );
    protected $_index_list = array(
        'uid',
        'vuid',
    );
    
    public function addPersonVistior($dataarr) {
        return $this->add($dataarr);
    }
    
    
    public function getPersonVistiorInfo($wheresql,$orderby,$offset,$limit) {
       return $this->getInfo($wheresql,$orderby,$offset,$limit);
    }
   
    public function modifyPersonVistior($datarr,$id) {
        return $this->modify($datarr,$id);
    }
    
    public function delPersonVistior($id) {
        return $this->delete($id);
    }
}