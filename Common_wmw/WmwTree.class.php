<?php
/**
 * PHP端使用组合模式生成树形结构
 * @author Administrator
 *
 */
class WmwTree {
    private $upid_field;                    //上级id在数组中的field名称
    private $id_field;                      //当前id在数组中的field名称
    private $text_field;                    //显示的内容在数组中的field名称
    private $userdata_fields = array();     //用户数据涉及到的fields列表
    private $expandattrs_fields = array();  //扩展数据中涉及到的fields列表
    
	/**
     * 创建一个属性结构
     * @param $root			          array  树形结构的根节点
     * @param $data_arr				  array  树形结构的其他孩子节点的数组
     * @param $return_data_type		  string 数据返回类型，支持json、html
     */
    public function build_tree($root, $data_arr, $return_data_type) {
        if(empty($root)) {
            return false;
        }
        
        $return_data_type = in_array($return_data_type, array('json', 'html')) ? $return_data_type : 'json';
        
        //创建根节点
        $rootObj = new Composite();
        
        $rootObj->setId($this->getId($root));
        $rootObj->setText($this->getText($root));
        $rootObj->setUserdata($this->getUserdata($root));
        $rootObj->setExpandAttrs($this->getExpandAttrs($root));
        $rootObj->setType(1);
        
        //创建一个容器对象，用来保存对象信息
        $containerObj = new Container();
        //初始化容器
        foreach($data_arr as $datas) {
            $containerObj->buildObjectList($this->getId($datas), new Composite());
        }
        
        //创建树形结构
        foreach($data_arr as $datas) {
            $id = $this->getId($datas);
            $upid = $this->getUpid($datas);
            
            $objComposite = $containerObj->getElementById($id);
            if(is_null($objComposite) || !is_object($objComposite)) {
                continue;
            }
            
            $objComposite->setId($id);
            $objComposite->setText($this->getText($datas));
            $objComposite->setExpandAttrs($this->getExpandAttrs($datas));
            $objComposite->setUserdata($this->getUserdata($datas));
            
            if($upid && ($parentObj = $containerObj->getElementById($upid))) {
                $objComposite->appendTo($parentObj);
            } else {
                $rootObj->addChild($objComposite);
            }
        }
        
        if($return_data_type == 'html') {
            return $rootObj->operationHtml();
        }
        
        return $rootObj->operationJson();
    }
    
    /**
     * 设置上级id对应的字段名称
     * @param $upid_field
     */
    public function setFeildForUpid($upid_field) {
        $this->upid_field = $upid_field;
    }
    
    /**
     * 设置当前id对应的字段名称
     * @param $id_field
     */
    public function setFeildForId($id_field) {
        $this->id_field = $id_field;
    }
    
    /**
     * 设置text对应的字段名称
     * @param $text_field
     */
    public function setFeildForText($text_field) {
        $this->text_field = $text_field;
    }
    
    /**
     * 设置用户数据对应的字段列表
     * @param $userdata_fields
     */
    public function setFeildsForUserdata($userdata_fields = array()) {
        $this->userdata_fields = (array)$userdata_fields;
    }
    
    /**
     * 设置扩展数据对应的字段列表
     * @param array $expandattrs_fields
     */
    public function setFeildsForExpandAttrs($expandattrs_fields = array()) {
        $this->expandattrs_fields = (array)$expandattrs_fields;
    }
    
    /**
     * 设置upid对应的字段
     * @param $datas
     */
    private function getUpid($datas) {
        if(empty($datas)) {
            return null;
        }
        
        return isset($datas[$this->upid_field]) ? $datas[$this->upid_field] : null;
    }
    
    /**
     * 获取id对应的值
     * @param $datas
     */
    private function getId($datas) {
        if(empty($datas)) {
            return null;
        }
        
        return isset($datas[$this->id_field]) ? $datas[$this->id_field] : null;
    }
    
    /**
     * 获取text
     * @param $datas
     */
    private function getText($datas) {
        if(empty($datas)) {
            return null;
        }
        
        return isset($datas[$this->text_field]) ? $datas[$this->text_field] : null;
    }
    
    /**
     * 获取树形结构扩展数据
     * @param $datas
     */
    private function getExpandAttrs($datas) {
        if(empty($datas) || empty($this->expandattrs_fields)) {
            return null;
        }
        
        $expand_attrs = array();
        foreach((array)$this->expandattrs_fields as $field) {
            $expand_attrs[$field] = isset($datas[$field]) ? $datas[$field] : "";
        }
        
        return !empty($expand_attrs) ? $expand_attrs : null;
    }
    
    /**
     * 获取用户自定义数据
     * @param $datas
     */
    private function getUserdata($datas) {
        if(empty($datas) || empty($this->userdata_fields)) {
            return null;
        }
        
        $user_datas = array();
        foreach((array)$this->userdata_fields as $field) {
            $user_datas[] = array(
                'name' => $field,
                'content' => isset($datas[$field]) ? $datas[$field] : "",
            );
        }
        
        return !empty($user_datas) ? $user_datas : null;
    }
}

/**
 * 部门接口，用于规定统一的方法
 * @author $anlicheng 2012-5-5
 *
 */
interface ComponentInterface {
    public function appendTo($parentObj);
    public function addChild($newChild);
    public function removeChild();
    public function hasChild();
    public function getFirstChild();
    public function getTreeFirstChildOnLevel($level = 1);
    public function operationJson();
    public function operationHtml();
}

/**
 * 部门类，配合js提供相应树形结构数据(使用了：组合设计模式,避免多次循环和递归处理)
 * @author $anlicheng 2012-5-5
 *
 */
class Composite implements ComponentInterface {
    protected $id;
    protected $text;
    protected $userdata = array();
    protected $expand_attrs = array();
    
    protected $type = 1;
    
    protected $childNodes = array();
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setText($text) {
        $this->text = $text;
    }
    
    public function setExpandAttrs($expand_attrs) {
        if(empty($expand_attrs)) {
            return;
        }
        
        $this->expand_attrs = (array)$expand_attrs;
    }
    
    public function setUserdata($userdata) {
        if(empty($userdata)) {
            return;
        }
        
        $this->userdata = (array)$userdata;
    }
    
    public function setType($type) {
        $this->type = intval($type);
    }
    
    public function appendTo($parentObj) {
        if(empty($parentObj)) {
           return false;
        }
        
        return $parentObj->addChild($this);
    }
    
    public function addChild($newChild) {
        if(empty($newChild)) {
            return false;
        }
        
        $this->childNodes[] = $newChild;
        return $newChild;
    }
    
    public function hasChild() {
        return !empty($this->childNodes) ? true : false;
    }
    
    public function getFirstChild() {
        if(empty($this->childNodes)) {
            return false;
        }
        
        return reset($this->childNodes);
    }
    
    /**
     * 获取数在相应数深度上的第一个子节点
     * @param $root	数的根节点
     * @param $level 对应于树的深度值，从1开始
     */
    public function getTreeFirstChildOnLevel($level = 1) {
        $level = $level > 1 ? $level : 1;
        
        $first_child = $this;
        while($first_child->hasChild() && --$level > 0) {
            $first_child = $first_child->getFirstChild();
        }
        
        return $first_child;
    }

    public function removeChild() {
        $this->childNodes = null;
        return true;
    }
    
    /**
     * 按照需要的格式返回数据
     */
    public function operationJson() {
        $item = array(
            'id' => $this->id,
            'text' => $this->text,
        	'child' => 1
        );
        
        if(!empty($this->expand_attrs)) {
            $item = array_merge($item, (array)$this->expand_attrs);
        }
        
        //拼接用户自定义数据
        if(!empty($this->userdata)) {
            foreach($this->userdata as $datas) {
                if(!isset($datas['name'], $datas['content'])) {
                    continue;
                }
                $item['userdata'][] = array(
                    'name' => $datas['name'],
                    'content' => $datas['content'],
                );
            }
        }
        
        //处理孩子节点数据
        if(!empty($this->childNodes)) {
            foreach($this->childNodes as $child) {
                $item['item'][] = $child->operationJson();
            }
        }

        return $item;
    }
    
    public function operationHtml() {
        
        $html = "<li id='{$this->id}'><span type='{$this->type}'>{$this->text}</span>";
        
        if(!empty($this->childNodes)) {
            $html .= "<ul>";
            foreach($this->childNodes as $child) {
               $html .= $child->operationHtml();
            }
            $html .= "</ul>";
        }
        $html .= "</li>";
        
        return $html;
    }
    
}

/**
 * Department容器管理类，用户管理预定义的组合对象集合
 * @author $anlicheng
 */
class Container {
    protected $ObjectList = array();
    
    public function getElementById($id) {
        $id = max(intval($id), 0);
        
        return isset($this->ObjectList[$id]) ? $this->ObjectList[$id] : null;
    }
    
    public function buildObjectList($id, $object) {
        if(empty($object)) {
            return null;
        }
        
        $this->ObjectList[$id] = $object;
        return $object;
    }
}