<?php
class mResource extends mBase {
	/******************* resource_product表相关**********************************/
    protected $_dResource = null;
    
    public function __construct() {
        $this->_dResource = ClsFactory::Create('Data.dResource');
    }
    
    public function getResourceProductById($product_id){
        if(empty($product_id)){
            return false;
        }   
        return $this->_dResource->getResourceProductById($product_id);
    }     
	/************************************* resource_excel表相关**************************************************/
    public function getResourceexcelById($excel_ids) {
        if(empty($excel_ids)) {
            return false;
        }
        
        return $this->_dResource->getResourceexcelById($excel_ids);
    }

    public function addResourceexcel($datas, $return_insert_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dResource->addResourceexcel($datas, $return_insert_id);
    }

    public function modifyResourceexcel($datas, $excel_id) {
        if(empty($datas) || !is_array($datas) || empty($excel_id)) {
            return false;
        }
        
        return $this->_dResource->modifyResourceexcel($datas, $excel_id);
    }


    public function delResourceexcel($excel_id) {
        if(empty($excel_id)) {
            return false;
        }
        
        return $this->_dResource->delResourceexcel($excel_id);
    }

	/************************************* resource_feed表相关**************************************************/
    /**
     * 通过属性id获取相应的资源id信息 
     * @param  $attr_ids 属性id
     * @param  $need_sort 是否需要对id进行排序处理
     * @return 返回满足条件的资源id信息
     */ 
    public function getResourcefeedById($attr_ids, $need_sort = false, $strict = true) {
        if(empty($attr_ids)) {
            return false;
        }
        
        $feed_list = $this->getResourcefeedBaseById($attr_ids);
        $intersect_arr = array();
        if(!empty($feed_list)) {
            $need_intersect = true;
            //是否要求严格检查参数
            if($strict) {
                $database_attr_ids = array_keys($feed_list);
                $diff_arr = array_diff((array)$attr_ids, (array)$database_attr_ids);
                if(!empty($diff_arr)) {
                    $need_intersect = false;
                }
            }
            
            if($need_intersect) {
                //对资源进行求交集的操作,$intersect_arr = array($resource_id=>$add_time,);
                $state_real = defined('RESOURCE_FEED_STATE_REAL') ? RESOURCE_FEED_STATE_REAL : 1;
                $intersect_arr = $this->getIntersectFeedinfo($feed_list, $state_real);
                unset($feed_list);
                //是否需要对资源id进行排序
                if($need_sort) {
                    arsort($intersect_arr);
                }
            }
        }
        
        return !empty($intersect_arr) ? array_keys($intersect_arr) : false;
     }
     
     /**
      * 获取资源的信息，包括占位信息
      * @param  $attr_ids
      * @param  $need_sort
      * @param  $strict
      */
     public function getallResourcefeedWithUselessById($attr_ids, $is_unless = 2, $is_intersect = true) {
        if(empty($attr_ids) || !is_array($attr_ids)) {
            return false;
        }
        foreach($attr_ids as $key=>$val){
            if(empty($val))
                unset($attr_ids[$key]);
        }
        $feed_list = $this->getResourcefeedBaseById($attr_ids);
        $new_attr_resourceid = $resource_ids = array();
        foreach($feed_list as $key=> & $feed){
           $feed_content_list = unpack("L*", $feed['feed_content']);
           for($i=3;$i<count($feed_content_list);$i+=3){
               if($feed_content_list[$i] == $is_unless){
                   $new_attr_resourceid[$key][] = $feed_content_list[$i-2];
               }
               $resource_ids[] = $feed_content_list[$i-2];
           }
        }
        return !empty($new_attr_resourceid) && $is_intersect ? $new_attr_resourceid : $new_attr_resourceid;
     }
     
     public function getResourcefeedWithUselessById($attr_ids, $need_sort = false, $strict = true) {
        if(empty($attr_ids)) {
            return false;
        }
        
        $feed_list = $this->getResourcefeedBaseById($attr_ids);
        $intersect_arr = array();
        if(!empty($feed_list)) {
            $need_intersect = true;
            //是否要求严格检查参数
            if($strict) {
                $database_attr_ids = array_keys($feed_list);
                $diff_arr = array_diff((array)$attr_ids, (array)$database_attr_ids);
                if(!empty($diff_arr)) {
                    $need_intersect = false;
                }
            }
            
            if($need_intersect) {
                //对资源进行求交集的操作,$intersect_arr = array($resource_id=>$add_time,);
                $state_useless = defined('RESOURCE_FEED_STATE_USELESS') ? RESOURCE_FEED_STATE_USELESS : 2;
                $intersect_arr = $this->getIntersectFeedinfo($feed_list, $state_useless);
                unset($feed_list);
                //是否需要对资源id进行排序
                if($need_sort) {
                    arsort($intersect_arr);
                }
            }
        }
        
        return !empty($intersect_arr) ? array_keys($intersect_arr) : false;
     }
     
     public function getIntersectFeedinfo($feed_list, $states) {
         if(empty($feed_list)) {
             return false;
         }
         
         return $this->_dResource->getIntersectFeedinfo($feed_list, $states);
     }
     
     /**
      * 获取动态的原始基本信息
      * @param $attr_ids
      */
     public function getResourcefeedBaseById($attr_ids) {
         if(empty($attr_ids)) {
            return false;
        }
        
        return $this->_dResource->getResourcefeedById($attr_ids);
     }
     
     
    
    public function addResourcefeed($datas, $is_return_id=false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dResource->addResourcefeed($datas, $is_return_id);
    }
    
    /**
     * 其中传入的数据中feed_content字段必须是2维度的
     * @param $feed_datas = array('attr_id' => $attr_id, 'add_time' => $add_time, 'feed_content' => array(0=>array($resource_id,$add_time)));
     */
    public function addResourcefeedBat($feed_datas, $is_return_id=false) {
        if(empty($feed_datas)) {
            return false;
        }
        
        //记录操作失败的记录集
        $fail_arr = array();
        $attr_ids = array_keys($feed_datas);
        $resourcefeed_list = $this->getResourcefeedBaseById($attr_ids);
        $exists_attr_ids = array();
        if(!empty($resourcefeed_list)) {
            $exists_attr_ids = array_keys($resourcefeed_list);
        }
        //及时unset不用的大数据
        unset($resourcefeed_list);
        
        //对数据进行分组
        $add_list = $update_list = array();
        foreach($feed_datas as $attr_id=>$datas) {
            if(!empty($exists_attr_ids) && in_array($attr_id, $exists_attr_ids)) {
                $update_list[$attr_id] = $datas;
            } else {
                $add_list[$attr_id] = $datas;
            }
            unset($feed_datas[$attr_id]);
        }
        //修改数据库的相关记录
        if(!empty($update_list)) {
            foreach($update_list as $attr_id=>$datas) {
                $effect_rows = $this->_dResource->modifyResourcefeed($datas, $attr_id);
                if(empty($effect_rows)) {
                    $fail_arr[$attr_id] = $datas;
                }
            }
        }
        
        //批量增加数据库表的记录
        if(!empty($add_list)) {
            $chunk_arr = array_chunk($add_list, 100, true);
            foreach($chunk_arr as $feed_list) {
                $effect_rows = $this->_dResource->addResourcefeedBat($feed_list, $is_return_id);
                if(empty($effect_rows)) {
                    $fail_arr = array_merge((array)$fail_arr, (array)$feed_list);
                }
            }
        }
        return $fail_arr;
     }

    public function modifyResourcefeed($datas, $attr_id) {
        if(empty($datas) || !is_array($datas) || empty($attr_id)) {
            return false;
        }
        
        return $this->_dResource->modifyResourcefeed($datas, $attr_id);
    }
    
    
    public function delResourcefeed($attr_id) {
        if(empty($attr_id)) {
            return false;
        }
        
        return $this->_dResource->delResourcefeed($attr_id);
    }


 	/************************************* resource_info表相关**************************************************/
    public function getResourceInfo() {
        
        $this->_dResource->switchToInfo();
        $resourceinfo_list = $this->_dResource->getInfo(null, null, 0, 500);
        if(!empty($resourceinfo_list)) {
            $attr_ids = array();
            foreach($resourceinfo_list as $resource_id=>$resource) {
                if(!empty($resource['attrs'])) {
                    $resource['attrs'] = explode(",", $resource['attrs']);
                    $attr_ids = array_merge((array)$attr_ids, (array)$resource['attrs']);
                }
                $resourceinfo_list[$resource_id] = $resource;
            }
            $attr_ids = array_unique($attr_ids);
            $attr_names_list = $this->getResourceAttrNames($attr_ids);
            
            if(!empty($attr_names_list)) {
                foreach($resourceinfo_list as $resource_id=>$resource) {
                    $display_order = 0;
                    if(!empty($resource['attrs'])) {
                        $attrs = (array)$resource['attrs'];
                        $attr_names = array();
                        foreach($attrs as $attr_id) {
                            $attr_names = array_merge((array)$attr_names, (array)$attr_names_list[$attr_id]);
                            if(isset($attr_names_list[$attr_id]['chapter'])) {
                                $display_order = $attr_names_list[$attr_id]['display_order'];
                            }
                        }
                        $resource = array_merge($resource, $attr_names);
                        unset($resource['attrs']);
                    }
                    if(!empty($resource['mixed'])) {
                        $resource = array_merge((array)$resource, (array)unserialize($resource['mixed']));
                        unset($resource['mixed']);
                    }
                    $resource['display_order'] = $display_order;
                    
                    $resourceinfo_list[$resource_id] = $resource;
                }
            }
        }
        
        return !empty($resourceinfo_list) ? $resourceinfo_list : false;
    }
    
    public function getResourceinfoById($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        $resourceinfo_list = $this->_dResource->getResourceinfoById($resource_ids);
        
        if(!empty($resourceinfo_list)) {
            $attr_ids = array();
            foreach($resourceinfo_list as $resource_id=>$resource) {
                if(!empty($resource['attrs'])) {
                    $resource['attrs'] = explode(",", $resource['attrs']);
                    $attr_ids = array_merge((array)$attr_ids, (array)$resource['attrs']);
                }
                $resourceinfo_list[$resource_id] = $resource;
            }
            $attr_ids = array_unique($attr_ids);
            $attr_names_list = $this->getResourceAttrNames($attr_ids);
            
            if(!empty($attr_names_list)) {
                foreach($resourceinfo_list as $resource_id=>$resource) {
                    if(!empty($resource['attrs'])) {
                        $attrs = (array)$resource['attrs'];
                        $attr_names = array();
                        foreach($attrs as $attr_id) {
                            $attr_names = array_merge((array)$attr_names, (array)$attr_names_list[$attr_id]);
                        }
                        $resource = array_merge($resource, $attr_names);
                    }
                    $resourceinfo_list[$resource_id] = $resource;
                }
            }
        }
        
        return !empty($resourceinfo_list) ? $resourceinfo_list : false;
    }
    
    /**
     * 获取属性名称和值的映射
     * @param $attr_ids
     */
    protected function getResourceAttrNames($attr_ids) {  
        if (empty($attr_ids)) {
            return false;
        }
        
        $mResource = ClsFactory::Create ('Model.mResource');
        
        $attr_names_list = array ();
        $attr_list = $mResource->getResourceAttributeById($attr_ids);
        //过滤base_attr_id
        $base_attr_ids = $base_attr_names = array ();
        foreach ( $attr_list as $attr_id => $attr ) {
            $base_attr_ids[] = $attr['base_attr_id'];
        }
        $base_attr_ids = array_unique($base_attr_ids);
        $base_attr_list = $mResource->getResourceBaseAttributeById($base_attr_ids);
        foreach ( $base_attr_list as $base_attr_id => & $val ) {
            $base_attr_names[$base_attr_id] = $val ['base_attr_name'];
        }
        
        foreach ($attr_list as $attr_id => & $attr ) {
            $base_attr_id = intval($attr ['base_attr_id'] );
            if (isset ( $base_attr_names [$base_attr_id] )) {
                $field = $base_attr_names [$base_attr_id];
                $attr_names_list[$attr_id] = array (
                    $field => $attr['attr_name'],
                    'display_order' => $attr['display_order'],
                );
            }
        }
        unset($attr_list, $base_attr_names);
        
        return !empty($attr_names_list) ? $attr_names_list : false;
    }
    
    function getResourceInfoByTitleAndProductid($partTitle,$product_id,$offset,$length = 15){
        if(empty($partTitle) || empty($product_id)){
            return false;
        }
        $resource_info = $this->_dResource->getResourceInfoByTitleAndProductid($partTitle,$product_id,$offset,$length);
        if(!empty($resource_info)){
            import("@.Common_wmw.Constancearr"); //文件类型
            $file_type = Constancearr::file_type();
        
            $attr_ids = array();
            foreach($resource_info as $resource_id => $info){
                $file_type_code = $info['file_type'];
                $resource_info[$resource_id]['file_type_str'] = $file_type[$file_type_code];
                $temp = explode(',', $info['attrs']);  
                $resource_info[$resource_id]['attr_arr'] = $temp;
                $attr_ids = array_merge($attr_ids, $temp);
            }
            $attr_ids = array_unique($attr_ids);
            $attr_info_list = $this->getResourceAttrNames($attr_ids);  
            foreach($resource_info as $resource_id => $info){
                
                foreach($info['attr_arr'] as $key => $attr_id){
                    $resource_info[$resource_id] += $attr_info_list[$attr_id];
                }
            }
        }
        return !empty($resource_info)?$resource_info:false;
     }

    public function addResourceinfo($datas, $return_insert_id = false) {
        if(empty($datas) || !is_array($datas)) {
            return false;
        }
        
        return $this->_dResource->addResourceinfo($datas, $return_insert_id);  
     }
     
    public function addResourceinfoBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        return $this->_dResource->addResourceinfoBat($dataarr, $is_return_id);
    }

    public function modifyResourceinfo($datas, $resource_id) {
        if(empty($datas) || !is_array($datas) || empty($resource_id)) {
            return false;
        }
        
        return $this->_dResource->modifyResourceinfo($datas, $resource_id);
    }

    public function delResourceinfo($resource_id) {
        if(empty($resource_id)) {
            return false;
        }
        
        return $this->_dResource->delResourceinfo($resource_id);
    }
    
    public function delResourceinfoBat($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        return $this->_dResource->delResourceinfoBat($resource_ids);
    }
    
    /**
     * 批量处理方法，在删除资源基本信息的同时清除数据库中的关系
     * @param $resource_ids
     */
    public function delResourceinfoWithRelationBat($resource_ids) {
        if(empty($resource_ids)) {
            return false;
        }
        
        $resource_ids = is_array($resource_ids) ? $resource_ids : array($resource_ids);
        //批量删除资源的基本信息
        $del_rows = $this->delResourceinfoBat($resource_ids);
        //获取资源的关系信息
        $resource_attr_relation_list = $this->getResourceAttributeRelationByResource_id($resource_ids);
        //将数据按照attr_id进行分组处理,并统计要删除的关系id
        $attr_groups = $rar_ids = array();
        if(!empty($resource_attr_relation_list)) {
            foreach($resource_attr_relation_list as $resource_id=>$list) {
                foreach($list as $rar_id=>$datas) {
                    $attr_id = $datas['attr_id'];
                    $attr_groups[$attr_id][$resource_id] = $resource_id;
                    $rar_ids[] = $rar_id;
                }
            }
            unset($resource_attr_relation_list);
        }
        
        //批量删除关系表中数据
        if(!empty($rar_ids)) {
            $chunck_arr = array_chunk($rar_ids, 500);
            foreach($chunck_arr as $ids) {
                $this->delResourceAttributeRelationBat($ids);
            }
        }
        
        //封装要更新的feed数据
        if(!empty($attr_groups)) {
            $state_del = defined('RESOURCE_FEED_STATE_DEL') ? RESOURCE_FEED_STATE_DEL : 0;
            foreach($attr_groups as $attr_id=>$ids) {
                $feed = array();
                $feed['attr_id'] = $attr_id;
                foreach($ids as $resource_id) {
                    $feed['feed_content'][] = array(
                        'resource_id'=>$resource_id,
                        'add_time' => time(),
                        'state' => $state_del,
                    ); 
                }
                //批量更新feed的数据
                $this->modifyResourcefeed($feed, $attr_id);
            }
        }
        
        return $del_rows;
    }

/*
 * 表resource_attribute       start
 */
    function getResourceAttributeById($ResourceAttributeByIds){
        if(empty($ResourceAttributeByIds)){
            return false;
        }
        $new_ResourceAttribute_list = $this->_dResource-> getResourceAttributeById($ResourceAttributeByIds);
		return !empty($new_ResourceAttribute_list) ? $new_ResourceAttribute_list : false;
    }
    
     public function getResourceAttributeByBaseAttrId($base_attr_ids) {
        if(empty($base_attr_ids)) {
            return false;
        }
        
        return $this->_dResource->getResourceAttributeByBaseAttrId($base_attr_ids);
     }


    function addResourceAttribute($dataarr, $return_insertid){
        if(empty($dataarr)){
            return false;
        }
        
        return $this->_dResource->addResourceAttribute($dataarr, $return_insertid);
    }
   
    /**
     * 批量增加
     * @param $dataarr
     */
    public function addResourceAttributeBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->_dResource->addResourceAttributeBat($dataarr, $is_return_id);
    }
    
    function modifyResourceAttribute($dataarr, $ResourceAttributeByIds){
        if(empty($dataarr) || empty($ResourceAttributeByIds)){
            return false;
        }
        $result = $this->_dResource-> modifyResourceAttribute($dataarr, $ResourceAttributeByIds);
		return $result ? $result : false;
    }
    
    function delResourceAttribut($ResourceAttributeById){
        if(empty($ResourceAttributeById)){
            return false;
        }
        $result = $this->_dResource-> delResourceAttribut($ResourceAttributeById);
		return $result ? $result : false;
    }
    
    function getResourceAttributeRelationByResource_id($ResourceAttributeRelationResourceids){
        if(empty($ResourceAttributeRelationResourceids)){
            return false;
        }
        $new_ResourceAttributeRelation_lists = $this->_dResource->getResourceAttributeRelationByResource_id($ResourceAttributeRelationResourceids);
        return !empty($new_ResourceAttributeRelation_lists)?$new_ResourceAttributeRelation_lists:false;
    }
    
    function addResourceAttributeRelation($dataarr, $return_insertid){
        if(empty($dataarr)){
            return false;
        }
        $result = $this->_dResource->addResourceAttributeRelation($dataarr, $return_insertid);
        return !empty($result)?$result:false;
    }
    
    public function addResourceAttributeRelationBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        return $this->_dResource->addResourceAttributeRelationBat($dataarr, $is_return_id);
    }
    
    function modifyResourceAttributeRelationById($dataarr, $ResourceAttributeRelationId){
        if(empty($dataarr) || empty($ResourceAttributeRelationId)){
            return false;
        }
        $result = $this->_dResource->modifyResourceAttributeRelationById($dataarr, $ResourceAttributeRelationId);
        return !empty($result)?$result:false;
    }
    
    function delResourceAttributeRelationById($ResourceAttributeRelationId){
        if(empty($ResourceAttributeRelationId)){
            return false;
        }
        $result = $this->_dResource->delResourceAttributeRelationById($ResourceAttributeRelationId);
        return !empty($result)?$result:false;
    }
    
    public function delResourceAttributeRelationBat($rar_ids) {
        if(empty($rar_ids)) {
            return false;
        }
        
        return $this->_dResource->delResourceAttributeRelationBat($rar_ids);
    }
/*
 * 表resource_attribute_relation       end
 */
    
/*
 * 表resource_base_attribute       start
 */

    function getResourceBaseAttributeById($ResourceBaseAttributeIds){
        if(empty($ResourceBaseAttributeIds)){
            return false;
        }
		$new_ResourceBaseAttribute_list = $this->_dResource->getResourceBaseAttributeById($ResourceBaseAttributeIds);
		return !empty($new_ResourceBaseAttribute_list) ? $new_ResourceBaseAttribute_list : false;
    }
    
    public function getResourceBaseAttributeByProductId($product_ids) {
        if(empty($product_ids)) {
            return false;
        }
        return $this->_dResource->getResourceBaseAttributeByProductId($product_ids);
    }
    
    function addResourceBaseAttribute($dataarr, $returninsertid){
        if(empty($dataarr)){
            return false;
        }
        $result = $this->_dResource-> addResourceBaseAttribute($dataarr, $returninsertid);
        return !empty($result)?$result:false;
    }
    
    function modifyResourceBaseAttribute($dataarr, $ResourceBaseAttributeId){
        if(empty($dataarr) || empty($ResourceBaseAttributeId)){
            return false;
        }
        $result = $this->_dResource-> modifyResourceBaseAttribute($dataarr, $ResourceBaseAttributeId);
        return !empty($result)?$result:false;
    }
    
    function delResourceBaseAttribute ($ResourceBaseAttributeId){
        if(empty($ResourceBaseAttributeId)){
            return false;
        }
        $result = $this->_dResource-> delResourceBaseAttribute ($ResourceBaseAttributeId);
        return !empty($result)?$result:false;
    }
    
/*
 * 表resource_base_attribute       end
 */
    public function createResourceId() {
        return $this->_dResource->createResourceId();
    }
    
    //得到资源的基本属性
    protected function getbaseattrinfo($product_id) {
        $baseinfo = $this->getResourceBaseAttributeByProductId ($product_id);
        return ! empty($baseinfo[$product_id]) ? $baseinfo [$product_id] : false;
    }
    
    //根据资源id资源属性
    function getattrInfo($product_id, $base_attr_name) {
        $baseinfo = $this->getbaseattrinfo ( $product_id );
        foreach ( $baseinfo as $key => & $val ) {
            if ($val ['base_attr_name'] == $base_attr_name) {
                $base_id = $key;
                break;
            }
        }
        $attr_list = $this->getResourceAttributeByBaseAttrId ( $base_id );
        return ! empty ( $attr_list [$base_id] ) ? $attr_list [$base_id] : false;
    }
    
    function nav_paramters($paramter_arr, $nva_name, $is_unless = false , $ifintersect = true){
        if(empty($paramter_arr) || empty($nva_name)) {
            return false;
        }
        foreach($paramter_arr as $key => $val){
            if(empty($val))
                unset($paramter_arr[$key]);
        }
        $paramter_arr = is_array($paramter_arr) ? $paramter_arr : (array)$paramter_arr;
        $product_id = array_shift($paramter_arr);
        $allattrinfo = $this->getattrInfo($product_id, $nva_name);
        $new_attrinfo = $tem_arr = array();
        if($ifintersect){
            if($is_unless){
                $allattrids = $this->getallResourcefeedWithUselessById(array_keys($allattrinfo), 1);
                $searattrids = $this->getResourcefeedById($paramter_arr);
            }else{
                $allattrids = $this->getallResourcefeedWithUselessById(array_keys($allattrinfo), 2);
                $searattrids = $this->getResourcefeedWithUselessById($paramter_arr);
            }
            foreach($allattrids as $key=> & $val){
                    $temp_arr_a = array_intersect($searattrids, $val);
                    if(!empty($temp_arr_a)) {
                        $temp_arr[] = $key;
                    }
                    unset($temp_arr_a);
                
            }
            unset($allattrids, $searattrids);
            $new_attrinfo = $this->getResourceAttributeById(array_unique($temp_arr));
        }
        return $ifintersect ? $new_attrinfo : $allattrinfo;
    }
    
	/**
     * 对属性信息进行替换
     * @param $attr_name
     */
    protected function formart_attr_name($attr_name) {
        if(empty($attr_name)) {
            return false;
        } elseif(empty($this->pattern_attr_name)) {
            return $attr_name;
        }
        
        return preg_replace($this->pattern_attr_name, "", trim($attr_name));
    }
    
    
}