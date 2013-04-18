<?php
/**
 * 日志分类管理控制器
 */
class PersonTypeAction extends SnsController {
    public function __construct() {
        parent::__construct();
    }
    
    public function _initBlogObj($client_account) {
        static $BlogObj = false;
        
        if (empty($BlogObj)) {
            import('@.Control.Sns.Blog.Ext.PersonBlog');
            $BlogObj = new PersonBlog($client_account);
        }
        
        return $BlogObj;
    }
    
    /**
     * 日志分类管理
     * 注： 个人日志分类只能自己管理
     */
    public function index() {

        $this->assign('client_account', $this->user['client_account']);
        
        $this->display("person_type");
    }
    
    /**
     * ajax 获取日志分类列表和每个分类下的日志数目
     */
    public function getBlogTypeListAjax() {
        $client_account = $this->objInput->getInt('client_account');
        
        //获取个人的分类列表
        $BlogObj = $this->_initBlogObj($client_account);
        $type_list = $BlogObj->getBlogType($client_account);
        $type_list[0] = array('type_id'=>'0', 'name'=>'个人日志');
        ksort($type_list,SORT_NUMERIC);
         
        //统计分类下的日志数量
        $grant_where = $this->getSelectGrant($client_account);
        $mBlogPersonType = ClsFactory::Create('Model.Blog.mBlogPersonType');
        $nums_list = $mBlogPersonType->getBlogNumsByUid($client_account, $grant_where);

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
        $name = $this->objInput->postStr('name');
        if (empty($name)) {
             $this->ajaxReturn(null, '分类名称不能为空', -1, 'JSON'); 
        }
        
        $client_account = $this->user['client_account'];
        import("@.Common_wmw.WmwString");
        if(WmwString::mbstrlen($name, 2) > 12) {
            $this->ajaxReturn(null, '分类名称长度不能超过12个字母/6个汉字', -1, 'JSON'); 
        }
        
        $blogObj = $this->_initBlogObj($client_account);
        $person_type_list = $blogObj->getBlogType();
        //dump($person_type_list);dump($name);exit;
        if (!empty($person_type_list)) {
            foreach ($person_type_list as $type_id=>$type_info) {
                if ($name == $type_info['name'] || $name == '个人日志') {
                    $this->ajaxReturn(null, "分类 \"$name\"已经存在，请不要重复添加", -1, 'JSON'); 
                    break;
                }
            }
        }
        
        $data = array(
            'name' => $name,
            'add_time' => time(),
            'add_account' => $client_account
        );
        
        $type_id = $blogObj->publishBlogType($data, true); 
        if(empty($type_id)) {
            $this->ajaxReturn(null, '系统繁忙稍后重试', -1, 'JSON'); 
        }
        
        $this->ajaxReturn(array('name' => $name, 'type_id' => $type_id), '添加成功', 1, 'JSON');
    }
    
    /**
     * ajax 删除日志分类 删除后
     * 日志分类默认为个人分类 即分类id 为0
     */
    public function deleteTypeAjax() {
        $type_id = $this->objInput->getInt('type_id');
        
        $client_account = $this->user['client_account'];
        //判断权限是否可以删除
        $can_del = $this->can_del($type_id);
        if (empty($can_del)) {
            $this->ajaxReturn(null, '您暂时没有权限删除该分类！', -1, 'JSON');
        }
    
        //删除分类
        $BlogObj = $this->_initBlogObj($client_account);
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
        $type_id    = $this->objInput->postInt('type_id');
        $name       = $this->objInput->postStr('name');

        if (empty($name)) {
             $this->ajaxReturn(null, '分类名称不能为空', -1, 'JSON'); 
        }
        import("@.Common_wmw.WmwString");
        if(WmwString::mbstrlen($name, 2) > 12) {
            $this->ajaxReturn(null, '分类名称长度不能超过12个字母/6个汉字', -1, 'JSON'); 
        }
        
        $client_account = $this->user['client_account'];
        $blogObj = $this->_initBlogObj($client_account);
        $person_type_list = $blogObj->getBlogType();
        if (!empty($person_type_list)) {
            foreach ($person_type_list as $type_info) {
                if ($name == $type_info['name'] || $name == '个人日志') {
                    $this->ajaxReturn(null, "分类 \"$name\"已经存在，请不要重复添加", -1, 'JSON'); 
                }
            }
        }
        
        //验证权限
        $can_modify = $this->can_modify($type_id);
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
     * 注：1，只能删除自己添加的分类
     *     
     * @param  $type_id
     */
    private function can_del($type_id) {
        if(empty($type_id)) {
            return false;
        }
        
        //判断是否是自己添加
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_info = $mBlogTypes->getByTypeId($type_id);
        $add_account = $type_info[$type_id]['add_account'];
        
        return ($this->user['client_account'] == $add_account) ? true : false;
    }
    
    /**
     * 是否有权限修改日志分类
     * 注：只能修改自己添加的分类
     *  
     * @param  $client_account
     * @param  $type_id
     */
    private function can_modify($type_id) {
        if(empty($type_id)) {
            return false;
        }
        
        //判断是否是自己添加
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_info = $mBlogTypes->getByTypeId($type_id);
        $add_account = $type_info[$type_id]['add_account'];
        
        return ($this->user['client_account'] == $add_account) ? true : false;
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