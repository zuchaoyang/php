<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');


class testClassBlog {

    public static function createClassBlog($uid, $class_code) {
         $blog_datas = array (
            'title'        => "不他妈的发工资",
            'content'      => "不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资不他妈的发工资",
            'type_id'      => 13,
            'views'        => 0,
            'is_published' => 1,  //默认发布 1 发布 0 草稿
            'add_account'  => $uid,
            'add_time'     => time(),
            'contentbg'    => "",
            'summary'      => "不他妈的发工资不他妈的发工资不他妈的发工资",
            'comments'     => 0,
            'grant'        => 0
        );
        
        import('@.Control.Sns.Blog.Ext.ClassBlog');
        $BlogObj = new ClassBlog($class_code);
        $blog_id = $BlogObj->publishBlog($blog_datas, true);
        /**
         * 创建日志动态
         */
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();
        $feed_id = $feed->class_create($class_code,$uid,$blog_id,FEED_BLOG, FEED_ACTION_PUBLISH);
        $comment_feed_id = $feed->class_create($class_code,$uid,$blog_id,FEED_BLOG, FEED_ACTION_COMMENT);
        print_r("blog_id = $blog_id \n");
        print_r("feed_id = $feed_id \n");
        print_r("comment_feed_id = $comment_feed_id \n");
        
        return $feed_id;
    }
    
    public static function debugClassBlog($class_code, $last_id) {
        if(empty($class_code)) {
            return false;
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        //1.全部动态

        $datas = $FeedApi->getClassAllFeed($class_code, $last_id);

        return $datas;
    }
}


$client_account = 56067742;
$class_code = 23527;

// 创建一个班级相册实体

for($i = 0 ; $i < 110; $i++) {
    $feed_id = testClassBlog::createClassBlog($client_account, $class_code);
}


$last_id = 0;
$page = 1;
$limit = 10;


$result =  testClassBlog::debugClassBlog($class_code, $last_id);


print_r(" page = 1   count = " . count($result) . " \n");
print_r($result);
while (true) {
    
    if (!empty($result) && count($result) == 10 && $page <=23 ) {
        $last_datas = end($result);
        $last_id = $last_datas['feed_id'];
        print_r(" last_id = $last_id \n");

        $result =  testClassBlog::debugClassBlog($class_code, $last_id);

        $page++;
        print_r(" page = $page   count = " . count($result) . " \n");
        print_r($result);
    } else {
        break;
        
    }
}


