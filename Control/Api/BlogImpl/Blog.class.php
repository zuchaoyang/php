<?php
/**
 * 此处处理班级 和个人 日志都相关的公共数据
 * 
 * 日志内容表，日志表，日志类型，日志评论四大块
 * @author sailong
 *
 */
class Blog {
    protected $_mBlog = null;
    protected $_mBlogContent = null;
    protected $_mBlogTypes = null;
    protected $_mBlogComments = null;
    
    /**
     * 实例化mBlog
     */
    private function _initmBlog() {
        $this->_mBlog = ClsFactory::Create('Model.Blog.mBlog');
    }
    
    /**
     * 实例化 mBlogContent
     */
    private function _initmBlogContent() {
        $this->_mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
    }
    
    /**
     * 实例化mBlogTypes
     */
    private function _initmBlogTypes() {
        $this->_mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
    }
    
    /**
     * 实例化mBlogComments
     */
    private function _initmBlogComments() {
        $this->_mBlogComments = ClsFactory::Create('Model.Blog.mBlogComments');
    }
    
    /**
     * 通过登录账号获取草稿列表
     * @param int $uid
     * 
     * @return array $blog_list
     * 
     * 注： 只取出最后添加的20 个草稿
     */
    public function getDraftListByAddUid($uid, $offset = 0, $limit = 20) {
        if(empty($uid)) {
            return false;
        }
        
        $where_arr = array(
            "add_account='$uid'",
            'is_published=' . NO_PUBLISHED
        );
        
        $this->_initmBlog();
        $blog_list = $this->_mBlog->getBlogInfo($where_arr, 'blog_id desc', $offset, $limit);
        //获取日志内容
        //$blog_list = $this->getConListByBlogList($blog_list);
        //获取日志类型
        ///$blog_list = $this->getTypeListByBlogList($blog_list);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    
    
    /**
     * 通过日志ID获取日志内容
     * @param array or int $blog_ids
     * 
     * @return array $blog_list
     */
    public function getListByBlodIds($blog_ids) {
        if(empty($blog_ids)) {
            return false;
        }
        $blog_list = $this->getBlogByBlogId($blog_ids);
        if(empty($blog_list)) {
            return false;
        }
        //获取日志内容
        $blog_list = $this->getConListByBlogList($blog_list);
        //获取日志类型
        $blog_list = $this->getTypeListByBlogList($blog_list);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    /**
     * 通过blog_id获取日志表信息
     */
    public function getBlogByBlogId($blog_ids) {
        if(empty($blog_ids)) {
            return false;
        }
        $this->_initmBlog();
        return $this->_mBlog->getBlogById($blog_ids);
    }
	/**
     * 通过班级日志列表获取日志类型
     * @param array $blog_list
     * 
     * @return array $blog_type
     */
    public function getTypeByBlogList($blog_list) {
        if(empty($blog_list)) {
            return false;
        }
        
        $type_ids = array();
        foreach($blog_list as $blog_id=>$blog_val) {
            $type_ids[$blog_val['type_id']] = $blog_val['type_id'];
        }
        
        if(empty($type_ids)) {
            return false;
        }
        
        $type_list = $this->getTypeListByTypeId($type_ids);
        
        return !empty($type_list) ? $type_list : false;
    }
    
    /**
     * 通过type_id获取类型信息
     * 
     */
    public function getTypeListByTypeId($type_id) {
        if(empty($type_id)) {
            return false;
        }
        
        $this->_initmBlogTypes();
        return $this->_mBlogTypes->getByTypeId($type_id);
    }
    
    
	/**
     * 通过日志列表获取日志内容信息
     * @param array $blog_list
     * 
     * @return array $blog_list
     */
    private function getConListByBlogList($blog_list) {
        if(empty($blog_list)) {
            return false;
        }
        $blog_ids = array_keys($blog_list);
        $this->_initmBlogContent();
        $blog_content_list = $this->_mBlogContent->getBlogById($blog_ids);
        if(empty($blog_content_list)) {
            return false;
        }
        
        
        foreach($blog_list as $blog_id=>$content_val) {
            $content = $blog_content_list[$blog_id]['content'];
            if(empty($content)) {
                //没有日志内容的清空
                unset($blog_list[$blog_id]);
            }else{
                $blog_list[$blog_id]['content'] = $content;
            }
            unset($blog_content_list[$blog_id]);
        }
        
        return !empty($blog_list) ? $blog_list: false;
    }
    
    
	/**
     * 通过班级日志列表获取日志含有类型列表
     * @param array $blog_list
     * 
     * @return array $blog_list
     */
    private function getTypeListByBlogList($blog_list) {
        if(empty($blog_list)) {
            return false;
        }
        //获取日志类型列表
        $type_list = $this->getTypeByBlogList($blog_list);
        
        if(empty($type_list)) {
            return false;
        }
        
        foreach($blog_list as $blog_id=>$blog_val) {
            $type_name = $type_list[$blog_val['type_id']];
            if(empty($type_name)) {
                $type_name = '未知';
            }
            $blog_list[$blog_id]['type_name'] = $type_name;
            unset($type_list[$blog_val['type_id']]);
        }
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    /**
     * 添加日志
     * $data = array(
     * 		'title'
            'type_id'
            'views'
            'is_published'
            'add_account'
            'add_time'
            'upd_account'
            'upd_time'
            'contentbg'
            'summary'
            'comments'

     * )
     */
    public function addBlogInfo($data) {
        if(empty($data)) {
            return false;
        }
        //初始化日志表数据
        $blog_data = $this->initBlogData($data);
        $this->_initmBlog();
        $blog_id = $this->_mBlog->addBlog($blog_data, true);
        
        return !empty($blog_id) ? $blog_id : false;
    }
    /**
     * 修改日志表
     * @param array $data
     * @param int   $blog_id
     * 
     * @return boolean
     */
    public function updBlogInfoByBlogId($data, $blog_id) {
        if(empty($data) || empty($blog_id)) {
            return false;
        }
        //初始化信息
        $blog_data = array(
            'title'        => $data['title'],
            'type_id'      => $data['type_id'],
            'is_published' => $data['is_published'],
            'upd_account'  => $data['uid'],
            'upd_time'     => time(),
            'contentbg'    => $data['contentbg'],
            'summary'      => $this->getSummary($data['content']),
        );
        
        $this->_initmBlog();
        
        return $this->_mBlog->modifyByBlogId($blog_data, $blog_id);
    }
    /**
     * 修改日志内容表
     * @param array $data
     * @param int   $blog_id
     * 
     * @return boolean
     */
    public function updContentByBlodId($data, $blog_id) {
        if(empty($data) || empty($blog_id)) {
            return false;
        }
        
        $content_data = array(
                'content' => $data['content']
        );
        $this->_initmBlogContent();
        
        return $this->_mBlogContent->modifyByBlogId($content_data, $blog_id);
    }
    
    /**
     * 添加日志内容表
     * @param array $data
     * 
     * @return boolean
     */
    public function addContent($content, $blog_id) {
        if(empty($content) || empty($blog_id)) {
            return false;
        }
        //初始化日志内容表数据
        $content_data = array(
                'blog_id'   => $blog_id,
                'content'   => $content
        );
        $this->_initmBlogContent();
        
        return $this->_mBlogContent->addContent($content_data);
    }
    
	/**
     * 初始化日志表数据
     * @param array $data
     * 
     * @return array $split_data
     */
    private function initBlogData($data) {
        if(empty($data)) {
            return false;
        }
        $now_time = time();
        $uid = $data['uid'];
        //初始化日志表
        
        $blog_data = array(
            'title'        => $data['title'],
            'type_id'      => $data['type_id'],
            'views'        => 0,
            'is_published' => $data['is_published'],
            'add_account'  => $data['uid'],
            'add_time'     => $now_time,
            'upd_account'  => $data['uid'],
            'upd_time'     => $now_time,
            'contentbg'    => $data['contentbg'],
            'summary'      => $this->getSummary($data['content']),
            'comments'     => 0
        );
        
        return $blog_data;
    }
    /**
     * 根据日志ID获取日志评论
     * @param int $blog_id
     * @param int $offset
     * @param int $limit
     * 
     * @return array
     *
     */
    public function getCommentListByBlogId($blog_id, $offset=0, $limit=10) {
        if(empty($blog_id)) {
            return false;
        }
        $this->_initmBlogComments();
        //获取一级评论
        $comment_list = $this->_mBlogComments->getFirstLevel($blog_id, $offset, $limit);
        if(empty($comment_list)) {
            return false;
        }
        //获取以及评论ID
        $comment_ids = array_keys($comment_list);
        
        $second_level = $this->getSecondCommentByCommentId($comment_ids);
        if(!empty($second_level)) {
            foreach($comment_list as $com_id=>$com_val) {
                $second_list = $second_level[$com_id];
                if(empty($second_list)) {
                    $second_list = '';
                }
                $comment_ids[$com_id]['second'] = $second_list;
                unset($second_level[$com_id]);
            }
        }
        
        return $comment_list;
    }
    
    /**
     * 根据一级ID获取二级评论
     */
    public function getSecondCommentByCommentId($comment_id, $offset, $limit) {
        if(empty($comment_id)) {
            return false;
        }
        $this->_initmBlogComments();
        
        return $this->_mBlogComments->getSecondLevel($comment_id, $offset, $limit);
    }
    
    /**
     * 截取日志内容
     * @param String $content
     */
    private function getSummary($content) {
        if(empty($content)) {
            return false;
        }
        
        $pattern="/<img([^>]*)\/>/im";
        
        //提取第一张图片
        preg_match_all($pattern, $content,$matches);
        $matches = reset($matches);
        $img = $matches[0];
        
        
        $content = preg_replace($pattern, "", $content);
        import('@.Common_wmw.WmwString');
        //去除html标签
        $content = WmwString::delhtml($content);
        //截取内容
        $content = WmwString::mbstrcut($content, $start=0, 108, 1, $suffix=true);
        
        $content = $img.$content;
        
        return $content;
    }
    
    /**
     * 根据日志ID删除日志表信息
     * @param int $blog_id
     * 
     * @return boolean
     */
    public function delBlogByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $this->_initmBlog();
        
        return $this->_mBlog->delBlog($blog_id);
    }
    
 	/**
     * 根据日志ID删除日志内容表信息
     * @param int $blog_id
     * 
     * @return boolean
     */
    public function delContentByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $this->_initmBlog();
        
        return $this->_mBlogContent->delByBlogId($blog_id);
    }
    
    /**
     * 添加日志评论
     * @param array $dataarr
     * 
     * @return  int $comment_id
     */
    public function  addComment($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->_initmBlogComments();
        
        return $this->_mBlogComments->addComment($dataarr, true);
    }
    
    
    /**
     * 根据日志ID删除日志评论
     * @param int $blog_id
     * 
     * @return boolean
     */
    public function delCommentByBlogId($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $this->_initmBlogComments();
        
        return $this->_mBlogComments->delAllByBlogId($blog_id);
    }
    /*
     * 根据评论ID删除日志评论
     * @param int $comment_id
     * 
     * @return boolean
     */
    public function delCommentByCommentId($comment_id) {
        if(empty($comment_id)) {
            return false;
        }
        $this->_initmBlogComments();
        
        return $this->_mBlogComments->delByCommentId($comment_id);
    }
    /**
     * 添加日志类型
     * @param array $dataarr
     * 
     * @return boolean
     */
    public function addType($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->_initmBlogTypes();
        
        $this->_mBlogTypes->addType($dataarr);
    }
    
	/**
     * 修改日志类型
     * @param array $dataarr
     * @param int $type_id
     * 
     * @return boolean
     */
    public function updType($dataarr, $type_id) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->_initmBlogTypes();
        
        $rs = $this->_mBlogTypes->modifyById($dataarr, $type_id);
        
        if($rs === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除日志类型
     * @param int $type_id
     * 
     * @return boolean
     */
    public function delType($type_id) {
        if(empty($type_id)) {
            return false;
        }
        
        $this->_initmBlogTypes();
        
        $rs = $this->_mBlogTypes->delByTypeId($type_id);
        if($rs === false) {
            return false;
        }
        
        return $rs;
    }
}