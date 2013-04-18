<?php
$preloadSize = 15; // number of images per page to load
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Test Page</title>
        <link rel="stylesheet" type="text/css" href="http://localhost/css/style.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="http://localhost/css/navigation.css" media="screen" />
        <script src="http://localhost/js/hover.js" type="text/javascript"></script>
        <script src="js/jquery-1.8.2.min.js" type="text/javascript"></script>
        <script src="js/galleria-1.2.8.min.js" type="text/javascript"></script>
        <link type="text/css" rel="stylesheet" href="js/themes/classic/galleria.classic.css">
        <script>
            var showSize=0;//count of number of images total
            $.ajax({
                url : 'galleriaAjax.php?count=1',
                success : function (data) {showSize=data;}
            });

            Galleria.loadTheme('js/themes/classic/galleria.classic.min.js');
            Galleria.run('#galleria', {
                autoplay: 5000, 
                transition: 'fade',
                fullscreenDoubleTap: true
            });
            Galleria.ready(function() {
                $('.galleria-total').text(showSize); //update total # of slides
                var gallery = this;
                var toggler = $('<img>',{
                    'src': 'js/fullscreen.gif',
                    'class': 'toggler',
                    'click': function(e) {
                        e.preventDefault();
                        gallery.toggleFullscreen();
                    }
                }).appendTo('body');


                this.$('thumb-nav-right').click(function(e) {
                    if ($('div').hasClass('galleria-thumb-nav-right disabled')){
                        if(showSize==Galleria.get(0).getDataLength()) return; //show is done loading
                        //load more images
                        //get total loaded and add next preloadSize worth
                        $.getJSON('galleriaAjax.php?next='+(Galleria.get(0).getDataLength()-1)+'&size='+<?php echo $preloadSize ?>, function(data) {
                            var gallery = Galleria.get(0);
                            gallery.push(data);
                        });
                    }
                    //update total # of slides 
                    // the .push is asynchronous, so total count will show total loaded
                    // for a short time.
                    $('.galleria-total').text(showSize); 
                });

                this.bind("loadstart", function(e) {
                    $('.galleria-total').text(showSize); //update total # of slides

                    //check e.index and see if more needs loaded
                    //compare dataLength with (preloadSize+e.index)        
                    if(e.index%<?php echo $preloadSize ?>==0 && e.index!=0 && (Galleria.get(0).getDataLength()< <?php echo $preloadSize ?>+e.index)){
                        if(showSize==Galleria.get(0).getDataLength()) return; //show is done loading
                        $.getJSON('galleriaAjax.php?next='+e.index+'&size='+<?php echo $preloadSize ?>, function(data) {
                            var gallery = Galleria.get(0);
                            gallery.push(data);
                        });
                    }
        
                });
            });
        </script>
        <style>
            #galleria{ width: 700px; height: 500px; background: #fff }
        </style>
    </head>
    <body>
            <?php
            $folder = 'images';
            $extList = array();
            $extList['gif'] = 'image/gif';
            $extList['jpg'] = 'image/jpeg';
            $extList['jpeg'] = 'image/jpeg';
            $extList['png'] = 'image/png';

            if (substr($folder, -1) != DIRECTORY_SEPARATOR) {
                $folder = $folder . DIRECTORY_SEPARATOR; // append directory seperator
            }
            $fileList = array();
            $file_index = 0;
            $handle = opendir($folder);
            while (false !== ( $file = readdir($handle) )) {
                $file_info = pathinfo($file);
                if (isset($extList[strtolower($file_info['extension'])])) {
                    //ignore thumnail files
                    if (stristr($file, "_small") === FALSE) {
                        $fileList[$file_index] = $file;
                        $file_index++;
                    }
                }
            }
            closedir($handle);
            natsort($fileList);
            echo '<div id="galleria">';
            $count = 0;
            foreach ($fileList as $key => $filename) {
                //note filename may not contain extra periods
                //thumbnail files are saved as filename_small.extension
                $info = substr($filename, 0, strpos($filename, ".", 0));
                $thumb = $folder . $info . "_small" . substr($filename, stripos($filename, "."), strlen($filename));
                if (!file_exists($thumb)) {
                    createthumb($folder . $filename, $thumb, 150, 100);
                }
                echo '<a href="' . $folder . $filename .
                '"><img src="' . str_replace(' ', '%20', $thumb) .
                '"/></a>' . "\n";
                $count++;
                if ($count > $preloadSize)
                    break;
            }
            echo '</div>';
            ?>
    </body>
</html>
<?php
/*
  Function createthumb($name,$filename,$new_w,$new_h)
  Required PHP GD library
  creates a resized image (filenames must not contain extra "." characters)
  variables:
  $name		Original filename
  $filename	Filename of the resized image
  $new_w	width of resized image
  $new_h	height of resized image
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
 //       echo "failed to create thumnail";
        return;
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