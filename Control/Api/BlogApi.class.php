<?php
/**
* author:sailong<shailong123@126.com>
* 功能：Blog manage
* 说明：作为日志操作的统一接口
* 
* @return json
*/
class BlogApi extends ApiController{
    /**
     * 班级日志**********************************************************************************************************************
     */
    
    public function _initByClass() {
        import('@.Control.Api.BlogImpl.ByClass');
        return new ByClass();
    }
    
    /**
     *根据班级ID获取班级日志里表
     * 
     */
    public function getListByClass () {
        $class_code = $this->objInput->getInt('class_code');
        $page = $this->objInput->getInt('page');
        
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级不存在', -1, 'json');
        }
        
        //检查班级是否存在
        $rs = $this->class_is_exist($class_code);
        
        if($rs) {
            $this->ajaxReturn(null, '班级不存在', -1, 'json');
        }
        
        $page = max(1, $page);
        $length = 10;
        $offset = ($page-1)*$length;
        
        import('@.Control.Api.BlogImpl.ByClass');
        $ByClass = $this->_initByClass();
        $blog_list = $ByClass->getPageListByClassCode($class_code, $offset, $length);
        if(empty($blog_list)) {
            $this->ajaxReturn(null, '没有找到日志信息', -1, 'json');
        }
        
        $this->ajaxReturn($blog_list, '', 1, 'json');
    }
    
    /**
     * 根据日志ID获取日志信息
     */
    public function getClassBlogByBlogId() {
        $blog_id = $this->objInput->getInt('blog_id');
        
        $ByClass = $this->_initByClass();
        
        $blog_info = $ByClass->getBlogListByBlogIds($blog_id);
        
        $this->ajaxReturn($blog_info, '',1,'json');
    }
    
    /**
     * 通过日志搜索日志
     */
    public function getClassBlogByTime() {
        $re_time = strtotime($this->objInput->postStr('re_time'));
        $end_time = strtotime($this->objInput->postStr('end_time'));
        $page = $this->objInput->postStr('page');
        $class_code = $this->objInput->postInt('class_code');
        
        $length = 10;
        $offset = ($page-1)*10;
        
        $where_time['re_time'] = $re_time;
        $where_time['end_time'] = $end_time;
        
        $ByClass = $this->_initByClass();
        
        $blog_list = $ByClass->getClassBlogByTime($where_time, $class_code, $offset, $limit=$length+1);
        
        $this->ajaxReturn($blog_list, '',1,'json');
    }
    /**
     * 添加班级日志
     * @param array data
     * data = array(
     * 		'class_code',
            'title',
            'type_id',
            'is_published',
            'uid',
            'contentbg',
			'content',
			'grant',
     * );
     * 
     * @return json
     */
     public function addByClass() {
         $data = $this->objInput->postArr('data');
         $class_code = $this->objInput->postArr('class_code');
         empty($class_code) ? $class_code : $this->ajaxReturn(null, '系统错误（班级编号为空）', -1, 'json');
         empty($data['title']) ? $data['title'] : $this->ajaxReturn($data['title'], '标题不可为空', -1, 'json');
         empty($data['type_id']) ? $data['type_id'] : $this->ajaxReturn($data['type_id'], '请选择日志类型', -1, 'json');
         empty($data['is_published']) ? $data['is_published'] : $data['is_published']=0;
         empty($data['uid']) ? $data['uid'] : $this->ajaxReturn($data['uid'], '系统错误', -1, 'json');
         empty($data['contentbg']) ? $data['contentbg'] : $data['contentbg']='';
         empty($data['content']) ? $data['content'] : $this->ajaxReturn($data['content'], '内容不可为空', -1, 'json');
         empty($data['grant']) ? $data['grant'] : $data['grant']=0;
         
         $ByClass = $this->_initByClass();
         
         $blog_id = $ByClass->addBlog($data, $class_code);
         
         if(empty($blog_id)) {
            $this->ajaxReturn(null, '添加日志失败', -1, 'json');
         }
        
         $this->ajaxReturn($blog_id, '添加日志成功', 1, 'json');
     }
    /**
     * 修改日志
     * @param array $data
     * $data = array(
     * 		'blog_id',
     * 		'class_code',
            'title',
            'type_id',
            'is_published',
            'uid',
            'contentbg',
			'content',
			'grant',
     * );
     * 
     * @return boolean
     */
    public function updClassBlogByBlogId() {
        $data = $this->objInput->postArr('data');
        $class_code = $this->objInput->postArr('class_code');
        $blog_id = $this->objInput->postArr('blog_id');
        empty($class_code) ? $class_code : $this->ajaxReturn(null, '系统错误（班级编号为空）', -1, 'json');
        empty($blog_id) ? $blog_id : $this->ajaxReturn(null, '系统错误（日志ID为空）', -1, 'json');
        empty($data['class_code']) ? $data['class_code'] : $this->ajaxReturn($data['class_code'], '系统错误（班级编号为空）', -1, 'json');
        empty($data['title']) ? $data['title'] : $this->ajaxReturn($data['title'], '标题不可为空', -1, 'json');
        empty($data['type_id']) ? $data['type_id'] : $this->ajaxReturn($data['type_id'], '请选择日志类型', -1, 'json');
        empty($data['is_published']) ? $data['is_published'] : $data['is_published']=0;
        empty($data['uid']) ? $data['uid'] : $this->ajaxReturn($data['uid'], '系统错误', -1, 'json');
        empty($data['contentbg']) ? $data['contentbg'] : $data['contentbg']='';
        empty($data['content']) ? $data['content'] : $this->ajaxReturn($data['content'], '内容不可为空', -1, 'json');
        empty($data['grant']) ? $data['grant'] : $data['grant']=0;
         
        $ByClass = $this->_initByClass();
        
        $rs = $ByClass->updBlogByBlogId($data, $blog_id, $class_code);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '修改日志失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '修改日志成功', 1, 'json');
    }
    
    /**
     * 添加班级日志类型表
     * @param array $dataarr
     * @param int   $class_code
     * 
     * @return and  $id
     */
    public function addClassBlogType() {
        $data = $this->objInput->postArr('data');
        $class_code = $this->objInput->postInt('class_code');
        if(empty($data) || empty($class_code)) {
            $this->ajaxReturn(null, '日志类型数据错误', -1, 'json');
        }
        
        if(empty($data['name'])) {
            $this->ajaxReturn(null, '日志类型不可为空', -1, 'json');
        }
        if(empty($data['add_account'])) {
            $this->ajaxReturn(null, '添加人不可为空', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        
        $rs = $ByClass->addBlogType($data, $class_code);
        if(empty($rs)) {
            $this->ajaxReturn(null, '日志类型添加失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '日志类型添加成功', 1, 'json');
    }
    
	/**
     * 修改班级日志类型表
     * @param array $dataarr
     * @param int   $class_code
     * @param int   $type_id
     * 
     * @return and  $id
     */
    public function updClassBlogTypeById() {
        $data = $this->objInput->postArr('data');
        $type_id = $this->objInput->postInt('type_id');
        $class_code = $this->objInput->postInt('class_code');
        if(empty($data) || empty($class_code) || empty($type_id)) {
            $this->ajaxReturn(null, '日志类型数据错误', -1, 'json');
        }
        
        if(empty($data['name'])) {
            $this->ajaxReturn(null, '日志类型不可为空', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        
        $rs = $ByClass->updType($data, $type_id);
        if($rs === false) {
            $this->ajaxReturn(null, '日志类型修改失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '日志类型修改成功', 1, 'json');
    }
    
	/**
     * 删除班级日志类型表
     * @param int   $class_code
     * @param int   $type_id
     * 
     * @return and  $id
     */
    public function delClassBlogTypeById() {
        $type_id = $this->objInput->postInt('type_id');
        $class_code = $this->objInput->postInt('class_code');
        if(empty($class_code) || empty($type_id)) {
            $this->ajaxReturn(null, '日志类型数据错误', -1, 'json');
        }
        
        
        $ByClass = $this->_initByClass();
        $rs = $ByClass->delClassTypeByTypeId($type_id, $class_code);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '日志类型删除失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '日志类型删除成功', 1, 'json');
    }
        
    /**
     * 班级日志类型及日志数列表
     */
    public function getTypeNumsListByClass() {
        $class_code = $this->objInput->getInt('class_code');
        
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级编号错误', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        //二维
        $type_list = $ByClass->getTypeListByClass($class_code);
        if(empty($type_list)) {
            $this->ajaxReturn(null,'类型更新错误',-1,'json');
        }
        $type_ids = array_keys($type_list);
        
        //获取日志数
        $type_nums_list = $ByClass->getBlogNumsInType($type_ids, $class_code);
        if(!empty($type_nums_list)) {
            foreach($type_list as $type_id=>$type_val) {
                if(empty($type_nums_list[$type_id])) {
                    $type_nums_list[$type_id]=0;
                }
                $type_list[$type_id]['blog_num'] = $type_nums_list[$type_id];
            }
        }
        
        $this->ajaxReturn($type_list,'',1,'json');
    }
    
    /**
     * 删除日志
     */
    public function delClassBlogByBlogId() {
        $blog_id = $this->objInput->getInt('blog_id');
        if(empty($blog_id)) {
            $this->ajaxReturn(null, '系统错误', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        
        $rs = $ByClass->delBlogAllInfoByBlogId($blog_id);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '系统错误', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除成功', 1, 'json');
        
    }
    /**
     * 获取班级草稿列表
     * 
     */
    public function getDraftBlogByClass() {
        $uid = $this->objInput->getInt('uid');
        if(empty($uid)) {
            $this->ajaxReturn(null, '账号错误', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        $blog_list = $ByClass->getDraftListByAddUid($uid);
        
        if(empty($blog_list)) {
            $this->ajaxReturn(null, '信息错误', -1, 'json');
        }
        
        $this->ajaxReturn($blog_list, '', 1, 'json');
    }
    
	/**
     * 班级是否存在
     * @param int $class_code
     * 
     * @return bool
     */
    private function class_is_exist($class_code) {
        if(empty($class_code)) {
            return false;
        }
        $mClassInfo = ClsFactory::Create('Model.mClassInfo');
        $class_info = $mClassInfo->getClassInfoBaseById($class_code);
        if(empty($class_info)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 添加日志评论
     * @param array $dataarr
     * ef:
     * 		$dataarr = array(
     * 			'blog_id'
                'content'
                'up_id'
                'client_account'
                'level'
     * 		)
     * @return json
     */
    public function addComments() {
        $dataarr = $this->objInput->post($dataarr);
        
        if(empty($dataarr)) {
            $this->ajaxReturn(null, '信息错误', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        
        $id = $ByClass->addComment($dataarr, true);
        
        if(empty($id)) {
            $this->ajaxReturn(null, '添加评论失败', -1, 'json');
        }
        
        $this->ajaxReturn($id, '添加评论成功', 1, 'json');
    }
    
    /**
     * 删除日志评论
     */
    public function delCommentsById($comment_id) {
        if(empty($comment_id)) {
            $this->ajaxReturn(null, '信息错误', -1, 'json');
        }
        
        $ByClass = $this->_initByClass();
        
        $rs= $ByClass->delCommentByCommentId($comment_id);
        
        if(empty($rs)) {
            $this->ajaxReturn(null, '删除评论失败', -1, 'json');
        }
        
        $this->ajaxReturn($rs, '删除评论成功', 1, 'json');
    }
    /**
     * 个人日志**********************************************************************************************************************
     */
    
    /**
     * 根据用户账号获取日志列表
     */
    public function getListByPerson() {
        
    }
    /**
     * 添加日志
     */
    public function addByPerson() {
        
    }
    /**
     * 修改日志
     * 
     */
    public function updPersonBlogByBlogId() {
        
    }
    /**
     * 删除日志
     */
    public function delPersonBlogByBlobId() {
        
    }
    /**
     * 获取草稿列表
     * 
     */
    public function getDraftBlogByUid() {
        
    }
}
