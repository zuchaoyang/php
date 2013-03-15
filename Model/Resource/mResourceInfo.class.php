<?php
class mResourceInfo extends mBase{
    protected $_dResourceInfo = null;
    
    private $_limit = 30;
    
    public function __construct() {
        $this->_dResourceInfo = ClsFactory::Create("Data.Resource.dResourceInfo");
    }
    
    public function getResourceInfoById($resource_ids){
        if(empty($resource_ids)) {
            return false;
        }
        
        $resource_list = $this->_dResourceInfo->getResourceInfoById($resource_ids);
        if(!empty($resource_list)) {
            foreach($resource_list as $resource_id => $resource) {
                //反向解析资源的其他属性
                $resource_list[$resource_id] = $this->parseResourceInfo($resource);
            }
        }
        
        return !empty($resource_list) ? $resource_list : false;
    }
    
    public function modifyResourceInfo($dataarr, $resource_id) {
        if(empty($resource_id) || empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceInfo->modifyResourceInfo($dataarr, $resource_id);
    }
    
    /**
     * 获取资源的通用方法
     * @param $wherearr
     * @param $orderby
     * @param $offset
     * @param $limit
     */
    public function getResourceInfo($wherearr, $orderby, $offset = 0, $limit = 10) {
        
        $offset = max($offset, 0);
        $limit = $limit > 0 ? $limit : 10;
        
        $resource_list = $this->_dResourceInfo->getInfo($wherearr, $orderby, $offset, $limit);
        if(!empty($resource_list)) {
            foreach($resource_list as $resource_id => $resource) {
                //反向解析资源的其他属性
                $resource_list[$resource_id] = $this->parseResourceInfo($resource);
            }
        }
        
        return !empty($resource_list) ? $resource_list : false;
    }
    
    /**
     * 获取资源的通用方法，带有统计功能
     * @param $wherearr
     * @param $orderby
     * @param $offset
     * @param $limit
     */
    public function getResourceInfoWithCount($wherearr, $orderby, $offset = 0, $limit = 10) {
        $offset = max($offset, 0);
        $limit = $limit > 0 ? $limit : 10;
        $resource_list = $this->_dResourceInfo->getInfo($wherearr, $orderby, $offset, $limit);
        $resource_count = $this->_dResourceInfo->getCount($wherearr);
        
        if(!empty($resource_list)) {
            foreach($resource_list as $resource_id => $resource) {
                //反向解析资源的其他属性
                $resource_list[$resource_id] = $this->parseResourceInfo($resource);
            }
        }
        
        return array($resource_count, $resource_list);
    }
    
    public function getReourceInfoByCombinedKey($CombinedKey, $orderby, $offset = 0, $limit) {
        if(empty($CombinedKey)) {
            return false;
        }
        
        $this->_limit = empty($limit) ? $this->_limit : $limit;
        
        $AllowCombinedKey = $this->_dResourceInfo->getCombinedKey();
        
        foreach($CombinedKey as $field => $val) {
            if(!in_array($field, $AllowCombinedKey)) {
                continue;
            }
            
            $where_arr[$field] = $val;
        }
        $orderby = !empty($orderby) ? $orderby : null;
        $resource_list = $this->_dResourceInfo->getInfo($where_arr, $orderby, $offset, $this->_limit);
        foreach($resource_list as $key => &$resrouce_info) {
            $resource_list[$key] = $this->parseResourceInfo($resrouce_info);
        }
        $resource_count = $this->_dResourceInfo->getCount($where_arr);
        
        return array($resource_count, $resource_list);
    }
    
    public function getResourceInfoByName($resource_name, $offset = 0, $limit, $product_id = null) {
        if(empty($resource_name)) {
            return false;
        }
        
        $this->_limit = empty($limit) ? $this->_limit : $limit;
        
        $partTitle = str_replace(array('%','_','&quot;'),array('','','"'), $resource_name);

        $partTitle_reg = "$partTitle%"; //检索以检索内容开头的记录
        
        $wherearr = array(
            'product_id=' . $product_id,
            'title like ' . "'$partTitle_reg'"
        );
        
        $resource_count = $this->_dResourceInfo->getCount($wherearr);
        $resource_list = $this->_dResourceInfo->getInfo($wherearr, null, $offset, $this->_limit);
        if(!empty($resource_list)) {
            foreach($resource_list as $resource_id => $resource) {
                //反向解析资源的其他属性
                $resource_list[$resource_id] = $this->parseResourceInfo($resource);
            }
        }

        return !empty($resource_list) ? array('total_num' => $resource_count,'resource_list' => $resource_list) : false;
        
    }
    
    
    public function addResourceInfo($dataarr, $is_return_id = false) {
        if(empty($dataarr) || !is_array($dataarr)){
            return false;
        }
        return $this->_dResourceInfo->addResourceInfo($dataarr, $is_return_id);
    }
    
    public function addResourceInfoBat($dataarr) {
        if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        
        return $this->_dResourceInfo->addBat($dataarr);
    }
    
    public function delResourceInfo($resource_id) {
        if(empty($resource_id)) {
            return false;
        }
        return $this->_dResourceInfo->delResourceInfo($resource_id);
    }
    
    public function delResourceInfoBat($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        return $this->_dResourceInfo->delResourceInfoBat($resource_ids);
    }
    
    /**
     * 转换查询到得资源信息
     * @param $resource_list
     */
    private function parseResourceInfo($resource) {
        if(empty($resource)) {
            return false;
        }
        
        if(!empty($resource['mixed'])) {
            $mixed_arr = @ unserialize($resource['mixed']);
            $resource = array_merge((array)$resource, (array)$mixed_arr);
        }
        unset($resource['mixed']);
        
        return $resource;
    }
}