<?php
class ResourceAction extends SnsController{
    public function _initialize(){
        C(include WEB_ROOT_DIR . '/Config/Resource/config.php');
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
            
            $val['url'] = "/Sns/Resource/Resource/synchroresource/screening/$nav_str" . "_" . "$key";
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
            $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
            if($product_id==1){
                $backurl="/Sns/Resource/Resource/synchroresource/screening/1_$grade_id";
            }elseif($product_id==2){
                $backurl="/Sns/Resource/Resource/qualityschool/screening/2_$grade_id";
            }else{
                $backurl="/Sns/Resource/Resource/qualityresource/screening/3_$grade_id";
            }
            $this->showError("请输入要搜索的资源标题！",$backurl);
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
        $resource_info['resource_list'] = $this->addresourceOperationbtn($resource_info['resource_list']);
        
        $school_info = array_shift($userinfo['school_info']);
        $class_info = array_shift($userinfo['class_info']);
        $this->assign("tpl_school_Name", $school_info['school_name']);
        $this->assign("tpl_grade_id_name", $class_info['grade_id_name']);
        $this->assign("tpl_class_name", $class_info['class_name']);
        $this->assign ( "product_id", $product_id );
        $this->assign ( 'page', $page );
        $this->assign('is_end_page', $is_end_page);
        $this->assign('totalpage', $totalpage);
        $this->assign ( "resource_info", $resource_info['resource_list'] );
        $this->assign ( "totalrow", $resource_info['total_num'] );
        $this->assign ( 'resource_name', $resource_name );
        
        $this->display ( "search" );
    }
    
    private function resource($nav_str, $page) { 
        $offset = ($page - 1) * $this->_limit;
        $nav_list = $this->show_nav_list($nav_str);
        $new_nav_arr = $this->ParamTransfor($nav_str);
        
        array_pop($new_nav_arr);
        foreach($new_nav_arr as $key => $val) {
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
        
        list($total_num, $ResourceInfo) = $ResourceApi->getReourceInfoByCombinedKey($new_nav_arr_1, null,  $offset, $this->_limit);
        
        $ResourceInfo = $this->addresourceOperationbtn($ResourceInfo);
        return array('resource_info' => $ResourceInfo, 'nav_list' => $nav_list, 'resource_total_num' => $total_num, 'checked_nav' => $new_nav_arr);
    }
    
    /**
     * 根据目录显示同步资源列表
     */
    public function synchroresource() {
        if (!in_array($this->getuserlevel($this->user ['client_account']), array(1,3))) {
            $this->showError("你没有查看此类资源的权限", "/Homeuser/Index/spacehome/spaceid/" . $this->user['client_account']);
        }
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
        
        if(empty($nav_str)) {
            $nav_str = '1_' . $grade_id;
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
        $total_page = !empty($total_page) ? $total_page : 1;
        
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
        if (!in_array($this->getuserlevel($this->user ['client_account']), array(3))) {
            $this->showError("你没有查看此类资源的权限", "/Sns/Resource/Resource/synchroresource");
        }
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
        
        if(empty($nav_str)) {
            $nav_str = '2_' . $grade_id;
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
        
        $total_page = !empty($total_page) ? $total_page : 1;
        
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
        if (!in_array($this->getuserlevel($this->user ['client_account']), array(3))) {
            $this->showError("你没有查看此类资源的权限", "/Sns/Resource/Resource/synchroresource");
        }
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
        
        if(empty($nav_str)) {
            $nav_str = '2_' . $grade_id;
        }
        
        $page = max(1, intval($page));
        
        $page = 1;
        $page = max ( $page, 1 );
        $userinfo = $this->user;
        
        if (!in_array($this->getuserlevel($this->user['client_account']), array(2,3))) {
            $this->showError("你没有查看此类资源的权限", "/Sns/Resource/Resource/synchroresource");
        }
        
        $this->_limit = 11;
        $screening = "2_" . $grade_id . "______5";
        $resource = $this->resource($screening, $page);
        $spzx_info = $resource['resource_info'];
        
        $screening = "2_" . $grade_id . "______6";
        $resource = $this->resource($screening, $page);
        $yskt_info = $resource['resource_info'];
        
        $screening = "2_" . $grade_id . "______7";
        $resource = $this->resource($screening, $page);
        $msdb_info = $resource['resource_info'];
        
        $screening = "2_" . $grade_id . "______8";
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
        $screening = "2_" . $grade_id;
        $resource = $this->resource($screening, $page);
        $nav_list = $resource['nav_list'];

        $checked_nav = $resource['checked_nav'];
        $tmp = array_keys($userinfo['class_info']); 
        $class_code = $tmp[0];
        $this->assign("UserInfo",$userinfo );
        $this->assign("class_code", $class_code);
        $this->assign("tpl_class_code", $class_code);
        $school_info = array_shift($userinfo['school_info']);
        $class_info = array_shift($userinfo['class_info']);
        $this->assign("tpl_school_Name", $school_info['school_name']);
        $this->assign("tpl_grade_id_name", $class_info['grade_id_name']);
        $this->assign("tpl_class_name", $class_info['class_name']);
        $this->assign ( 'user_level', $userinfo ['user_level'] );
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
        if ($this->getuserlevel($this->user['client_account']) != 3) {
            $this->showError("你没有查看此类资源的权限", "/Sns/Resource/Resource/synchroresource");
        }
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            $nav_str = $this->objInput->getStr('screening');
            $page = $this->objInput->getInt('page');
        }else{
            $nav_str = $this->objInput->postStr('screening');
            $page = $this->objInput->postInt('page');
        }
        
        $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
        
        if(empty($nav_str)) {
            $nav_str = '3_' . $grade_id;
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
        
        $total_page = !empty($total_page) ? $total_page : 1;
        
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
                   $resource_info ['file_path'] = '/Sns/Resource/Resource/download_zy/resource_id/' . $resource_id;//$resource_val ['file_path'] . $resource_val ['file_name'];
                } elseif (in_array ( $resource_info ['file_type'], array (4, 5 ) )) {
                $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "播放";
                        $resource_info ['file_path'] = "/Sns/Resource/Resource/displayResource/resource_id/$resource_id";
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Sns/Resource/Resource/download_zy/resource_id/' . $resource_id;
                    }
                } elseif (in_array ( $resource_info ['file_type'], array (3, 8, 6 ) )) {
                    $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "查看";
                        if($resource_info ['show_type'] != 1){
                            $resource_info['file_path'] = "/Sns/Resource/Resource/displayResource/resource_id/$resource_id";
                        }else{
                            $resource_info['file_path'] = $resource_info ['file_path'] . $resource_info ['file_name'];
                        }
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Sns/Resource/Resource/download_zy/resource_id/' . $resource_id;
                    }
                }
                
                $resource_info['short_title'] = cutstr ( $resource_info ['title'], 24, true );
                $ResourceInfo[$resource_id] = $resource_info;
            }
            
            return !empty($ResourceInfo) ? $ResourceInfo : false;
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
    
   /*********************************************前台上传资源页面**********************************************/
    public function upload_resource() {
        $resource_type = 1;//$this->objInput->getInt('resource_type');
        $grade_list = $this->gradelist($resource_type);
        $column_list = $this->columnlist($resource_type);
        
        //获取上传时的密钥处理
        import('@.Control.Sns.Resource.Extra.UploadSecretkey');
        $UploadSecretKeyObject = new UploadSecretkey();
        $secret_key = $UploadSecretKeyObject->getSecretkey($this->user['client_account']);
        
        //获取上传时允许的附加类型
        $allow_upload_types = $this->getAllowUploadTypes();
        
        $this->assign('uid', $this->user['client_account']);
        $this->assign('secret_key', $secret_key);
        
        $this->assign('allow_upload_types', $allow_upload_types);
        
        $this->assign('resource_type',$resource_type);
        $this->assign('grade_list',$grade_list);
        $this->assign('column_list',$column_list);
        
        $this->display('upload_resource');
    }
    
    /**
     * 获取允许上传的文件类型,多种类型之间的分割符号使用";"
     */
    private function getAllowUploadTypes() {
        C(include WEB_ROOT_DIR . '/Config/Resource/config.php');
        $allow_upload_types = C('allow_upload_types');
        
        $allow_list = array();
        foreach($allow_upload_types as $key => $types) {
            if(empty($types)) {
                continue;
            }
            $allow_list = array_merge($allow_list, (array)explode(',', $types));
        }
        $allow_list = array_unique($allow_list);
        
        foreach($allow_list as $key => $type) {
            $allow_list[$key] = "*.$type";
        }
        
        return implode(';', $allow_list);
    }
    
    /**
     * 
     */
    public function sectionvallist() {
        $nav_str = $this->objInput->getStr('nav_str');
        $nav_list = $this->show_nav_list($nav_str);
        echo json_encode($nav_list);
    }
    
	/**
     * 节列表
     */
    public function section_json() {
        $nav_str = $this->objInput->getStr('nav_str');
        $nav_list = $this->get_nav_list($nav_str, 5);
        //得到grade_ids
        
        if(!empty($nav_list)) {
            $subject_ids = array();
            foreach($nav_list as $nav) {
                $section_ids[$nav['section_id']] = $nav['section_id'];
            }
        }
        
        $mResourceGrade = ClsFactory::Create("Model.Resource.mResourceSection");
        
        $section_list = $mResourceGrade->getResourceSectionById($section_ids);
        
        echo json_encode($section_list);
    }
    
    
    private function getuserlevel($uid) {
        if(empty($uid)) {
            return false;
        }
        
        $mUser = ClsFactory::Create("Model.mUser");
        return $mUser->getResourceUserLevel($uid);
    }
    
    //同步资源上传方法
    public function upload_resource_do() {
        $section_id     = $this->objInput->postStr('section_id');
        $title          = $this->objInput->postStr('title');
        $column_id      = $this->objInput->postInt('column_id');
        $description    = $this->objInput->postStr('description');
        $upload_file    = $this->objInput->postStr('upload_file');
        
        //解析上传文件的相关属性
        $upload_file_attrs = json_decode(base64_decode($upload_file), true);
        $path_infos = pathinfo($upload_file_attrs['filename']);
        
        $ext = $path_infos['extension'];
        $file_name = $path_infos['basename'];
        
        //解析资源的属性
        $param_list = $this->ParamTransfor($section_id);
        
        //错误处理
        if(empty($param_list['grade_id'])) {
            $this->showError('年级信息不能为空!');
        }
        if(empty($param_list['subject_id'])) {
            $this->showError('科目信息不能为空!');
        }
        if(empty($param_list['version_id'])) {
            $this->showError('版本信息不能为空!');
        }        
        if(empty($param_list['chapter_id'])) {
            $this->showError('章信息不能为空!');
        }
        if(empty($param_list['section_id'])) {
            $this->showError('节信息不能为空!');
        }
        if(empty($column_id)) {
            $this->showError('分类信息不能为空!');
        }
        if(empty($title)) {
            $this->showError('资源名称不能为空!');
        }
        if(empty($upload_file_attrs)) {
            $this->showError('请上传附件信息!');
        }
        
        //对资源描述进行截取
        if(!empty($description)) {
            import('@.Common_wmw.WmwString');
            $description = WmwString::mbstrcut($description, 0, 400, 1, true);
        }
        
        import('@.Common_wmw.Pathmanagement_sns');
        $dataarr = array(
        	'title'         => $title,
            'description'   => $description,
            'product_id'    => 1,
            'grade_id'      => $param_list['grade_id'],
            'subject_id'    => $param_list['subject_id'],
            'version_id'    => $param_list['version_id'],
            'chapter_id'    => $param_list['chapter_id'],
            'section_id'    => $param_list['section_id'],
            'column_id'     => $column_id,
            'file_type'     => $this->checkoutFileTypeByExt($ext),
            'show_type'     => $this->checkoutShowTypeByExt($ext),
            'file_path'     => Pathmanagement_sns::getResource(),
            'file_name'     => $file_name,
            'is_system'     => 0,
            'add_account'   => $this->user['client_account'],
            'resource_status' => 0,
            'add_time'      => time(),
        );
        
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        list($success_resource_ids, $fail_resource_list) = $ResourceApi->addResource($dataarr);
        if(empty($success_resource_ids)) {
           $this->showError('上传失败','/Sns/Resource/Resource/upload_resource');
        }
        
        $this->showSuccess('上传成功!', "/Sns/Resource/Resource/upload_resource");
    }
    
    //我的资源列表
    public function my_upload_resource_list() {
        if($this->isGet()) {
            $page                = $this->objInput->getInt('page');
            $search_name_encode  = $this->objInput->getStr('search_name');
            $resource_status     = $this->objInput->getInt('resource_status');
            
            $search_name = urldecode($search_name_encode);
        } else {
            $search_name     = $this->objInput->postStr('search_name');
            $resource_status = $this->objInput->postInt('resource_status');
        }
        
        $page = max(1, $page);
        $search_name = str_replace(array('%', '_'), '', $search_name);
        $resource_status = $resource_status !==false && in_array($resource_status, array(-1, 0, 1)) ? $resource_status : 0;
        
        $resource_status_new[] = $resource_status;
        if($resource_status === -1){
            $resource_status_new[] = -2;
        }
        
        $limit = 10;
        $offset = ($page-1) * $limit;
        $where_appends = array(
            'is_system' => "is_system=0",
            'resource_status' => "resource_status in ('" . implode("','",$resource_status_new)."')",
        );  
        
        //支持标题搜索
        if(!empty($search_name)) {
            $where_appends['title'] = "title like '%$search_name%'";
        }
        
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        list($total_nums, $resource_list) = $ResourceApi->getResourceInfoByUid($this->user['client_account'], $where_appends, $offset, $limit);
        
        $total_page = ceil($total_nums / $limit);
        
        //页码显示
        $url_append = !empty($search_name) ? "&search_name=" . urlencode($search_name) : "";
        $page_url = "/Sns/Resource/Resource/my_upload_resource_list?page=#page#&resource_status=$resource_status" . $url_append;
        $page_list = array(
            'pre_page' => $page > 1 ? str_replace("#page#", $page - 1, $page_url) : false,
            'next_page' => $page < $total_page ? str_replace('#page#', $page + 1, $page_url) : false,
        );
        
        import('@.Common_wmw.WmwString');
        $resource_list = $this->addresourceOperationbtn($resource_list);
        foreach($resource_list as $resource_id=>$resource_info) {
            $resource_info['add_time'] = date('Y-m-d H:i:s',$resource_info['add_time']);
            $resource_info['file_size'] = $this->getFileSize($resource_info['file_path'], $resource_info['file_name']);
            $resource_list[$resource_id] = $resource_info;
        }
        
        $this->assign('page', $page);
        $this->assign('resource_status', $resource_status);
        $this->assign('search_name', $search_name);
        $this->assign('total_page', $total_page);
        $this->assign('page_list', $page_list);
        
        $this->assign('resource_list', $resource_list);
        
        $this->display('my_upload');
    }
    
    //删除资源列表
    public function delete_resource() {
        $delete_resources = $this->objInput->postArr('delete_resources');
        //验证是否合法
        $resource_ids = array();
        if(!empty($delete_resources)) {
            foreach($delete_resources as $key => $resource) {
                list($resource_id, $md5_key) = explode('_', $resource);
                if($this->getMd5key($resource_id) == $md5_key) {
                    $resource_ids[] = $resource_id;
                }
            }
        }
        //删除用户数据
        if(!empty($resource_ids)) {
            $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
            $wherearr = array(
                'resource_status' => 3
            );
            
            foreach($resource_ids as $resource_id) {
                $resault = $mResourceInfo->modifyResourceInfo($wherearr,$resource_id);
            }
        }
        
        if(!empty($resault)) {
            $this->showSuccess('删除成功!','/Sns/Resource/Resource/my_upload_resource_list');
        } else {
            $this->showSuccess('删除失败!','/Sns/Resource/Resource/my_upload_resource_list');
        }
        
    }
    
    
    //展示已删除资源的列表
    public function delete_resource_list() {
        if($this->isGet()) {
            $page = $this->objInput->getInt('page');
            $search_name_encode  = $this->objInput->getStr('search_name');
            
            $search_name = urldecode($search_name_encode);
        } else {
            $search_name = $this->objInput->postStr('search_name');
        }
        
        $page = max(1, $page);
        $search_name = str_replace(array('%', '_'), '', $search_name);
        
        $limit = 10;
        $offset = ($page-1) * $limit;
        
        $wherearr = array(
            'add_account' => "add_account=".$this->user['client_account'],
            'is_system' => "is_system=3",
        );
        if(empty($search_name)) {
            $wherearr[] = "title like %$search_name%";
        }
        
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        list($total_nums, $resource_list) = $ResourceApi->getReourceInfoByCombinedKey($wherearr, null, $offset, $limit);
        
        $total_page = ceil($total_nums / $limit);
        
        //页码显示
        $url_append = !empty($search_name) ? "&search_name=" . urlencode($search_name) : "";
        $page_url = "/Sns/Resource/Resource/my_upload_resource_list?page=#page#" . $url_append;
        $page_list = array(
            'pre_page' => $page > 1 ? str_replace("#page#", $page - 1, $page_url) : false,
            'next_page' => $page < $total_page ? str_replace('#page#', $page + 1, $page_url) : false,
        );
        
        import('@.Common_wmw.WmwString');
        if(!empty($resource_list)) {
            foreach($resource_list as $resource_id => $resource) {
                if(!empty($search_name)) {
                    $resource['title'] = str_replace($search_name, '<span style="color:red;">' . $search_name . '</span>', $resource['title']);
                }
                
                $resource['description'] = WmwString::mbstrcut($resource['description'], 0, 200, 2, true);
                
                $resource['md5_key'] = $this->getMd5key($resource_id);
                $resource['add_time'] = date('Y-m-d H:i:s', $resource['add_time']);
                $resource['file_size'] = $this->getFileSize($resource['file_path'], $resource['file_name']);
                
                $resource_list[$resource_id] = $resource;
            }
        } else {
            $resource_list = array();
        }
        
        $this->assign('page', $page);
        $this->assign('search_name', $search_name);
        $this->assign('total_page', $total_page);
        $this->assign('page_list', $page_list);
        
        $this->assign('resource_list', $resource_list);
        
        $this->display('delete_resource_list');
    }
    /**
     * 加密，作为资源删除时的校验
     */
    private function getMd5key($resource_id) {
        return md5($resource_id . $this->user['client_account'] . substr(time(), 0, 6));
    }
    
    /**
     * 通过后缀获取文件类型
     * @param $ext
     */
    private function checkoutFileTypeByExt($ext) {
        if(empty($ext)) {
            return 0;
        }
        
        $return_file_type = 0;
        $file_type_settings = C('file_type_settings');
        foreach((array)$file_type_settings as $file_type => $setting_str) {
            if(stripos($setting_str . ',', $ext . ',') !== false) {
                $return_file_type = $file_type;
                break;
            }
        }
        
        return $return_file_type;
    }
    
    /**
     * 通过后缀获取展示类型
     * @param $ext
     */
    private function checkoutShowTypeByExt($ext) {
        if(empty($ext)) {
            return 0;
        }
        
        $return_show_type = 0;
        $show_type_settings = C('show_type_settings');
        foreach((array)$show_type_settings as $show_type => $setting_str) {
            if(stripos($setting_str . ',', $ext . ',') !== false) {
                $return_show_type = $show_type;
                break;
            }
        }
        
        return $return_show_type;
    }
    
    /**
     * 获取文件的大小
     * @param $filename
     */
    private function getFileSize($file_path, $basename) {
        if(empty($file_path) || empty($basename)) {
            return '0 kb';
        }
        
        //判断file_path是否是以"/"结尾的
        if(substr($file_path, -1) != '/') {
            $file_path .= '/';
        }
        $filename = $file_path . $basename;
        
        //判断是否是远程文件
        $is_remote_file = preg_match("/^http(s)?:\/\/(.+)$/", trim($filename)) ? true : false;
        
        $file_size = 0;
        if($is_remote_file) {
            $response_headers = get_headers($filename, true);
            if(preg_match('/200/', $response_headers[0]) && !is_array($response_headers['Content-Length'])) {
                $file_size = $response_headers['Content-Length'];
            }
        } else {
            import('@.Common_wmw.Pathmanagement_sns');
            $filename = Pathmanagement_sns::uploadResource() . $basename;
            
            $file_size = @ filesize($filename);
        }
        $file_size = intval($file_size);
        
        if($file_size >= 1024 * 1024) {
            return number_format($file_size / (1024 * 1024), 2) . ' M';
        } else if($file_size > 1024) {
            return number_format($file_size / 1024, 2) . ' kb';
        } else if($file_size > 0) {
            return $file_size . ' b';
        }
        
        return '0 kb';
    }
    
    public function getresource() { 
        $grade_id = $this->user['class_info'][current(current($this->user['class_info']))]['grade_id'];
        $resource_type = rand(1,3);
        $nav_str = $resource_type .'_'.$grade_id;
        $more_url = '/Sns/Resource/Resource/';
        
        switch ($resource_type){
            case 1:
                $more_url .= 'synchroresource/screening/' . $nav_str;
                break;
            case 2:
                $more_url .= 'qualityschool/screening/' . $nav_str;
                break;
            case 3:
                $more_url .= 'qualityresource/screening/' . $nav_str;
                break;
        }
        
        $limit = $this->objInput->getInt('limit');
        $limit = empty($limit) ? 20 : max(1, $limit);
        $page = 1;
        $offset = ($page - 1) * $limit;
        $nav_list = $this->show_nav_list($nav_str);
        $new_nav_arr = $this->ParamTransfor($nav_str);
        
        array_pop($new_nav_arr);
        foreach($new_nav_arr as $key => $val) {
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
        
        list($total_num, $ResourceInfo) = $ResourceApi->getReourceInfoByCombinedKey($new_nav_arr_1, null,  $offset, $limit);
        
        $ResourceInfo = $this->addresourceOperationbtn($ResourceInfo);
        
        !empty($ResourceInfo) ? $this->ajaxReturn(array('more_url'=>$more_url,'resource_info'=>$ResourceInfo), '获取资源成功！', 1, 'json') : $this->ajaxReturn(null, '获取资源失败！', -1, 'json');
    }
    
}