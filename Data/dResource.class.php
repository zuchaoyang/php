<?php
/*todo list
 * 方法提取
 * 
 */
class dResource extends dBase {
    
    protected $_tablename = null; //主表
    protected $_index_list = array();
    protected $_pk = null;
    protected $_fields=array();
    public function switchToInfo() {
        $this->_tablename = 'resource_info';
        $this->_index_list = array(
            'resource_id'
        );
        $this->_pk = 'resource_id';
        $this->_fields = array(
            'resource_id',
            'title',
            'discription',
            'product_id',
            'attrs',
            'file_type',
            'show_type',
            'file_path',
            'file_name',
            'mixed',
            'add_time',
        );
    }
    
    public function switchToAttribute() {
        $this->_tablename = 'resource_attribute';
        $this->_index_list = array(
            'attr_id',
            'base_attr_id'
        );
        $this->_pk = 'attr_id';
        $this->_fields = array(
            'attr_id',
            'base_attr_id',
            'attr_name',
            'display_order',
            'add_time'
        );
    }
    
    public function switchToBaseAttribute() {
        $this->_tablename = 'resource_base_attribute';
        $this->_index_list = array(
            'base_attr_id',
            'product_id'
        );
        $this->_pk = 'base_attr_id';
        $this->_fields = array(
            'base_attr_id',
            'product_id',
            'base_attr_name',
            'add_time'
        );
    }
    
    public function switchToAttributeRelation() {
        $this->_tablename = 'resource_attribute_relation';
        $this->_index_list = array(
            'rar_id',
            'attr_id',
            'resource_id'
        );
        $this->_pk = 'rar_id';
        $this->_fields = array(
            'rar_id',
            'attr_id',
            'resource_id',
            'add_time'
        );
    }
    
    public function switchToFeed() {
        $this->_tablename = 'resource_feed';
        $this->_index_list = array(
            'attr_id'
        );
        $this->_pk = 'attr_id';
        $this->_fields = array(
            'attr_id',
            'feed_content',
            'add_time'
        );
    }

    public function switchToProduct() {
        $this->_tablename = 'resource_product';
        $this->_index_list = array(
            'product_id'
        );
        $this->_pk = 'product_id';
        $this->_fields = array(
            'product_id',
            'product_name',
            'add_time'
        );
    }
    
    public function switchToExcel() {
        $this->_tablename = 'resource_excel';
        $this->_index_list = array(
            'excel_id'
        );
        $this->_pk = 'excel_id';
        $this->_fields = array(
            'excel_id',
            'excel_name',
            'origin_file_path',
            'resource_ids',
            'sucess_nums',
            'fail_nums',
            'fail_file_path',
            'state',
            'add_time'
        );
    }
    
    public function switchToAutoIncrement() {
        $this->_tablename = 'resource_auto_increment';
        $this->_index_list = array(
        
        );
        $this->_pk = '';
        $this->_fields = array(
            'increment_id'
        );
    }
    
    public function _initialize() {
        $this->connectDb('old_resource', true);
    }
    /******************* resource_product表相关**********************************/
    public function getResourceProductById($product_id) {
        $this->switchToProduct();
        
        return $this->getInfoByPk($product_id);        
    }
    /************************************* resource_excel表相关**************************************************/
    public function getResourceexcelById($excel_ids) {
        $this->switchToExcel();
        
        return $this->getInfoByPk($excel_ids);
    }


    public function addResourceexcel($dataarr, $is_return_id=false) {
        $this->switchToExcel();
        
        return $this->add($dataarr, $is_return_id);
    }

    public function modifyResourceexcel($dataarr, $excel_id) {
        $this->switchToExcel();
        
        return $this->modify($dataarr, $excel_id);
    }

    public function delResourceexcel($excel_id) {
        $this->switchToExcel();
                
        return $this->delete($excel_id);
    }
    
	/************************************* resource_feed表相关**************************************************/
     public function getResourcefeedById($attr_ids) {
        $this->switchToFeed();
        
        return $this->getInfoByPk($attr_ids);
    }
    
    public function getIntersectFeedinfo($feed_list, $states = null) {
        if(empty($feed_list)) {
            return false;
        }
        $state_del = defined('RESOURCE_FEED_STATE_DEL') ? RESOURCE_FEED_STATE_DEL : 0;
        $state_real = defined('RESOURCE_FEED_STATE_REAL') ? RESOURCE_FEED_STATE_REAL : 1;
        $state_useless = defined('RESOURCE_FEED_STATE_USELESS') ? RESOURCE_FEED_STATE_USELESS : 2;
        $state_arr = array($state_del, $state_real, $state_useless);
        //获取要过滤的状态
        $states = empty($states) ? array($state_real) : (array)$states;
        $states = array_intersect($states, $state_arr);
        $resource_list = array();
        //解析数据，并去掉删除的资源信息
        foreach($feed_list as $attr_id=>$feed) {
            $feed_content_list = unpack("L*", $feed['feed_content']);
            unset($feed['feed_content']);
            $counter = count($feed_content_list);
            $feed_content_arr = $del_resource_ids = array();
            for($i = 1; $i <= $counter; $i += 3) {
                $resource_id = $feed_content_list[$i];
                $add_time = $feed_content_list[$i+1];
                $state = $feed_content_list[$i+2];
                if(in_array($state, $states)) {
                    if(!isset($del_resource_ids[$resource_id])) {
                        $feed_content_arr[$resource_id] = $add_time;
                    }
                } else {
                    $del_resource_ids[$resource_id] = $resource_id;
                }
                unset($feed_content_list[$i], $feed_content_list[$i+1], $feed_content_list[$i+2]);
            }
            
            $resource_list[$attr_id] = $feed_content_arr;
            unset($feed_content_list, $feed_content_arr, $feed_list[$attr_id]);
        }
        //对资源的id进行求交集的处理
        $intersect_arr = array();
        if(count($resource_list) > 1) {
            $intersect_arr = call_user_func_array('array_intersect_key', $resource_list);
        } else {
            $intersect_arr = array_shift($resource_list);
        }
        unset($resource_list);
        
        return !empty($intersect_arr) ? $intersect_arr : false;
    }
    
    public function addResourcefeed($datas, $is_return_id=false) {
        if(!isset($datas['attr_id'], $datas['feed_content'], $datas['add_time'])) {
            return false;
        }
        $this->switchToFeed();
        
        if(intval($datas['attr_id']) <= 0) {
            return false;
        } elseif(empty($datas['feed_content']) || !is_array($datas['feed_content'])) {
            return false;
        }
        //对传入的数据进行拼装
        $pack_str = "";
        foreach($datas['feed_content'] as $record) {
            if(is_int($record['resource_id']) && is_int($record['add_time']) && is_int($record['state'])) {
                $pack_str .= pack("L3", $record['resource_id'], $record['add_time'], $record['state']);
            }
        }
        $datas['feed_content'] = mysql_escape_string($pack_str);
        
        if(!empty($datas['feed_content'])) {
            $insertsql = 'insert into ' . $this->_tablename . ' set ' .
            			 ' attr_id="' . $datas['attr_id'] . '",' .
            			 ' feed_content="' . $datas['feed_content'] . '",' .
            			 ' add_time="' . $datas['add_time'] . '"';
            $effect_rows = $this->execute($insertsql);
            if ($is_return_id && !empty($effect_rows)) {
                return $this->getLastInsID();
            }
            
            return !empty($effect_rows) ? $effect_rows : false;
        }
        return false;
    }
    
    public function addResourcefeedBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->switchToFeed();
        //对传入的数据格式进行处理
        foreach($dataarr as $attr_id=>$datas) {
            if(isset($datas['feed_content'])) {
                $pack_str = "";
                foreach($datas['feed_content'] as $record) {
                    if(is_int($record['resource_id']) && is_int($record['add_time']) && is_int($record['state'])) {
                        $pack_str .= pack("L3", $record['resource_id'], $record['add_time'], $record['state']);
                    }
                }
                $datas['feed_content'] = mysql_escape_string($pack_str);
            }
            $dataarr[$attr_id] = $datas;
        }
        $fields_val = $this->lotAdd($dataarr, $this->_fields);
        $fields = $fields_val['fields'];
        $fields_values = $fields_val['fields_values'];
        if(!empty($fields_values)) {
            $fields_str = implode(",", $fields);
            $values_str = implode(",", $fields_values);
            $effect_rows = $this->execute("insert into $this->_tablename ($fields_str) values $values_str");
        
            if ($is_return_id && !empty($effect_rows)) {
                return $this->getLastInsID();
            }
        }
        return !empty($effect_rows) ? $effect_rows : false;
    }

    public function modifyResourcefeed($datas, $attr_id) {
        $attr_id = $this->checkIds($attr_id);
        if(empty($datas) || !is_array($datas) || empty($attr_id)) {
            return false;
        }
        
        $this->switchToFeed();
        
        $attr_id = array_shift($attr_id) ; 
        $datas = $this->checkFields($datas);
        
         //对传入的数据进行拼装
        if(isset($datas['feed_content'])) {
            $pack_str = "";
            foreach($datas['feed_content'] as $record) {
                if(is_int($record['resource_id']) && is_int($record['add_time']) && is_int($record['state'])) {
                    $pack_str .= pack("L3", $record['resource_id'], $record['add_time'], $record['state']);
                }
            }
        }
        //修改的时候要把不相关的数据去掉
        unset($datas);
        
        if(!empty($pack_str)) {
            $pack_str = mysql_escape_string($pack_str);
            $update_sql = 'update ' .$this->_tablename. ' set feed_content=CONCAT("' . $pack_str . '", feed_content) where attr_id="' . $attr_id . '" limit 1';
            $effect_rows = $this->query($update_sql);
            return !empty($effect_rows) ? $effect_rows : false;
        }
        
        return false;
    }

    public function delResourcefeed($attr_id) {
        $this->switchToFeed();
        
        return $this->delete($attr_id);
    }

    /************************************* resource_info表相关**************************************************/
    public function getResourceinfoById($resource_ids) {
        $resource_ids = $this->checkIds($resource_ids);
        if(empty($resource_ids)) {
            return false;
        }
        
        $this->switchToInfo();
        
        $wheresql = "where resource_id in('" . implode("','", $resource_ids) . "')";
        $resource_list = $this->query("select * from $this->_tablename $wheresql");
        
        $new_resource_list = array();
        if(!empty($resource_list)) {
            foreach($resource_list as $resource) {
                //反向解析资源的其他属性
                if(isset($resource['mixed'])) {
                    if(!empty($resource['mixed'])) {
                        $mixed_arr = @ unserialize($resource['mixed']);
                        $resource = array_merge((array)$resource, (array)$mixed_arr);
                    }
                    unset($resource['mixed']);
                }
                $new_resource_list[$resource['resource_id']] = $resource;
            }
        }
        
        return !empty($new_resource_list) ? $new_resource_list : false;
    }

    public function getResourceInfoByTitleAndProductid($partTitle,$product_id, $offset=0, $limit=10) {
        if(empty($partTitle) || empty($product_id)) {
            return false;
        }
        
        $this->switchToInfo();
        
        $product_id = intval($product_id);
        $offset = intval($offset);
        $limit = intval($limit);
        $partTitle = str_replace(array('%','_','&quot;'),array('','','"'), $partTitle);
        $partTitle_reg = "$partTitle%"; //检索以检索内容开头的记录
        $sql = "select * from $this->_tablename where product_id='$product_id' and title like '$partTitle_reg' limit $offset, $limit";
        $resource_list = $this->query($sql); 
        
        $new_resource_list = array();
        if(!empty($resource_list)) {
            foreach($resource_list as $resource) {
                //反向解析资源的其他属性
                if(isset($resource['mixed'])) {
                    if(!empty($resource['mixed'])) {
                        $mixed_arr = @ unserialize($resource['mixed']);
                        $resource = array_merge((array)$resource, (array)$mixed_arr);
                    }
                    unset($resource['mixed']);
                }
                $new_resource_list[$resource['resource_id']] = $resource;
            }
        }
        return !empty($new_resource_list) ? $new_resource_list : false;
    }
    public function addResourceinfo($dataarr, $is_return_id=false) {
       $this->switchToInfo();
        
        return $this->add($dataarr, $is_return_id);
    }
    
    public function addResourceinfoBat($dataarr, $is_return_id=false) {
        $this->switchToInfo();
        if(empty($dataarr)) {
            return false;
        }
        
        $this->switchToInfo();
        
        $fields_val = $this->lotAdd($dataarr);
        $fields = $fields_val['fields'];
        $fields_values = $fields_val['fields_values'];
        
        if(!empty($fields_values)) {
            $effect_rows = $this->execute("insert into $this->_tablename(" . implode(",", $fields) . ") values" . implode(",", $fields_values));
            
            if ($is_return_id && !empty($effect_rows)) {
                return $this->getLastInsID();
            }
        }
        return !empty($effect_rows) ? $effect_rows : false;
    }

    public function modifyResourceinfo($dataarr, $resource_id) {
        $this->switchToInfo();
        
        return $this->modify($dataarr, $resource_id);
    }

    public function delResourceinfo($resource_id) {
        $this->switchToInfo();
                
        return $this->delete($resource_id);
    }
    
    /**
     * 批量删除资源基本信息
     * @param $resource_ids
     */
    public function delResourceinfoBat($resource_ids) {
        $this->switchToInfo();
        $resource_ids = $this->checkIds($resource_ids);
        if(empty($resource_ids)) {
            return false;
        }
        
        $wheresql = "where resource_id in('" . implode("','", $resource_ids) . "')";
        $limitsql = "limit " . count($resource_ids);
        
        $effect_rows = $this->execute("delete from $this->_tablename $wheresql $limitsql");
        return !empty($effect_rows) ? $effect_rows : false;
    }

/*
 * 表resource_attribute       start
 */
    function getResourceAttributeById($ResourceAttributeByIds) {
        $this->switchToAttribute();
		
		return $this->getInfoByPk($ResourceAttributeByIds);
    }
    
    public function getResourceAttributeByBaseAttrId($base_attr_ids) {
        $this->switchToAttribute();
		
		return $this->getInfoByFk($base_attr_ids, 'base_attr_id');
    }

    function addResourceAttribute($dataarr, $is_return_id=false) {
        $this->switchToAttribute();
        
        return $this->add($dataarr, $is_return_id);
    }
    
    public function addResourceAttributeBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->switchToAttribute();
        
        $fields_val = $this->lotAdd($dataarr);
        $fields = $fields_val['fields'];
        $fields_values = $fields_val['fields_values'];
        
        if(!empty($fields_values)) {
            $effect_rows = $this->execute("insert into $this->_tablename (" . implode(",", $fields) . ") values" . implode(",", $fields_values));
            
            if ($is_return_id && !empty($effect_rows)) {
                return $this->getLastInsID();
            }
        }
        return !empty($effect_rows) ? $effect_rows : false;
    }
    
    function modifyResourceAttribute($dataarr, $ResourceAttributeById) {
        $this->switchToAttribute();
        
        return $this->modify($dataarr, $ResourceAttributeById);        
    }
    
    function delResourceAttribut($ResourceAttributeById) {
        $this->switchToAttribute();
        
        return $this->delete($ResourceAttributeById);
    }
    
    function getResourceAttributeRelationByResource_id($ResourceAttributeRelationResourceids) {
        $this->switchToAttributeRelation();
        
        return $this->getInfoByFk($ResourceAttributeRelationResourceids, 'resource_id');
    }
    
    function addResourceAttributeRelation($dataarr, $is_return_id=false) {
        $this->switchToAttributeRelation();
        
        return $this->add($dataarr, $is_return_id);
    }
    
    public function addResourceAttributeRelationBat($dataarr, $is_return_id=false) {
        if(empty($dataarr)) {
            return false;
        }
        
        $this->switchToAttributeRelation();
        
        $fields_val = $this->lotAdd($dataarr);
        $fields = $fields_val['fields'];
        $fields_values = $fields_val['fields_values'];
        
        if(!empty($fields_values)) {
            $effect_rows = $this->execute("insert into $this->_tablename (" . implode(",", $fields) . ") values" . implode(",", $fields_values));
            if ($is_return_id && !empty($effect_rows)) {
                return $this->getLastInsID();
            }
        }
        
        return !empty($effect_rows) ? $effect_rows : false;
    }
    
    function modifyResourceAttributeRelationById($dataarr, $ResourceAttributeRelationId) {
        $this->switchToAttributeRelation();
       
        return $this->modify($dataarr, $ResourceAttributeRelationId);
    }
    
    function delResourceAttributeRelationById($ResourceAttributeRelationId) {
       $this->switchToAttributeRelation();
        
        return $this->delete($ResourceAttributeRelationId);
    }
    
    public function delResourceAttributeRelationBat($rar_ids) {
        $this->switchToAttributeRelation();
        
        return $this->delete($rar_ids);
    }
    
/*
 * 表resource_attribute_relation       end
 */
    
/*
 * 表resource_base_attribute       start
 */

    function getResourceBaseAttributeById($ResourceBaseAttributeIds) {
        $this->switchToBaseAttribute();
		
		return $this->getInfoByPk($ResourceBaseAttributeIds);
    }
    
    public function getResourceBaseAttributeByProductId($product_ids) {
        $this->switchToBaseAttribute();
		return $this->getInfoByFk($product_ids, 'product_id');
    }
    
    function addResourceBaseAttribute($dataarr, $is_return_id=false) {
        $this->switchToBaseAttribute();
        
        return $this->add($dataarr, $is_return_id);
    }
    
    function modifyResourceBaseAttribute($dataarr, $ResourceBaseAttributeId) {
        $this->switchToBaseAttribute();
        
        return $this->modify($dataarr, $ResourceBaseAttributeId);
    }
    
    function delResourceBaseAttribute ($ResourceBaseAttributeId) {
        $this->switchToBaseAttribute();
        
        return $this->delete($ResourceBaseAttributeId);
    }
    
	 /**
         * 同一张表批量增加对应的记录
         * @param $dataarr
         * @$this->checkFields()
         */
     private function lotAdd($dataarr, $fields_arr) {
     	if(empty($dataarr) || !is_array($dataarr)) {
            return false;
        }
        $field_vals = array();
        //获取fields数据
        $fields = array();
        $new_arr = array();
        foreach($dataarr as $key=>$data) {
            //检查并得到正确的数据
            $data = $this->checkFields($data, $fields_arr);
            $new_arr[] = $data;
            $fields = array_merge($fields, array_keys($data));
        }

        //得到正确的字段名
        $fields = array_unique($fields);
        if(empty($fields)) {
            return false;    
        }
        //排序
        sort($fields);

        //insert 的values
        $fields_values = array();
        foreach($new_arr as &$user) {
            if(empty($user) || !is_array($user)) {
                continue;
            }
            $keys = array_keys($user);
            $diff = array_diff($fields, $keys);
            if(!empty($diff)) {
                //没有数据的字段默认为空
                $arr = array_combine($diff, array_fill(0, count($diff), null));
                $user = array_merge($user, $arr);
            }
            //字段排序
            ksort($user);
            $vals = array();
            foreach($user as $val) {
                if(is_null($val)) {
                    $vals[] = "DEFAULT";
                } else {
                    $vals[] = "\"".$val."\"";
                }
            }
            $fields_values[] = "(".implode(',', $vals).")";
        }
        $field_vals['fields'] = $fields;
        $field_vals['fields_values'] = $fields_values;
        return !empty($field_vals['fields_values']) ? $field_vals : false;
    }
    
    /*
     * 表resource_base_attribute       end
     */
    public function createResourceId() {
        $this->switchToAutoIncrement();
       $this->execute("insert into $this->_tablename (increment_id)values(0)");
       $id = intval($this->getLastInsId());
       if ($id % 100 == 0) {
           $this->execute("delete from $this->_tablename");
       }
       return $id;
    }
    
 
}