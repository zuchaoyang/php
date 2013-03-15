<?php
class packSubjectInfo extends packAbstract {
    public function __construct() {
        $this->addChildNode('school_id', new packSchoolInfo());
    }
    protected function initInfoList() {
        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
        $this->info_list = $mSubjectInfo->getSubjectInfoById($this->ids);
    }
}