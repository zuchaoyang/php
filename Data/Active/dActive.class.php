<?php
class dActive extends dBase {
    protected  $_tablename = 'wmw_client_active';
    protected  $_pk = 'active_id';
    protected  $_index_list = array(
        'client_account',
        'active_id',
    );
    protected  $_fields = array(
        'active_id',
        'client_account',
        'value',
    );
    
    public function getActiveById($active_id){
        return $this->getInfoByPk($active_id);
    }
    
    public function getActiveByClientAccount($client_account,$orderby,$offset ,$limit){
        return $this->getInfoByFk($client_account, 'client_account',$orderby,$offset ,$limit);
    }
    
    public function addActive($dataarr, $is_return_id){
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyActive($dataarr, $active_id){
        return $this->modify($dataarr, $active_id);
    }
    
    public function delActive($active_id){
        return $this->delete($active_id);
    }
}