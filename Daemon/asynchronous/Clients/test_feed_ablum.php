<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');


class testClassAblum {
    public static function createClassAblum($uid, $class_code) {
        $data=array(
			'album_id'      => 23,  
			'name'          => "", // 类中返回的$up_r['filename']有误，同$up_r['getfilename']值相同
			'file_big'      => "qqqq",  //该字段可删除
            'file_middle'   => "wwwww",
			'file_small'    => "233333",
			'description'   => "",
            'comments'      => 0,
			'upd_time'      => time(),
			'upd_account'   => $uid,
		);
		
		import("@.Control/Api/AlbumImpl/ByClass");
        $ByClass = new ByClass();
        $photo_id = $ByClass->addClassPhoto($data,true);
            
        /**
         * 创建日志动态
         */
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();
        $feed_id = $feed->class_create($class_code,$uid,$photo_id,FEED_ALBUM, FEED_ACTION_PUBLISH);
        $comment_feed_id = $feed->class_create($class_code,$uid,$photo_id,FEED_ALBUM, FEED_ACTION_COMMENT);
        print_r("blog_id = $photo_id \n");
        print_r("feed_id = $feed_id \n");
        print_r("comment_feed_id = $comment_feed_id \n");
        
        return $feed_id;
    }
    
    public static function debugClassAblum($class_code, $last_id) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        //1.全部动态
        $datas = $FeedApi->getClassAlbumFeed($class_code, $last_id);
        return $datas;
    }
}


$client_account = 56067742;
$class_code = 23527;

// 创建一个班级相册实体
for($i = 0 ; $i < 110; $i++) {
    $feed_id = testClassAblum::createClassAblum($client_account, $class_code);
}


$last_id = 0;
$page = 1;
$limit = 10;

$result =  testClassAblum::debugClassAblum($class_code, $last_id);
print_r(" page = 1   count = " . count($result) . " \n");
print_r($result);
while (true) {
    
    if (!empty($result) && count($result) == 10 && $page <=23 ) {
        $last_datas = end($result);
        $last_id = $last_datas['feed_id'];
        print_r(" last_id = $last_id \n");
        $result =  testClassAblum::debugClassAblum($class_code, $last_id);
        $page++;
        print_r(" page = $page   count = " . count($result) . " \n");
        print_r($result);
    } else {
        break;
        
    }
}
    