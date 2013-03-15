<?php
define('REQUEST_DECORATE_SWITCH', true);    //是否开启别名处理

class RequestDecorate {
    protected $objInput = null;
    protected $alias_list = null;
    
    public function __construct($objInput) {
        $this->objInput = $objInput;
        $this->alias_list = C('REQUEST_ALIAS');
    }
    
    public function __call($func_name, $args) {
        if(empty($func_name)) {
            return false;
        }
        
        $alias_list = $this->getAliasList($args);
        
        $return_val = null;
        if(!empty($alias_list)) {
            foreach($alias_list as $alias_key) {
                $val = call_user_func_array(array($this->objInput, $func_name), array($alias_key));
                if(!empty($val)) {
                    $return_val = $val;
                    break;
                }
            }
        }
        
        return !empty($return_val) ? $return_val : false;
    }
    
    protected function getAliasList($args) {
        if(empty($args)) {
            return false;
        }
        
        $alias_list = array();
        $args = is_array($args) ? reset($args) : $args;
        foreach($this->alias_list as $key=>$list) {
            $search_key = array_search($args, $list);
            if($search_key !== false) {
                $alias_list = $list;
                break;
            }
        }
        
        return array_unique(array_merge(array($args), (array)$alias_list));
    }
}