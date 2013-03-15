<?php
class mClientAlias extends mBase {
    protected $_dClientAlias = null;
    
    public function __construct() {
        $this->_dClientAlias = ClsFactory::Create("Data.dClientAlias");
    }
    
    public function getClientAliasByAliasAccount($alias_accounts) {
        if(empty($alias_accounts)) {
            return false;
        }
        
        return $this->_dClientAlias->getClientAliasByAliasAccount($alias_accounts);
    }
    
    public function getClientAliasByAliasSeq($alias_seqs) {
        if(empty($alias_seqs)) {
            return false;
        }
        
        $alias_seqs = is_array($alias_seqs) ? $alias_seqs : array($alias_seqs);
        foreach($alias_seqs as $id => $alias_seq) {
            $alias_seqs[$id] = self::time33($alias_seq);
        }
        
        return $this->_dClientAlias->getClientAliasByAliasSeq($alias_seqs);
    }
    
    static function times33($string) {
        $string = strval($string);
        
        $code = 5381;
        for ($i = 0, $len = strlen($string); $i < $len; $i++) {
            $code = (int)(($code<<5) + $code + ord($string{$i})) & 0x7fffffff;
        }
        
        return $code;
    }    
    
    public function addClientAlias($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dClientAlias->addClientAlias($dataarr, $is_return_id);
    }
    
    public function addClientAliasBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dClientAlias->addBat($dataarr);
    }
    
    public function modifyClientAlias($dataarr, $alias_id) {
        if(empty($alias_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dClientAlias->modifyClientAlias($dataarr, $alias_id);
    }
    
    public function delClientAlias($alias_id) {
        if(empty($alias_id)) {
            return false;
        }
        
        return $this->_dClientAlias->delClientAlias($alias_id);
    }
    
    
}