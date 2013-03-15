<?php
class packClassLog extends packAbstract {
     public function __construct() {
        $this->addChildNode('log_id',new packPersonLogs());
    }
    
    public function initInfoList(){
        $mClasslog = ClsFactory::Create('Model.mClasslog');
        $this->info_list = reset($mClasslog->getLogInfoByClassCode($this->ids));
    }
}