<?php
class dUidDispatcher extends dBase {
    protected $_tablename = 'wmw_uid_dispatcher';
    protected $_pk = 'uid';
    protected $_fields = array(
        'uid',
    );
    protected $_index_list = array(
        'uid',
    );
    
    public function addUidDispatcher() {
        $this->execute("insert into {$this->_tablename} values(null)");
        
        return $this->getLastInsID();
    }
    
    public function delUidDispatcher($uid) {
        return $this->delete($uid);
    }
    
    public function setAutoIncrement($auto_increment) {
        if(empty($auto_increment)) {
            return false;
        }
        
        return $this->query("alter table {$this->_tablename} auto_increment='$auto_increment'");
    }
}