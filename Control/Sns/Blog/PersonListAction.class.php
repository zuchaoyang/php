<?php
/**
 * 个人日志列表页面  控制类
 * @author zlei 2013-1-5
 */
class PersonListAction extends SnsController {
    private $blog_perpage = 10;
    private $draft_perpage = 4;
    public function __construct() {
        parent::__construct();
        //tudo 检查是否有访问个人空间的权限权限
    }
    
    //自动分发班级还是个人 默认班级
    public function _initBlogObj($client_account) {
        static $BlogObj = false;
        
        if (empty($BlogObj)) {
            import('@.Control.Sns.Blog.Ext.PersonBlog');
            $BlogObj = new PersonBlog($client_account);
        }
        
        return $BlogObj;
    }
    
    public function index() {
        $client_account = $this->objInput->getStr('client_account');
        $type_id    = $this->objInput->getInt('type_id');

        $start_time = $this->objInput->getStr('start_time');
        $end_time   = $this->objInput->getStr('end_time');
        
        $client_account_isset = $this->checkoutAccount($client_account);
        if (empty($client_account_isset)) {
        	$this->showError('你访问的用户不存在！', "/Sns/Blog/PersonList/index/client_account/" . $this->user['client_account']);
        }
        
        $this->assign('can_publish_blog', $this->canPublishBlog($client_account));
        $this->assign('client_account', $client_account);
        $this->assign('type_id', $type_id);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);

        $this->display("person_list");
    } 
    
    /**
     * 草稿列表
     */
    public function draftList() {
        $client_account = $this->user['client_account'];
        
        $blogObj = $this->_initBlogObj($client_account);
        $where_arr = array(
            "add_account='$client_account'",
            "is_published=0"  //0 草稿 1 发布
        );
        
        $draft_list = $blogObj->getBlogList($where_arr, 'blog_id desc', 0, 200);
        $draft_list = $draft_list[$client_account];
        $draft_list = $this->formatBlogList($draft_list);

        $this->assign('count_num', !empty($draft_list) ? count($draft_list) : 0);
        $this->assign('client_account', $client_account);
        $this->assign('draft_list', $draft_list);
        
        $this->display('person_draft_list');
    }
    /**
     * 获取日志列表包括添加人等
     */
    public function getBlogListAjax() {
        $client_account = $this->objInput->getStr('client_account');
        $page       = $this->objInput->getInt('page');
        $type_id    = $this->objInput->postInt('type_id');
        $start_time = $this->objInput->postStr('start_time');
        $end_time   = $this->objInput->postStr('end_time');
        
        $client_account_isset = $this->checkoutAccount($client_account);
        if (empty($client_account_isset)) {
        	$this->ajaxReturn(null, '你访问的用户不存在！', -1, 'json'); 
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
        $grant = $this->getSelectGrant($client_account);
        if(!empty($grant)) {
        	$where_arr[] = $grant;
        }
        
        $BlogObj = $this->_initBlogObj($client_account);
        $blog_list = $BlogObj->getBlogList($where_arr, 'blog_id desc', $offset, $perpage + 1);

        $blog_list = & $blog_list[$client_account];
        
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
        $blog_list = $this->appendBlogListAccess($blog_list, $client_account);
        
        $return_list['blog_list'] = $blog_list;
        
        $this->ajaxReturn($return_list, '加载成功', 1, 'json');
    }
    
    
    /*******************************************************************
     * 公共辅助函数
     ******************************************************************/
    /**
     * 追加日志的权限信息
     * @param $blog_list
     * @param $client_account
     */
    private function appendBlogListAccess($blog_list, $client_account) {
        if(empty($blog_list)) {
            return false;
        } else if(empty($client_account)) {
            return $blog_list;
        }
    
        foreach($blog_list as $blog_id=>$blog) {
            //判断是否有删除权限
            $blog['can_del_blog'] = $this->canDelBlog($blog, $client_account);
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
        import("@.Common_wmw.Date");
        foreach($blog_list as $blog_id=>$blog_info) {
            $add_account = $blog_info['add_account'];
            $type_id = $blog_info['type_id'];
            
            $blog_info['add_time']     = Date::timestamp($blog_info['add_time']);
            $blog_info['type_name']    = !empty($type_list[$type_id]['name']) ? $type_list[$type_id]['name'] : '个人日志';
            $blog_info['client_name']  = $user_list[$add_account]['client_name'];
            
            $blog_list[$blog_id] = $blog_info;
        }
        
        return $blog_list;
    }
    
    
    /**
     * 判断用时候有删除班级日志的权限
     * 
     * 注：只有添加人才可以删除个人日志 
     */
    private function canDelBlog($blog_info) {
        if (empty($blog_info)) {
            return false;
        }
        
        //自己发布的日志 有权限删除,班主任或者班级管理员有权限删除
        if($blog_info['add_account'] == $this->user['client_account']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 查看当前用户是否在自己的空间
     * 注：
     * 只有在自己的个人空间才显示发布按钮
     */
    private function canPublishBlog($client_account) {
        if (empty($client_account) || $this->user['client_account'] == $client_account) {
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