<?php

abstract class BlogBase {
    /**
     * 获取日志详情包含权限，内容，分类等
     * @param $blog_ids
     * 注明：最多返回20 篇日志
     */
    abstract public function getBlogInfoById($blog_ids);
    
    abstract public function publishBlog($blog_datas, $is_return_id = false);
    
    abstract public function modifyBlog($blog_datas, $blog_id);
    
    abstract public function delBlog($blog_id);

    //日志分类管理
    abstract public function getBlogType();
    
    abstract public function publishBlogType($type_datas,  $is_return_id = false);
    
    abstract public function delBlogType($blog_id);
    
    /**
     * 处理日志图片
     * 1 生成大中小缩略图
     * 2 将图片从临时文件夹移动到实际文件夹中
     * 
     * @param $content 要处理的内容
     * @param $max_width 最大宽度 800
     * @param $scale_arr array(100,70,50);
     */
    public function processBlogImage($content, $max_width = null, $scale_arr = null) {
        if (empty($content)) {
            return $content;
        }
        
        import('@.Common_wmw.WmwString');
        // html 实体转换成一般的html 代码
        $content = WmwString::unhtmlspecialchars($content);

        //提取日志中的所有图片
        import('@.Common_wmw.HtmlParser');
        $HtmlParser = new HtmlParser($content);
        $img_list = $HtmlParser->getElementsByTagName('img');
        
        //提取需要处理的图片并将图片移动到实际文件夹中（1 本地图片, 2 放在临时文件夹中的）
        import("@.Common_wmw.Pathmanagement_sns");
        $tmp_path  = Pathmanagement_sns::getXheditorimgPathTmp();    //临时文件夹
        $new_path = Pathmanagement_sns::getXheditorimgPath();  //绝对路径 真实路径
        $scale_list = array();
       
        import("@.Common_wmw.WmwImage");
        $scaleObj = new WmwImage();
        
        if(!empty($img_list)) {
            foreach($img_list as $img) {
                $ImgParser = HtmlParser::createTagParser('img', $img);
                $tmp_src = $ImgParser->attr('src');
                $is_remote_file = $this->isRemoteFile($tmp_src); //是否是远程文件
                if (($is_remote_file == false) && (stripos($tmp_src, $tmp_path) !== false)) {
                    $new_src = str_replace($tmp_path, $new_path, $tmp_src);
                    $old_url = WEB_ROOT_DIR . $tmp_src;
                    $new_url = WEB_ROOT_DIR . $new_src;
                    
                    $scale_list = array(
                                          array(
                                          	  'path' => $new_url,
                                          	  'scale' => 100 
                                          )
                                      );
                                    
                   $scaleObj->scale($old_url, $scale_list);
                   $content = str_replace($tmp_src, $new_src, $content); 
                }
                
            }
        }
        
        return $content;
        
        //处理图片 按比例缩放图片
        /* 对外接口函数:scale($src_img, $dst_files);
         * 参数格式说明: $src_img: 源图片文件完整路径，如:/home/src.jpg
         * 				$dst_files:
         *                         array(
         *                           	array(
         *                        			'path' => '目标图片完整路径,如:/home/test.jpg'
         *                        			'scale' => '图片缩放比列,大于0的正整数'
         *                         		),
         *                         )
         */
        if (!empty($max_width) && !empty($scale_arr)) {
            //                $img_str = $ImgParser->attr('weight', '60px')->toString();
            //                $img_str = $ImgParser->attr('alt', 'hdhhs')->toString();
            //                var_dump($ImgParser->attr('src'));
            //                
            //                dump($img_str);
        }
        
        //替换处理后的图片路径
        
        
    }
    
    /**
     * 截取日志摘要
     * @param String $content
     * @param $str_length 截取长度
     */
    public function getSummary($content, $str_length = 200) {
        if(empty($content)) {
            return false;
        }
        import('@.Common_wmw.WmwString');
        // html 实体转换成一般的html 代码
        $content = WmwString::unhtmlspecialchars($content);
                
        //去除html标签 包括 img 标签
        $content = WmwString::delhtml($content);

        //截取内容
        $content = WmwString::mbstrcut(trim($content), 0, $str_length, 1, $suffix=true);

        return $content;
    }
    
    /**
     * 截取日志第一张图片用于列表页面展示
     * @param String $content
     */
    public function getFirstImg($content) {
        import('@.Common_wmw.HtmlParser');
        $HtmlParser = new HtmlParser($content);
        $img = $HtmlParser->getElementByTagName('img');
        
        return !empty($img) ? $img : '' ;
    }
    
    
    
    /**************************************************************************************************
     * 辅助函数
     *************************************************************************************************/
    /**
     * 提取日志内容
     * @param $blog_datas
     */
    protected function extractBlogContent($blog_datas) {
        if(empty($blog_datas)) {
            return false;
        }
        
        $blog_content_datas = array(
            'blog_id' => $blog_datas['blog_id'],
            'content' => $blog_datas['content']
        );
        
        return $blog_content_datas;
    }
    
    /**
     * 提取日志的对应关系
     * 注：数据结构调整原来权限表合并到关系表
     * @param $blog_datas
     */
    abstract protected function extractBlogRelation($blog_datas);
    /**
     * 判断是否需要修改日志的对应关系 和权限
     * @param $blog_datas
     */
    abstract protected function needModifyBlogRelation($blog_datas);
    
    /**
     * 判断是否需要修改日志内容
     * @param $blog_datas
     */
    protected function needModifyBlogContent($blog_datas) {
        if(empty($blog_datas)) {
            return false;
        }
        
        return isset($blog_datas['content']) ? true : false;
    }
    
    /**
     * 判断是否需要修改实体表
     * @param $blog_datas
     */
    protected function needModifyBlogEntity($blog_datas) {
         $fields = array(
             'title',
             'type_id',
             'views',
             'is_published',
             'upd_time',
             'contentbg',
             'summary',
         	 'first_img',
             'comments',
         );
         
         foreach($fields as $field) {
             if(isset($blog_datas[$field])) {
                 return true;
             }
         }
         
         return false;
    }
    
	/**
     * 判断是否是远程文件
     */
    private function isRemoteFile($pFileName) {
        if(empty($pFileName)) {
            return false;
        }
        
        return preg_match("/^http(s)?:\/\/(.+)$/", trim($pFileName)) ? true : false;
    }
}