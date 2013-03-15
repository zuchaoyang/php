<?php
class packClassAlbum extends packAbstract{
    public function __construct() {
        $this->addChildNode('album_id',new packAlbumInfo());
    }
    
    public function initInfoList(){
        $mClassalbum = ClsFactory::Create('Model.mClassalbum');
        $this->info_list = $mClassalbum->getAlbumInfoByClassCode($this->ids);
    }
    
}
