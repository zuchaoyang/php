<?php
class packClassInfo extends packAbstract {
    public function initInfoList() {
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $this->info_list = $mClassInfo->getClassInfoBaseById($this->ids);
    }
}