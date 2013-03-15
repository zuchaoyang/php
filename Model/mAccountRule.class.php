<?php
class mAccountRule extends mBase {
    protected $_dAccountRule = null;
    
    public function __construct() {
        $this->_dAccountRule = ClsFactory::Create('Data.dAccountRule');
    }
    
    //获取所有账号规则
    public function getAccountRuleAll() {
        
        return $this->_dAccountRule->getInfo();
    }
    
    //获取当前正在使用的账号规则
    public function getAccountRuleByUseFlag($use_flag) {
        if(empty($use_flag)) {
            return false;
        }
        $wheresql = "use_flag='$use_flag'";
        
        return $this->_dAccountRule->getInfo($wheresql);
    }
    
    //修改
    public function modifyAccountRule($datas, $id) {
        if(empty($datas) || !is_array($datas) || empty($id)) {
            return false;
        }
        
        return $this->_dAccountRule->modifyAccountRule($datas, $id);
    }
} 