<?php
/**
 * @author Administrator
 * @return array(
 * 		id,		//标签的id
 * 		text,	//要显示的内容
 * 		//追加用户自定数据
 * 		userdata => array( 0 => array(
 * 						'name' => '',
 * 						'content' => ''
 * 					);
 * 		item => array(
 * 			//子节点信息
 * 		),
 * );
 *
 */
class DepartmentAction extends Controller {
    
    public function _initialize() {
        header("Content-Type:text/html;charset=utf-8;");
    }
    
    public function loadTree() {
        $school_id = $this->objInput->getInt('school_id');
        $data_type = $this->objInput->getStr('data_type');
        
        $data_type = strtolower($data_type);
        $data_type = !empty($data_type) && in_array($data_type, array('json', 'html')) ? $data_type : "json";
        
        //获取学校的基本信息
        $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
        $schoolinfo_list = $mSchoolInfo->getSchoolInfoById($school_id);
        $schoolinfo = & $schoolinfo_list[$school_id];
        
        //获取部门的基本信息
        $mDepartment = ClsFactory::Create('Model.mDepartment');
        $department_arr = $mDepartment->getDepartmentBySchoolId($school_id);
        $department_list = & $department_arr[$school_id];
        
        //Department对象管理容器类
        $Container = new Container();
        //树形根节点,根节点是dialogtree中是不可以选的
        $rootObj = new Department(0, $schoolinfo['school_name']);
        $rootObj->setType(0);
        
        //构建树形数据结构
        if(!empty($department_list)) {
            $dpt_ids = array_keys($department_list);
            //创建所有的Department对象，因为如果不预先建立，可能会导致在追加到父对象的时候出错
            foreach($dpt_ids as $id) {
                $departmentObj = new Department();
                $Container->buildObjectList($id, $departmentObj);
            }
            
            /**
             * 数据按照sort_id的值升序排列
             * 注意：排序会重设数组的键值
             */
            $sort_keys = array();
            foreach($department_list as $id=>$dpt) {
                $sort_keys[$id] = intval($dpt['sort_id']);
            }
            array_multisort($sort_keys, SORT_ASC, SORT_NUMERIC, $department_list);
            
            //建立树形的依赖结构
            foreach($department_list as $department) {
                $id = $department['dpt_id'];
                
                $departmentObj = $Container->getElementById($id);
                $departmentObj->setId($id);
                $departmentObj->setName($department['dpt_name']);
                
                $up_id = intval($department['up_id']);
                $up_id && $parentObj = $Container->getElementById($up_id);
                
                if(empty($up_id) || empty($parentObj)) {
                    $rootObj->addChild($departmentObj);
                } else {
                    $departmentObj->appendTo($parentObj);
                }
            }
        }
        
        $first_child = $rootObj->getTreeFirstChildOnLevel(2);
        if(!empty($first_child)) {
            $first_child->setExpandAttrs(array('select'=>1));
        }
        
        //判断是否存在部门信息
        if(!$rootObj->hasChild()) {
            $dpt_obj = new Department(-1, '暂无部门', null, array('select'=>1));
            $rootObj->addChild($dpt_obj);
        }
        
        if($data_type == 'html') {
            echo $rootObj->operationHtml();
        } elseif($data_type == 'json') {
            echo json_encode($rootObj->operationJson());
        }
    }
    
    /**
     * 加载Dialogtree的显示模板文件
     */
    public function loadDialogTreeTemplate() {
        $school_id = $this->objInput->getInt('school_id');
        
        if(!empty($school_id)) {
            //获取学校的基本信息
            $mSchoolInfo = ClsFactory::Create('Model.mSchoolInfo');
            $schoolinfo_list = $mSchoolInfo->getSchoolInfoById($school_id);
            $school_name = $schoolinfo_list[$school_id]['school_name'];
        }
        
        $school_name = !empty($school_name) ? $school_name : "暂无学校相关信息";
        
        $this->assign('school_name', $school_name);
        
        $this->display(WEB_ROOT_DIR . "/View/Template/Public/Dialogtree/template.html");
    }
    
    /**
     *  通过部门id获取部门成员信息
     */
    public function getDptMemberByDptId() {
        $dpt_id = $this->objInput->getInt('dpt_id');
        
        $mDptMember = ClsFactory::Create('Model.mDepartmentMembers');
        $dpt_member_arr = $mDptMember->getDepartmentMembersByDptId($dpt_id);
        $dpt_member_list = & $dpt_member_arr[$dpt_id];
        
        $new_dpt_member_list = $uids = $userlist = array();
        if(!empty($dpt_member_list)) {
            //数据排序处理
            $sort_keys = array();
            foreach($dpt_member_list as $key=>$member) {
                $sort_keys[$key] = intval($member['sort_id']);
            }
            array_multisort($sort_keys, SORT_ASC, SORT_NUMERIC, $dpt_member_list);
            
            foreach($dpt_member_list as $member) {
                $uid = $member['client_account'];
                $uids[] = $uid;
                $new_dpt_member_list[$uid] = array(
                    'duty_name' => $member['duty_name'],
                );
            }
            unset($dpt_member_list, $dpt_member_arr);
        }
        
        if(!empty($uids)) {
            $mUser = ClsFactory::Create('Model.mUser');
            $userlist = $mUser->getUserBaseByUid($uids);
        }
        
        $json_data = array();
        if(!empty($new_dpt_member_list)) {
            foreach($new_dpt_member_list as $uid=>$member) {
                $member['client_name'] = isset($userlist[$uid]['client_name']) ? $userlist[$uid]['client_name'] : $uid;
                $new_dpt_member_list[$uid] = $member;
            }
            $json_data = array(
                'error' => array(
                    'code' => 1,
                    'message' => '获取成功!',
                ),
                'data' => $new_dpt_member_list,
            );
        } else {
            $json_data = array(
                'error' => array(
                    'code' => -1,
                    'message' => '成员信息不存在!',
                ),
                'data' => array(),
            );
        }
        
        echo json_encode($json_data);
    }
    
}

/**
 * 部门接口，用于规定统一的方法
 * @author $anlicheng 2012-5-5
 *
 */
interface DepartmentInterface {
    public function appendTo($parentObj);
    public function addChild($newChild);
    public function removeChild();
    public function hasChild();
    public function getFirstChild();
    public function getTreeFirstChildOnLevel($level);
    public function operationJson();
    public function operationHtml();
}

/**
 * 部门类，配合js提供相应树形结构数据(使用了：组合设计模式,避免多次循环和递归处理)
 * @author $anlicheng 2012-5-5
 *
 */
class Department implements DepartmentInterface {
    protected $id;
    protected $text;
    protected $userdata = array();
    protected $expand_attrs = array();
    
    protected $type = 1;
    
    protected $childNodes = array();
    
    public function __construct($id = null, $text = null, $userdata = null, $expand_atts = null) {
        $this->id = $id;
        $this->text = $text;
        $this->userdata = (array)$userdata;
        $this->expand_attrs = (array)$expand_atts;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setName($text) {
        $this->text = $text;
    }
    
    public function setExpandAttrs($expand_attrs) {
        $this->expand_attrs = (array)$expand_attrs;
    }
    
    public function setUserdata($userdata) {
        $this->userdata = (array)$userdata;
    }
    
    public function setType($type) {
        $this->type = intval($type);
    }
    
    public function appendTo($parentObj) {
        if(!empty($parentObj) && $parentObj instanceof Department) {
            return $parentObj->addChild($this);
        }
        
        return false;
    }
    
    public function addChild($newChild) {
        if(!empty($newChild) && $newChild instanceof Department) {
            $this->childNodes[] = $newChild;
            return $newChild;
        }
        return false;
    }
    
    public function hasChild() {
        return !empty($this->childNodes) ? true : false;
    }
    
    public function getFirstChild() {
        if(!empty($this->childNodes)) {
            return reset($this->childNodes);
        } else {
            return false;
        }
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
 * Department容器管理类，用户管理预定义的Department对象集合
 * @author $anlicheng
 *
 */
class Container {
    protected $deparmentObjectList = array();
    
    public function getElementById($id) {
        $id = max(intval($id), 0);
        if(isset($this->deparmentObjectList[$id])) {
            return $this->deparmentObjectList[$id];
        }
        return false;
    }
    
    public function buildObjectList($id, $object) {
        if(empty($object) || !($object instanceof Department)) {
            return false;
        }
        
        $this->deparmentObjectList[$id] = $object;
        return $object;
    }
}