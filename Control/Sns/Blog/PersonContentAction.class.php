<?php
/**
 * 个人日志展示页面
 * @author Administrator
 *
 */
class PersonContentAction extends SnsController {
    public function __construct() {
        parent::__construct ();
        import("@.Common_wmw.Date");
    }
    
    public function index(){
        $blog_id = $this->objInput->getInt("blog_id");
        $client_account = $this->objInput->getStr("client_account");
        $checkout = $this->checkoutAccount($client_account);
        if (empty($checkout)) {
            $this->showError('你访问的用户不存在！', "/Sns/Blog/PersonList/index/client_account/" . $this->user['client_account']);
        }

        import("@.Control.Sns.Blog.Ext.PersonBlog");
        $PersonBlog = new PersonBlog($client_account);
        $blog_info = reset($PersonBlog->getBlogInfoById($blog_id));

        if(empty($blog_info)){
            $this->showError('你查看日志不存在或者已被删除！', "/Sns/Blog/PersonList/index/client_account/$client_account");
        }
        
        //验证用户是否有权限查看日志详情
        $can_view = $this->can_view($blog_info);
        if(empty($can_view)) {
            $this->showError('你暂时没有权限查看日志详情！', "/Sns/Blog/PersonList/index/client_account/$client_account");
        }

        $add_account = $blog_info['add_account'];
        $blog_info["add_time"] = Date::timestamp($blog_info["add_time"]);
        
        $mUser = ClsFactory::Create("Model.mUser");
        $user_info = reset($mUser->getUserBaseByUid($add_account));
        
        $this->assign("client_account", $client_account);
        $this->assign("can_edit", $this->canEditBlog($blog_info));
        $this->assign("can_del", $this->canDelBlog($blog_info));
        $this->assign("client_account", $client_account);
        $this->assign("add_blog_client_name", $user_info['client_name']);
        $this->assign("head_pic_url_", $user_info["client_headimg_url"]);
        $this->assign("blog_info", $blog_info);
        
        $this->display("person_show");
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
    
    public function up_blog(){
        $client_account = $this->objInput->getStr("client_account");
        $blog_id = $this->objInput->getInt("blog_id");
        $mBlogPersonRelation = ClsFactory::Create("Model.Blog.mBlogPersonRelation");
        $wherearr = array(
            'b.blog_id>'.$blog_id,
            'is_published=1'
        );
            
        //追加权限条件 防止用户看到没有权限看到的日志
        $grant = $this->getSelectGrant($client_account);
        if (!empty($grant)) {
        	$wherearr[] = $grant; 
        }
        
        $blog_list = $mBlogPersonRelation->getPersonBlogByUid($client_account, $wherearr, 'blog_id asc', 0, 1);
        $blog_list = $blog_list[$client_account];
        $BlogPersonRelation = reset($blog_list);
        $next_blog_id = !empty($BlogPersonRelation) ? $BlogPersonRelation['blog_id'] : $blog_id;
        //dump($blog_list);exit;
        $this->redirect("/Sns/Blog/PersonContent/index/client_account/$client_account/blog_id/$next_blog_id");
    }
    
    public function next_blog() {
        $client_account = $this->objInput->getStr("client_account");
        $blog_id = $this->objInput->getInt("blog_id");
        $mBlogPersonRelation = ClsFactory::Create("Model.Blog.mBlogPersonRelation");
        $wherearr = array(
            'b.blog_id<'.$blog_id,
            'is_published=1'
        );
        
        //追加权限条件 防止用户看到没有权限看到的日志
        $grant = $this->getSelectGrant($client_account);
        if (!empty($grant)) {
        	$wherearr[] = $grant; 
        }
        
        $blog_list = $mBlogPersonRelation->getPersonBlogByUid($client_account, $wherearr, 'blog_id desc', 0, 1);
        $blog_list = $blog_list[$client_account];
        $BlogPersonRelation = reset($blog_list);
        $up_blog_id = !empty($BlogPersonRelation) ? $BlogPersonRelation['blog_id'] : $blog_id;
        //dump($BlogPersonRelation);exit;
        $this->redirect("/Sns/Blog/PersonContent/index/client_account/$client_account/blog_id/$up_blog_id");
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
            
            import("@.Control.Api.FeedApi");
            $feed_api = new FeedApi();
            $feed_api->user_create($this->user['client_account'], $blog_id, FEED_ACTION_COMMENT);
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
    
    
    
    /**********************************************************************************************
     * 辅助函数
     * *******************************************************************************************/

    /**
     * 验证当前用户是否有权限查看日志详情
     * 注 ： 根据日志权限判断当前用户时候具有查看日志的权限
     * 1 公开      0   所有人都可以查看
     * 2 仅好友 1   只有是好友才能查看
     * 3 仅主人 2   只有添加人自己才能查看
     * 
     * @param $blog_info
     * @return boolean ture 可以 false 不可以
     */
    private function can_view($blog_info) {
        if(empty($blog_info)) {
            return false;
        }
        
        $client_account = $this->user['client_account'];
        // 公开或者当前用时添加人
        if (($blog_info['grant'] == 0) || $blog_info['add_account'] == $client_account) {
            return true;
        }
        
        // 好友
        if ($blog_info['grant'] == 1) {
            // 判断是否是好友
            $mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
            $is_friend = $mAccountrelation->getAccountTrelationByUidAndFriendAccount($blog_info['add_account'], $client_account);
            
            return $is_friend ? true : false;
        }
        
        return false;
    }
    
    
    /**
     * 用户是否有权限修改日志
     */
    private function canEditBlog($blog_info) {
        if(empty($blog_info)) {
            return false;
        }
        
        // 验证权限 只能修改自己添加的权限
        if(empty($blog_info) || $blog_info['add_account'] != $this->user['client_account']) {
            return false;  
        }
        
        return true;
    }
    
    /**
     * 判断用时候有删除班级日志的权限
     * 只有添加人可以删除自己的日志  
     * todo 管理员 有没有可能删除用户日志 或者冻结用户账号 当用户发表不和谐信息时
     */
    private function canDelBlog($blog_info) {
        if (empty($blog_info)) {
            return false;
        }
        
        //自己发布的日志 有权限删除
        if ($blog_info['add_account'] == $this->user['client_account']) {
            return true;
        }

        return false;
    }
    
    
    /**
     * 获取当前账号对 被访问的用户的可见程度
     * 注： grant 是mysql 关键字 要在加上引号 `grant`
     * @param $client_account 被访问的账号
     * return 返回权限数据sql string 
     */
    private function getSelectGrant($client_account) {
    	if(empty($client_account)) {
    		return '`grant`=-1';
    	}
    	
    	//自己本人不受限制
    	if($client_account == $this->user['client_account']) {
    		return '';
    	}
    	
    	//好友
    	$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
        $is_friend = $mAccountrelation->getAccountTrelationByUidAndFriendAccount($client_account, $this->user['client_account']);
        if (!empty($is_friend)) {
        	return "`grant`<=1";
        }   
    	
    	//陌生人
    	return "`grant`=0";
    	
    }
    
}