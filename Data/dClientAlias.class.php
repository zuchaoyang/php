<?php
class dClientAlias extends dBase{
    protected $_tablename = 'wmw_client_alias';
    protected $_fields = array(
        'alias_id',
        'alias_seq',
        'alias_account',
        'uid_alias',
        'uid_alias_type',
        'add_time',
        'upd_time',
    );
    protected $_pk = 'alias_id';
    protected $_index_list = array(
        'alias_id',
        'alias_account',
        'alias_seq'
    );
    
    
    public function getClientAliasByAliasSeq($alias_seqs) {
        return $this->getInfoFk($alias_seqs, 'alias_seq');
    }
    public function getClientAliasByAliasAccount($alias_accounts) {
        return $this->getInfoByFk($alias_accounts, 'alias_account');
    }
    
    public function addClientAlias($dataarr, $is_return_id) {
        return $this->add($dataarr, $is_return_id);
    }
    
    public function modifyClientAlias($dataarr, $alias_id) {
        return $this->modify($dataarr, $alias_id);
    }
    
    public function delClientAlias($alias_id) {
        return $this->delete($alias_id);
    }
}