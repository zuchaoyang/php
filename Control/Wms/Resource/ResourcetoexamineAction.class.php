<?php
class ResourcetoexamineAction extends WmsController{
    public function _initialize() {
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
    
    public function show_upload_resource_list() {
        $page = $this->objInput->getInt('page');
        $resource_status = $this->objInput->getInt("resource_status"); 
        
        $resource_status = empty($resource_status) ? 0 : $resource_status;

        $page = max(1,$page);
        $limit = 10;
        $offset = ($page -1) * $limit;
        import('@.Control.Api.Resource.ResourceApi');
        $ResourceApi = new ResourceApi();
        
        $wherearr = array(
            'is_system' => "is_system=0",
            'resource_status' => "resource_status=".$resource_status
        );
        
        $orderby = !empty($resource_status) ? 'add_time desc' : "add_time asc";
        list($total_num, $ResourceInfo) = $ResourceApi->getReourceInfoByCombinedKey($wherearr, $orderby, $offset, $limit);
        if(!empty($ResourceInfo)) {
            $total_page = ceil($total_num/$limit);
            $uids = array();
            $ResourceInfo = $this->addresourceOperationbtn($ResourceInfo);
            $mUser = ClsFactory::Create("Model.mUser");
            
            $userInfo = $mUser->getUserBaseByUid($uids);
            foreach($uids as $resource_id => $uid) {
                $ResourceInfo[$resource_id]['client_name'] = $userInfo[$uid]['client_name'];
                $ResourceInfo[$resource_id]['add_time'] = date("Y-m-d H:i:s", $ResourceInfo[$resource_id]['add_time']);
                $ResourceInfo[$resource_id]['description'] = !empty($ResourceInfo[$resource_id]['description']) ? $ResourceInfo[$resource_id]['description'] : '暂无';
            }
            
        }

        $total_page = !empty($total_page) ? $total_page : 1;
        $this->assign('resource_status', $resource_status);
        $this->assign('resource', $ResourceInfo);
        $this->assign('page',$page);
        $this->assign('total_num', $total_num);
        $this->assign('total_page', $total_page);
        $this->display('resource_examine_list');
    }
    
    public function examine_pass() {
        $resource_id = $this->objInput->getInt('resource_id');
        if(empty($resource_id)) {
            $this->showError("参数丢失请重新操作！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
        $mResourceInfo = ClsFactory::Create("Model.Resource.mResourceInfo");
        $dataarr = array(
            'resource_status' => 1
        );
        
        $result = $mResourceInfo->modifyResourceInfo($dataarr, $resource_id);
        if(!empty($result)) {
            $this->showSuccess("操作成功！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }else{
            $this->showError("操作失败！请重试。","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
    }
    
    public function examine_no_pass() {
        $resource_id = $this->objInput->postInt('resource_id');
        $yuanyin = $this->objInput->postStr('yuanyin');
        if(empty($resource_id)) {
            $this->showError("参数丢失请重新操作！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
        $mResourceInfo = ClsFactory::Create("Model.Resource.mResourceInfo");
        $dataarr = array(
            'refuse_reason' => $yuanyin,
            'resource_status' => -1
        );
        $result = $mResourceInfo->modifyResourceInfo($dataarr, $resource_id);
        if(!empty($result)) {
            $this->showSuccess("操作成功！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }else{
            $this->showError("操作失败！请重试。","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
    }
    
    public function examine_del() {
        $resource_id = $this->objInput->getInt("resource_id");
        
        if(empty($resource_id)) {
            $this->showError("参数丢失请重新操作！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
        
        $mResourceInfo = ClsFactory::create("Model.Resource.mResourceInfo");
        
        $dataarr = array(
            'resource_status' => -2
        );
        $result = $mResourceInfo->modifyResourceInfo($dataarr, $resource_id);
        if(!empty($result)) {
            $this->showSuccess("操作成功！","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }else{
            $this->showError("操作失败！请重试。","/Wms/Resource/Resourcetoexamine/show_upload_resource_list");
        }
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
           if(!file_exists($real_file)) {
               $this->showError('文件不存在！', "http://vm.wmw.cn/Adminbase/Body/index");
           }
           
           $down_file = ClsFactory::Create('@.Common_wmw.WmwDownload');
           $down_file->downfile($real_file, $title);
       } catch(Exception $e) {
           
           $this->showError($e->getMessage(), "http://vm.wmw.cn/Adminbase/Body/index");
       }
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
    
    
    private function addresourceOperationbtn($ResourceInfo){
        
        if(empty($ResourceInfo)) {
            return false;
        }

        foreach ( $ResourceInfo as $resource_id => & $resource_info ) {
                if (empty ( $resource_info ['title'] )) {
                    continue;
                }
                $resource_info['add_time']  = date("Y-m-d H:i:s", $resource_info['add_time']);
                if (in_array ( intval($resource_info ['file_type']), array (1, 2, 7, 9, 10) )) {
                    $resource_info['show_btn'] = "下载";
                   $resource_info ['file_path'] = '/Wms/Resource/Resourcetoexamine/download_zy/resource_id/' . $resource_id;//$resource_val ['file_path'] . $resource_val ['file_name'];
                } elseif (in_array ( $resource_info ['file_type'], array (4, 5 ) )) {
                $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "播放";
                        $resource_info ['file_path'] = "/Wms/Resource/Resourcetoexamine/displayResource/resource_id/$resource_id";
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Wms/Resource/Resourcetoexamine/download_zy/resource_id/' . $resource_id;
                    }
                } elseif (in_array ( $resource_info ['file_type'], array (3, 8, 6 ) )) {
                    $file_ext = pathinfo($resource_info ['file_name'], PATHINFO_EXTENSION);
                    if(in_array($file_ext, $this->_file_ext[$resource_info ['file_type']])){
                        $resource_info ['show_btn'] = "查看";
                        if($resource_info ['show_type'] != 1){
                            $resource_info['file_path'] = "/Wms/Resource/Resourcetoexamine/displayResource/resource_id/$resource_id";
                        }else{
                            $resource_info['file_path'] = $resource_info ['file_path'] . $resource_info ['file_name'];
                        }
                    }else{
                        $resource_info ['show_btn'] = "下载";
                        $resource_info ['file_path'] = '/Wms/Resource/Resourcetoexamine/download_zy/resource_id/' . $resource_id;
                    }
                }
                
                $resource_info['short_title'] = cutstr ( $resource_info ['title'], 24, true );
                $ResourceInfo[$resource_id] = $resource_info;
            }
            
            return !empty($ResourceInfo) ? $ResourceInfo : false;
    }
    
}