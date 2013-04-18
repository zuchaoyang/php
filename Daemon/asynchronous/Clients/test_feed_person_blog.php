<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/Daemon.inc.php');


class testPersonBlog {

    public static function createPersonBlog($uid) {
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
        
        import('@.Control.Sns.Blog.Ext.PersonBlog');
        $BlogObj = new PersonBlog($uid);
        $blog_id = $BlogObj->publishBlog($blog_datas, true);

        return $blog_id;
    }
    
    public static function createPersonBlogComment($uid, $blog_id) {
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();

        $comment_feed_id = $feed->user_create($uid,$blog_id,FEED_BLOG, FEED_ACTION_COMMENT);
        print_r("comment_feed_id = $comment_feed_id \n");
        
        return $comment_feed_id;
    }  

    
    public static function createPersonBlogFeed($uid, $blog_id) {

        /**
         * 创建日志动态
         */
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();
        $feed_id = $feed->user_create($uid,$blog_id,FEED_BLOG, FEED_ACTION_PUBLISH);
        print_r("blog_id = $blog_id \n");
        print_r("feed_id = $feed_id \n");
        
        return $feed_id;
    }    
    
    public static function debugPersonBlog($client_account, $last_id) {
        if(empty($client_account)) {
            return false;
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        //1.全部动态

        $datas = $FeedApi->getUserAllFeed($client_account, $last_id);

        return $datas;
    }
}


$client_account = 11070004;
$class_code = 146;

// 创建一个班级相册实体

for($i = 0 ; $i < 1; $i++) {
    $blog_id = testPersonBlog::createPersonBlog($client_account);
    //创建动态
    $feed_id = testPersonBlog::createPersonBlogFeed($client_account, $blog_id);
    //评论
//    $feed_comment_id = testPersonBlog::createPersonBlogComment($client_account, $blog_id);
}




$last_id = 0;
$page = 1;
$limit = 10;


$result =  testPersonBlog::debugPersonBlog($class_code, $last_id);


print_r(" page = 1   count = " . count($result) . " \n");
print_r($result);
while (true) {
    
    if (!empty($result) && count($result) == 10 && $page <=23 ) {
        $last_datas = end($result);
        $last_id = $last_datas['feed_id'];
        print_r(" last_id = $last_id \n");

        $result =  testPersonBlog::debugPersonBlog($class_code, $last_id);

        $page++;
        print_r(" page = $page   count = " . count($result) . " \n");
        print_r($result);
    } else {
        break;
        
    }
}

