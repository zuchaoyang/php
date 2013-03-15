<?php
class ResourceParser {
    /**
     * 转换资源的属性信息
     * @param $resource_list
     */
    public function parseResource($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        $resource_list = $this->appendGradeInfo($resource_list);
        $resource_list = $this->appendSubjectInfo($resource_list);
        $resource_list = $this->appendVersionInfo($resource_list);
        $resource_list = $this->appendTermInfo($resource_list);
        $resource_list = $this->appendColumnInfo($resource_list);
        $resource_list = $this->appendFileType($resource_list);
        $resource_list = $this->appendShowTemplate($resource_list);
        $resource_list = $this->appendChapterInfo($resource_list);
        $resource_list = $this->appendSectionInfo($resource_list);
        
        return $resource_list;
    }
    
    /**
     * 追加年级信息
     * @param $resource_list
     */
    private function appendGradeInfo($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        $mResourceGrade = ClsFactory::Create('Model.Resource.mResourceGrade');
        $grade_list = $mResourceGrade->getAllResourceGrade();
        
        foreach($resource_list as $resource_id => $resource) {
            $grade_id = intval($resource['grade_id']);
            $resource['grade_name'] = isset($grade_list[$grade_id]) ? $grade_list[$grade_id]['grade_name'] : '暂无';
            $resource_list[$resource_id] = $resource;
        }
        
        return $resource_list;
    }
    
    /**
     * 追加科目信息
     * @param $resource_list
     */
    private function appendSubjectInfo($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        $subject_ids = array();
        foreach($resource_list as $resource) {
            $subject_ids[] = $resource['subject_id'];
        }
        $subject_ids = array_unique($subject_ids);
        
        $mResourceSubject = ClsFactory::Create('Model.Resource.mResourceSubject');
        $subject_list = $mResourceSubject->getResourceSubjectById($subject_ids);
        
        foreach($resource_list as $resource_id => $resource) {
            $subject_id = $resource['subject_id'];
            $resource['subject_name'] = isset($subject_list[$subject_id]) ? $subject_list[$subject_id]['subject_name'] : '暂无';
            
            $resource_list[$resource_id] = $resource;
        }
        
        return $resource_list;
    }
    
    /**
     * 追加版本信息
     * @param $resource_list
     */
    private function appendVersionInfo($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        $version_ids = array();
        foreach($resource_list as $resource_id => $resource) {
            $version_ids[] = $resource['version_id'];    
        }
        $version_ids = array_unique($version_ids);
        
        $mResourceVersion = ClsFactory::Create('Model.Resource.mResourceVersion');
        $version_list = $mResourceVersion->getResourceVersionById($version_ids);
        
        foreach($resource_list as $resource_id=>$resource) {
            $version_id = $resource['version_id'];
            $resource['version_name'] = isset($version_list[$version_id]) ? $version_list[$version_id]['version_name'] : '暂无';
            
            $resource_list[$resource_id] = $resource;
        }
        
        return $resource_list;
     }
     
     /**
      * 追加学期信息
      * @param $resource_list
      */
     private function appendTermInfo($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $mResourceTerm = ClsFactory::Create('Model.Resource.mResourceTerm');
         $term_list = $mResourceTerm->getAllResourceTerm();
         
         foreach($resource_list as $resource_id => $resource) {
             $term_id = $resource['term_id'];
             $resource['term_name'] = isset($term_list[$term_id]) ? $term_list[$term_id]['term_name'] : '暂无';
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
     
     /**
      * 追加栏目信息
      * @param $resource_list
      */
     private function appendColumnInfo($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $mResourceColumn = ClsFactory::Create('Model.Resource.mResourceColumn');
         $column_list = $mResourceColumn->getAllResourceColumn();
         
         foreach($resource_list as $resource_id => $resource) {
             $column_id = $resource['column_id'];
             $resource['column_name'] = isset($column_list[$column_id]) ? $column_list[$column_id]['column_name'] : '暂无';
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
    
     /**
      * 获取文件类型 
      * @param $resource_list
      */
     private function appendFileType($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $mResourceFileType = ClsFactory::Create('Model.Resource.mResourceFileType');
         $file_type_list = $mResourceFileType->getAllResourceFileType();
         
         foreach($resource_list as $resource_id => $resource) {
             $file_type = $resource['file_type'];
             $resource['file_type_name'] = isset($file_type_list[$file_type]) ? $file_type_list[$file_type]['file_type'] : '暂无';
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
     
     /**
      * 追加展示类型
      * @param $resource_list
      */
     private function appendShowTemplate($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $mResourceShowTemplate = ClsFactory::Create('Model.Resource.mResourceShowTemplate');
         $show_template_list = $mResourceShowTemplate->getAllResourceShowTemplate();
         
         foreach($resource_list as $resource_id => $resource) {
             $show_type = $resource['show_type'];
             $resource['show_type_name'] = isset($show_template_list[$show_type]) ? $show_template_list[$show_type]['showtemplate_name'] : '暂无';
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
     
     /**
      * 追加章信息
      * @param $resource_list
      */
     private function appendChapterInfo($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $chapter_ids = array();
         foreach($resource_list as $resource) {
             $chapter_ids[] = $resource['chapter_id'];
         }
         $chapter_ids = array_unique($chapter_ids);
         
         $mResourceChapter = ClsFactory::Create('Model.Resource.mResourceChapter');
         $chapter_list = $mResourceChapter->getResourceChapterById($chapter_ids);
         
         foreach($resource_list as $resource_id => $resource) {
             $chapter_id = $resource['chapter_id'];
             $resource['chapter_name'] = isset($chapter_list[$chapter_id]) ? $chapter_list[$chapter_id]['chapter_name'] : '暂无';
             
             //去掉前缀
             $resource['chapter_name'] = $this->dropPrefix($resource['chapter_name']);
             
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
     
     /**
      * 追加节信息
      * @param $resource_list
      */
     private function appendSectionInfo($resource_list) {
         if(empty($resource_list)) {
             return false;
         }
         
         $section_ids = array();
         foreach($resource_list as $resource) {
             $section_ids[] = $resource['section_id'];
         }
         $section_ids = array_unique($section_ids);
         
         $mResourceSection = ClsFactory::Create('Model.Resource.mResourceSection');
         $section_list = $mResourceSection->getResourceSectionById($section_ids);
         
         foreach($resource_list as $resource_id => $resource) {
             $section_id = $resource['section_id'];
             $resource['section_name'] = isset($section_list[$section_id]) ? $section_list[$section_id]['section_name'] : '暂无';
             //去掉前缀
             $resource['section_name'] = $this->dropPrefix($resource['section_name']);
             
             $resource_list[$resource_id] = $resource;
         }
         
         return $resource_list;
     }
     
     /**
      * 去掉章节的前缀
      * @param $str
      */
     private function dropPrefix($str) {
         if(empty($str)) {
             return false;
         }
         
         $arr = explode('_', $str);
         if(count($arr) > 1 && preg_match("/^[a-zA-Z0-9]+$/", $arr[0])) {
             unset($arr[0]);
         }
         
         return implode('_', $arr);
     }
     
}