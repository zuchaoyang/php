<?php
class AddResource {
    public function __construct() {
        C(include_once WEB_ROOT_DIR . '/Config/Resource/config.php');
    }
 	/**
     * 批量插入资源信息
     * 注明: 
     * 1. api导入数据的是否最多每次 200多余的数据自动截取,
     * 2. 可以需要回执数据，为插入的资源id号
     * @param $dataarr
     */
    public function addResourceBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $dataarr = $this->bindDynamicAttrs($dataarr);
        $this->buildNavs($dataarr);
        
        $dataarr = $this->appendResourceId($dataarr);
        $dataarr = $this->packDatas($dataarr);
        $this->importResourceToDatas($dataarr);
        
        return $this->extractResourceInfo($dataarr);
    }
    
    /**
     * 批量导入章节信息
     * @param $dataarr
     */
    public function addDynamicAttrsBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $dataarr = $this->bindDynamicAttrs($dataarr);
        $this->buildNavs($dataarr);
        
        return true;
    }
    
    
    /**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 资源导入Api辅助函数开始
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    /**
     * 获取导入成功的资源id和失败的资源列表信息
     * @param $dataarr
     */
    private function extractResourceInfo($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $resource_ids = $import_resource_list = array();
        foreach($dataarr as $datas) {
            $resource_id = $datas['resource_id'];
            
            $resource_ids[] = $resource_id;
            $import_resource_list[$resource_id] = $datas;
        }
        
        //检测数据是否导入成功
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        $exist_resource_list = $mResourceInfo->getResourceInfoById($resource_ids);
        
        $success_resource_ids = array_keys($exist_resource_list);
        $fail_resource_list = array_diff_key((array)$import_resource_list, (array)$exist_resource_list);
        
        return array($success_resource_ids, $fail_resource_list);
    }
    
    /**
     * 绑定章节动态属性
     */
    private function bindDynamicAttrs($dataarr) {
        $dataarr = $this->parseChapter($dataarr);
        $dataarr = $this->parseSection($dataarr);
        
        return $dataarr;
    }
    
    /**
     * 通过名字获取章节的动态属性
     * 注明：
     * 1. 对章相应的字符串做了特殊字符的替换
     */
    private function parseChapter($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        $total_md5_key = $add_chapter_datas = array();
        foreach($dataarr as $key=>$datas) {
            if(empty($datas['chapter_name'])) {
                continue;
            }
            //对章字符串进行预处理
            $datas['chapter_name'] = $this->replaceSpecialChars($datas['chapter_name']);
            $datas['display_order'] = intval($datas['display_order']);
            
            //通过字符串加密，来保证章名字和显示顺序之间的一一映射
            $md5_key = $this->getMd5key($datas['chapter_name'], $datas['display_order']);
            $add_chapter_datas[$md5_key] = array(
                'chapter_name' => $datas['chapter_name'],
                'display_order' => $datas['display_order'],
                'md5_key' => $md5_key,
                'add_time' => time(),
            );
            
            $total_md5_key[] = $md5_key;
            $dataarr[$key] = $datas;
        }
        
        $mResourceChapter = ClsFactory::Create('Model.Resource.mResourceChapter');
        $chapter_list = $mResourceChapter->getResourceChapterByMd5key($total_md5_key);
        
        //检测数据库中相应的记录是否都已经添加
        foreach($add_chapter_datas as $md5_key=>$chapter) {
            if(isset($chapter_list[$md5_key])) {
                unset($add_chapter_datas[$md5_key]);
            }
        }
        
        if(!empty($add_chapter_datas)) {
            $mResourceChapter->addResourceChapterBat($add_chapter_datas);
            $chapter_list = $mResourceChapter->getResourceChapterByMd5key($total_md5_key);
        }
        
        //将章信息绑定到数据上
        if(!empty($dataarr) && !empty($chapter_list)) {
            foreach($dataarr as $key=>$datas) {
                if(empty($datas['chapter_name'])) {
                    $datas['chapter_id'] = 0;
                } else {
                    $md5_key = $this->getMd5key($datas['chapter_name'], $datas['display_order']);
                    $datas['chapter_id'] = intval($chapter_list[$md5_key]['chapter_id']);
                }
                
                $dataarr[$key] = $datas;
            }
        }
        
        return !empty($dataarr) ? $dataarr : false;
    }
    
   /**
     * 通过名字获取节的动态属性
     */
    private function parseSection($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        $total_md5_key = $add_section_datas = array();
        foreach($dataarr as $key=>$datas) {
            if(empty($datas['section_name'])) {
                continue;
            }
            
            $datas['section_name'] = $this->replaceSpecialChars($datas['section_name']);
            $datas['display_order'] = intval($datas['display_order']);
            
            $md5_key = $this->getMd5key($datas['section_name'], $datas['display_order']);
            $add_section_datas[$md5_key] = array(
                'section_name' => $datas['section_name'],
                'display_order' => $datas['display_order'],
                'md5_key' => $md5_key,
                'add_time' => time(),
            );
            
            $total_md5_key[] = $md5_key;
            $dataarr[$key] = $datas;
        }
        
        $mResourceSection = ClsFactory::Create('Model.Resource.mResourceSection');
        $section_list = $mResourceSection->getResourceSectionByMd5key($total_md5_key);
        
        //检测数据库中相应的记录是否都已经添加
        foreach($add_section_datas as $md5_key=>$chapter) {
            if(isset($section_list[$md5_key])) {
                unset($add_section_datas[$md5_key]);
            }
        }
        if(!empty($add_section_datas)) {
            $mResourceSection->addResourceSectionBat($add_section_datas);
            $section_list = $mResourceSection->getResourceSectionByMd5key($total_md5_key);
        }
        
        //将节信息绑定到数据上
        if(!empty($dataarr) && !empty($section_list)) {
            foreach($dataarr as $key=>$datas) {
                if(empty($datas['section_name'])) {
                    $datas['section_id'] = 0;
                } else {
                    $md5_key = $this->getMd5key($datas['section_name'], $datas['display_order']);
                    $datas['section_id'] = intval($section_list[$md5_key]['section_id']);
                }
                
                $dataarr[$key] = $datas;
            }
        }
        
        return !empty($dataarr) ? $dataarr : false;
    }
    
    private function getMd5key($name, $display_order) {
        return md5($name . ':' . $display_order);
    }
    
    /**
     * 数据打包，即有些字段是存放在mixed字段里面的
     * @param $datas
     */
    private function packDatas($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $resource_settings = C('resource_settings');
        foreach($dataarr as $key=>$datas) {
            $product_id = $datas['product_id'];
            $mixed = $resource_settings[$product_id]['mixed'];
            
            $mix_arr = array();
            foreach((array)$mixed as $field) {
                $mix_arr[$field] = isset($datas[$field]) ? $datas[$field] : '';
                $datas['mixed'] = @ serialize($mix_arr);
            }
            $dataarr[$key] = $datas;
        }
        
        return $dataarr;
    }
    
    /**
     * 追加资源的id号
     * @param $dataarr
     */
    private function appendResourceId($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        foreach($dataarr as $key => $datas) {
            $datas['resource_id'] = $this->getResourceId();
            $dataarr[$key] = $datas;
        }
        
        return $dataarr;
    }
    
    /**
     * 为记录添加目录结构信息
     */
    private function buildNavs($dataarr) {
        if(empty($dataarr)) {
            return false;
        }
        
        $resource_navs = C('resource_navs');
        
        $nav_values = array();
        //提取数据的nav标示信息
        foreach($dataarr as $datas) {
            $navs = array();
            foreach($resource_navs as $field) {
                $navs[$field] = intval($datas[$field]);
            }
            $nav_values[] = implode('_', $navs);
        }
        $nav_values = array_unique($nav_values);
        
        //检测数据库中是否存在相应的记录
        $mResourceNavs = ClsFactory::Create('Model.Resource.mResourceNavs');
        $nav_list = $mResourceNavs->getResourceNavsByNavValue($nav_values);
        
        $exists_nav_values = array();
        if(!empty($nav_list)) {
            foreach($nav_list as $nav) {
                $exists_nav_values[] = $nav['nav_value'];
            }
        }
        
        $diff_arr = array_diff((array)$nav_values, (array)$exists_nav_values);
        if(!empty($diff_arr)) {
            $nav_dataarr = array();
            foreach($diff_arr as $nav_val) {
                $nav_dataarr[] = array(
                    'nav_value' => $nav_val,
                    'add_time' => time(),
                );
            }
            $mResourceNavs->addResourceNavsBat($nav_dataarr);
        }
        
        return true;
    }
    
    /**
     * 将资源信息批量导入到数据库
     * @param $dataarr
     */
    private function importResourceToDatas($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
        return $mResourceInfo->addResourceInfoBat($dataarr);
    }
    
   /**
     * 获取相应的唯一的资源id号
     */
    private function getResourceId() {
        static $mResourceAutoIncrement = null;
        
        if(empty($mResourceAutoIncrement)) {
             $mResourceAutoIncrement = ClsFactory::Create('Model.Resource.mResourceAutoIncrement');
        }
        
        $test_times = 10;
        while($test_times-- > 0) {
            $resource_id = $mResourceAutoIncrement->createResourceId();
            if(!empty($resource_id)) {
                break;
            }
        }
        $resource_id = intval($resource_id);
        if(empty($resource_id)) {
            throw new Exception("资源id生成机制出错!", -1);
        }
        return $resource_id;
    }
    
    /**
     * 中英文数据差异的处理
     * @param $str
     */
    private function replaceSpecialChars($str) {
        $str = trim($str);
        if(empty($str)) {
            return false;
        }
        
        //将连续的多个空格替换成一个
        $str = preg_replace("/(\s)+/", ' ', $str);
        
        $search_arr = array(
            '“',
            "‘",
        	"？",
        	"，",
            "；",
            "！",
            "（",
            "）",
        );
        $replace_arr = array(
            "\"",
            "'",
            "?",
            ",",
            ";",
            "!",
            "(",
            ")",
        );
        
        return str_replace($search_arr, $replace_arr, $str);
    }
}