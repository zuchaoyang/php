<?php
class mPretreatSms extends mBase{

    protected $_dPretreatSms = null;
    
    public function __construct() {
        $this->_dPretreatSms = ClsFactory::Create("Data.dPretreatSms");
    }
    
    /*添加预发短信信息
     * @param $dataarr
     * return $effect_rows
     */
    function addPretreatSms($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dPretreatSms->addPretreatSms($dataarr);
    }

     /*删除与发短信信息
     * @param $pretreat_id
     * return $effect_rows
     */
    public function delPretreatSms($pretreat_id) {
        if(empty($pretreat_id)) {
            return false;
        }
        
        return $this->_dPretreatSms->delPretreatSms($pretreat_id);
    }
}