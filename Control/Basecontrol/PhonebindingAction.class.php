<?php
define('AUTO_CLEAR', true); 
class PhonebindingAction extends BmsController{
	public function _initialize(){
	    parent::_initialize();
		header("Content-Type:text/html; charset=utf-8");
		import("@.Common_wmw.Pathmanagement_bms");
		//判断用户是否登录
		$mBmsAccount = ClsFactory::Create('Model.mBmsAccount');
		$this->assign('baseinfo',$this->user);
	}
	//获取cookie中的用户账号
	public function getCookieAccount(){
		return $this->user['base_account'];
	}
	public function index(){
		$this->display('phoneBindingBat');
	}
	public function goOld(){
		$this->display('oldBinding');
	}
	public function goNew(){
		$this->display('newBinding');
	}
	
	public function newRs(){
		$file = $_FILES['file'];
		$this->upload_excel('file','goNew');
		
		$HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        $xmlarr = $HandlePHPExcel->toArray($file['tmp_name']);
        $a = array_slice($xmlarr[0]['datas'],1,count($xmlarr[0]['datas'])-1,true);
        
        $newdata = array();
        foreach($a as $key=>&$val){
        	$newdata[$key]['name']=$val[0];
        	$newdata[$key]['account']=$val[1];
        	$newdata[$key]['phone']=$val[2];
        }
       	unset($a);
        $a = $this->enddata($newdata,2);
		if(!empty($a['error_path'])){
        	$this->assign('error_path','/error_path/'.$a['error_path']);
        }
        $this->assign('success',$a['success']);
        $this->assign('error',$a['error']);
        
		$this->display('newinrs');
	}
	public function oldRs(){
		$file = $_FILES['file'];
		$this->upload_excel('file','goOld');
		
		$HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
        $xmlarr = $HandlePHPExcel->toArray($file['tmp_name']);
        $a = array_slice($xmlarr[0]['datas'],1,count($xmlarr[0]['datas'])-1,true);
        
        $newdata = array();
        foreach($a as $key=>&$val){
        	$newdata[$key]['name']=$val[0];
        	$newdata[$key]['account']=$val[1];
        	$newdata[$key]['phone']=$val[2];
        }
       	unset($a);
        $a = $this->enddata($newdata,1);
        if(!empty($a['error_path'])){
        	$this->assign('error_path','/error_path/'.$a['error_path']);
        }
        $this->assign('success',intval($a['success']));
        $this->assign('error',intval($a['error']));
        
		$this->display('oldinrs');
	}
	public function enddata($xmlarr,$phone_type){
		$tiparr = array(
			'no_name'=>"空姓名",
			'no_account'=>"空账号",
			'ff_phone'=>"非法手机号",
			'ct_account'=>"账号冲突",
			'ct_phone'=>"手机号冲突",
			'cf_account'=>"账号重复",
			'cf_phone'=>"手机号重复",
			'bf_type'=>"账号类型不符",
			'wz'=>"未知错误"
		);
		
		$new_arr = $xmlarr;
		$no_arr = array();
		foreach($new_arr as $key9=>&$val9){
			if($val9['phone'] == ""){
				$val9['tip'] = $tiparr['ff_phone'];
				$no_arr[$key9]=$new_arr[$key9];
				unset($new_arr[$key9]);
			}else{
				$phones[$val9['phone']] = $val9['phone'];
			}
		}
		//根据基地账号得出运营策略
		$mschoolInfo = ClsFactory::Create('Model.mSchoolRequest');
		$schoolInfo = $mschoolInfo->getSchoolRequestByUid($this->getCookieAccount(),$offset = 0,$length = 500);
	    
		foreach ($schoolInfo as $key=>$val){
            $schoolIds[$val['school_id']] = $val['school_id'];
	    }
	    unset($schoolInfo);
	    $mSchool = ClsFactory::Create('Model.mSchoolInfo');
	    $schoolInf = $mSchool->getSchoolInfoById($schoolIds);
	    //运营策略数组
	    $operatenumArr = array();
	    foreach($schoolInf as $schoolkey=>&$schoolval){
	    	if(!empty($schoolval['operation_strategy'])){
	    		$operatenumArr[$schoolval['operation_strategy']] = $schoolval['operation_strategy'];
	    	}
	    }
	    unset($schoolInf,$schoolIds);
		$mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
		//得到检测后的手机号array('sucess'=>array(),'error'=>array())
		$check_array = $mBusinessphone->checkPhoneNumByOperator($operatenumArr,$phones);
		unset($phones);
		//空账号  空姓名
		$cf_phone_ids = array();//重复手机号
		$account = array();//重复账号
		foreach($new_arr as $key5=>& $val5){
			if($val5['account'] == ""){//检测空账号
				$val5['tip'] = $tiparr['no_account'];
				$no_arr[$key5]=$new_arr[$key5];
				unset($new_arr[$key5]);
			}elseif(in_array($val5['account'],$account)){//重复账号//			if($val5['name']==""){//检测空姓名//				$val5['tip'] = $tiparr['no_name'];//				$no_arr[$key5]=$new_arr[$key5];//				unset($new_arr[$key5]);//			}else
				$val5['tip'] = $tiparr['cf_account'];
				$no_arr[$key5]=$new_arr[$key5];
				$cf_account_error[$val5['account']]=$val5['account'];
				unset($new_arr[$key5]);
			}elseif(in_array($val5['phone'],$cf_phone_ids)){//重复手机号
				$val5['tip'] = $tiparr['cf_phone'];
				$no_arr[$key5]=$new_arr[$key5];
				$cf_phone_error[$val5['phone']]=$val5['phone'];
				unset($new_arr[$key5]);
			}elseif(in_array($val5['phone'], $check_array['error'])){//非法手机号
				$val5['tip'] = $tiparr['ff_phone'];
				$no_arr[$key5]=$new_arr[$key5];
				unset($new_arr[$key5]);
			}else{
				$cf_phone_ids[$val5['phone']] = $val5['phone'];
				$account[$val5['account']] = $val5['account'];
				$excel_ids[$key5]=$key5;
			}
			
		}
		unset($check_array);
		unset($cf_phone_ids);
		$mUser = ClsFactory::Create('Model.mUser');
		$mBusinessphone = ClsFactory::Create('Model.mBusinessphone');
		$splice_account = array_splice($account,0,40);
		$excel_id = array_splice($excel_ids,0,40);
		$client_info_keys = array();
		while($splice_account){
			$phone_ids = array();
			$client_info = $mUser->getUserBaseByUid($splice_account);
			$client_info_keys1 = array_keys($client_info);
			if(!empty($client_info_keys1)){
				$client_info_keys = array_merge($client_info_keys, $client_info_keys1);
			}
			//无效账号 重复账号 重复手机号
			foreach($excel_id as $key2=>& $val2){
				if($new_arr[$val2]){
					if(in_array($new_arr[$val2]['account'],$cf_account_error)){//重复账号
						$new_arr[$val2]['tip'] = $tiparr['cf_account'];
						$no_arr[$val2]=$new_arr[$val2];
						unset($new_arr[$val2]);
					}elseif(in_array($new_arr[$val2]['phone'],$cf_phone_error)){//重复手机号
						$new_arr[$val2]['tip'] = $tiparr['cf_phone'];
						$no_arr[$val2]=$new_arr[$val2];
						unset($new_arr[$val2]);
					}elseif(!in_array($new_arr[$val2]['account'],$client_info_keys)){//检测账号是否存在
						$new_arr[$val2]['tip'] = $tiparr['no_account'];
						$no_arr[$val2]=$new_arr[$val2];
						unset($new_arr[$val2]);
					}elseif(!in_array(intval($client_info[$new_arr[$val2]['account']]['client_type']),array(1,2))){//检测账号类型
						$new_arr[$val2]['tip'] = $tiparr['bf_type'];
						$no_arr[$val2]=$new_arr[$val2];
						unset($new_arr[$val2]);
					}else{
						$phone_ids[$new_arr[$val2]['phone']] = $new_arr[$val2]['phone'];
						$phone_ids[$new_arr[$val2]['account']] = $new_arr[$val2]['account'];
					}
				}
			}
			$client_phone_infos = $mBusinessphone->getBusinessPhone($phone_ids);
			unset($phone_ids);
			$client_phone_keys = array_keys($client_phone_infos);
			//手机号冲突
			if(!empty($client_phone_keys)){
				foreach($excel_id as $key4=>& $val4){
					if(in_array($new_arr[$val4]['phone'],$client_phone_keys)){
						if($new_arr[$val4]){
							$new_arr[$val4]['tip'] = $tiparr['ct_phone'];
							$no_arr[$val4]=$new_arr[$val4];
							unset($new_arr[$val4]);
						}
					}elseif(in_array($new_arr[$val4]['account'],$client_phone_keys)){
						if($new_arr[$val4]){
							$new_arr[$val4]['tip'] = $tiparr['ct_account'];
							$no_arr[$val4]=$new_arr[$val4];
							unset($new_arr[$val4]);
						}
					}
				}
			}
			unset($splice_account);
			$splice_account = array_splice($account,0,40);
			$excel_id = array_splice($excel_ids,0,40);
			if(empty($splice_account)){
				break;
			}
		}
		unset($account,$cf_phone_error,$cf_account_error,$client_info_keys,$client_info_keys1,$splice_account,$excel_id);
		if(!empty($new_arr)){
			$START = array_slice($new_arr,0,50,true);
			$n = 0;
			$i = 0;
			$success_count = 0;
			while(!empty($START)){
				$data_arr = array('START'=>array(), 'XMLCONTENT'=>"手动批量绑定手机号");
				foreach($START as $key6=>$val6){
					if(!empty($val6)){
						if(!empty($val6['phone'])){
							$data_arr['START'][$i]['mphone_num'] = $val6['phone'];
							$data_arr['START'][$i]['phone_type'] = $phone_type;
						}
						$data_arr['START'][$i]['flag'] = 1;
						$data_arr['START'][$i]['business_num'] = $val6['account'];
						//$data_arr['START'][$i]['mphone_user_name'] = $val6['name'];
						$data_arr['START'][$i]['opening_time'] = date('Ymd', time());
						$data_arr['START'][$i]['notify_type'] = 'START';
						$i++;
					}
				}
				$rs = $mBusinessphone->batchmangebusinessphone($data_arr);
				$success_count += count($rs['success']);
				if(!empty($rs['faile'])){
					//未知错误
					foreach($START as $key8=>& $val8){
						if(in_array($val8['account'], $rs['faile'])){
							if($new_arr[$key8]){
								$new_arr[$key8]['tip'] = $tiparr['wz'];
								$no_arr[$key8]=$new_arr[$key8];
								unset($new_arr[$key8]);
							}
						}
					}
				}
				unset($data_arr,$START,$rs);
				$n++;
				$START = array_slice($new_arr,$n*50,50,true);
			}
		}
		
		$return_array = array();
		$error_count_array = array();
		$error_count_array[1] = array(
			"学生姓名",
			"家长账号",
			"绑定手机号",
			"失败类型",
			"备注"
		);
		foreach($no_arr as $key7=>& $val7){
			if(!empty($val7)){
				$return_array[$key7][] = $val7['name'];
				$return_array[$key7][] = $val7['account'];
				$return_array[$key7][] = $val7['phone'];
				$return_array[$key7][] = $val7['tip'];
				$return_array[$key7][] = '';
				$error_count_array[] = $return_array[$key7];
				unset($return_array[$key7]);
			}
		}
		$error_count = count($error_count_array);
		$export_array = array(
			array(
				"title" => "失败数据",
    			"cols" => 5,
    			"rows" => $error_count,
				"datas"=>$error_count_array
			)
		);
		if(!empty($error_count_array)) {
			$HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
			$error_path = Pathmanagement_bms::uploadExcel() . rand(10000,99999).'.xls';
			unset($error_count_array);
			$HandlePHPExcel->saveToExcelFile($export_array, $error_path);
			unset($export_array);
			//Session::set('errorarr',$return_array);
		}
		return array('success'=>$success_count,'error'=>$error_count-1,'error_path'=>base64_encode($error_path));
	}
	function outerror(){
		$up_path = base64_decode($this->objInput->getStr('error_path'));
		
		$HandlePHPExcel = ClsFactory::Create('@.Common_wmw.WmwPHPExcel');
    	$HandlePHPExcel->export($up_path, date('YmdHis').rand(10000,99999).'.xls');
	}
	/**
	 * 文件上传
	 * @param  $filename
	 * @param  $up_path
	 */
	protected function upload_excel($filename,$gowhere) {
	    if(empty($filename) || !isset($_FILES[$filename]['name'])) {
	        return false;
	    }
	    
	    //允许上传文件类型
	    $allow_type = array('xls', 'xlsx');
        
        $up_path = Pathmanagement_bms::uploadExcel();
		if(!file_exists($up_path)){
        	mkdir($up_path, 0777);
        }
        
        $ext = pathinfo($_FILES[$filename]['name'], PATHINFO_EXTENSION);
        if(empty($ext) || !in_array($ext, $allow_type)) {
        	$this->assign('tips',"请上传文件格式为". implode(',', $allow_type) . "的文件!");
        	if($gowhere == 'goNew'){
        		$this->goNew();
        	}elseif($gowhere == 'goOld'){
        		$this->goOld();
        	}
        	die;
            //throw new Exception('文件类型不正确,允许上传的文件类型:' . implode(',', $allow_type) . "!", -2);
        }
        return true;
	}
	
	protected function autoClear() {
	    if(!constant('AUTO_CLEAR')) {
	        return false;
	    }
	    
	    //文件过期时间设置,单位小时
	    $expiration_time = 5;
	    //每次清理的最大文件数
	    $filenums_limit = 10;
	    $dir_name = Pathmanagement_bms::uploadExcel();
	    if(empty($dir_name) || !is_dir($dir_name)) {
	        return false;
	    }
	    
	    $time_now = time();
	    
	    $counter = 0;
	    $dir = dir($dir_name);
	    while(($file = $dir->read()) !== false) {
	        if(in_array($file, array('.', '..')) || !in_array(pathinfo($file, PATHINFO_EXTENSION), array('xls', 'xlsx'))) {
	            continue;
	        }
	        //查找满足当前自动清理的excel文件
            $filename = $dir_name . $file;
            $filectime = filectime($filename);
            if($time_now - $filectime >= $expiration_time*60) {
                @unlink($filename);
            }
            if($counter++ >= $filenums_limit) {
                break;
            }
	    }
	    
	    return $counter;
	}
	
	//析构函数，清理系统不必要的文件
	public function __destruct() {
	    $this->autoClear();
	}
}