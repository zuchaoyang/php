<?php
interface ImageInterface {
     /**
     * 图片缩放函数
     * @param $src_img 源图片文件完整路径，如:/home/src.jpg
     * @param $dst_files,数据格式
     * array(
     * 		array(
     * 			'path' => '目标图片完整路径,如:/home/test.jpg'
     * 			'scale' => '图片缩放比列,大于0的正整数'
     * 		),
     * )
     */
    public function scale($src_file, $dst_files = array());
}