<?php
class packAlbumInfo extends packAbstract {
    public function __construct() {
        $this->addChildNode('album_id', new packPhotosInfo());
    }
    
    public function initInfoList() {
        $mAlbumInfo = ClsFactory::Create('Model.mAlbumInfo');
        $this->info_list = $mAlbumInfo->getAlbumListByAlbumid($this->ids);
    }
}

class packPhotosInfo extends packAbstract {
    public function initInfoList() {
        $mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
        $this->info_list = $mPhotosInfo->getPhotoInfoByAlbumId($this->ids);
    }
}