<?php

$folder = "images"; //location of images

if (!isset($_GET)) {
    exit();
} // no parameters passed

$cleanInput = array();
foreach ($_GET as $key => $value) {
    $cleanInput[$key] =
            filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

if (isset($cleanInput['count'])) {
    if ($cleanInput['count'] == 1) {
        echo count(getImageList($folder));
        exit();
    }
}

if (isset($cleanInput['next'])) {
    if(isset($cleanInput['size'])){
        $imgCount = $cleanInput['size']; //number of images to load each call
    }else $imgCount=10;

    $imageList=  getImageList($folder);
    returnJSON($folder,$imageList,$cleanInput['next']+1,$imgCount);
}

function returnJSON($folder,$imageList, $imageNumber, $count) {
    if (substr($folder, -1) != DIRECTORY_SEPARATOR) {
       $folder = $folder . DIRECTORY_SEPARATOR; // append directory seperator
    }
   if ($count < 0) {
        $imageNumber+=$count;
        $count*=-1;
    }
    $i = 0;
    while ($count > $i) {
        if ($i + $imageNumber >= count($imageList))
        {
            echo json_encode($string);
            return; //out of images
        }
        $filename = $imageList[$i + $imageNumber];
        $info = substr($filename, 0, strpos($filename, ".", 0));
        $thumb = $folder . $info . "_small" . substr($filename, stripos($filename, "."), strlen($filename));
        if (!file_exists($thumb)) {
            createthumb($folder . $filename, $thumb, 53, 40); // default theme size
        }
        if(is_file($thumb)) $string[$i]['thumb']=$thumb;  //in case thumbnail file doesn't exist
        $string[$i]['image']=$folder.$filename;            
        $i++;
    }
    echo json_encode($string);
}

function getImageList($folder) {
    $extList = array();
    $extList['gif'] = 'image/gif';
    $extList['jpg'] = 'image/jpeg';
    $extList['jpeg'] = 'image/jpeg';
    $extList['png'] = 'image/png';

    if (substr($folder, -1) != DIRECTORY_SEPARATOR) {
        $folder = $folder . DIRECTORY_SEPARATOR; // append directory seperator
    }
    $fileList = array();
    $handle = opendir($folder);
    while (false !== ( $file = readdir($handle) )) {
        $file_info = pathinfo($file);
        if (isset($extList[strtolower($file_info['extension'])])) {
            //ignore thumnail files
            if (stristr($file, "_small") === FALSE) {
                $fileList[] = $file;
            }
        }
    }
    closedir($handle);
    natsort($fileList);
    return array_values($fileList);
}
/*
  Function createthumb($name,$filename,$new_w,$new_h)
  creates a resized image
  variables:
  $name		Original filename
  $filename	Filename of the resized image
  $new_w		width of resized image
  $new_h		height of resized image
 */

function createthumb($name, $filename, $new_w, $new_h) {
    if (!extension_loaded('gd') && !function_exists('gd_info')) {
        return; // Appears GD library not loaded
    }

    $system = explode(".", $name);
    if (preg_match("/JPG|JPEG/i", $system[1])) {
        $src_img = imagecreatefromjpeg($name);
    }
    if (preg_match("/PNG/i", $system[1])) {
        $src_img = imagecreatefrompng($name);
    }

    if ($src_img == FALSE) {
        return; //problem creating thumbnail
    }
    $old_x = imageSX($src_img);
    $old_y = imageSY($src_img);
    if ($new_w / $old_x < $new_h / $old_y) { //wide photo scale height to be less than thumbnail size limit
        $thumb_w = $new_w;
        $thumb_h = $old_y * ($new_w / $old_x);
    } else if ($new_w / $old_x > $new_h / $old_y) { //tall photo scale width to be less than thumbnail size limit
        $thumb_w = $old_x * ($new_h / $old_y);
        $thumb_h = $new_h;
    } else if ($new_w / $old_x == $new_h / $old_y) { //scaled image fits thumbnail exactly 
        $thumb_w = $new_w;
        $thumb_h = $new_h;
    }

    $dst_img = ImageCreateTrueColor($new_w, $new_h); // create background thumbnail
    $white = imagecolorallocate($dst_img, 255, 255, 255); // fill with color
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    imagefill($dst_img, 0, 0, $black);
    if ($new_w > $thumb_w) { // fill sides because the width doesn't fit thumbnail size
        $insertx = ($new_w - $thumb_w) / 2;
        //Center edge of resampled image at: $insertx,0
        imagecopyresampled($dst_img, $src_img, $insertx, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
    } else if ($new_h > $thumb_h) { // fill top/bottom
        $inserty = ($new_h - $thumb_h) / 2;
        // center height of resampled image by placing it at 0,$inserty
        imagecopyresampled($dst_img, $src_img, 0, $inserty, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
    } else {
        //no fill needed place resampled image at 0,0
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
    }
    if (preg_match("/png/", $system[1])) {
        imagepng($dst_img, $filename);
    } else {
        imagejpeg($dst_img, $filename);
    }
    imagedestroy($dst_img);
    imagedestroy($src_img);
}

?>
