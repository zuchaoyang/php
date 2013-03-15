<?php

class HtmlParser {
    private $html;
    
    //简单的标签,是以"/>"作为关闭符号的,如: <img />
    private $simpleTags = array(
        'br',
        'img',
        'input'
    );
    
    //有关闭符号的标签: 如: <div></div>
    private $closedTags = array(
        'html',
        'head',
        'body',
        'div',
        'table',
        'tr',
        'td',
        'a',
        'p',
        'span',
        'em',
        'textarea',
    );
    
    public function __construct($html) {
        $this->html = $html;
    }
    
    /**
     * 创建html代码解析器
     * @param $tag_name
     * @param $tag_html
     */
    public static function createTagParser($tag_name, $tag_html) {
        if(empty($tag_name)) {
            trigger_error("tag_name is null!", E_USER_ERROR);
            return false;
        }
        
        $class_name = ucfirst(strtolower($tag_name)) . "Parser";
        import("@.Common_wmw.Vendor.HtmlParser.$class_name");
        
        if(!class_exists($class_name)) {
            trigger_error("$class_name not defined!", E_USER_ERROR);
            return false;
        }
        
        return new $class_name($tag_html);
    }
    
    /**
     * 提取内容中的标签信息
     * @param $tag_name
     * @param $html
     */
    public function getElementByTagName($tag_name) {
          if(empty($tag_name) || empty($this->html)) {
            return false;
        }
        
        if($this->isSimpleTag($tag_name)) {
            if(preg_match($this->getSimpleTagPattern($tag_name), $this->html, $matches)) {
                return $matches[0];
            }
        } else if($this->isClosedTag($tag_name)) {
            if(preg_match($this->getClosedTagPattern($tag_name), $this->html, $matches)) {
                return $matches[0];
            } 
        }
        
        return false;
    }
    
	/**
     * 提取内容中的所有同类标签信息
     * @param $tag_name
     * @param $html
     */
    public function getElementsByTagName($tag_name) {
        if(empty($tag_name) || empty($this->html)) {
            return false;
        }
        
        if($this->isSimpleTag($tag_name)) {
            if(preg_match_all($this->getSimpleTagPattern($tag_name), $this->html, $matches)) {
                return $matches[0];
            }
        } else if($this->isClosedTag($tag_name)) {
            if(preg_match_all($this->getClosedTagPattern($tag_name), $this->html, $matches)) {
                return $matches[0];
            } 
        }
        
        return false;
    }
    
    
    /**
     * 判断是否是缩略标签
     * @param $tag_name
     */
    private function isSimpleTag($tag_name) {
        return in_array($tag_name, $this->simpleTags) ? true : false;
    }
    
    /**
     * 判断是否是关闭标签
     * @param $tag_name
     */
    private function isClosedTag($tag_name) {
        return in_array($tag_name, $this->closedTags) ? true : false;
    }
    
    /**
     * 获取简单的标签正则表达式
     * @param $tag_name
     */
    private function getSimpleTagPattern($tag_name) {
        return "/<$tag_name([^><]*?)\/>/im";
    }
    
    /**
     * 获取关闭标签的正则表达式
     * @param $tag_name
     */
    private function getClosedTagPattern($tag_name) {
        return "/<$tag_name([^><]*?)>([^><]*?)<\/$tag_name>/im";
    }
    
}