<?php
return array(
    'action' => array(
        1    => '发布公告',
        2    => '发布班级作业',
        3    => '发布班级成绩',
        4    => '上传班级照片',
        5    => '发表班级日志',
        6    => '发表班级说说',
        7    => '发表评论（说说、相册、日志）',
        8    => '分享（说说、相册、日志）',
        9    => '发表个人说说',
        10   =>	'发表个人日志',
        11   =>	'上传个人照片',
        12   => '发布圈圈话题',
        13   => '查看学习资源',
        14   => '上传头像',
        15   => '完善性别',
        16   => '完善血型',
        17   => '填写生日',
        18   => '设置邮箱',
        19   => '每日登录',
        20   => '每日签到',
        21   => '激活账号',
        22   => '查看班级公告',
        23   => '查看班级作业',
        24   => '查看班级成绩',
        25   => '学生激活账号',
        26   => '父/母亲激活账号',
    ),
    'active_list'=>array(
        0 =>array(
            101 => 21, //激活账号
            102 => 19, //登录
            103 => 20, //签到
            301 => array( //个人资料
                14,15,16,17,18
            ),
            201 => 22, //班级公告
            202 => 23, //班级作业
            203 => 24, //班级成绩
            204 => 4,  //班级照片
            205 => 5,  //班级日志
            206 => 6,  //班级说说
            207 => 7,  //评论
            208 => 8,  //分享
            302 => 9,  
            303 => 10,
            304 => 11,
            305 => 7,
            306 => 8,
            401 => 12,
        ),
        1 =>array(
            101 => array(
                21,25,26
            ), //激活账号
            102 => 19, //登录
            103 => 20, //签到
            301 => array( //个人资料
                14,15,16,17,18
            ),
            201 => 1, //班级公告
            202 => 2, //班级作业
            203 => 3, //班级成绩
            204 => 4, //班级照片
            205 => 5, //班级日志
            206 => 6, //班级说说
            207 => 7, //评论
            208 => 8, //分享
            302 => 9,   
            303 => 10,
            304 => 11,
            305 => 7,
            306 => 8,
            401 => 12,
            402 => 13,
        ),
        2 =>array(
            101 => 21, //激活账号
            102 => 19, //登录
            103 => 20, //签到
            301 => array( //个人资料
                14,15,16,17,18
            ),
            201 => 22, //班级公告
            202 => 23, //班级作业
            203 => 24, //班级成绩
            302 => 9,   
            303 => 10,
            304 => 11,
            305 => 7,
            306 => 8,
            401 => 12,
            402 => 13,
        ),
        'header_teacher' => array(
            101 => array(
                25,
                26,
            ),
            307 => array(
                14,15,16,17,18
            ),
        ),
    ),
    'module' => array(
        101 => array(
        	'msg'      => '激活账号',
            21=>array(
                'value'    => 5,
            	'day_limit' => 5,
                'is_once' => true,
            ),
            25=>array(
                'value'    => 5,
            	'day_limit' => 5,
                'is_once' => true,
            ),
            26=>array(
                'value'    => 5,
            	'day_limit' => 5,
                'is_once' => true,
            ),
        ),
        102 => array(
        	'msg'      => '登录',
        	19=>array(
                'value'    => 5,
            	'day_limit' => 5,
        		'is_once' => false,
            ),

        ),
        103 => array(
        	'msg'      => '签到',
        	20=>array(
                'value'    => 5,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
        301 => array(
        	'msg'      => '个人资料',
        	14=>array(
                'value'    => 1,
            	'day_limit' => 1,
        	    'is_once' => true,
            ),
            15=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
            16=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
            17=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
            18=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
        ),
        201 => array(
        	'msg'      => '班级公告',
        	1=>array(
                'value'    => 5,
            	'day_limit' => 15,
        	    'is_once' => false,
            ),
            22=>array(
                'value'    => 5,
            	'day_limit'=> 15,  
                'is_once' => false,
            ), 
        ),
        202 => array(
        	'msg'      => '班级作业',
        	2=>array(
                'value'    => 3,
            	'day_limit' => 15,
        	    'is_once' => false,
            ),
            23=>array(
                'value'    => 5,
            	'day_limit'=> 15,
                'is_once' => false, 
            ), 
        ),
        203 => array(
        	'msg'      => '班级成绩',
        	3=>array(
                'value'    => 10,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
            24=>array(
                'value'    => 5,
            	'day_limit'=> 5,  
                'is_once' => false,
            ),  
        ),
        204 => array(
        	'msg'      => '班级相册',
        	4=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        205 => array(
        	'msg'      => '班级日志',
        	5=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        206 => array(
        	'msg'      => '班级说说',
        	6=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        207 => array(
        	'msg'      => '班级评论',
        	7=>array(
                'value'    => 1,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
        208 => array(
        	'msg'      => '班级分享',
        	8=>array(
                'value'    => 1,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
        302 => array(
        	'msg'      => '个人说说',
        	9=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        303 => array(
        	'msg'      => '个人日志',
        	10=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        304 => array(
        	'msg'      => '个人相册',
        	11=>array(
                'value'    => 2,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        305 => array(
        	'msg'      => '个人空间评论',
        	7=>array(
                'value'    => 1,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
        306 => array(
        	'msg'      => '个人空间分享',
        	8=>array(
                'value'    => 1,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
        307 => array(
            'msg'      => '班级成员完善个人资料',
        	14=>array(
                'value'    => 1,
            	'day_limit' => 1,
        	    'is_once' => true,
            ),
            15=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
            16=>array(
                'value'    => 1,
            	'day_limit'=> 1,  
                'is_once' => true,
            ), 
            17=>array(
                'value'    => 1,
            	'day_limit'=> 1, 
                'is_once' => true, 
            ), 
            18=>array(
                'value'    => 1,
            	'day_limit'=> 1, 
                'is_once' => true, 
            ), 
        ),
        401 => array(
            'msg'      => '圈圈话题',
        	12=>array(
                'value'    => 5,
            	'day_limit' => 10,
        	    'is_once' => false,
            ),
        ),
        402 => array(
            'msg'      => '学习资源',
        	13=>array(
                'value'    => 1,
            	'day_limit' => 5,
        	    'is_once' => false,
            ),
        ),
    ),
);