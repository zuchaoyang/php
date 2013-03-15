<?php
//标示导入信息的类型
define("TITLE_MARK", '<notitle>');

class ResourcemanageAction extends WmsController {
    protected $uid;
    
    public function _initialize() {
        parent::_initialize();
        header("Content-Type:text/html; charset=utf-8");
        $this->uid = $this->user['wms_account'];
        //判断用户是否登录 
		import("@.Control.Adminbase.WmsadminloginAction");
		import("@.Common_wmw.Pathmanagement_wms");
		
		C(include_once WEB_ROOT_DIR . '/Config/Resource/config.php');
    }
    
    /**
     * 资源文件的上传
     */
    public function resource_import() {
        $excel_id = $this->objInput->getInt('excel_id');
        
        if(!empty($excel_id)) {
            $mResourceExcel = ClsFactory::Create('Model.Resource.mResourceExcel');
            $excel_datas_arr = $mResourceExcel->getResourceExcelById($excel_id);
            $excel_datas = & $excel_datas_arr[$excel_id];
            
            $sucess_nums = max(intval($excel_datas['sucess_nums']), 0);
            $fail_nums = max(intval($excel_datas['fail_nums']), 0);
            $fail_file_path = $excel_datas['fail_file_path'];
            $excel_name = $excel_datas['excel_name'];
            
            unset($excel_datas, $excel_datas_arr);
            
            $suffix = pathinfo($fail_file_path, PATHINFO_EXTENSION);
            $suffix = in_array($suffix, array('xls', 'xlsx')) ? $suffix : 'xls';
            
            $file_attrs = array(
                'fail_file_path' => $fail_file_path,
                'export_file_name' => $excel_name . "_fail",
            );
            
            $this->assign('excel_id', $excel_id);
            $this->assign('sucess_nums', $sucess_nums);
            $this->assign('fail_nums', $fail_nums);
            $this->assign('fail_file_exists', file_exists($fail_file_path) ? true : false);
            $this->assign('file_attrs', @ json_encode($file_attrs));
        }
        
        $this->display('resource_import');
    }
    
	/**
     * 资源导入的逻辑处理
     */
    public function import() {
        $product_id = $this->objInput->postInt('product_id');
        $excel_name = $this->objInput->postStr('excel_name');
        
        $product_id = in_array($product_id, array(1,2,3)) ? $product_id : 1;
        //第一步，上传Excel文件并保存
        $file_attrs = $this->uploadExcelFile('excel_filename');
        //第二步，将相应的Excel文件转换成数组
        $pFileName = $file_attrs['filename'];
        
        $sheet_datas = $this->toArray($pFileName);
        
        $head_settings = $this->getSheetHeadSettings(& $sheet_datas);
        //第三步，以sheet为单位对Excel中的数据进行处理
        list($success_resource_ids, $fail_resource_list) = $this->importBySheet(& $sheet_datas['datas'], $product_id);
        
        //初始化导入失败的数据信息
        $fail_nums = count($fail_resource_list);
        $pFailFileName = $this->buildFailExcelDatas(& $fail_resource_list, $head_settings, $product_id);
        
        //保存导入excel的相关数据信息
        $excel_dataarr = array(
            'excel_name' => !empty($excel_name) ? $excel_name : "excel_" . time(),
            'origin_file_path' => $pFileName,
            'resource_ids' => !empty($success_resource_ids) ? implode(",", $success_resource_ids) : "",
            'sucess_nums' => count($success_resource_ids),
            'fail_nums' => $fail_nums,
            'fail_file_path' => $pFailFileName ? $pFailFileName : '',
            'state' => 1,
            'add_time' => time(),
        );
        $mResourceExcel = ClsFactory::Create('Model.Resource.mResourceExcel');
        $excel_id = $mResourceExcel->addResourceExcel($excel_dataarr, true);
        
        $this->redirect("/Resource/Resourcemanage/resource_import", array('excel_id' => $excel_id));
    }
    
    /**
     * 资源信息到处
     */
    public function export() {
        $file_attrs = $this->objInput->postStr('file_attrs');
        $file_attrs = htmlspecialchars_decode($file_attrs);
        
        $file_attrs = json_decode($file_attrs, true);
        $pFileName = $file_attrs['fail_file_path'];
        $export_file_name = $file_attrs['export_file_name'];
        
        $Download = ClsFactory::Create('@.Common_wmw.WmwDownload');
        $Download->downfile($pFileName, $export_file_name);
    }
    
	/**
     * 资源回滚显示页面
     */
    public function resource_rollback() {
        $excel_id = $this->objInput->postInt('excel_id');
        if(!empty($excel_id)) {
            $mResourceExcel = ClsFactory::Create('Model.Resource.mResourceExcel');
            $excel_datas_arr = $mResourceExcel->getResourceExcelById($excel_id);
            if(!empty($excel_datas_arr)) {
                $show_index = 1;
                foreach($excel_datas_arr as $key=>$excel_datas) {
                    unset($excel_datas['resource_ids']);
                    $state = intval($excel_datas['state']);
                    $state_msg = "未知";
                    $can_rollback = false;
                    if($state == -1) {
                        $state_msg = "导入失败";
                    } elseif($state == 1) {
                        $state_msg = "已成功/可回滚";
                        $can_rollback = true;
                    } elseif($state == 2) {
                        $state_msg = "已成功/已回滚";
                    }
                    $excel_datas['state_msg'] = $state_msg;
                    $excel_datas['can_rollback'] = $can_rollback;
                    $excel_datas['show_index'] = $show_index++;
                    $excel_datas['md5_key'] = $this->excel_getmd5key($excel_datas['excel_id']);
                    
                    $excel_datas_arr[$key] = $excel_datas;
                }
            }
            $this->assign('excel_id', $excel_id);
            $this->assign('excel_datas_arr', $excel_datas_arr);
        }
        
        $this->display('resource_rollback');
    }
    
     /**
     * 导入的excel数据回滚
     */
    public function rollback() {
        $excel_id = $this->objInput->getInt('excel_id');
        $md5_key = $this->objInput->getStr('md5_key');
        
        $back_url = "/Wms/Resource/Resourcemanage/resource_rollback";
        
        if(empty($excel_id) || $md5_key != $this->excel_getmd5key($excel_id)) {
            $this->showError('传入的参数错误!', $back_url);
        }
        
        //第一步，获取excel中相应的数据信息
        $mResourceExcel = ClsFactory::Create('Model.Resource.mResourceExcel');
        $excel_arr = $mResourceExcel->getResourceExcelById($excel_id);
        $excel = & $excel_arr[$excel_id];
        
        if(empty($excel)) {
            $this->showError("相应的Excel文件信息不存在!", $back_url);
        }
        if($excel['state'] == -1) {
            $this->showError("该编号相应的Excel导入失败，不能回滚!", $back_url);
        } else if($excel['state'] == 2) {
            $this->showError("Excel文件已经回滚!", $back_url);
        }
        
        $resource_ids = & $excel['resource_ids'];
        $total_del_rows = 0;
        if(!empty($resource_ids)) {
            $resource_ids = explode(",", $resource_ids);
            foreach($resource_ids as $key=>$id) {
                if(empty($id)) {
                    unset($resource_ids[$key]);
                }
            }
            $chunk_arr = array_chunk($resource_ids, 500);
            unset($resource_ids);
            
            $mResourceInfo = ClsFactory::Create('Model.Resource.mResourceInfo');
            foreach($chunk_arr as $key=>$ids) {
                $del_rows = $mResourceInfo->delResourceInfoBat($ids);
                $total_del_rows += $del_rows;
                
                unset($chunk_arr[$key]);
            }
        }
        //更新记录状态，不允许用户重复回滚 
        $excel_dataarr = array(
            'state' => 2,
            'resource_ids' => '',
        );
        $mResourceExcel->modifyResourceExcel($excel_dataarr, $excel_id);
        
        $this->showSuccess("资源回滚成功,共删除资源" . $total_del_rows . "条!", $back_url);
    }
    
    /**
     * 导入Excel的相关数据
     * @param $resource_list
     * @param $product_id
     */
    private function importBySheet($resource_list, $product_id) {
        if(empty($resource_list)) {
            return false;
        }
        //将资源信息转换成关联数组
        foreach($resource_list as $key => $resource) {
            if(self::isEmptyArray($resource)) {
                unset($resource_list[$key]);
                continue;
            }
            $resource_list[$key] = $this->parseToAssoc($resource, $product_id);
        }
        
        //过滤信息不完整的资源信息
        $total_success_resource_ids = $total_fail_resource_list = array();
        foreach($resource_list as $key => $resource) {
            //资源信息不完整
            if(! self::checkFields($resource)) {
                $total_fail_resource_list[] = $resource;
                unset($resource_list[$key]);
            }
        }
        
        //将信息分组处理
        list($resource_list, $attr_list) = $this->groupResource($resource_list);
        
        $resourceApi = ClsFactory::Create('@.Control.Api.Resource.ResourceApi');
        
        //导入属性信息
        if(!empty($attr_list)) {
            $attr_chunk_arr = array_chunk($attr_list, 500, true);
            unset($attr_list);
            foreach($attr_chunk_arr as $key => $bat_attr_datas) {
                $resourceApi->addDynamicAttrsBat($bat_attr_datas);
                unset($attr_chunk_arr[$key]);
            }
        }
        
        //导入实际资源信息
        if(!empty($resource_list)) {
            //分组导入
            $chunk_arr = array_chunk($resource_list, 500, true);
            unset($resource_list);
            
            foreach($chunk_arr as $key => $bat_datas) {
                list($success_resource_ids, $fail_resource_list) = $resourceApi->addResourceBat($bat_datas);
                
                $total_success_resource_ids = array_merge((array)$total_success_resource_ids, (array)$success_resource_ids);
                $total_fail_resource_list = array_merge((array)$total_fail_resource_list, (array)$fail_resource_list);
                unset($chunk_arr[$key]);
            }
        }
        
        return array($total_success_resource_ids, $total_fail_resource_list);
    }
    
    /**
     * 通过title的标示将资源信息进行分组处理
     * @param $resource_list
     */
    private function groupResource($resource_list) {
        if(empty($resource_list)) {
            return false;
        }
        
        $title_mark = defined('TITLE_MARK') ? TITLE_MARK :  '<notitle>';
        
        $group_resource_list = $group_attr_list = array();
        foreach($resource_list as $key => $resource) {
            if(stripos($resource['title'], $title_mark) !== false) {
                $group_attr_list[] = $resource;
            } else {
                $group_resource_list[] = $resource;
            }
            unset($resource_list[$key]);
        }
        
        return array($group_resource_list, $group_attr_list);
    }
    
    /**
     * 保存错误信息的excel文件
     * @param $fail_resource_list
     */
    private function buildFailExcelDatas($fail_resource_list, $head_settings, $product_id) {
        if(empty($fail_resource_list) || empty($head_settings) || empty($product_id)) {
            return false;
        }
        
        $resource_settings = C('resource_settings');
        $fields = & $resource_settings[$product_id]['fields'];
        
        //正式的数据下标是从2开始的
        $i = 2;
        $fail_sheet_datas[1] = $head_settings['first_rows'];
        foreach($fail_resource_list as $key => $resource) {
            $excel_datas = array();
            //将关联数组转换成数组索引的数组
            foreach($fields as $j => $field) {
                $excel_datas[$j] = isset($resource[$field]) ? $resource[$field] : '';
            }
            $fail_sheet_datas[$i++] = $excel_datas;
            
            unset($fail_resource_list[$key]);
        }
        
        $fail_excel_datas[0] = array(
            'title' => $head_settings['title'],
            'cols' => $head_settings['cols'],
            'rows' => count($fail_sheet_datas),
            'datas' => & $fail_sheet_datas,
        );
        $excel_pre = "import_fail_" . date('Ymd', time()) . "_";
        //第四步，保存excel的相关数据
        $pFailFileName = Pathmanagement_wms::UploadExcel() . uniqid($excel_pre) . ".xls";
        if(! $this->saveExcelFile($fail_excel_datas, $pFailFileName)) {
            $pFailFileName = "";
        }
        
        return $pFailFileName;
    }
    
    /**
     * 获取excel的头部信息设置
     * @param $sheet_datas
     */
    private function getSheetHeadSettings($sheet_datas) {
        if(empty($sheet_datas)) {
            return false;
        } 
        
        $head_settings = array(
            'title' => $sheet_datas['title'],
            'cols' => $sheet_datas['cols'],
            'rows' => $sheet_datas['rows'],
            'first_rows' => array_shift($sheet_datas['datas'])
        );
        
        return $head_settings;
    }
    
    /**
     * 资源回滚的MD5验证规则
     * @param $excel_id excel文件的id编号
     */
    protected function excel_getmd5key($excel_id) {
        $excel_id = max(intval($excel_id), 0);
        $md5_pre = substr(strval(time()), 0, 7) . $this->uid;
        $md5_key = md5($md5_pre . $excel_id);
        
        return substr($md5_key, 0, 20);
    }
    
    /**
     * 上传对应的Excel文件
     */
    private function uploadExcelFile($inputfilename = null) {
        if(empty($inputfilename) || !isset($_FILES[$inputfilename])) {
            return false;
        }
        
        $up_init = array(
            'max_size' => 1024 * 10,
            'attachmentspath' => Pathmanagement_wms::UploadExcel(),
        	'renamed' => true,
            'allow_type' => array('xls', 'xlsx')
        );
        $uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
        $uploadObj->_set_options($up_init);
        $up_rs = $uploadObj->upfile($inputfilename);
        
        return !empty($up_rs) ? $up_rs : false;
    }
    
    /**
     * 将Excel文件转换成数组
     * @param $pFileName
     */
    protected function toArray($pFileName = null) {
        if(empty($pFileName)) {
            return false;
        }
        
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        
        return  $HandlePHPExcel->getSheetDatasByIndex($pFileName, 0);
    }
    
    /**
     * 将数据保存到excel文件
     * @param $excel_datas
     * @param $pFileName
     */
    protected function saveExcelFile($excel_datas, $pFileName) {
        if(empty($pFileName) || empty($excel_datas)) {
            return false;
        }
        
        $HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        return $HandlePHPExcel->saveToExcelFile($excel_datas, $pFileName);
    }
    
    /**
     * 将数组转换成关联数组
     * @param  $datas
     * @param  $product_id
     */
    protected function parseToAssoc($datas = array(), $product_id) {
        if(empty($datas) || empty($product_id)) {
            return false;
        }
        
        $resource_settings = C('resource_settings');
        $settings_fields = & $resource_settings[$product_id]['fields'];
        
        $arr = array();
        foreach((array)$settings_fields as $key=>$field) {
            $arr[$field] = isset($datas[$key]) ? trim($datas[$key]) : "";
        }
        $arr['product_id'] = $product_id;
        $arr['add_time'] = time();
        
        return $arr;
    }
    
    /**
     * 检测数据的必须字段是否完整
     * @param $datas
     * @param $product_id
     */
    private static function checkFields($datas) {
        if(empty($datas)) {
            return false;
        }
        
        $product_id = $datas['product_id'];
        $resource_settings = C('resource_settings');
        $checkfields = & $resource_settings[$product_id]['checkfields'];
        
        foreach((array)$checkfields as $field) {
            if(empty($datas[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
 	/**
     * 判断是否是空数组
     * @param $arr
     */
    private static function isEmptyArray($arr) {
        if(empty($arr)) {
            return true;
        }
        
        foreach((array)$arr as $val) {
            if(!empty($val)) {
                return false;
            }
        }
        
        return true;
    }
}