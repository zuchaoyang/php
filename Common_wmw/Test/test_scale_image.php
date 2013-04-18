<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon/Daemon.inc.php');

$path = dirname(__FILE__) . '/tmp';
$filename = $path . '/1.jpg';


import('@.Common_wmw.WmwScaleImage');
$scaleImage = new WmwScaleImage();
$scaleImage->scaleSmall($filename);
$scaleImage->scaleMiddle($filename);

