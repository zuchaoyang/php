<?php
return array(
    //允许上传的文件类型限制
    'allow_upload_types' => array(
        //课件
        1=>'ppt',
        //文本
        2=>'txt,doc,wps,xls,pdf,chm',
        //网页
        3=>'html,htm',
        //视频
        4=>'mov,vob,dat,mp4,mkv,amv,avi,asf,wmv,mpeg,mpg,3gp,rmvb,rm,flv,swf',
        //音频
        5=>'mpa,voc,mka,mic,wav,mp3',
        //swf
        6=>'swf',
        //其他
        7=>'',
        //图片
        8=>'ppm,pgx,tga,tiff,tif,bmp,png,jpe,jpeg,gif,jpg',
        //itf
        9=>'itf',
        //压缩包
        10=>'zip,rar',
    ),
    //显示类型配置
    'show_type_settings' => array(
        //无模版HTML
        1=>'html,htm',
        //横版HTML
        2=>'html,htm',
        //竖版HTML
        3=>'html,htm',
        //下载
        4=>'mka,voc,mpa,rm,rmvb,3gp,mpg,mpeg,wmv,asf,avi,amv,mkv,mp4,dat,vob,mov,txt,doc,wps,xls,pdf,chm,ppt,zip,rar,tiff,tga,pgx,ppm',
        //视频播放
        5=>'flv',
        //音频播放
        6=>'mp3,wav,mic',
        //动画播放
        7=>'swf',
        //加载图片
        8=>'jpg,gif,jpeg,jpe,png,bmp,tif',
    ),
    //文件类型的值和文件类型的对应
    'file_type_settings' => array(
        //课件
        1=>'ppt',
        //文本
        2=>'txt,doc,wps,xls,pdf,chm',
        //网页
        3=>'html,htm',
        //视频
        4=>'mov,vob,dat,mp4,mkv,amv,avi,asf,wmv,mpeg,mpg,3gp,rmvb,rm,flv',
        //音频
        5=>'mpa,voc,mka,mic,wav,mp3',
        //swf
        6=>'swf',
        //其他
        7=>'',
        //图片
        8=>'ppm,pgx,tga,tiff,tif,bmp,png,jpe,jpeg,gif,jpg',
        //itf
        9=>'itf',
        //压缩包
        10=>'zip,rar',
        
    ),
    
    'resource_navs' => array(
        'product_id',
        'grade_id',
        'subject_id',
        'version_id',
        'term_id',
        'chapter_id',
        'section_id',
        'colnum_type'
    ),
     //同步资源的相关配置
    'resource_settings' => array(
        1 => array(
             'fields' => array(
                0 => 	'grade_id',
                1 => 	'subject_id',
                2 => 	'version_id',
                3 => 	'title',
                4 => 	'description',
                5 => 	'file_type',
                6 => 	'file_path',
                7 => 	'file_name',
                8 => 	'chapter_name',
                9 => 	'section_name',
                10 =>   'column_id',
                11 => 	'thumb_img',
                12 =>   'term_id',
                13 =>   'show_type',
                14 =>   'learn_type',
                15 => 	'display_order',
              ),
              'checkfields' => array(
                  'grade_id', 'subject_id', 'version_id', 'chapter_name', 'section_name', 'title'
              ),
              'mixed' => array(
                  'thumb_img',
                  'learn_type',
              ),
        ),
        
        //精品资源有章无节，缩略图,多种学习方式，+学期检索
        2 => array(
             'fields' => array(
                0 => 	'grade_id',
                1 => 	'subject_id',
                2 => 	'version_id',
                3 => 	'title',
                4 => 	'description',
                5 => 	'file_type',
                6 => 	'file_path',
                7 => 	'file_name',
                8 => 	'chapter_name',
                9 => 	'section_name',
                10 =>   'column_id',
                11 => 	'thumb_img',
                12 =>   'term_id',
                13 =>   'show_type',
                14 =>   'learn_type',
                15 => 	'display_order',
              ),
              'checkfields' => array(
                  'grade_id', 'subject_id', 'version_id', 'term_id', 'title'
              ),
              'mixed' => array(
                  'learn_type',
              ),
        ),
        
        //精品网校无章节，缩略图，不通过版本检索
        3 => array(
             'fields' => array(
                0 => 	'grade_id',
                1 => 	'subject_id',
                2 => 	'version_id',
                3 => 	'title',
                4 => 	'description',
                5 => 	'file_type',
                6 => 	'file_path',
                7 => 	'file_name',
                8 => 	'chapter_name',
                9 => 	'section_name',
                10 =>   'column_id',
                11 => 	'thumb_img',
                12 =>   'term_id',
                13 =>   'show_type',
                14 =>   'learn_type',
                15 => 	'display_order',
              ),
              'checkfields' => array(
                  'grade_id', 'subject_id', 'version_id', 'title'
              ),
              'mixed' => array(//同步及精品无学习方式
                  'learn_type',
              ),
        ),
   ),
);