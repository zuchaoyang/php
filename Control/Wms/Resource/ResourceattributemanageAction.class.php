<?php
class ResourceattributemanageAction extends WmsController{
    
    public function _initialize() {
        parent::_initialize();
    }
    
    private $_type = array(
        1=>'grade',2=>'subject',3=>'version',4=>'term',5=>'column'
    );
    
    
    public function show_list() {
        $option_str = $this->objInput->getStr('option_str');
        if(empty($option_str)) {
            $option_str = '1_1';
        }
       
        list($type,$option) = explode("_",$option_str);
        $mResource = ClsFactory::Create("Model.Resource.mResource" . ucfirst($this->_type[$type]));
        $function_name = "getAllResource" . ucfirst($this->_type[$type]);
        
        $list = $mResource->$function_name();
        
        $ids = array();
        foreach($function_name as $id => $info){
            $ids[$ids] = $ids;
        }
        
        array_multisort($list,SORT_ASC);
        $this->assign("list", $list);
        $this->assign('type',$type);
        $this->display('resource_attribute_mana');
    }
    
    public function delete_attribute() {
        $option_str = $this->objInput->getStr('option_str');
        $id = $this->objInput->getInt('id');
        if(empty($option_str)) {
            $this->showError("参数丢失！","/Wms/Resource/Resourceattributemanage");
        }
       
        list($type,$option) = explode("_",$option_str);
        $mResource = ClsFactory::Create("Model.Resource.mResource" . ucfirst($this->_type[$type]));
        $function_name = "delResource" . ucfirst($this->_type[$type]);
        $result = $mResource->$function_name($id);
        if(!empty($result)) {
            $this->showSuccess("删除成功！","/Wms/Resource/Resourceattributemanage/show_list/option_str/".$type.'_'.$option);
        }else{
            $this->showError("删除失败！","/Wms/Resource/Resourceattributemanage/show_list/option_str/".$type.'_'.$option);
        }
    }
    
    public function modify_info() {
        $type = $this->objInput->postInt('type');
        $name = $this->objInput->postStr("value");
        $product_id = $this->objInput->postInt('product_id');
        $id = $this->objInput->postInt('id');
        
        if(empty($type) || empty($name) || empty($id)) {
            $this->ajaxReturn(null, '参数丢失，请重新操作!', -1, 'json');
        }

        $mResource = ClsFactory::Create("Model.Resource.mResource" . ucfirst($this->_type[$type]));
        $function_name = "modifyResource" . ucfirst($this->_type[$type]);
        
        $dataarr = array(
            'upd_time'=>time(),
            $this->_type[$type] . '_name' => $name,
        );
        if($type == 5) {
            $dataarr['product_id'] = $product_id;
        }
        
        $result = $mResource->$function_name($dataarr,$id);
        if(!empty($result)) {
            $this->ajaxReturn(null, '操作成功!', 1, 'json');
        }else{
            $this->ajaxReturn(null, '操作失败!', -1, 'json');
        }
    }
    
    public function add_info() {
        $type = $this->objInput->postInt("type");
        $product_id = $this->objInput->postInt("product_id");
        $value = $this->objInput->postStr("value");
        
        if(empty($type) || empty($value)) {
            $this->ajaxReturn(null, '参数丢失，请重新操作!', -1, 'json');
        }
        
        $mResource = ClsFactory::Create("Model.Resource.mResource" . ucfirst($this->_type[$type]));
        $function_name = "addResource" . ucfirst($this->_type[$type]);
        $dataarr = array(
            'upd_time'=>time(),
            'add_time'=>time(),
            $this->_type[$type] . '_name' => $value
        );
        if($type == 5) {
            $dataarr['product_id'] = $product_id;
        }
        $result = $mResource->$function_name($dataarr);
        if(!empty($result)) {
            $this->ajaxReturn(null, '操作成功!', 1, 'json');
        }else{
            $this->ajaxReturn(null, '操作失败!', -1, 'json');
        }
    }
}