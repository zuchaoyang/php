<?php
/**
 * 班级日志发布   控制类
 * @author zlei 2013-1-5
 */
class PublishAction extends SnsController {
    private $summary_len = 200;
    public function __construct() {
        parent::__construct ();
    }
    
    public function _initBlogObj($class_code) {
        static $BlogObj = false;
        
        if (empty($BlogObj)) {
            import('@.Control.Sns.Blog.Ext.ClassBlog');
            $BlogObj = new ClassBlog($class_code);
        }
        
        return $BlogObj;
    }
    
    /*发布首页 同时兼容修改页面 （包括草稿修改）*/
    public function index() {
        $class_code = $this->objInput->getStr('class_code');
        $edit_id    = $this->objInput->getStr('edit_id');  
        $draft_id   = $this->objInput->getStr('draft_id'); 
        //验证班级信息
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在!', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }
        
        //验证是否有发布权限（只有家长没有发布权限）
        $can_publish = $this->canPublish($class_code);
        if (empty($can_publish)) {
            $this->showError('您暂时没有发布班级日志的权限', '/Sns/Blog/List/index/class_code/'.$class_code);
        } 
        
        $blogByClass = $this->_initBlogObj($class_code);
        //获取日志详情并验证权限
        if (!empty($edit_id) || !empty($draft_id)) {
            //获取班级日志详细信息(包含 权限,日志内容)
            $blog_id = !empty($edit_id) ? $edit_id : $draft_id ;
            $blog_info = $blogByClass->getBlogInfoById($blog_id);
            $blog_info = $blog_info[$blog_id];

            //兼容草稿修改处理
            if (!empty($draft_id)) {
                $blog_info['draft_id'] = $blog_info['blog_id'];
                unset($blog_info['blog_id']);
            }
            $can_edit = $this->canEditBlog($blog_info);
            if(empty($can_edit)) {
                $this->showSuccess('日志不存在或没有权限修改', '/Sns/Blog/List/index/class_code/'.$class_code);
            } 
            
            //是否是修改
            $this->assign('blog_info', $blog_info);
        }
       
        import("@.Common_wmw.Constancearr");
        $blog_grant = Constancearr::get_blog_class_grant();
        $blog_type  = $blogByClass->getBlogType();

        $this->assign('class_code', $class_code);
        $this->assign('blog_type', $blog_type);
        $this->assign('blog_grant', $blog_grant);

        $this->display("publish_edit");
    }
    
    /**
     * 兼容 发布和添加草稿
     */
    public function publishBlogAjax() {
        
        //1 表示发表个人日志，2表示发表班级日志
        $title = $this->objInput->postStr('title');
        $content = $this->objInput->postStr('content');
        $type_id = $this->objInput->postInt('type_id');
        $grant = $this->objInput->postInt('grant');
        $is_published = $this->objInput->postInt('is_published');
        $contentbg = $this->objInput->postStr('contentbg');
        
        $class_code   = $this->objInput->postInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '班级不存在!', -1, 'json');
        }
        
        //验证是否有发布权限（只有家长没有发布权限）
        $can_publish = $this->canPublish($class_code);
        if (empty($can_publish)) {
            $this->showError('您暂时没有发布班级日志的权限', '/Sns/Blog/List/index/class_code/'.$class_code);
        } 
        
        //验证数据的完整性
        if (empty($title) || empty($content) || $type_id < 0) {
             $this->ajaxReturn(null, '数据填写不完整!', -1, 'json');
        }
        
        $BlogObj = $this->_initBlogObj($class_code);
        // 处理日志内容 主要是把图片从临时文件夹移动到真实路径
        $content = $BlogObj->processBlogImage($content);  
        
        // 提取日志第一张图片
        $first_img = $BlogObj->getFirstImg($content);

        // 提取日志摘要
        $summary   = $BlogObj->getSummary($content, $this->summary_len);
              
        $blog_datas = array (
            'title'   => $title,
            'content' => $content,
            'type_id' => $type_id,
            'views'   => 0,
            'is_published' => $is_published,  //默认发布 1 发布 0 草稿
            'add_account'  => $this->user['client_account'],
            'add_time'     => time(),
            'contentbg'    => $contentbg,
            'summary'      => $summary,
        	'first_img'    => $first_img,
            'comments'     => 0,
            'grant'        => $grant
        );
        
        $blog_id = $BlogObj->publishBlog($blog_datas, true);
        
        if (!empty($is_published)) {
            $error_msg = '日志发布失败!';
            $succeed_msg = '日志发布成功!';
            import("@.Control.Api.FeedApi");
            $feed_api = new FeedApi();
            $feed_api->class_create($class_code, $this->user['client_account'], $blog_id, FEED_BLOG, FEED_ACTION_PUBLISH);
        } else {
            $error_msg = '草稿保存失败,请稍后重试!';
            $succeed_msg = '草稿保存成功!';
        }
        
        if(empty($blog_id)) {
            $this->ajaxReturn(null, $error_msg, -1, 'json');
        }
        
        $this->ajaxReturn($blog_id, $succeed_msg, 1, 'json');
    }
    
    /**
     * 兼容 修改日志和修改草稿
     */
    public function editBlogAjax() {
        //1 表示发表个人日志，2表示发表班级日志
        $blog_id      = $this->objInput->postInt('blog_id');
        $class_code   = $this->objInput->postInt('class_code');
        $contentbg    = $this->objInput->postStr('contentbg');
        
        $title   = $this->objInput->postStr('title');
        $content = $this->objInput->postStr('content');
        $type_id = $this->objInput->postInt('type_id');
        $grant   = $this->objInput->postInt('grant');
        
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '班级不存在!', -1, 'json');
        }
        
        //验证是否有修改权限（只能修改自己发布的日志）
        $BlogObj = $this->_initBlogObj($class_code);
        $blog_info = $BlogObj->getBlogInfoById($blog_id);
        $blog_info = & $blog_info[$blog_id];
        $can_edit = $this->canEditBlog($blog_info);
        if (empty($can_edit)) {
            $this->showError('日志不存在或没有权限修改', '/Sns/Blog/List/index/class_code/'.$class_code);
        } 
        //验证数据的完整性
        if (empty($title) || empty($content) || $type_id < 0) {
             $this->ajaxReturn(null, '数据填写不完整!', -1, 'json');
        }
        
        // 处理日志内容 主要是把图片从临时文件夹移动到真实路径
        $content = $BlogObj->processBlogImage($content);  
        
        // 提取日志第一张图片
        $first_img = $BlogObj->getFirstImg($content);

        // 提取日志摘要
        $summary   = $BlogObj->getSummary($content, $this->summary_len);
        
        //拼装数据 为修改做准备  
        $blog_datas = array (
            'title'   => $title,
            'content' => $content,
            'type_id' => $type_id,
            'contentbg'    => $contentbg,
            'summary'      => $summary,
            'first_img'    => $first_img,
            'grant'        => $grant,
            'upd_account'  => $this->user['client_account'], 
            'upd_time'     => time()
        );
        
        //用户权限的判断
        $is_succeed = $BlogObj->modifyBlog($blog_datas, $blog_id);
        if(empty($is_succeed)) {
            $this->ajaxReturn(null, '保存失败,请稍后重试!', -1, 'json');
        }
        
        $this->ajaxReturn($blog_id, '保存成功!', 1, 'json');
    }
    
    /**
     * 删除班级日志 方法
     */
    public function deleteBlogAjax() {
        $blog_id = $this->objInput->getStr('blog_id'); //其实是bigint 类型
        $class_code = $this->objInput->getStr('class_code'); //其实是bigint 类型

        if(empty($blog_id)) {
            $this->ajaxReturn(null, '要删除的信息不存在!', -1, 'json');   
        }
        
        $BlogByClass = $this->_initBlogObj($class_code);
        //验证是否具有删除权限
        $del_blog_arr = $BlogByClass->getBlogInfoById($blog_id);
        $del_blog_arr = $del_blog_arr[$blog_id];
        
        $can_del_blog = $this->canDelBlog($del_blog_arr, $class_code);
        if (empty($can_del_blog)) {
            $this->ajaxReturn(null, '要删除的日志不存在或没有权限删除', -1, 'json'); 
        }
        
        $is_success = $BlogByClass->delBlog($blog_id);
        if (empty($is_success)) {
             $this->ajaxReturn(null, '删除失败稍后重试', -1, 'json');
        }
        
        $this->ajaxReturn(null, '删除成功', 1, 'json');
    }
    
    /**
     * 获取草稿详情（读取草稿）
     */
    public function readDraftAjax() {
        $blog_id      = $this->objInput->getInt('blog_id');
        $class_code   = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }
        
        $blogObj = $this->_initBlogObj($class_code);
        $draft_arr = $blogObj->getBlogInfoById($blog_id);
        $draft_info = & $draft_arr[$blog_id];
        if(empty($draft_info)) {
             $this->ajaxReturn(null, '草稿或者已被删除不存在!', -1, 'json');
        }
        
        $draft_info['content'] = htmlspecialchars_decode($draft_info['content']);

        $this->ajaxReturn($draft_info, '读取成功!', 1, 'json');
    }
    
    /**
     * ajax获取用户的草稿列表信息
     * 注明：1. 需要获取草稿的详细信息包含草稿的内容分类权限 等；
     * 		 2. 只获取用户最后添加的20  个草稿
     */
    public function getDraftListAjax() {
        $page = $this->objInput->getInt('page');
        $class_code = $this->objInput->getStr('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }
        
        $limit = 4;
        $page = $page > 1 ?  $page : 1;
        $offset = ($page-1) * $limit;

        //获取用户在当前班级的 班级日志草稿
        $where_arr = array(
            "add_account='". $this->user['client_account'] ."'",
            "is_published=0"  //0 草稿 1 发布
        );
        $blogObj = $this->_initBlogObj($class_code);
        $draft_list = $blogObj->getBlogList($where_arr,'blog_id desc', $offset, $limit + 1);
        $draft_list = & $draft_list[$class_code];
        
        $has_next_page = false;
        //格式化数据
        if(!empty($draft_list)){
            $has_next_page = count($draft_list) > $limit ? true : false;
            $draft_list = array_slice($draft_list,0 ,$limit);
            import("@.Common_wmw.Date");
            foreach($draft_list as $blog_id => $blog_info) {
                $blog_info['add_time'] =Date::timestamp($blog_info["add_time"]);
                
                $draft_list[$blog_id] = $blog_info;
            }
        }
        $page_arr = array(
            'page' => $page,
            'has_next_page' => $has_next_page
        );
        $this->ajaxReturn(array('pager'=>$page_arr, 'draft_list'=>$draft_list), '草稿获取成功!', 1, 'json');
    }
    
    /**
     * 删除草稿 Ajax 方法
     */
    public function deleteDraftAjax() {
        $blog_id = $this->objInput->getStr('blog_id'); //其实是bigint 类型
        $class_code = $this->objInput->getStr('class_code'); //其实是bigint 类型
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在!', -1, 'json');
        }
        if(empty($blog_id)) {
            $this->ajaxReturn(null, '要删除的信息不存在!', -1, 'json');   
        }
        
        $BlogByClass = $this->_initBlogObj($class_code);
        //验证是否具有删除权限
        $del_blog_arr = $BlogByClass->getBlogInfoById($blog_id);
        $del_blog_arr = $del_blog_arr[$blog_id];
        if ($del_blog_arr['add_account'] != $this->user['client_account']) {
            $this->ajaxReturn(null, '要删除的草稿不存在或没有权限删除', -1, 'json'); 
        }
        
        $is_success = $BlogByClass->delBlog($blog_id);
        if (empty($is_success)) {
             $this->ajaxReturn(null, '删除失败稍后重试', -1, 'json');
        }
        
        $this->ajaxReturn(null, '删除成功', 1, 'json');
    }
    
    
    
    
    /*
     * 编辑器上传图片通用方法
     */
    public function uploadPath() {
        import("@.Common_wmw.Pathmanagement_sns");
        $uploadPath = Pathmanagement_sns::uploadXheditorimgPathTmp();
        $showPath = Pathmanagement_sns::getXheditorimgPathTmp();

        import('@.Control.Api.XheditorApi');
        $uploadobj = new XheditorApi();
        $uploadobj->upload($uploadPath, $showPath);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*****************************************************************************************
    *									公共辅助函数							
    *****************************************************************************************/
    private function canPublish($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        if($this->user['client_class'][$class_code]['client_type'] == CLIENT_TYPE_FAMILY) {
            return false;
        }
        
        return true;
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
     * 1 班主任
     * 2 管理员
     * 3 添加人
     */
    private function canDelBlog($blog_info, $class_code) {
        if (empty($blog_info)) {
            return false;
        }
        
        //自己发布的日志 有权限删除
        if ($blog_info['add_account'] == $this->user['client_account']) {
            return true;
        }
        
        //班主任或者班级管理员有权限删除
        if ($this->isClassAdminTeacher($class_code) || $this->isClassAdmin($class_code)) {
            return true;
        }
        
        return false;
    }
}