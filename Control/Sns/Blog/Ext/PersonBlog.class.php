<?php
import('@.Control.Sns.Blog.Ext.BlogBase');
class PersonBlog extends BlogBase {
    private $client_account = 0;
    
    public function __construct($client_account) {
        $this->client_account = $client_account;
    }
    
    /**
     * 获取用户在当前个人个人的 草稿 
     * @param $offset
     * @param $limit
     * @return $draft_list
     */
    public function getBlogList($where_arr, $orderby=null, $offset = 0, $limit = 20) {
        if(empty($where_arr)) {
            return false;
        }
        
//        $where_arr = array(
//            "add_account='$client_account'",
//            "is_published=0"  //0 草稿 1 发布
//        );

        $mBlogPersonRelation = ClsFactory::Create('Model.Blog.mBlogPersonRelation');
        $blog_list = $mBlogPersonRelation->getPersonBlogByPersonCode($this->client_account, $where_arr, $orderby, $offset, $limit);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    /**
     * 获取个人日志详情包含权限，内容，分类等
     * @param $blog_ids
     *  注明：默认直取一条日志
     */
    public function getBlogInfoById($blog_ids, $offset = 0, $limit = 1) {
        if(empty($blog_ids)) {
            return false;
        }
        
        $in_str = implode(',', (array)$blog_ids);
        $where_arr = array(
            "blog_id in($in_str)"
        );
        $mBlogPersonRelation = ClsFactory::Create('Model.Blog.mBlogPersonRelation');
        $blog_list = $mBlogPersonRelation->getPersonBlogByUid($this->client_account, $where_arr, null, $offset, $limit);
        $blog_list = & $blog_list[$this->client_account];
        $blog_ids = array_keys($blog_list);

        //获取日志内容
        $mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
        $content_list = $mBlogContent->getBlogContentById($blog_ids);
        
        //获取日志类型
        $type_list  = $this->getBlogType();

        // 获取个人日志权限 列表
        import("@.Common_wmw.Constancearr");
        $blog_grant = Constancearr::get_blog_person_grant();
        $where_arr = array(
            "client_account='$this->client_account'",
            "blog_id in($in_str)"
        );
        $mBlogPersonGrants = ClsFactory::Create('Model.Blog.mBlogPersonGrants');
        $grant_list = $mBlogPersonGrants->getGrantInfo($where_arr, "blog_id");

        $new_grant_list = array();
        foreach ($grant_list as $key=>$grant) {
            $new_grant_list[$grant['blog_id']] = $grant;
        }
        
        //数据组装
        $new_blog_list = array();
        foreach ($blog_list as $blog_id=>$blog_info) {
            $blog_info['content'] = $content_list[$blog_id]['content'];
            $blog_info['type_name']    = $type_list[$blog_info['type_id']]['name'];
            $blog_info['grant']      = $new_grant_list[$blog_id]['grant'];
            $blog_info['grant_name'] = Constancearr::get_blog_class_grant($blog_info['grant']);
            
            $new_blog_list[$blog_id] = $blog_info;
        }
        
        return !empty($new_blog_list) ? $new_blog_list : false;
    }
    
    /**
     * 发表个人日志
     * @param $blog_datas
     *   title
     *   content
     *   type_id
     *   views
     *   is_published
     *   add_account
     *   add_time
     *   upd_account
     *   upd_time
     *   contentbg
     *   summary
     *   comments
     *   grant
     */
    public function publishBlog($blog_datas, $is_return_id = false) {
        if(empty($blog_datas)) {
            return false;
        }
        
        //实体表的数据保存
        $mBlog = ClsFactory::Create('Model.Blog.mBlog');
        $blog_id = $mBlog->addBlog($blog_datas, true);
        if(empty($blog_id)) {
            return false;
        }
        
        $blog_datas['blog_id'] = $blog_id;
        //保存日志内容
        $blog_content_datas = $this->extractBlogContent($blog_datas);
        $mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
        if(!$mBlogContent->addContent($blog_content_datas, true)) {
            return false;
        }

        //权限表的数据保存
        $blog_class_grants_datas = $this->extractBlogGrant($blog_datas);
        $mBlogPersonGrants = ClsFactory::Create('Model.Blog.mBlogPersonGrants');
        if(!$mBlogPersonGrants->addBlogPersonGrants($blog_class_grants_datas)) {
            return false;
        }
        
        //个人和日志的关系表
        $blog_class_relation_datas = $this->extractBlogRelation($blog_datas);
        $mBlogPersonRelation = ClsFactory::Create('Model.Blog.mBlogPersonRelation');
        if(!$mBlogPersonRelation->addBlogPersonRelation($blog_class_relation_datas, true)) {
            return false;
        }

        return !empty($is_return_id) ? $blog_id : true;
    }

    /**
     * 修改日志
     * @param $blog_datas
     * @param $blog_id
     */
    public function modifyBlog($blog_datas, $blog_id) {
        if(empty($blog_datas) || empty($blog_id) || !is_array($blog_datas)) {
            return false;
        }
        
        //修改涉及到的表: 权限表，日志内容表，日志基本信息表
        if($this->needModifyBlogEntity($blog_datas)) {
            $mBlog = ClsFactory::Create('Model.Blog.mBlog');
            $mBlog->modifyBlog($blog_datas, $blog_id);
        }
        //保存日志内容
        if($this->needModifyBlogContent($blog_datas)) {
            $mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
            $mBlogContent->modifyBlogContent($blog_datas, $blog_id);
        }
        //日志的关系表示不会涉及到修改的
        if($this->needModifyBlogGrants($blog_datas)) {
            $mBlogPersonGrants = ClsFactory::Create('Model.Blog.mBlogPersonGrants');
            $where_arr = array(
                "blog_id='$blog_id'",
                "class_code='$this->client_account'"
            );
            
            $mBlogPersonGrants->modifyBlogPersonGrantByWhere($blog_datas, $where_arr);
        }
        
        return true;
    }
    
    /**
     * 删除个人日志 
     * 
     */
    public function delBlog($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
     
        //删除 日志内容表
        $mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
        $content_del = $mBlogContent->delByBlogId($blog_id);

        //删除日志评论表
        $mBlogComments = ClsFactory::Create('Model.Blog.mBlogComments');
        $comment_del = $mBlogComments->delAllByBlogId($blog_id);

        //删除个人日志权限表
        $mBlogPersonGrants = ClsFactory::Create('Model.Blog.mBlogPersonGrants');
        $grants_del = $mBlogPersonGrants->delGrantByBlogId($blog_id);

        //删除个人和日志的关系表
        $mBlogPersonRelation = ClsFactory::Create('Model.Blog.mBlogPersonRelation');
        if(!$mBlogPersonRelation->delBlogPersonRelationByBlogId($blog_id)) {
            return false;
        }
        
        //删除日志详细信息表
        $mBlog = ClsFactory::Create('Model.Blog.mBlog');
        $blog_del = $mBlog->delBlog($blog_id);
        if (empty($blog_del)) {
            return false;
        }

        return true;
    }
    
    /******************************************************************************************
     * 日志类型  管理
     *****************************************************************************************/
    
    /**
     * 获取个人日志类型
     */
    public function getBlogType() {
        if(empty($this->client_account)) {
            return false;
        }
        
        $mBlogTypePersonRelation = ClsFactory::Create('Model.Blog.mBlogPersonType');
        $class_type_list = $mBlogTypePersonRelation->getBlogPersonTypeByUid($this->client_account);
        $class_type_list = & $class_type_list[$this->client_account];
        if (!empty($class_type_list)) {
            foreach ($class_type_list as $key=>$type_relation) {
                $type_ids[] = $type_relation['type_id'];
            }
            $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
            $type_list  = $mBlogTypes->getByTypeId(array_unique($type_ids));
        }
        //添加上默认个人分类
        $type_list[0] = array('type_id'=>0, 'name'=>'个人日志');
        ksort($type_list);
        
        return !empty($type_list) ? $type_list : false;
    }
    
    /**
     * 添加日志分类
     * 
     * @param array $type_datas
     * 数组详细
     * array(
     *     name,
     *     add_account,
     *     add_time,
     * );
     * @param boolean $is_return_id
     */
    public function publishBlogType($type_datas,  $is_return_id = false) {
        if(empty($type_datas)) {
            return false;
        }
        
        //保存实体表 blog_types
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $type_id = $mBlogTypes->addType($type_datas, true);
        if(empty($type_id)) {
            return false;
        }
        
        //数组追加上日志类型
        $type_datas['type_id'] = $type_id;
        //保存关系表
        $blog_type_relation_datas = $this->extractBlogTypeRelation($type_datas);
        $mBlogPersonType = ClsFactory::Create('Model.Blog.mBlogPersonType');
        $is_succeed = $mBlogPersonType->addBlogPersonType($blog_type_relation_datas);
        if (empty($is_succeed)) {
            return false;
        }
        
        return !empty($is_return_id) ? $type_id : true;
    }
    
    public function modifyBlogType($blog_datas, $blog_id) {
    
    }
    
    /**
     * 删除个人日志分类
     * @param unknown_type $blog_id
     */
    public function delBlogType($type_id) {
        //更新日志表
        $mBlog = ClsFactory::Create('Model.Blog.mBlog');
        $blog_list = $mBlog->getBlogByTypeId($type_id);
        $blog_list = $blog_list[$type_id];
        if (!empty($blog_list)) {
            $blog_ids = array_keys($blog_list);
            $modify_data = array(
                'type_id' => 0
            );
            
            //循环修改
            $modify_success = true;
            foreach ($blog_ids as $blog_id) {
                if ($blog_list[$blog_id]['type_id'] == 0) {
                    continue;
                }
                $tem_success = $mBlog->modifyBlog($modify_data, $blog_id);
                if (empty($tem_success)) {
                    $modify_success = false;
                }
            }
            
            if (empty($modify_success)) {
                return false;
            }
        }

        //删除日志分类与个人关系表
        $mBlogPersonType = ClsFactory::Create('Model.Blog.mBlogPersonType');
        $class_type_relations = $mBlogPersonType->getBlogPersonTypeByTypeId($type_id);
        $class_type_relations = $class_type_relations[$type_id];
        if (!empty($class_type_relations)) {
            $relation_ids = array_keys($class_type_relations);
            $del_realtion_success = $mBlogPersonType->delById($relation_ids);
            if (empty($del_realtion_success)) {
                return false;
            }
        }

        
        //删除日志分类实体
        $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
        $del_type_success = $mBlogTypes->delBlogTypes($type_id); 
        
        return !empty($del_type_success) ? true : false;
    }
    
    
    
    
    
    
    
    
    
    /*************************************************************************************
     * 个人日志添加修改辅助函数
     **************************************************************************************/
    protected function extractBlogRelation($blog_datas) {
        if(empty($blog_datas)) {
            return false;
        }
        
        $blog_person_relation = array(
            'class_code' => $this->client_account,
            'blog_id' => $blog_datas['blog_id'],
        );
        
        return $blog_person_relation;
    }
    
    
    protected function extractBlogGrant($blog_datas) {
        if(empty($blog_datas)) {
            return false;
        }
        
        $blog_person_grants_datas = array(
            'blog_id' => $blog_datas['blog_id'],
            'class_code' => $this->client_account,
            'grant' => $blog_datas['grant'],
        );
        
        return $blog_person_grants_datas;
    }
    
    
    /*************************************************************************************
     * 日志分类  添加修改辅助函数
     **************************************************************************************/
    protected function extractBlogTypeRelation($datas) {
        if(empty($datas)) {
            return false;
        }
        
        $blog_type_class_relation = array(
            'class_code' => $this->client_account,
            'type_id' => $datas['type_id'],
        );
        
        return $blog_type_class_relation;
    }
}