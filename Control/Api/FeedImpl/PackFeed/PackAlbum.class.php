<?php
import('@.Control.Api.FeedImpl.PackFeed.IPack', null, '.php');

/**
 * 提取相册中的动态信息
 * @author Administrator
 *
 */
class PackAlbum implements IPack {

    /**
     * 获取feed内容并且添加relation_id
     * @param int $phtot_id
     * @return 
     * array(
            'feed_type'     => FEED_ALBUM,
            'add_account'   => $album_datas['add_account'],
            'timeline'		=> $album_datas['add_time'],
            'feed_content'  => $this->formatContent($album_datas),
            'img_url'		=> $this->getAlbumFirstPhoto($album_datas['album_id']),
        );
     */
    public function getFeedDatas($photo_id) {
        if(empty($photo_id)) {
            return false;
        }
        
        $mAlbumPhotos = ClsFactory::Create("Model.Album.mAlbumPhotos");
        $photo_info = $mAlbumPhotos->getPhotosByPhotoId($photo_id);
        
        if(empty($photo_info)) return false;
        
        $photo_info = $photo_info[$photo_id];
        import("@.Common_wmw.Pathmanagement_sns");
        $feed_datas = array(
            'feed_type'     => FEED_ALBUM,
            'add_account'   => $photo_info['upd_account'],
            'timeline'		=> time(),
            'feed_content'  => null,
            'img_url'		=> Pathmanagement_sns::getAlbum($photo_info['upd_account']) . $photo_info['file_middle'],
            'from_id'       => $photo_id,
        );
        
        return $feed_datas;
    }
}