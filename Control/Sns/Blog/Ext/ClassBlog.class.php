<?php
import('@.Control.Sns.Blog.Ext.BlogBase');
class ClassBlog extends BlogBase {
    private $class_code = 0;
    
    public function __construct($class_code) {
        $this->class_code = $class_code;
    }
    
    /**
     * 获取用户在当前班级班级的 草稿 
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

        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        $blog_list = $mBlogClassRelation->getClassBlogByClassCode($this->class_code, $where_arr, $orderby, $offset, $limit);
        
        return !empty($blog_list) ? $blog_list : false;
    }
    
    /**
     * 获取班级日志详情包含权限，内容，分类等
     * @param $blog_ids
     *  注明：最多返回20 篇日志
     */
    public function getBlogInfoById($blog_ids, $offset = 0, $limit = 20) {
        if(empty($blog_ids)) {
            return false;
        }
        
        $in_str = implode(',', (array)$blog_ids);
        $where_arr = array(
            "blog_id in($in_str)"
        );
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        $blog_list = $mBlogClassRelation->getClassBlogByClassCode($this->class_code, $where_arr, null, $offset, $limit);
        $blog_list = & $blog_list[$this->class_code];
        $blog_ids = array_keys($blog_list);

        //获取日志内容
        $mBlogContent = ClsFactory::Create('Model.Blog.mBlogContent');
        $content_list = $mBlogContent->getBlogContentById($blog_ids);
        
        //获取日志类型
        $type_list  = $this->getBlogType();

        // 获取班级日志权限 列表
        import("@.Common_wmw.Constancearr");
        
        //数据组装
        $new_blog_list = array();
        foreach ($blog_list as $blog_id=>$blog_info) {
            $blog_info['content'] = $content_list[$blog_id]['content'];
            $blog_info['type_name']  = $type_list[$blog_info['type_id']]['name'];
            $blog_info['grant']      = $blog_info['grant'];
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

        //班级和日志的关系表
        $blog_class_relation_datas = $this->extractBlogRelation($blog_datas);
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        if(!$mBlogClassRelation->addBlogClassRelation($blog_class_relation_datas, true)) {
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
        
        //修改的班级日志关系表 主要是权限修改
        if($this->needModifyBlogRelation($blog_datas)) {
            //个人和日志的关系表 包含权限
            $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
            $where = array(
                'class_code='. $this->class_code,
                "blog_id='$blog_id'"
            );
            $relation_info = $mBlogClassRelation->getBlogClassRelationInfo($where, null, 0, 1); 
            $relation_info = reset($relation_info);

            $mBlogClassRelation->modifyBlogClassRelation($blog_datas, $relation_info['id']);

        }
        
        return true;
    }
    
    /**
     * 删除班级日志 
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

        //删除班级和日志的关系表
        $mBlogClassRelation = ClsFactory::Create('Model.Blog.mBlogClassRelation');
        if(!$mBlogClassRelation->delBlogClassRelationByBlogId($blog_id)) {
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
     * 获取班级日志类型
     */
    public function getBlogType() {
        if(empty($this->class_code)) {
            return false;
        }
        
        $mBlogTypeClassRelation = ClsFactory::Create('Model.Blog.mBlogClassType');
        $class_type_list = $mBlogTypeClassRelation->getBlogClassTypeByClassCode($this->class_code);
        $class_type_list = & $class_type_list[$this->class_code];
        if (!empty($class_type_list)) {
            foreach ($class_type_list as $key=>$type_relation) {
                $type_ids[] = $type_relation['type_id'];
            }
            $mBlogTypes = ClsFactory::Create('Model.Blog.mBlogTypes');
            $type_list  = $mBlogTypes->getByTypeId(array_unique($type_ids));
        }
        //添加上默认班级分类
        $type_list[0] = array('type_id'=>0, 'name'=>'班级日志');
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
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        $is_succeed = $mBlogClassType->addBlogClassType($blog_type_relation_datas);
        if (empty($is_succeed)) {
            return false;
        }
        
        return !empty($is_return_id) ? $type_id : true;
    }
    
    /**
     * 删除班级日志分类
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

        //删除日志分类与班级关系表
        $mBlogClassType = ClsFactory::Create('Model.Blog.mBlogClassType');
        $class_type_relations = $mBlogClassType->getBlogClassTypeByTypeId($type_id);
        $class_type_relations = $class_type_relations[$type_id];
        if (!empty($class_type_relations)) {
            $relation_ids = array_keys($class_type_relations);
            $del_realtion_success = $mBlogClassType->delById($relation_ids);
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
     * 班级日志添加修改辅助函数
     **************************************************************************************/
    protected function extractBlogRelation($blog_datas) {
        if(empty($blog_datas)) {
            return false;
        }
        
        $blog_class_relation = array(
            'class_code' => $this->class_code,
            'grant' => $blog_datas['grant'],
        	'blog_id' => $blog_datas['blog_id'],
        );
        
        return $blog_class_relation;
    }
    
    
    /*****************************************************************************************
     * 日志分类修改辅助函数
     * ***************************************************************************************/
     protected function needModifyBlogRelation($blog_datas) {
         if(empty($blog_datas)) {
             return false;
         }
         $fields = array(
            'class_code',
            'grant',
         	'blog_id',
         );
         
         foreach($fields as $field) {
             if(isset($blog_datas[$field])) {
                 return true;
             }
         }
         
         return false;
     }
    
    /*************************************************************************************
     * 日志分类  添加修改辅助函数
     **************************************************************************************/
    protected function extractBlogTypeRelation($datas) {
        if(empty($datas)) {
            return false;
        }
        
        $blog_type_class_relation = array(
            'class_code' => $this->class_code,
            'type_id' => $datas['type_id'],
        );
        
        return $blog_type_class_relation;
    }
}