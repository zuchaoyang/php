<?php
class packPersonLogs extends packAbstract {
    public function __construct() {
        $this->addChildNode('log_type', new packLogTypes());
    }
    
    public function initInfoList() {
        $mPersonLogs = ClsFactory::Create('Model.mPersonlogs');
        $this->info_list = $mPersonLogs->getPersonLogsById($this->ids);
    }
}
