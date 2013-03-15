<?php
/**
 * 日志分类管理控制器
 */
class TypeAction extends SnsController {
    public function __construct() {
        parent::__construct();
    }
    
    public function _initBlogObj($class_code) {
        static $BlogObj = false;
        
        if (empty($BlogObj)) {
            import('@.Control.Sns.Blog.Ext.ClassBlog');
            $BlogObj = new ClassBlog($class_code);
        }
        return $BlogObj;
    }
    
    /**
     * 日志分类管理
     */
    public function index() {
        $class_code = $this->objInput->getInt('class_code');
        $class_code = $this->checkoutClassCode($class_code);
        
        //不属于班级跳转到个人空间首页
        if(empty($class_code)) {
            $this->showSuccess('您不属于任何班级！', '/Sns/Person/index');
        }
        
        $this->assign('class_code', $class_code);
        
        $this->display("class_type");
    }
    
    /**
     * ajax 获取日志分类列表和每个分类下的日志数目
     */
    public function getBlogTypeListAjax() {
        $class_code = $this->objInput->getInt('class_code');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '班级信息不存在！', -1, 'json');
        }
        
        //获取班级的分类列表
        $BlogObj = $this->_initBlogObj($class_code);
        $type_list = $BlogObj->getBlogType($class_code);
        $type_list[0] = array('type_id'=>'0', 'name'=>'班级日志');
        ksort($type_list,SORT_NUMERIC);
         
        //统计分类下的日志数量
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        $nums_list = $mBlogClassType->getBlogNumsByClassCode($class_code);

        //数据整理便于前台显示
        foreach ($type_list as $type_id=>$class_type_info) {
            $class_type_info['nums'] = !empty($nums_list[$type_id]['nums']) ? $nums_list[$type_id]['nums'] : 0;
  
            $class_type_list[$type_id] = $class_type_info;
        }
        
        $this->ajaxReturn($class_type_list, '获取成功', 1, 'JSON');
    }
    
    
    /**
     * ajax 添加日志分类 
     */
    public function publishAjax() {
        $class_code = $this->objInput->postInt('class_code');
        $name = $this->objInput->postStr('name');
        
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '您不属于任何班级', -1, 'JSON'); 
        }
        
        if (empty($name)) {
             $this->ajaxReturn(null, '分类名称不能为空', -1, 'JSON'); 
        }
        import("@.Common_wmw.WmwString");
        if(WmwString::mbstrlen($name, 2) > 12) {
            $this->ajaxReturn(null, '分类名称长度不能超过12个字母/6个汉', -1, 'JSON'); 
        }
        
        $blogObj = $this->_initBlogObj($class_code);
        $class_type_list = $blogObj->getBlogType();
        if (!empty($class_type_list)) {
            foreach ($class_type_list as $type_id=>$type_info) {
                if ($name == $type_info['name'] || $name == '班级日志') {
                    $this->ajaxReturn(null, "分类 \"$name\"已经存在，请不要重复添加", -1, 'JSON'); 
                    break;
                }
            }
        }
        
        $data = array(
            'name' => $name,
            'add_time' => time(),
            'add_account' => $this->user['client_account']
        );
        
        $type_id = $blogObj->publishBlogType($data, true); 
        if(empty($type_id)) {
            $this->ajaxReturn(null, '系统繁忙稍后重试', -1, 'JSON'); 
        }
        
        $this->ajaxReturn(array('name' => $name, 'type_id' => $type_id), '添加成功', 1, 'JSON');
    }
    
    /**
     * ajax 删除日志分类 删除后
     * 日志分类默认为班级分类 即分类id 为0
     */
    public function deleteTypeAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $type_id    = $this->objInput->getInt('type_id');
        
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '您不属于任何班级！', -1, 'JSON');
        }
        
        //判断权限是否可以删除
        $can_del = $this->can_del($class_code, $type_id);
        if (empty($can_del)) {
            $this->ajaxReturn(null, '您暂时没有权限删除该分类！', -1, 'JSON');
        }
    
        //删除分类
        $BlogObj = $this->_initBlogObj($class_code);
        $is_success = $BlogObj->delBlogType($type_id);
        
        if(empty($is_success)) {
            $this->ajaxReturn(null, '你要删除的信息不存在或者已被删除！', -1, 'JSON');
        }
        
        $this->ajaxReturn(null, '删除成功！', 1, 'JSON');
    }

    /**
     * 修改日志分类名称
     */
    public function modlfyTypeAjax() {
        
        $class_code = $this->objInput->getInt('class_code');
        $type_id    = $this->objInput->postInt('type_id');
        $name       = $this->objInput->postStr('name');
        
        $class_code = $this->checkoutClassCode($class_code);
        if (empty($class_code)) {
            $this->ajaxReturn(null, '您不属于任何班级！', -1, 'JSON');
        }
        
        if (empty($name)) {
             $this->ajaxReturn(null, '分类名称不能为空', -1, 'JSON'); 
        }
        import("@.Common_wmw.WmwString");
        if(WmwString::mbstrlen($name, 2) > 12) {
            $this->ajaxReturn(null, '分类名称长度不能超过12个字母/6个汉', -1, 'JSON'); 
        }
        
        //验证权限
        $can_modify = $this->can_modify($class_code, $type_id);
        if (empty($can_modify)) {
            $this->ajaxReturn(null, '您暂时没有权限修改', -1, 'JSON');
        }
        $modify_data = array(
            'name' => $name
        );
        
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $is_success = $mBlogTypes->modifyBlogTypes($modify_data, $type_id);
        
        if (empty($is_success)) {
             $this->ajaxReturn(null, '系统繁忙请稍后重试！', -1, 'JSON');
        }
        
         $this->ajaxReturn(null, '修改成功', 1, 'JSON');
    }
    
    
    
    
    
    
    
    
    /*********************************************************************************************************
     * 辅助方法
    **********************************************************************************************************/
    /**
     * 是否有权限删除日志分类
     * 注：1，除了家长外都有权限删除
     *     2，班主任和管理员可以删除所有本班的分类
     *  
     * @param  $class_code
     * @param  $type_id
     */
    private function can_del($class_code, $type_id) {
        if(empty($class_code) || empty($type_id)) {
            return false;
        }
        
        $client_class = $this->user['client_class'][$class_code];
        //是否是家长
        if (empty($client_class) || $client_class['client_type'] == CLIENT_TYPE_FAMILY) {
            return false;
        }
        //判断是否是班级人和管理员
        if (in_array($client_class['teacher_class_role'], array(TEACHER_CLASS_ROLE_CLASSADMIN, TEACHER_CLASS_ROLE_CLASSBOTH))) {
            return true;
        }
        //判断是否是自己添加
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_info = $mBlogTypes->getByTypeId($type_id);
        $add_account = $type_info[$type_id]['add_account'];
        
        return ($this->user['client_account'] == $add_account) ? true : false;
    }
    
    /**
     * 是否有权限修改日志分类
     * 注：1，除了家长外都有权限修改
     *     2，班主任和管理员可以修改所有本班的分类
     *  
     * @param  $class_code
     * @param  $type_id
     */
    private function can_modify($class_code, $type_id) {
        if(empty($class_code) || empty($type_id)) {
            return false;
        }
        
        $client_class = $this->user['client_class'][$class_code];
        //是否是家长
        if (empty($client_class) || $client_class['client_type'] == CLIENT_TYPE_FAMILY) {
            return false;
        }
        //判断是否是班级人和管理员
        if (in_array($client_class['teacher_class_role'], array(TEACHER_CLASS_ROLE_CLASSADMIN, TEACHER_CLASS_ROLE_CLASSBOTH))) {
            return true;
        }
        //判断是否是自己添加
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_info = $mBlogTypes->getByTypeId($type_id);
        $add_account = $type_info[$type_id]['add_account'];
        
        return ($this->user['client_account'] == $add_account) ? true : false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}