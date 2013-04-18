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

        return $blog_id;
    }
    
    public static function createClassBlogComment($uid, $class_code, $blog_id) {
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();

        $comment_feed_id = $feed->class_create($class_code,$uid,$blog_id,FEED_BLOG, FEED_ACTION_COMMENT);
        print_r("comment_feed_id = $comment_feed_id \n");
        
        return $comment_feed_id;
    }  

    
    public static function createClassBlogFeed($uid, $class_code, $blog_id) {

        /**
         * 创建日志动态
         */
        
        import("@.Control.Api.FeedApi");
        $feed = new FeedApi();
        $feed_id = $feed->class_create($class_code,$uid,$blog_id,FEED_BLOG, FEED_ACTION_PUBLISH);
        print_r("blog_id = $blog_id \n");
        print_r("feed_id = $feed_id \n");
        
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
    
    public static function debugClassBlogDispatch($uid, $class_code, $feed_id = 0, $feed_type = 0, $action = 1) {
        
        if(empty($class_code)) {
            return false;
        }        

        $params = array('class_code'    => $class_code,
                        'uid'		    =>  $uid,
                        'feed_id'   => $feed_id,
                        'feed_type'	=> $feed_type,
                        'action'	=> $action
                        );  
        $params = serialize($params);
        Gearman::send('feed_class_dispatch', $params, PRIORITY_NORMAL, false);
    }
}


$client_account = 96159664;
$class_code = 146;

// 创建一个班级相册实体

for($i = 0 ; $i < 1; $i++) {
    $blog_id = testClassBlog::createClassBlog($client_account, $class_code);
    //创建动态
    $feed_id = testClassBlog::createClassBlogFeed($client_account, $class_code, $blog_id);
    //评论
//    $feed_comment_id = testClassBlog::createClassBlogComment($client_account, $class_code, $blog_id);
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


//转发测试

testClassBlog::debugClassBlogDispatch($client_account, $class_code, $feed_id, FEED_BLOG, FEED_ACTION_PUBLISH);


