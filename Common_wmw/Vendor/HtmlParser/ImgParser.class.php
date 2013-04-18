<?php
/**
 * 图片img解析器
 * @author Administrator
 *
 */
class ImgParser {
    //基本属性
    private $standard_attrs = array(
        'src',
        'alt',
    	'align',
        'height',
    	'width',
        'id',
        'class',
        'title',
        'style',
        'usemap',
        'longdesc',
        'ismap',
    );
    //事件属性
    private $evt_attrs = array(
        'onclick',
        'ondblclick',
        'onmousedown',
        'onmouseup', 
        'onmouseover',
		'onmousemove',
		'onmouseout',
		'onkeypress',
		'onkeydown',
		'onkeyup'
    );
    
    private $img;
    //标签开始符号
    private $startTag = "<img";
    //标签结束符号
    private $endTag = "/>";
    //标签属性列表
    private $attrs = array();
    
    public function __construct($img) {
        $this->img = $img;
        $this->parseTag();
    }
    
    /**
     * 获取图片信息
     */
    public function toString() {
        //拼装图片的属性字符串
        $attr_arr = array();
        foreach($this->attrs as $attr_name=>$attr_val) {
            $attr_arr[] = $attr_name . "=" . "\"$attr_val\"";
        }
        $attr_str =  implode(" ", $attr_arr);
        
        return "{$this->startTag} $attr_str{$this->endTag}";
    }
    
    /**
     * 设置属性值
     * @param $attr_name
     * @param $attr_val
     */
    public function attr($attr_name, $attr_val) {
        if(empty($attr_name)) {
            return $this;
        }
        
        //获取方法
        if(func_num_args() == 1) {
            return isset($this->attrs[$attr_name]) ? $this->attrs[$attr_name] : "";
        } else {
            //属性设置方法
            if(is_array($attr_name)) {
                foreach($attr_name as $k=>$v) {
                    $this->attrs[$k] = $v;
                }
            } else if(is_string($attr_name)) {
                $this->attrs[$attr_name] = $attr_val;
            }
            
            return $this;
        }
    }
    
    /**
     * 获取图片的属性字符串
     * @param $img
     */
    private function parseTag() {
        if(empty($this->img)) {
            return false;
        }
        
        //标准的img写法, <img src="/Public/img.gif"/>
        if(preg_match("/<img([^><]*?)\/>/im", $this->img, $matches)) {
            $this->startTag = "<img";
            $this->endTag = "/>";
            $this->attrs = $this->extractAttrs($matches[1]);
            
            return true;
        }
        
        //老版本写法: <img src="/Public/img.gif"></img>
        if(preg_match("/<img([^><]*?)>(\s*)<\/img>/im", $this->img, $matches)) {
            $this->startTag = "<img";
            $this->endTag = ">{$matches[2]}</img>";
            $this->attrs = $this->extractAttrs($matches[1]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 提取图片的属性信息
     * 
     * //todolist 可以作用通用的方法提取到父类中去
     * 
     */
    private function extractAttrs($attr_str) {
        //提取属性字符串
        if(empty($attr_str)) {
            return false;
        }
        
        $attrs = array();
        //提取标准属性信息: src='' | src=""
        $pattern_1 = "/([^\s]+?)(\s*=\s*)(\'|\")([^\\3]*?)(\\3)/im";
        if(preg_match_all($pattern_1, $attr_str, $matches)) {
            $attrs = array_merge($attrs, (array)$matches[0]);
            foreach((array)$matches[0] as $str) {
                $attr_str = str_replace($str, "", $attr_str);
            }
        }
        
        //提取非规范属性，src=不包含空格的连续字符串
        $pattern_2 = "/([^\s]+?)(\s*=\s*)([^\s]+)/im";
        if(preg_match_all($pattern_2, $attr_str, $matches)) {
            $attrs = array_merge($attrs, (array)$matches[0]);
        }
        //对于非规范格式，忽略
        
        $img_attrs = array();
        //将属性解析成，{key,value}对
        foreach($attrs as $attr) {
            //以第一个'='出现的位置切分属性
            if(($pos = strpos($attr, '=')) === false) {
                continue;                    
            }
            $attr_name = trim(substr($attr, 0, $pos));
            $attr_val = trim(substr($attr, $pos + 1));
            //去掉属性值2边得引号
            if(preg_match("/(\'|\")([^\\1]*?)(\\1)/im", $attr_val, $attr_matches)) {
                $attr_val = $attr_matches[2];
            }
            $img_attrs[$attr_name] = $attr_val;
        }
        
        return $img_attrs;
    }
    
}