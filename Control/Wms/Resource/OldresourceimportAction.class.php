<?php
class OldresourceimportAction extends Controller {
    private $clear_resource_ids = array();
    private $resource_list = array();
    
    public function import() {
        $this->initResourceList();
        $this->renameResourceFields();
        $this->parseResourceInfo();
        
        $this->initDataBaseBat();
        $this->clearResourceInfo();
    }
    
    /**
     * 提取要过滤的资源信息
     */
    private function initResourceList() {
        $mResource = ClsFactory::Create('Model.mResource');
        $resource_list = $mResource->getResourceInfo();
        
        $this->resource_list = !empty($resource_list) ? $resource_list : false;
        $this->clear_resource_ids = array_keys((array)$resource_list);
    }
    
    /**
     * 字段的重命名
     */
    private function renameResourceFields() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        $name_map = array(
            'grade' => 'grade_id',
            'subject' => 'subject_id',
            'version' => 'version_id',
         	'term' => 'term_id',
            'column_type' => 'column_id',
        
            'chapter' => 'chapter_name',
            'section' => 'section_name',
        );
        
        foreach($this->resource_list as $resource_id => $resource) {
            foreach($name_map as $old_field=>$new_field) {
                if(isset($resource[$old_field])) {
                    $resource[$new_field] = $resource[$old_field];
                    unset($resource[$old_field]);
                }
            }
            $this->resource_list[$resource_id] = $resource;
        }
        
        return true;
    }
    
    /**
     * 将数据字段中的名字转换成对应的id值
     */
    private function parseResourceInfo() {
        $this->parseGrade();
        $this->parseSubject();
        $this->parseVersion();
        $this->parseTerm();
        $this->parseColumntype();
    }
    
    private function parseGrade() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        //获取年级信息，并建立年级id到年级名称的映射关系
        $mResourceGrade = ClsFactory::Create('Model.Resource.mResourceGrade');
        $grade_list = $mResourceGrade->getAllResourceGrade();
        foreach($grade_list as $grade_id=>$grade_info) {
            $grade_list[$grade_id] = trim($grade_info['grade_name']);
        }
        
        foreach($this->resource_list as $resource_id => $resource) {
            $grade_name = trim($resource['grade_id']);
            if(($grade_id = array_search($grade_name, $grade_list)) !== false) {
                $resource['grade_id'] = $grade_id;
            } else {
                $resource['grade_id'] = key($grade_list);
            }
            $this->resource_list[$resource_id] = $resource;
        }
        
        return true;
    }
    
    private function parseSubject() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        //获取科目信息，并建立科目id到科目名称的映射关系
        $mResourceSubject = ClsFactory::Create('Model.Resource.mResourceSubject');
        $subject_list = $mResourceSubject->getAllResourceSubject();
        foreach($subject_list as $subject_id=>$subject_info) {
            $subject_list[$subject_id] = trim($subject_info['subject_name']);
        }
        
        foreach($this->resource_list as $resource_id => $resource) {
            if(!isset($resource['subject_id'])) {
                continue;
            }
            
            $subject_name = trim($resource['subject_id']);
            if(($subject_id = array_search($subject_name, $subject_list)) !== false) {
                $resource['subject_id'] = $subject_id;
            } else {
                $resource['subject_id'] = 0;
            }
            $this->resource_list[$resource_id] = $resource;
        }
        
        return true;
        
    }
    
    private function parseVersion() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        $mResourceVersion = ClsFactory::Create('Model.Resource.mResourceVersion');
        $version_list = $mResourceVersion->getAllResourceVersion();
        foreach($version_list as $version_id => $version) {
            $version_list[$version_id] = trim($version['version_name']);
        }
        
        foreach($this->resource_list as $resource_id => $resource) {
            if(!isset($resource['version_id'])) {
                continue;
            }
            
            $version_name = trim($resource['version_id']);
            if(($version_id = array_search($version_name, $version_list)) !== false) {
                $resource['version_id'] = $version_id;
            } else {
                $resource['version_id'] = 0;
            }
            $this->resource_list[$resource_id] = $resource;
        }
        
        return true;
    }
    
    private function parseTerm() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        $mResourceTerm = ClsFactory::Create('Model.Resource.mResourceTerm');
        $term_list = $mResourceTerm->getAllResourceTerm();
        foreach($term_list as $term_id => $term) {
            $term_list[$term_id] = trim($term['term_name']);
        }
        
        foreach($this->resource_list as $resource_id => $resource) {
            if(!isset($resource['term_id'])) {
                continue;
            }
            
            $term_name = trim($resource['term_id']);
            if(($term_id = array_search($term_name, $term_list)) !== false) {
                $resource['term_id'] = $term_id;
            } else {
                $resource['term_id'] = 0;
            }
            $this->resource_list[$resource_id] = $resource;
        }
    }
    
    private function parseColumntype() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        $mResourceColumn = ClsFactory::Create('Model.Resource.mResourceColumn');
        $column_list = $mResourceColumn->getAllResourceColumn();
        foreach($column_list as $column_id => $column) {
            $column_list[$column_id] = trim($column['column_name']);
        }
        
        foreach($this->resource_list as $resource_id => $resource) {
            if(!isset($resource['column_id'])) {
                continue;
            }
            
            $column_name = trim($resource['column_id']);
            if(($column_id = array_search($column_name, $column_list)) !== false) {
                $resource['column_id'] = $column_id;
            } else {
                $resource['column_id'] = 0;
            }
            
            $this->resource_list[$resource_id] = $resource;
        }
    }
    
    private function initDataBaseBat() {
        import('@.Control.Api.Resource.ResourceApi');
        $resourceApi = new ResourceApi();
        
        list($resource_list, $attr_list) = $this->groupResource();
        if(!empty($resource_list)) {
            $resourceApi->addResourceBat($resource_list);
        }
        if(!empty($attr_list)) {
            $resourceApi->addDynamicAttrsBat($attr_list);
        }
    }
    
    private function clearResourceInfo() {
        $mResource = ClsFactory::Create('Model.mResource');
        return $mResource->delResourceinfoBat($this->clear_resource_ids);
    }
    
 	/**
     * 通过title的标示将资源信息进行分组处理
     * @param $resource_list
     */
    private function groupResource() {
        if(empty($this->resource_list)) {
            return false;
        }
        
        $title_mark = defined('TITLE_MARK') ? TITLE_MARK :  '<notitle>';
        
        $group_resource_list = $group_attr_list = array();
        foreach($this->resource_list as $key => $resource) {
            if(stripos($resource['title'], $title_mark) !== false) {
                $group_attr_list[] = $resource;
            } else {
                $group_resource_list[] = $resource;
            }
            unset($this->resource_list[$key]);
        }
        
        return array($group_resource_list, $group_attr_list);
    }
}