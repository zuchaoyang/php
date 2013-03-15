<?php
/**
 * 个人日志列表页面  控制类
 * @author zlei 2013-1-5
 */
class ListAction extends SnsController {
    private $blog_perpage = 10;
    private $draft_perpage = 4;
    public function __construct() {
        parent::__construct();
        //tudo 检查是否有访问个人空间的权限权限
    }
    
    //自动分发班级还是个人 默认班级
    public function _initBlogObj($class_code) {
        static $BlogObj = false;
        
        if (empty($BlogObj)) {
            import('@.Control.Sns.Blog.Ext.ClassBlog');
            $BlogObj = new ClassBlog($class_code);
        }
        
        return $BlogObj;
    }
    
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $type_id    = $this->objInput->getInt('type_id');

        $start_time = $this->objInput->getStr('start_time');
        $end_time   = $this->objInput->getStr('end_time');
        
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->showError('班级信息不存在', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }
        
        $this->assign('can_publish_blog', $this->canPublishBlog($class_code));
        $this->assign('class_code', $class_code);
        $this->assign('type_id', $type_id);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);

        $this->display("class_list");
    } 
    
    /**
     * 草稿列表
     */
    public function draftList() {
        $class_code = $this->objInput->getInt('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('班级信息不存在', '/Homeuser/Index/spacehome/spaceid/' . $this->user['client_account']);
        }
        
        $blogObj = $this->_initBlogObj($class_code);
        $where_arr = array(
            "add_account='" . $this->user['client_account'] . "'",
            "is_published=0"  //0 草稿 1 发布
        );
        
        $draft_list = $blogObj->getBlogList($where_arr, 'blog_id desc', 0, 200);
        $draft_list = $draft_list[$class_code];
        $draft_list = $this->formatBlogList($draft_list);
        //dump($draft_list);exit;
        $this->assign('count_num', !empty($draft_list) ? count($draft_list) : 0);
        $this->assign('class_code', $class_code);
        $this->assign('draft_list', $draft_list);
        
        $this->display('class_draft_list');
    }
    /**
     * 获取日志列表包括添加人等
     */
    public function getBlogListAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $page       = $this->objInput->getInt('page');
        $type_id    = $this->objInput->postInt('type_id');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        
        //参数处理校验
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在', -1, 'json'); 
        }
        
        $page = max(1, $page);
        $perpage = $this->blog_perpage;
        $offset = ($page-1) * $perpage;
        
        //拼装where 查询条件
        $where_arr = array("is_published=1");
        if ($type_id >= 0) {
            $where_arr[] = "type_id='$type_id'";
        }
        if (!empty($start_time)) {
            $where_arr[] = "add_time>'" . strtotime($start_time) . "'";
        }
        if (!empty($end_time)) {
            $where_arr[] = "add_time<'" . (strtotime($end_time )+ 3600*24) . "'";
        }
        $BlogObj = $this->_initBlogObj($class_code);
        $blog_list = $BlogObj->getBlogList($where_arr, 'blog_id desc', $offset, $perpage + 1);
        $blog_list = & $blog_list[$class_code];
        
        //是否有上下页判断
        $page_list = array(
            'has_prev_page' => $page > 1 ? true : false,
            'has_next_page' => count($blog_list) > $perpage ? true : false
        );
        
        $return_list = array(
            'page_list' => $page_list,
            'blog_list' => array()
        );
        
        if (empty($blog_list)) {
            // 没有更多日志不算错误
            $this->ajaxReturn($return_list, '没有更多的日志了', 1, 'json'); 
        }
        
        //后续处理
        $blog_list = array_slice($blog_list, 0, $perpage, true);
        $blog_list = $this->formatBlogList($blog_list);
        $blog_list = $this->appendBlogListAccess($blog_list, $class_code);
        
        $return_list['blog_list'] = $blog_list;
        
        $this->ajaxReturn($return_list, '加载成功', 1, 'json');
    }
    
    
    /*******************************************************************
     * 公共辅助函数
     ******************************************************************/
    /**
     * 追加日志的权限信息
     * @param $blog_list
     * @param $class_code
     */
    private function appendBlogListAccess($blog_list, $class_code) {
        if(empty($blog_list)) {
            return false;
        } else if(empty($class_code)) {
            return $blog_list;
        }
    
        foreach($blog_list as $blog_id=>$blog) {
            //判断是否有删除权限
            $blog['can_del_blog'] = $this->canDelBlog($blog, $class_code);
            $blog_list[$blog_id] = $blog;
        }
        
        return $blog_list;
    }
    
    /**
     * 格式化日志列表
     * @param $blog_list
     */
    private function formatBlogList($blog_list) {
        if(empty($blog_list) || !is_array($blog_list)) {
            return false;
        }
        
        //获取 添加人 和分类 数组 为数据处理做准备 避免多次查询数据库
        foreach($blog_list as $blog_id=>$blog_info) {
            // 添加人
            $add_account_arr[] = $blog_info['add_account'];
            //分类
            $type_id_arr[] = $blog_info['type_id'];
        }
        
        //添加人列表
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getClientAccountById(array_unique($add_account_arr));
        
        //分类列表
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_list  = $mBlogTypes->getByTypeId(array_unique($type_id_arr));
        
        //数据页面显示处理
        foreach($blog_list as $blog_id=>$blog_info) {
            $add_account = $blog_info['add_account'];
            $type_id = $blog_info['type_id'];
            
            $blog_info['add_time']     = date('Y-m-d', $blog_info['add_time']);
            $blog_info['type_name']    = !empty($type_list[$type_id]['name']) ? $type_list[$type_id]['name'] : '班级日志';
            $blog_info['client_name']  = $user_list[$add_account]['client_name'];
            
            //获取内容中的第一张图片
            preg_match("/<img([^>]+?)\/>/im", $blog_info['summary'], $matches);
            if(!empty($matches[0])) {
                $blog_info['top_img_html'] = $matches[0];
                $blog_info['summary'] = str_replace($matches[0], '', $blog_info['summary']);
            }
            $blog_list[$blog_id] = $blog_info;
        }
        
        return $blog_list;
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
        
        //自己发布的日志 有权限删除,班主任或者班级管理员有权限删除
        if($this->isClassAdmin($class_code) || ($blog_info['add_account'] == $this->user['client_account'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 验证是否具有发布班级日志的权限
     * 只有家长不能写日志
     */
    private function canPublishBlog($class_code) {
        if ($this->user['client_class'][$class_code]['client_type'] == CLIENT_TYPE_FAMILY) {
            return false;
        }
        
        return true;
    }
    
}