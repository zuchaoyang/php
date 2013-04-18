<?php
include_once(dirname(dirname(__FILE__)) . '/Daemon/Daemon.inc.php');
import('@.Common_wmw.Pathmanagement_sns');
$Pathmanagement_sns = new Pathmanagement_sns();
import('@.Control.Api.AlbumImpl.PhotoInfo');
$PhotoInfo_obj = new PhotoInfo();
//数据总数
$all_count = $PhotoInfo_obj->getAllCount();
//一次获取的数据条数
$limit = 100;
//循环次数
$m = ceil($all_count / $limit);
for($i=0;$i<$m;$i++){
    $offset = $i*$limit;
    $photo_list = $PhotoInfo_obj->getPhotos($offset,$limit);
    foreach($photo_list as $photo_id=>$photo_info) {
        $file_path = $Pathmanagement_sns->uploadAlbum($photo_info['upd_account']);
        $file_path = $file_path . '/' .$photo_info['file_big'];
        if(!file_exists($file_path)) {
            continue;
        }
        scalePhoto($file_path);
    }
    sleep(1);
};


//相片缩略图（中：瀑布流，小：照片普通列表）
/**
 * @param  
 * @return array
 * $img_path_arr = array(
 * 		'_'
 * );
 */
 function scalePhoto($src_img) {
    if(empty($src_img) || !file_exists($src_img)) {
        return false;
    }
    //相册,照片列表width:198 height:162------small
    //瀑布流列表width:178--------------------middle
    $img_name = basename($src_img);

    import('@.Common_wmw.WmwScaleImage');
    $wmwScaleImage = new WmwScaleImage();
    $_s_path = $wmwScaleImage->scaleSmall($src_img);
    $_m_path = $wmwScaleImage->scaleMiddle($src_img);
    $img_name_arr = array(
        'img_name'=> $img_name,
        '_s'	  => basename($_s_path),
        '_m'	  => basename($_m_path)
    ); 
    return $img_name_arr;
}