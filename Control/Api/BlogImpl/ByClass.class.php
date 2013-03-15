<?php
import('@.Control.Api.BlogImpl.Blog');
/**
 * 班级日志Api接口
 * 所有关于班级日志操作处理
 * @author sailong
 * 
 * todo zlei 2013-3-12
 * 
 * 这个接口因权限问现已废弃不要使用其中的任何方法
 *
 */
class ByClass extends Blog {
    
    /**
     * 日志添加
     * @param array $data
     * @param int   $class_code
     * $data = array(
     * 		'title',
     * 		'type_id',
     * 		'is_published',
     * 		'uid',
     * 		'contentbg',
     * 		'content',
     * 		'grant',
     * );
     * @return int $blog_id
     */
    public function addBlog($data, $class_code) {
        if(empty($data)){
            return false;
        }
        
        //添加日志及内容
        $blog_id = $this->addBlogInfo($data, true);
        if(empty($blog_id)) {
            return false;
        }
        //添加日志内容
        $rs = $this->addContent($data['content'], $blog_id);
        if(empty($rs)) {
            $this->delBlogByBlogId($blog_id);
            return false;
        }
        
        //添加班级日志关系表
        $rel_id = $this->addRel($blog_id, $class_code); 
        if(empty($rel_id)) {
            $this->delBlogByBlogId($blog_id);
            $this->delContentByBlogId($blog_id);
            return false;
        }
        
        //添加日志权限表
        $grant_id = $this->addGrant($data['grant'], $blog_id, $class_code);
        
        if(empty($grant_id)) {
            $this->delBlogByBlogId($blog_id);
            $this->delContentByBlogId($blog_id);
            $this->delRelByRelId($rel_id);
            return false;
        }
        
        return $blog_id;
    }
    /**
     * 修改日志信息
     * @param array $data
     * $data = array(
     * 		'blog_id',
     * 		'class_code',
     * 		'title',
     * 		'type_id',
     * 		'is_published',
     * 		'uid',
     * 		'contentbg',
     * 		'content',
     * 		'grant',
     * );
     * 
     * @return true;
     */
    public function updBlogByBlogId($data, $blog_id, $class_code) {
        if(empty($data) || empty($blog_id) || empty($class_code)) {
            return false;   
        }
        //修改日志权限表
        
        $this->updGrantById($data, $blog_id, $class_code);
        
        //修改日志类型
        $this->updTypeById($data, $blog_id, $class_code);
        //修改日志内容表
        $this->updContentByBlodId($data, $blog_id);
        //修改日志表
        $this->updBlogInfoByBlogId($data, $blog_id);
        
        return true;
    }
    /**
     * 修改日志权限
     */
    public function updGrantById($dataarr ,$blog_id, $class_code){
        if(empty($blog_id) || empty($dataarr) || empty($class_code)) {
            return false;
        }
        $id = $this->check_grant($blog_id, $class_code);
        $grant_arr['grant'] = $dataarr['grant'];
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        $rs = $mBlogClassGrants->modifyById($dataarr, $id);
        if($rs === false) {
            return false;
        }
        
        return $rs;
    }
    
    /**
     * 通过日志ID删除日志todolist
     * @param int $blog_id
     * 
     * @return boolean 
     */
    public function delBlogAllInfoByBlogId($blog_id, $class_code){
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        
        //删除班级日志关系表
        $this->delRelByBlogId($blog_id, $class_code);
        //删除日志权限表
        $this->delGrantByBlogId($blog_id, $class_code);
        //删除日志表
        $this->delBlogByBlogId($blog_id);
        //删除日志内容表
        $this->delContentByBlogId($blog_id);
        //删除日志评论表
        $this->delCommentByBlogId($blog_id);
        
        return true;
    }
    
    /**
     * 通过班级code 和日志ID获取日志信息
     * 
     * @param int $blog_id
     * @param bigint $class_code
     * @return array $blog_list 返回日志信息 只是一条 
     * 包含 所属班级,所属分类
     */
    public function getBlogByClassCodeAndId($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        
        //日志班级关系
        $blog_rel_info = $this->getClassBlogRelation($blog_id, $class_code);
        //判断日志是否属于当前班级
        if (empty($blog_rel_info)) { 
            return false;
        }
        
        //班级日志权限
        $blog_grants_info = $this->getClassBlogGrants($blog_id, $class_code);
        //二维
        $blog_list = $this->getListByBlodIds($blog_id);
        $blog_list = & $blog_list[$blog_id];
        if(!empty($blog_list)){
            $blog_list['class_code'] = $blog_rel_info['class_code'];
            $blog_list['grant'] = $blog_grants_info['grant'];
        }
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    
    /**
     * 获取草稿列表
     */
    public function getDraftListByUid($uid) {
        if(empty($uid)) {
            return false;
        }
        //获取草稿列表
        $blog_list = $this->getDraftListByAddUid($uid, 0, 20);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    
    
	/**
     * 通过班级编号获取班级日志信息
     * @param int $class_code
     * 
     * return array 班级日志信息$blog_list数组
     */
    public function getPageListByClassCode($class_code, $offset, $limit) {
        if(empty($class_code)) {
            return false;
        }
        
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        //二维
        $rel_where_arr['class_code'] = $class_code;
        $rel_list = $mBlogClassRelation->getRelInfo($rel_where_arr, null, $offset, $limit);
        //通过关系获取日志ID
        $blog_ids = $this->getBlogIdsByRel($rel_list);
        //通过日志ID获取日志内容信息
        $blog_list = $this->getBlogListByBlogIds($blog_ids);
        //获取日志权限内容
        $blog_list = $this->getGrantListByBlogList($blog_list);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    
    
    /**
     * 通过班级编号获取日志权限信息列表
     * @param int $class_code
     * @return array $grant_list
     */
    public function getGrantIdsByClassCode($class_code) {
        if(empty($class_code)) {
            return false;
        }
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        
        $rel_where_arr = array(
            'class_code' => $class_code
        );
        //二维
        $grant_list_rel = $mBlogClassGrants->getGrantInfo($rel_where_arr);

        if(empty($grant_list_rel)) {
            return false;
        }
        //二维
        $grant_list = array();
        foreach($grant_list_rel as $id=>$val) {
            $grant_list[$val['grant']] = $this->getGrantsList($val['grant']);
            unset($grant_list_rel[$id]);
        }
        
        return !empty($grant_list) ? $grant_list : false;
    }
    
    /**
     * 通过时间搜索日志
     * @param array where_time
     * @param int   class_code
     * 
     * @return array blog_list 
     */
    public function getClassBlogByTime($where_time, $class_code, $offset=0, $limit=10) {
        if(empty($where_time) || empty($class_code)) {
            return false;
        }
        
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        $blog_ids = $mBlogClassRelation->getPageListByClassCodeAddTime($where_time, $class_code, $offset, $limit);
        
        $blog_list = $this->getListByBlodIds($blog_ids['blog_ids']);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    /**
     * 通过班级编号获取日志分类管理
     * 
     * 待定处理 需添加班级日志类型关系表 
     * $blog_count_flag boolean true 需要日志类型的的日志数; false 则不需要
     * 
     */
    public function getTypeListByClass($class_code) {
        if(empty($class_code)) {
            return false;
        }
        
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        //二维
        $type_where_arr = array(
        	"class_code='$class_code'"
        );
        $type_rel_list = $mBlogClassType->getTypeInfo($type_where_arr, 'id asc');
        //二维
        $type_ids = array();
        foreach($type_rel_list as $key=>$val) {
            $type_ids[$val['type_id']] = $val['type_id'];
            unset($type_rel_list[$key]);
        }
        //二维
        $type_list = $this->getTypeListByTypeId($type_ids);
        
        return !empty($type_list) ? $type_list : false;
    }
    
    /**
     * 获取班级日志类型中的日志数
     * @param array $type_ids
     * @param int   $class_code
     * 
     * @return array $type_log_nums 类型中的日志数
     */
    public function getBlogNumsInType($type_ids, $class_code) {
        if(empty($type_ids) || empty($class_code)) {
            return false;
        }       
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        //二维
        $type_blog_nums = $mBlogClassType->getBlogNumsByTypeIdAddClass($type_ids, $class_code);

        return !empty($type_blog_nums) ? $type_blog_nums : false;
    }
    
    /**
     * 添加班级日志类型表
     * @param array $dataarr
     * @param int   $class_code
     * 
     * @return boolean
     */
    public function addBlogType($type_id, $class_code) {
        if(empty($type_id) || empty($class_code)) {
            return false;
        }
        
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        $type_arr = array(
            'class_code' => $class_code,
            'type_id'    => $type_id
        );
        
        return $mBlogClassType->addTypeType($type_arr,true);
    }
    
    /**
     * 删除班级日志类型
     * @param int $type_id
     * @param int $class_code
     * 
     * @return boolean
     */
    public function delClassTypeByTypeId($type_id, $class_code) {
        if(empty($type_id) || empty($class_code)) {
            return false;
        }
        
        $id = $this->check_type($type_id, $class_code);
        
        if(empty($id)) {
            return false;
        }
        
        return $this->delType($type_id);
    }
    
     /**********************************************************************************
     * zlei 2013-1-5 add
     **********************************************************************************/
    /**
     * 获取班级日志关系  通过班级code和日志id获取 
     * return reset($rel_arr) 数据一定是一条记录
     */
    public function getClassBlogRelation($blog_id, $class_code) {
        if(empty($class_code) || empty($blog_id)) {
            return false;
        }
        
        $where_arr = array("class_code='$class_code'", "blog_id='$blog_id'");
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        $rel_arr = $mBlogClassRelation->getRelInfo($where_arr, 'id desc');

        return !empty($rel_arr) ? reset($rel_arr) : false;
    }
    
    /**
     * 获取班级日志权限  通过班级code和日志id获取
     * return reset($grants_arr) 数据一定是一条记录
     */
    public function getClassBlogGrants($blog_id, $class_code) {
        if(empty($class_code) || empty($blog_id)) {
            return false;
        }
        
        $where_arr = array("class_code='$class_code'", "blog_id='$blog_id'");
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        $grants_arr = $mBlogClassGrants->getGrantInfo($where_arr);

        return !empty($grants_arr) ? reset($grants_arr) : false;
    }
    
    /**********************************************************************************
     * zlei add end
     **********************************************************************************/
     
    
    /**
     * 添加班级日志关系表
     * @param array $data
     * @param int   $blog_id
     * 
     * @return int $rel_id
     */
    private function addRel($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        //初始化班级日志表数据
        $rel_data = array(
                'class_code' => $class_code,
                'blog_id'    => $blog_id
        );
        //添加班级日志关系表信息
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        
        $rel_id = $mBlogClassRelation->addRelation($rel_data, true);
        if(empty($rel_id)) {
            return false;
        }
        
        return $rel_id;
    }
    
	/**
     * 添加日志权限表
     * @param array $data
     * @param int   $blog_id
     * 
     * @return int  $id
     */
    private function addBlogClassGrants($grant, $blog_id, $class_code) {
        if((empty($grant) && $grant=='')  || empty($blog_id) || empty($class_code)) {
            return false;
        }
        //初始化班级日志权限表数据
        $grant_data = array(
                'class_code' => $class_code,
                'blog_id'    => $blog_id,
                'grant'      => $grant
        );
        //添加班级日志权限表信息
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        
        return $mBlogClassGrants->addBlogClassGrants($grant_data, true);
    }
    
    /**
     * 删除班级日志关系表信息
     * @param int $rel_id
     * 
     * @return boolean
     */
    private function delRelByRelId($rel_id) {
        if(empty($rel_id)) {
            return false;
        }
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        
        return $mBlogClassRelation->delById($rel_id);
    }
	/**
     * 删除班级日志关系表信息
     * @param int $rel_id
     * 
     * @return boolean
     */
    private function delRelByBlogId($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        $id = $this->check_rel($blog_id, $class_code);
        if(empty($id)) {
            return false;
        }
        
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        
        return $mBlogClassRelation->delById($id);
    }
    
    /**
     * 修改日志权限表
     * @param array $data
     * $param int   $blog_id
     * 
     * @return boolean
     */
    private function updGrantByBlogId($grant, $blog_id, $class_code) {
        if(empty($grant) || empty($blog_id) || empty($class_code)) {
            return false;
        }
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        //三维
        //$grant_list = $mBlogClassGrants->getListByBlogId($blog_id);
        //二维
        $grant_list = $grant_list[$blog_id];
        list($id, $grant_data) = each($grant_list);
        $grant_data['grant'] = $grant;
        
        return $mBlogClassGrants->modifyById($grant_data, $id);
    }
    /**
     * 根据日志ID删除班级日志权限表
     * @param int $blog_id
     * 
     * @return boolean
     */
    private function delGrantByBlogId($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        $id = $this->check_grant($blog_id, $class_code);
        if(empty($id)) {
            return false;
        }
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        
        return $mBlogClassGrants->delBlogClassGrants($id);
    } 
    
	/**
     * 通过日志ID获取日志权限id
     * 
     * @param array $blog_id
     * 
     * @return array $grant_ids
     */
    private function getGrantListByBlogId($blog_id, $class_code) {
        if(empty($blog_id) || $class_code) {
            return false;
        }
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        //三维
        //$grant_list = $mBlogClassGrants->getListByBlogId($blog_id);
        
        return !empty($grant_list) ? $grant_list : false;
    }
    
	/**
     * 通过blog_list获取权限信息
     * @param array $blog_list
     *
     * @return array $blog_list
     */
    private function getGrantListByBlogList($blog_list) {
        if(empty($blog_list)) {
            return false;   
        }
        $blog_ids = array_keys($blog_list);
        
        if(empty($blog_ids)) {
            return false;
        }
        //三维
        $grant_list = $this->getGrantListByBlogId($blog_ids);
        
        //获取权限名称grant_name
        foreach($blog_list as $blog_id=>$blog_val) {
            $grant_info = reset($grant_list[$blog_id]);
            $grant_id = $grant_info['grant'];
            $grant_name = $this->getGrantsList($grant_id);
            $blog_val['grant_id'] = $grant_id;
            $blog_val['grant_name'] = $grant_name;
            $blog_list[$blog_id] = $blog_val;
            unset($grant_list[$blog_id]);
        }
        
        return !empty($blog_list) ? $blog_list : false;
    }
   
    /**
     * 通过日志班级关系获取班级日志ID
     * 
     * @param array 日志班级关系表数组
     * 
     * @return array 日志ID数组
     */
    private function getBlogIdsByRel($rel_list) {
        if(empty($rel_list)) {
            return false;
        }
        $blog_ids = array();
        foreach($rel_list as $id=>$rel_val) {
            $blog_ids[$rel_val['blog_id']] = $rel_val['blog_id'];
            unset($rel_list[$id]);
        }
        
        return !empty($blog_ids) ? $blog_ids : false;
    }
    
    
    
    /**
     * 获取班级日志权限列表
     * 
     * @param int $grant_id 
     * 
     * @return array $prants_list
     */
    private function getGrantsList($grant_id) {
        $grant_info = array(
            0=>'公开',
            1=>'本班',
            2=>'本学校'
        );
        if(!empty($grant_id)) {
            return !empty($grant_info[$grant_id]) ? $grant_info[$grant_id] : false;
        }
        
        return $grant_info;
    }
    
    /**
     * 班级日志关系验证
     * @param int $blog_id
     * @param int $class_code
     * 
     * @return int $rel_id
     */
    private function check_rel($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        $wherearr = array(
            'blog_id' => $class_code,
            'class_code' => $blog_id
        );
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        //二维
        $relInfo = $mBlogClassRelation->getRelInfo($wherearr);
        if(empty($relInfo)) {
            return false;
        }
        
        return key($relInfo);
    }
    
    /**
     * 班级日志权限验证
     * @param int $blog_id
     * @param int $class_code
     * 
     * @return int $id 班级日志权限表的id
     */
    private function check_grant($blog_id, $class_code) {
        if(empty($blog_id) || empty($class_code)) {
            return false;
        }
        
        $wherearr = array(
            'blog_id' => $blog_id,
            'class_code' => $class_code
        );
        
        $mBlogClassGrants = ClsFactory::Create('Model.Blog.mBlogClassGrants');
        //二维
        $grantInfo = $mBlogClassGrants->getGrantInfo($wherearr);
        if(empty($grantInfo)) {
            return false;
        }
        
        return key($grantInfo);
    }
    
    /**
     * 班级日志类型验证
     * @param int $type_id
     * @param int $class_code
     * 
     * @return int $id
     */
    private function check_type($type_id, $class_code) {
        if(empty($type_id) || empty($class_code)) {
            return false;
        }
        
        $wherearr = array(
            'type_id' => $type_id,
            'class_code' => $class_code
        );
        $this->_initmBlogTypes();
        
        $typeInfo = $this->_mBlogTypes->getTypeInfo($wherearr);
        if(empty($typeInfo)) {
            return false;
        }
        
        return key($typeInfo);
    }
    
}