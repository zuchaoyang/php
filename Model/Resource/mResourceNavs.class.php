<?php
class mResourceNavs extends mBase{
    protected $_dResourceNavs = null;
    
    private $navs = array(
        'product_id',
        'grade_id',
        'subject_id',
        'version_id',
        'term_id',
        'chapter_id',
        'section_id',
        'column_id'
    );    
    
    public function __construct() {
        $this->_dResourceNavs = ClsFactory::Create("Data.Resource.dResourceNavs");
    }
    
    public function getResourceNavsByNavValueUseLike($nav_value) {
        if(empty($nav_value) || is_array($nav_value)) {
            return false;
        }
        $wheresql = "nav_value like '" . $nav_value . "%'";
        $resource_nav_list = $this->_dResourceNavs->getInfo($wheresql);
        
        $resource_nav_new_list = array();
        $resource_nav_new_list = $this->ParamTransfor($resource_nav_list);
        return !empty($resource_nav_new_list) ? $resource_nav_new_list : false;
    }
    
    public function getResourceNavsByNavValue($nav_values) {
        if(empty($nav_values)) {
            return false;
        }
        
        $wheresql = "nav_value in('" . implode("','", (array)$nav_values) . "')";
        $resource_nav_list = $this->_dResourceNavs->getInfo($wheresql);
        $resource_nav_new_list = array();
        $resource_nav_new_list = $this->ParamTransfor($resource_nav_list);
        return !empty($resource_nav_new_list) ? $resource_nav_new_list : false;
    }
    
    public function addResourceNavsBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceNavs->addBat($dataarr);
    }
    
    private function ParamTransfor($resource_nav_list) {
        if(empty($resource_nav_list)) {
            return false;
        }
        
        $new_nav_arr = array();
        
        foreach($resource_nav_list as $key => $resource_nav) {
            $nav_arr = explode("_",$resource_nav['nav_value']);
            
            foreach($this->navs as $id=>$val) {
                $resource_nav[$this->navs[$id]] = intval($nav_arr[$id]);
            }
            $new_nav_arr[$key] = $resource_nav;
        }
        
        return !empty($new_nav_arr) ? $new_nav_arr : false;
    }
}