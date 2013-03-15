<?php
class packLogTypes extends packAbstract {
    
    public function initInfoList() {
        $mLogtypes = ClsFactory::Create('Model.mLogtypes');
        $this->info_list = $mLogtypes->getLogTypesById($this->ids);
    }
}