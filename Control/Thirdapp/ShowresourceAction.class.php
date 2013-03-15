<?php
class ShowresourceAction extends SnsController{
    
    public function _initialize(){
        parent::_initialize();
    }
    
    
      private $_file_ext = array(
        3 => array(
            'html',
            'htm'
        ),
        4 => array(
            'flv'
        ),
        5 => array(
            'mp3'
        ),
        6 => array(
            'swf'
        ),
        8 => array(
            'jpg',
            'gif',
            'jpeg',
            'jpe',
            'png',
            'bmp',
        ),
    );
    
    
    public $_isLoginCheck = false;
    private $_Predisposing_conditions = true;
    
    private $_limit = 30;
    //资源类型_年级_科目_版本_学期(册)_章_节_栏目
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
    
    private function ParamTransfor($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_arr = explode("_",$nav_str);
        
        $new_nav_arr = array();
        foreach($nav_arr as $key=>$val) {
            $new_nav_arr[$this->navs[$key]] = intval($val);
        }
        
        $new_nav_arr['navs'] = implode("_",$new_nav_arr);

        return !empty($new_nav_arr) ? $new_nav_arr : false;
    }
    
    private function get_nav_list($nav_str, $limit) {
        $param_list = explode("_", $nav_str);
        
        foreach($param_list as $key => $val) {
            if(empty($val)) {
                unset($param_list[$key]);
            }
        }
        
        $new_nav_list = $this->ParamTransfor($nav_str);
        
        if(empty($new_nav_list)) {
            return false;
        }
        array_pop($new_nav_list);
        $nav_str = array_slice($new_nav_list, 0,$limit);
        
        $new_nav_str = implode("\_", $nav_str) . "\_";
        $mResourceNavs = ClsFactory::Create("Model.Resource.mResourceNavs");
        
        $nav_list = $mResourceNavs->getResourceNavsByNavValueUseLike($new_nav_str);
        
        return !empty($nav_list) ? $nav_list : false;
    }
    
    /**
     * 班级列表
     */
    private function gradelist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 1);
        //得到grade_ids
        if(!empty($nav_list)) {
            $grade_ids = array();
            foreach($nav_list as $nav) {
                $grade_ids[$nav['grade_id']] = $nav['grade_id'];
            }
        }
        
        $mResourceGrade = ClsFactory::Create("Model.Resource.mResourceGrade");
        $grade_list = $mResourceGrade->getResourceGradeById($grade_ids);
        return !empty($grade_list) ? $grade_list : false;
    }
    
    /**
     * 科目列表
     */
    private function subjectlist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 2);
        
        //得到grade_ids
        if(!empty($nav_list)) {
            $subject_ids = array();
            foreach($nav_list as $nav) {
                $subject_ids[$nav['subject_id']] = $nav['subject_id'];
            }
        }
        
        $mResourceGrade = ClsFactory::Create("Model.Resource.mResourceSubject");
        $subject_list = $mResourceGrade->getResourceSubjectById($subject_ids);
        return !empty($subject_list) ? $subject_list : false;
    }
    
    /**
     * 版本列表
     */
    private function versionlist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 3);
        
        //得到grade_ids
        if(!empty($nav_list)) {
            $version_ids = array();
            foreach($nav_list as $nav) {
                $version_ids[$nav['version_id']] = $nav['version_id'];
            }
        }
        
        $mResourceVersion = ClsFactory::Create("Model.Resource.mResourceVersion");
        $version_list = $mResourceVersion->getResourceVersionById($version_ids);
        return !empty($version_list) ? $version_list : false;
    }
    
    /**
     * 章列表
     */
    private function chapterlist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 4);
        
        //得到grade_ids
        if(!empty($nav_list)) {
            $chapter_ids = array();
            foreach($nav_list as $nav) {
                $chapter_ids[$nav['chapter_id']] = $nav['chapter_id'];
            }
        }
        
        $mResourceChapter = ClsFactory::Create("Model.Resource.mResourceChapter");
        $chapter_list = $mResourceChapter->getResourceChapterById($chapter_ids);
        $z_index = 9999;
        foreach($chapter_list as $key => $val) {
            $val ['short_chapter_name'] = cutstr ( $val ['chapter_name'], 18, true );
            $sort_keys [$key] = $val ['display_order'];
            $val['z_index'] = $z_index;
            $chapter_list [$key] = $val;
            $z_index --;
        }
        
        return !empty($chapter_list) ? $chapter_list : false;
    }
    
    
    /**
     * 节列表
     */
    public function sectionlist() {
        
        $nav_str = $this->objInput->getStr('nav_str');
        
        if(empty($nav_str)) {
            echo "参数丢失请重新操作！";
            exit;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 6);
        
        //得到grade_ids
        if(!empty($nav_list)) {
            $section_ids = array();
            foreach($nav_list as $nav) {
                $section_ids[$nav['section_id']] = $nav['section_id'];
            }
        }
        
        $mResourceSection = ClsFactory::Create("Model.Resource.mResourceSection");
        $section_list = $mResourceSection->getResourceSectionById($section_ids);
        
        foreach ( $section_list as $key => $val ) {
            
            $val['url'] = "/Thirdapp/Showresource/synchroresource/screening/$nav_str" . "_" . "$key";
            $ResourceAttributeinfo[$key] = $val;
            
            $sort_keys [$key] = $val ['display_order'];
        }

        array_multisort ( $sort_keys, SORT_ASC, SORT_NUMERIC, $ResourceAttributeinfo );
        
        foreach ( $ResourceAttributeinfo as & $val ) {
            if (! empty ( $val ))
                $fesival_str .= '<a style="text-decoration:underline;" href="' . $val ['url'] . '">' . $val ['section_name'] . '</a><br />';
        }
        if (! empty ( $fesival_str )) {
            echo $fesival_str;
        } else {
            echo "此章下没有节信息";
        }
    }
    
    /**
     * 学期列表
     */
    private function termlist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_list = $this->get_nav_list($nav_str, 4);
        //得到grade_ids
        if(!empty($nav_list)) {
            $term_ids = array();
            foreach($nav_list as $nav) {
                $term_ids[$nav['term_id']] = $nav['term_id'];
            }
        }
        
        $mResourceTerm = ClsFactory::Create("Model.Resource.mResourceTerm");
        $term_list = $mResourceTerm->getResourceTermById($term_ids);
        return !empty($term_list) ? $term_list : false;
    }
    
    /**
     * 栏目列表
     */
    private function columnlist($nav_str) {
        if(empty($nav_str)) {
            return false;
        }
        
        $nav_str = $this->ParamTransfor($nav_str);
        
        $new_nav_str = implode("_", $nav_str);
        
        $mResourceColumn = ClsFactory::Create("Model.Resource.mResourceColumn");
        $column_list = $mResourceColumn->getResourceColumnByProductId($new_nav_str['product_id']);
        return !empty($column_list) ? current($column_list) : false;
    }
    
    public function show_nav_list ($nav_str) {
        $nav_ed = $this->ParamTransfor($nav_str);
        $nav_list = array();
        if($this->_Predisposing_conditions) {
            $nav_list['grade_list'] = !empty($nav_ed['product_id']) ? $this->gradelist($nav_str) : false; 
            $nav_list['subject_list'] = !empty($nav_ed['grade_id']) ? $this->subjectlist($nav_str) : false; 
            $nav_list['version_list'] = !empty($nav_ed['subject_id']) ? $this->versionlist($nav_str) : false; 
            $nav_list['chapter_list'] = !empty($nav_ed['version_id']) ? $this->chapterlist($nav_str) : false; 
            $nav_list['term_list'] =  !empty($nav_ed['version_id']) ? $this->termlist($nav_str) : false; 
            $nav_list['column_list'] = $this->columnlist($nav_str); 
        }else{
            $nav_list['grade_list'] = $this->gradelist($nav_str); 
            $nav_list['subject_list'] = $this->subjectlist($nav_str); 
            $nav_list['version_list'] = $this->versionlist($nav_str); 
            $nav_list['chapter_list'] = $this->chapterlist($nav_str); 
            $nav_list['term_list'] = $this->termlist($nav_str); 
            $nav_list['column_list'] = $this->columnlist($nav_str); 
        }

        return !empty($nav_list) ? $nav_list : false;
    }
    
    /**
     * 搜索资源列表
     */
    public function searchresource() {
        
        $page = $this->objInput->postInt ( 'page' );
        $page = max ( $page, 1 );
        $resource_name = $this->objInput->postStr ( 'resource_name' );
        $product_id = $this->objInput->postInt ( "product_id" );
        if (empty ( $product_id )) {
            $product_id = 1;
        }
        
        if (empty ( $resource_name )) {
            $this->showError("请输入要搜索的资源标题！","");
        }
        
        $offset = ($page - 1) * $this->_limit;
        
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        if($product_id == 1) {
            list($resource_info['total_num'],$resource_info['resource_list']) = $ResourceApi->getSynchroResourceByTitle ( $resource_name, $offset, $this->_limit);
        }else if($product_id == 2) {
            list($resource_info['total_num'],$resource_info['resource_list']) = $ResourceApi->getQualitySchoolByTitle ( $resource_name, $offset, $this->_limit);
        }else if($product_id == 3) {
            list($resource_info['total_num'],$resource_info['resource_list']) = $ResourceApi->getQualityResourceByTitle ( $resource_name, $offset, $this->_limit);
        }
        
        $totalpage = ceil($resource_info['total_num']/$this->_limit);
        $is_end_page = false;
        if($page >= $totalpage) {
            $is_end_page = true;
            $page = $totalpage;
        }
        
        $totalrow = $resource_info['total_num'];
        $new_resource_info = $resource_info['resource_list'];
       
        $resource_info = $this->addresourceOperationbtn($new_resource_info);
        
        $this->assign ( "product_id", $product_id );
        $this->assign ( 'page', $page );
        $this->assign('is_end_page', $is_end_page);
        $this->assign('totalpage', $totalpage);
        $this->assign ( "resource_info", $resource_info);
        $this->assign ( "totalrow", $totalrow );
        $this->assign ( 'resource_name', $resource_name );
        
        $this->display ( "search" );
    }
    
    private function resource($nav_str, $page) { 
        
        $offset = ($page - 1) * $this->_limit;
        $nav_list = $this->show_nav_list($nav_str);
        
        $new_nav_arr = $this->ParamTransfor($nav_str);
        
        array_pop($new_nav_arr);
        
        foreach($new_nav_arr as $key => $val){
            if(!empty($val))
                $new_nav_arr_1[$key] = "$key=$val";
        }
        
        if(!empty($new_nav_arr)) {
            foreach($new_nav_arr as $key => $val) {
                if(empty($val)) {
                    unset($new_nav_arr[$key]);
                }
            }
        }
        
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        $new_nav_arr_1['resource_status'] = "resource_status=1";
        list($total_num,$ResourceInfo) = $ResourceApi->getReourceInfoByCombinedKey($new_nav_arr_1, null, $offset, $this->_limit);
        $ResourceInfo = $this->addresourceOperationbtn($ResourceInfo);
        return array('resource_info' => $ResourceInfo, 'nav_list' => $nav_list, 'resource_total_num' => $total_num, 'checked_nav' => $new_nav_arr);
    }
    
    /**
     * 根据目录显示同步资源列表
     */
    public function synchroresource() {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        if(empty($nav_str)) {
            $nav_str = '1_';
        }
        
        $page = max(1, intval($page));
        
        if(empty($nav_str)) {
            $this->showError("参数丢失，请重试！", "");
        }
        
        $resource = $this->resource($nav_str, $page);
        
        $flag = false;
        $total_page = ceil($resource['resource_total_num']/$this->_limit);
        if($total_page <= $page) {
            $flag = true;
        }
        
        $this->assign('page', $page);
        $this->assign('flag', $flag);
        $this->assign('totalpage', $total_page);
        $this->assign('totalrows', $resource['resource_total_num']);
        $this->assign('nav_list', $resource['nav_list']);
        $this->assign('checked_nav', $resource['checked_nav']);
        $this->assign('resource', $resource['resource_info']);
        $this->display('synchroresource');
    }
    
    /**
     * 根据目录显示精品网校资源列表
     */
    public function qualityschool() {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        if(empty($nav_str)) {
            $nav_str = '2_';
        }
        
        $page = max(1, intval($page));
        
        if(empty($nav_str)) {
            $this->showError("参数丢失，请重试！", "");
        }
        $resource = $this->resource($nav_str, $page);
        $flag = false;
        $total_page = ceil($resource['resource_total_num']/$this->_limit);
        if($total_page <= $page) {
            $flag = true;
        }
        
        $this->assign('page', $page);
        $this->assign('flag', $flag);
        $this->assign('totalpage', $total_page);
        $this->assign('totalrows', $resource['resource_total_num']);
        $this->assign('nav_list', $resource['nav_list']);
        $this->assign('checked_nav', $resource['checked_nav']);
        $this->assign('resource', $resource['resource_info']);
        $this->display('qualityschool');
    }
    
/**
     * 根据目录显示精品网校资源列表
     */
    public function qualityschool1() {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        
        if(empty($nav_str)) {
            $nav_str = '2_1';
        }
        
        $page = max(1, intval($page));
        
        $page = 1;
        $page = max ( $page, 1 );
        
        $this->_limit = 11;
        $screening = "2_______5";
        $resource = $this->resource($screening, $page);
        $spzx_info = $resource['resource_info'];
        
        $screening = "2______6";
        $resource = $this->resource($screening, $page);
        $yskt_info = $resource['resource_info'];
        
        $screening = "2_______7";
        $resource = $this->resource($screening, $page);
        $msdb_info = $resource['resource_info'];
        
        $screening = "2_______8";
        $resource = $this->resource($screening, $page);
        $jdtk_info = $resource['resource_info'];
        
        foreach($spzx_info as $key => $val) {
            $spzx_info[$key]['title'] = cutstr( $val['title'], 18, true );
        }
        
        foreach($yskt_info as $key => $val) {
            $yskt_info[$key]['title'] = cutstr( $val['title'], 18, true );
        }
        
        foreach($msdb_info as $key => $val) {
            $msdb_info[$key]['title'] = cutstr( $val['title'], 18, true );
        }
        
        foreach($jdtk_info as $key => $val) {
            $jdtk_info[$key]['title'] = cutstr( $val['title'], 18, true );
        }
        $tmp_info = array();
        $resource = $this->resource($nav_str, $page);
        $nav_list = $resource['nav_list'];
        
        $checked_nav = $resource['checked_nav'];
        $this->assign ( 'resource_spzx', array_chunk($spzx_info, 6));
        $this->assign ( 'resource_yskt', array_chunk($yskt_info, 6));
        $this->assign ( 'resource_msdb', array_chunk($msdb_info, 6));
        $this->assign ( 'resource_jdtk', array_chunk($jdtk_info, 6));
        $this->assign ( 'nav_list', $nav_list );
        $this->assign('checked_nav', $checked_nav);
        $this->display ( 'qualityschool1' );
    }
    
    /**
     * 根据目录显示精品资源列表
     */
    public function qualityresource() {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        if(empty($nav_str)) {
            $nav_str = '3_';
        }
        
        $page = max(1, intval($page));
        
        if(empty($nav_str)) {
            $this->showError("参数丢失，请重试！", "");
        }
        $resource = $this->resource($nav_str, $page);
        $flag = false;
        $total_page = ceil($resource['resource_total_num']/$this->_limit);
        if($total_page <= $page) {
            $flag = true;
        }
        
        $this->assign('page', $page);
        $this->assign('flag', $flag);
        $this->assign('totalpage', $total_page);
        $this->assign('totalrows', $resource['resource_total_num']);
        $this->assign('nav_list', $resource['nav_list']);
        $this->assign('checked_nav', $resource['checked_nav']);
        $this->assign('resource', $resource['resource_info']);
        $this->display('qualityresource');
    }
    
	/**
     * 有声课堂播放器下载
     */
    public function download_itf() {

	   $filename = WEB_ROOT_DIR.'/Public/downfile/SoundClassroomPlug_in.zip';
    	if (!file_exists($filename)) {
    		$filename = IMG_SERVER.'/Public/downfile/SoundClassroomPlug_in.zip';
        }
        
       $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
       $down_file->downfile($filename, '有声课堂播放器下载');
    }
    
    //资源查看页
    public function displayResource() {
       import('@.Common_wmw.Constancearr');
       $resource_id = $this->objInput->getInt ( 'resource_id' );
        //swf 21025  ; flv 21032 ; mp3 21033 
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        $resource_info = $ResourceApi->getResourceByIds($resource_id);
        $mResourceProduct = ClsFactory::Create("Model.Resource.mResourceProduct");
        $product_id = $resource_info [$resource_id] ['product_id'];
        $product_info = $mResourceProduct->getResourceProductById($product_id);
        $product_name = $product_info [$product_id] ['product_name'];
        $r_info = $resource_info [$resource_id]; 

        $show_type = $r_info ['show_type'];
        
        $templat_arr = array(
            'html_no' => 1,
            'html_heng' => 2,
            'html_shu' => 3,
            'download' => 4,
            'flv' => 5,
            'mp3' => 6,
            'swf' => 7,
            'img' => 8,
            'itf' => 9,
        );
        
        $template = "displayRadeo";
        $r_info['file_url'] = $r_info ['file_path'] . $r_info ['file_name'];
        
        if(in_array($show_type, array($templat_arr['html_heng'], $templat_arr['html_shu']))) { //多种学习方式 
            $r_info['file_url'] = "";
            
            $learn_type_val = Constancearr::learn_type();
            $learn_type_codes = explode ( ',', $r_info['learn_type'] ); 
            $file_name_arr = explode ( ',', $r_info['file_name']);  
            foreach ( $learn_type_codes as $key => $type_code ) {
                $r_info['class'][$key] = array(
                    'learn_type' => $learn_type_val [$type_code],
                    'file_url' => $r_info['file_path'].$file_name_arr [$key],
                );
            }
            
            if ($show_type == $templat_arr['html_heng']) {
                $template = "displayHengban";
            } else if ($show_type == $templat_arr['html_shu']) {
                $template = "displayShuban";
            }
        } else if ($show_type == $templat_arr['flv']) {
            $r_info ['show_type_flag'] = 'flv';
        } elseif ($show_type == $templat_arr['mp3']) {
            $r_info ['show_type_flag'] = 'mp3';
        } elseif ($show_type == $templat_arr['swf']) {
            //$r_info ['show_type_flag'] = 'swf'; //无法播放flash内部链接的flash
            header("Location:" .$r_info ['file_url']);
        } elseif ($show_type == $templat_arr['img']) {
            $r_info ['show_type_flag'] = 'img';
        }
        
        $this->assign ( 'product_name', $product_name );  
        $this->assign ( 'r_info', $r_info );
        
        $this->display ( $template );
    }
    
    function getstr() {
        $i = 0;
        $j = 1;
        $str = "array(";
        for($i; $i < 1000; $i ++) {
            $str .= "
                    'field$i' => array(<br />
                        0 => 	'" . rand ( 1, 7 ) . "',<br />
                        1 => 	'" . rand ( 1, 7 ) . "',<br />
                        2 => 	'" . rand ( 1, 7 ) . "',<br />
                        3 => 	'$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i$i',<br />
                        4 => 	'$i$i$i$i$i$i$i$i$i$i',<br />
                        5 => 	'1',<br />
                        6 => 	'$i.html',<br />
                        7 => 	'$i$i',<br />
                        8 => 	'第" . rand ( 1000, 9999 ) . "章',<br />
                        9 => 	'第" . rand ( 1000, 9999 ) . "节',<br />
                        10 => 	'$i.jpg',<br />
                        11 => 	'1',<br />
                      ),<br />
                      ";
            $j ++;
            if ($j > 10)
                $j = 1;
        }
        echo $str;
    }
    
    
/**
     * 文件下载
     */
    public function download_zy() {
       $resource_id = $this->objInput->getInt('resource_id');
       
       if(empty($resource_id)) {
           $this->showError('无法定位资源!');
       }
       
       $mResourceInfo = ClsFactory::Create("Model.Resource.mResourceInfo");
       $resource_list = $mResourceInfo->getResourceInfoById($resource_id);
       $resource_info = & $resource_list[$resource_id];
       
       if(empty($resource_info)) {
           $this->showError('资源信息不存在!');
       }
       
       $file_path = $resource_info['file_path'];
       $file_name = $resource_info['file_name'];
       $title     = $resource_info['title'];
       
       //处理文件路径不是以'/'结尾的情况
       if(substr($file_path, -1) != '/') {
           $file_path .= '/';
       }
       
       $real_file = $file_path . $file_name;
       
        if(strpos($real_file, '/attachment') === 0){
           $real_file = WEB_ROOT_DIR . $real_file;
       }
       
       try {
           $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
           $down_file->downfile($real_file, $title);
       } catch(Exception $e) {
           $this->showError($e->getMessage());
       }
    }
    
    
    private function addresourceOperationbtn($ResourceInfo){
        
        if(empty($ResourceInfo)) {
            return false;
        }
        
        foreach ( $ResourceInfo as $resource_id => & $resource_info ) {
                if (empty ( $resource_info ['title'] )) {
                    continue;
                }
                
                if (in_array ( intval($resource_info ['file_type']), array (1, 2, 7, 9, 10) )) {
                    $resource_info['show_btn'] = "下载";
                   $resource_info ['file_path'] = '/Thirdapp/Showresource/download_zy/resource_id/' . $resource_id;//$resource_val ['file_path'] . $resource_val ['file_name'];
                } elseif (in_array ( $resource_info ['file_type'], array (4, 5 ) )) {
                $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "播放";
                        $resource_info ['file_path'] = "/Thirdapp/Showresource/displayResource/resource_id/$resource_id";
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Thirdapp/Showresource/download_zy/resource_id/' . $resource_id;
                    }
                } elseif (in_array ( $resource_info ['file_type'], array (3, 8, 6 ) )) {
                    $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "查看";
                        if($resource_info ['show_type'] != 1){
                            $resource_info['file_path'] = "/Thirdapp/Showresource/displayResource/resource_id/$resource_id";
                        }else{
                            $resource_info['file_path'] = $resource_info ['file_path'] . $resource_info ['file_name'];
                        }
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Thirdapp/Showresource/download_zy/resource_id/' . $resource_id;
                    }
                }
                
                $resource_info['short_title'] = cutstr ( $resource_info ['title'], 24, true );
                $ResourceInfo[$resource_id] = $resource_info;
            }
            
            return !empty($ResourceInfo) ? $ResourceInfo : false;
    }
    
    

}