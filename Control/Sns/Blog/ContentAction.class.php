<?php
class ContentAction extends SnsController {
    public function __construct() {
        parent::__construct ();
        import("@.Common_wmw.Date");
    }
    
    public function index(){
        $class_code = $this->objInput->getInt("class_code");
        $class_code = $this->class_code = $this->checkoutClassCode($class_code);
        $class_blog_id = $this->objInput->getInt("blog_id");
        import("@.Control.Sns.Blog.Ext.ClassBlog");
        $ClassBlog = new ClassBlog($class_code);
        $blog_info = reset($ClassBlog->getBlogInfoById($class_blog_id));
        if(empty($blog_info)){
            $this->redirect("/Sns/Bolg/List/index/class_code/$class_code");
        }
        
        $add_account = $blog_info['add_account'];
        $mUser = ClsFactory::Create("Model.mUser");
        $user_info = reset($mUser->getUserBaseByUid($add_account));
        $mBlog = ClsFactory::Create();
        $blog_info["add_time"] = Date::timestamp($blog_info["add_time"]);
        $this->assign("add_blog_client_name", $user_info['client_name']);
        $this->assign("head_pic_url_", $user_info["client_headimg_url"]);
        $this->assign("blog_info", $blog_info);
        $this->assign("class_code", $class_code);
        $this->display("class_show");
    }
    
    public function update_blog_views(){
        $blog_id = $this->objInput->getInt("blog_id");
        $dataarr = array(
            "views" => "%views+1%"
        );

        $mBlog = ClsFactory::Create("Model.Blog.mBlog");
        $result = $mBlog->modifyBlog($dataarr,$blog_id);
        !empty($result) ? $this->ajaxReturn(null,"添加阅读数量成功！", 1, "json") : $this->ajaxReturn(null,"添加阅读数量失败！", -1, "json");
    }
    
    public function next_blog(){
        $class_code = $this->objInput->getInt("class_code");
        $class_code = $this->class_code = $this->checkoutClassCode($class_code);
        $class_blog_id = $this->objInput->getInt("blog_id");
        $mBlogClassRelation = ClsFactory::Create("Model.Blog.mBlogClassRelation");
        $wherearr = array(
            'blog_id>'.$class_blog_id,
            'class_code='.$class_code,
            'is_published=1'
        );
        
        $BlogClassRelation = $mBlogClassRelation->getBlogClassRelationInfo($wherearr, "blog_id asc", 0, 1);
        $BlogClassRelation = reset($BlogClassRelation);
        $next_blog_id = !empty($BlogClassRelation) ? $BlogClassRelation['blog_id'] : $class_blog_id;
        
        $this->redirect("/Sns/Blog/Content/index/class_code/$class_code/blog_id/$next_blog_id");
    }
    
    public function up_blog() {
        $class_code = $this->objInput->getInt("class_code");
        $class_code = $this->class_code = $this->checkoutClassCode($class_code);
        $class_blog_id = $this->objInput->getInt("blog_id");
        $mBlogClassRelation = ClsFactory::Create("Model.Blog.mBlogClassRelation");
        $wherearr = array(
            'blog_id<'.$class_blog_id,
            'class_code='.$class_code,
            'is_published=1'
        );
        
        $BlogClassRelation = $mBlogClassRelation->getBlogClassRelationInfo($wherearr, "blog_id desc", 0, 1);
        $BlogClassRelation = reset($BlogClassRelation);
        $up_blog_id = !empty($BlogClassRelation) ? $BlogClassRelation['blog_id'] : $class_blog_id;
        
        $this->redirect("/Sns/Blog/Content/index/class_code/$class_code/blog_id/$up_blog_id");
    }
    
    
    
    public function getcommentjson(){
        $comment_list = $this->getcomment();
        !empty($comment_list) ? $this->ajaxReturn($comment_list,"显示评论成功！", 1, "json") : $this->ajaxReturn(null,"显示评论失败！", -1, "json");
    }
    
    public function addcommentjson() {
        $blog_id = $this->objInput->postInt("blog_id");
        $uid = $this->user['client_account'];
        $content = $this->objInput->postStr("content");
        $up_id = $this->objInput->postInt("up_id");
        
        $level = !empty($up_id) ? 2 : 1;
        
        $dataarr = array(
            'blog_id' => $blog_id,
            'content' => $content,
            'up_id' => $up_id,
            'client_account' => $uid,
            'add_time' => time(),
            'level' => $level
        );
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $comment_id = $mBlogComments->addComment($dataarr, true);
        if(!empty($up_id) && !empty($comment_id)) {
            $dataarr = array(
                'add_time' => time()
            );
            
            $mBlogComments->modifyByCommentId($dataarr, $up_id);
        }
        
        if(!empty($comment_id)) {
            $mBlog = ClsFactory::Create("Model.Blog.mBlog");
            $Blog = $mBlog->getBlogById($blog_id);
            $add_blog_uid = $Blog[$blog_id]['add_account'];
            $dataarr = array(
                'comments' => '%comments+1%'
            );
            $mBlog->modifyBlog($dataarr, $blog_id);
            $comments_content = $mBlogComments->getById($comment_id);
            $mUser = ClsFactory::Create("Model.mUser");
            $user_info = $mUser->getUserBaseByUid($uid);
            if($add_blog_uid == $this->user['client_account']){
                $comments_content[$comment_id]['can_del'] = true;
            }else{
                $comments_content[$comment_id]['can_del'] = false;
            }
            $comments_content[$comment_id]['client_name'] = $user_info[$uid]['client_name'];
            $comments_content[$comment_id]['header_pic_url'] = $user_info[$uid]['client_headimg_url'];
            $comments_content[$comment_id]['add_time'] = Date::timestamp($comments_content[$comment_id]['add_time']);
        }
        
        
        
        !empty($comments_content) ? $this->ajaxReturn($comments_content,"添加评论成功！", 1, "json") : $this->ajaxReturn(null,"添加评论失败！", -1, "json");
    }
    
    public function delcommentjson() {
        $comment_id = $this->objInput->postInt("comment_id");
        
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
        $first_level = $mBlogComments->getById($comment_id);
        
        $first_level = $first_level[$comment_id];
        $blog_id = $first_level['blog_id'];
        $mBlog = ClsFactory::Create("Model.Blog.mBlog");
        $result = true;
        
        $up_id = $first_level['up_id'];
        
        //如果当前是一级评论的时候同时删除二级评论
        if(empty($up_id)){
            //删除二级评论
            $second_leve = $mBlogComments->getSecondLevel($comment_id);
            
            if(!empty($second_leve)) {
                foreach($second_leve as $id => $val){
                    $result = $mBlogComments->delByCommentId($id);
                    if(!empty($result)) {
                        $blog_id = $val['blog_id'];
                        $dataarr = array(
                            'comments' => '%comments-1%'
                        );
                        $mBlog->modifyBlog($dataarr, $blog_id);
                    }else{
                        break;
                    }
                }
            }
        }
        
        //删除一级评论
        if(!empty($result)) { 
            $result = $mBlogComments->delByCommentId($comment_id);
            if(!empty($result)) {
                $dataarr = array(
                    'comments' => '%comments-1%'
                );
                $mBlog->modifyBlog($dataarr, $blog_id);
            }
        }
        
        !empty($result) ? $this->ajaxReturn($up_id,"删除评论成功！", 1, "json") : $this->ajaxReturn(null,"删除评论失败！", -1, "json");
    }
    
    
    
    public function getcomment(){
        $blog_id = $this->objInput->getInt("blog_id");
        $page = $this->objInput->getInt("page");
        $current_uid = $this->user['client_account'];
        $page = max(1, $page);
        $limit = 10;
        $offset = ($page -1) * $limit;
        $mBlog = Clsfactory::Create("Model.Blog.mBlog");
        $Blog = $mBlog->getBlogById($blog_id);
        $Blog = reset($Blog);
        $add_blog_uid = $Blog['add_account'];
        $mBlogComments = ClsFactory::Create("Model.Blog.mBlogComments");
       
        $Comments = $mBlogComments->getFirstLevel($blog_id, 'add_time desc', $offset, $limit +1);
        
        $is_end_page = false;
        if(count($Comments) <= $limit) {
            $is_end_page = true;
        }else{
            array_pop($Comments);
        }
        $uids = array();
        if(!empty($Comments)) {
            foreach($Comments as $comment_id => $comment_info) {
                $uids[$comment_info['client_account']] = $comment_info['client_account'];
            }
        }
        $mUser = ClsFactory::Create("Model.mUser");
        $user_info = $mUser->getUserBaseByUid($uids);
        
        $comment_ids = !empty($Comments) ? array_keys($Comments): array();
        $new_comments = array();
        
        
        if(!empty($comment_ids)) {
            foreach($Comments as $comment_id => $Comment_info) {
                $second_comment = $mBlogComments->getSecondLevel($comment_id, 'add_time asc');
                if(!empty($second_comment)) {
                    foreach($second_comment as $key => $val){
                        $add_accounts[$val['client_account']] = $val['client_account'];
                    }
                    
                    $user_info_second = $mUser->getUserBaseByUid($add_accounts);

                    foreach($second_comment as $key => $val){
                        $val['client_name'] = $user_info_second[$val['client_account']]['client_name'];
                        $val['header_pic_url'] = $user_info_second[$val['client_account']]['client_headimg_url'];
                        $val['add_time'] = Date::timestamp($val['add_time']);
                        if($add_blog_uid == $current_uid){
                            $val['can_del'] = true;
                        }else{
                            $val['can_del'] = false;
                        }
                        $second_comment[$key] = $val;
                    }
                }
                
                if($add_blog_uid == $current_uid){
                    $Comments[$comment_id]['can_del'] = true;
                }else{
                    $Comments[$comment_id]['can_del'] = false;
                }
                $Comments[$comment_id]['client_name'] = $user_info[$Comment_info['client_account']]['client_name'];
                $Comments[$comment_id]['header_pic_url'] = $user_info[$Comment_info['client_account']]['client_headimg_url'];
                $Comments[$comment_id]['add_time'] = Date::timestamp($Comments[$comment_id]['add_time']);
                $Comments[$comment_id]['child_items'] = $second_comment;
                $new_comments[$comment_id]= $Comments[$comment_id];
                
            }
        }

        return !empty($new_comments) ? $new_comments : false;
    }
}