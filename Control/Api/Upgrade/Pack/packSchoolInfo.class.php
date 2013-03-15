<?php
class packSchoolInfo extends packAbstract {
    protected function initInfoList() {
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $this->info_list = $mSchoolInfo->getSchoolInfoById($this->ids);
    }
}