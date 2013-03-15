<?php
class packUser extends packAbstract {
    protected function initInfoList() {
        $mUser = ClsFactory::Create('Model.mUser');
        $this->info_list = $mUser->getUserBaseByUid($this->ids);
    }
}