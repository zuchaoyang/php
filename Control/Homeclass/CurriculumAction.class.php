<?php
class CurriculumAction extends SnsController{
	
	//课程表模板路径
	private $_CURRICULUM_PATH = '';
	//课程图标路径
	private $_CURRICULUMICO_PATH = '';

	public function _initialize(){
        parent::_initialize(); 
	    
	    import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		import("@.Control.Homepage.HomepageAction");
		 
		$this->assign('chanelid',"chanel1");
		
		$this->_CURRICULUM_PATH = IMG_SERVER.'/Public/curriculum/';
		$this->_CURRICULUMICO_PATH = IMG_SERVER.'/Public/Courseico/';
	}

	//显示课程表
	public function index(){
		$account = $this->getCookieAccount();
		$class_code = $this->objInput->getInt($class_code);
		$class_code = $this->checkclasscode($class_code);
		$client_type = $this->user['client_type'];

		
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$tmp_CurriculumInfo = $mCurriculum->getCurriculumInfoByClassCode($class_code);
		$CurriculumInfo = reset($tmp_CurriculumInfo[$class_code]);
		
		if(empty($CurriculumInfo)){
			$CurriculumInfo = $this->InitCurriculum($class_code);
		}
		
		$am_content = unserialize($CurriculumInfo['am_content']);
		$pm_content = unserialize($CurriculumInfo['pm_content']);
		
		for($n=0; $n<count($am_content); $n++){
			if(stristr($am_content[$n],"%")){
				$strsend = strrpos($am_content[$n],"%");
				$imgValue = substr($am_content[$n],1,$strsend-1);
				$keyVaue = substr($am_content[$n],$strsend+1,strlen($am_content[$n]));
				$am_content[$n] = "<span style=vertical-align:middle;><img src='".$this->_CURRICULUMICO_PATH.$imgValue."'></span>".$keyVaue;

			}
		}
	
		for($n=0;$n<count($pm_content);$n++){
			if(stristr($pm_content[$n],"%")){
				$strsend = strrpos($pm_content[$n],"%");
				$imgValue = substr($pm_content[$n],1,$strsend-1);
				$keyVaue = substr($pm_content[$n],$strsend+1,strlen($pm_content[$n]));
				$pm_content[$n] = "<span style=vertical-align:middle;><img src='".$this->_CURRICULUMICO_PATH.$imgValue."'></span>".$keyVaue;
			}
		}

		for($n=1;$n<=count($am_content)/5;$n++){
			$AMnums .= "<li id='amday".$n."'>".WmwString::getNumsUppercase($n)."</li>";
		}
		for($n=count($am_content)/5+1;$n<=count($pm_content)/5+count($am_content)/5;$n++){
			$PMnums .= "<li id='pmday".$n."'>".WmwString::getNumsUppercase($n)."</li>";
		}
		
		//加载课程表模版
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		$returndata = $mPersonconfig->getPersonConfigByaccount($account);
		if($returndata) {
			$returndata = array_shift($returndata);
			$cur_skin_id = $returndata[curriculum_bg_id];
			$mCurriculumskin = ClsFactory::Create('Model.mCurriculumskin');
			$skinData = $mCurriculumskin->getCurriculumSkinById($cur_skin_id);
			if($skinData){
				$skinData = array_shift($skinData);
				$curricumlumbg = $this->_CURRICULUM_PATH.$skinData['skin_value'];
			}
		} else {
			$curricumlumbg = $this->_CURRICULUM_PATH."1.jpg";
		}
		
		$this->assign('classcontent2',$pm_content);
		$this->assign('classcontent',$am_content);
		$this->assign('client_type',$client_type);
		$this->assign('class_code',$class_code);
		$this->assign('curricumlumbg',$curricumlumbg);
		$this->assign('AMcontent',$AMnums);
		$this->assign('PMcontent',$PMnums);
		$this->assign('AMclassnum',(intval(count($am_content))/5));
		$this->assign('PMclsssnum',(intval(count($pm_content))/5));

		$this->display('Curriculum');
	}

	/*保存课表 submit post: */
	public function save(){
		$client_type = $this->user['client_type'];
		$client_type ==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		homepageAction::chkUserJurisdiction($blnJurisdiction,"submit");
		
		//此处使用 $this->objInput->postStr 无法取到数据<br />
		//gzcompress  gzuncompress  update table_name set field=CONCAT('str',field)
		//serialize
		$amContent = $this->objInput->postArr('clspan');
		$pmContent = $this->objInput->postArr('rgspan');

		for($n=0;$n<count($amContent);$n++){
			$amContent[$n] = WmwString::unhtmlspecialchars($amContent[$n]);
			 preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$amContent[$n],$match);
			 $IcoPath = $match[1];
			 if($IcoPath){
				 $strsend = strrpos($IcoPath,"/");
				 $needReplaceVaue = substr($IcoPath,$strsend+1,strlen($IcoPath));
				 $needReplaceVaue = "%".$needReplaceVaue."%";
				 $amContent[$n] = $needReplaceVaue.strip_tags($amContent[$n]);
			}
		 }
		for($n=0;$n<count($pmContent);$n++){
			$pmContent[$n] = WmwString::unhtmlspecialchars($pmContent[$n]);
			 preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i',$pmContent[$n],$match);
			 $IcoPath = $match[1];
			 if($IcoPath){
				 $strsend = strrpos($IcoPath,"/");
				 $needReplaceVaue = substr($IcoPath,$strsend+1,strlen($IcoPath));
				 $needReplaceVaue = "%".$needReplaceVaue."%";
				 $pmContent[$n] = $needReplaceVaue.strip_tags($pmContent[$n]);
			}
		 }

		$class_code = trim($this->objInput->getInt('class_code'));
		$class_code = $this->checkclasscode($class_code);
		$CurriculumDataArr = array(
			'class_code' =>$class_code,
			'upd_account' =>$this->getCookieAccount(),
			'am_content' =>serialize($amContent),
			'pm_content' =>serialize($pmContent),
			'upd_time' =>time(),
		);	

	
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$returnData = $mCurriculum->modifyCurriculumInfoByClassCode($CurriculumDataArr,$class_code);
		$mFeed = ClsFactory::Create('Model.mFeed');				
		$a = $mFeed->addClassFeed(intval($class_code),intval($this->getCookieAccount()),intval($class_code),CLASS_FEED_CURRICULUM,FEED_UPD,time());
		$this->redirect("../Homepage/Homepage/index/class_code/$class_code");
	}

	
	

	/*课程表所有模板 ajax post: */
	public function ajaxGetCurrSkinList(){
		$mCurriculumskin = ClsFactory::Create('Model.mCurriculumskin');
		$resdata = $mCurriculumskin->getCurriculumSkinList();
		$t_value = '';
		foreach($resdata as $key=>$val){
			$value = $this->_CURRICULUM_PATH.$val['skin_value']."|".$val['skin_id'];
			$t_value=="" ? $t_value = $value : $t_value=$t_value.",".$value;
		}
		echo $t_value;
	}


	/*读取课程表科目
	  科目由系统及用户自定义科目拼合组成
	*/
	public function ajaxCurriculum(){
		$account = $this->getCookieAccount();
		$rowsid = trim($this->objInput->getInt('rowsid'));
		$position = trim($this->objInput->getStr('position'));
		$class_code = key($this->user['client_class']);
		
		//课程表系统科目
		$currSubjectArr = Constancearr::curriculumSubject();
		for($i=0;$i<count($currSubjectArr);$i++){
			$textSubjectName = $currSubjectArr[$i]['subjectName'];
			$currSubjectArr[$i]['subjectName'] = "<span style='vertical-align:middle;'><img src='".$this->_CURRICULUMICO_PATH."/".$currSubjectArr[$i]['subjectIco']."'></span>&nbsp;<a href=\"javascript:void(0);\" onclick=\"javascript:setCurriculum('<span style=vertical-align:middle;><img src=".$this->_CURRICULUMICO_PATH.$currSubjectArr[$i]['subjectIco']." ></span>&nbsp;".$currSubjectArr[$i]['subjectName']."','".$position."','".$rowsid."','%".$currSubjectArr[$i]['subjectIco']."%".$textSubjectName."')\">".$currSubjectArr[$i]['subjectName']."</a>" ;
		}
		
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$subjectInfo_arr = $mCurriculum->getCurriculumInfoByClassCode($class_code);
		$subjectInfo_list = & $subjectInfo_arr[$class_code];
		
		$subjectInfo = array_shift($subjectInfo_list);
		
		$arrSubject = array();
		if(!empty($subjectInfo)) { 
			$subject_content = $subjectInfo['subject_content'];
			if(!empty($subject_content)) {
				$arr_subject_content = explode(",",$subject_content);
				for($i=0;$i<count($arr_subject_content);$i++){
					$textSubjectName = $arr_subject_content[$i];

					$newarray[] = array('id'=>$i,'subjectName'=>"<img src='".$this->_CURRICULUMICO_PATH."/0.jpg'><a href=\"javascript:void(0);\" onclick=\"javascript:setCurriculum('<span style=vertical-align:middle;><img src=".$this->_CURRICULUMICO_PATH."0.jpg></span>".$arr_subject_content[$i]."','".$position."','".$rowsid."','%0.jpg%".$textSubjectName."')\">".$arr_subject_content[$i]."</a>&nbsp;<span style='vertical-align:middle;'><a href='javascript:void(0);' onclick=\"javascript:ajaxSubjectDelete('".$arr_subject_content[$i]."','".$class_code."');\"><img src='".IMG_SERVER."/Public/images/new/close_it.gif'></a></span>");
				}
				$NewArrSubject[] = array_merge($currSubjectArr , $newarray);
			} else {
				$NewArrSubject[] = $currSubjectArr;
			}
			
		} else {
			$NewArrSubject[] = $currSubjectArr;
		}
		$NewArrSubject = array_shift($NewArrSubject);
		
		$this->assign('subjectinfo',$NewArrSubject);
		
		$this->display('Curriculum_subject_tpl');
		
	}


	/*保存课程表模版 ajax post: */
	public function ajaxSaveTemplate(){
		$skinId = trim($this->objInput->postInt('skinId'));
		$account = $this->getCookieAccount();
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		$personConfigArr = array(
			'client_account' =>$account,
			'curriculum_bg_id' =>$skinId,
		);
		$PersonConfig_list = $mPersonconfig->getPersonConfigByaccount($account);
		if(!empty($PersonConfig_list)) {
		    $returndata = $mPersonconfig->modifyPersonConfig($personConfigArr,$account);
		} else {
		    $returndata = $mPersonconfig->addPersonConfig($personConfigArr);
		}
		
		if(!empty($returndata)) { 
			echo "success";exit;
		} else { 
			echo "fail";exit;
		}	
		
	}


	/*添加科目名
		处理方式为：直接修改课程表内科目字段名称
		当课程表默认不存在，直接INSERT 科目内容名称
	*/
	public function ajaxSubjectAdd(){
		$client_type = $this->user['client_type'];
		$client_type ==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		homepageAction::chkUserJurisdiction($blnJurisdiction,"ajax");
		
		$class_code = key($this->user['client_class']);
		$account = $this->getCookieAccount();
		$subject = trim($this->objInput->postStr('subject'));

		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$subjectInfo_arr = $mCurriculum->getCurriculumInfoByClassCode($class_code);
		$subjectInfo_list = & $subjectInfo_arr[$class_code];
		$subjectInfo = array_shift($subjectInfo_list);
		
		if(!empty($subjectInfo)) { 
			$subject_content = $subjectInfo[subject_content];
			if(!empty($subject_content)){
				if(strpos($subject_content,$subject) ===false){
					$subjectModfilyArr = array(
						'subject_content' =>$subject_content.",".$subject,
					);	
					$mCurriculum->modifyCurriculumInfoByClassCode($subjectModfilyArr, $class_code);
					echo "success";exit;
				}else {
					echo 'fail';exit;
				}			
			}else {
				$subjectInitArr = array(
					'subject_content' =>$subject,
				);	
				$mCurriculum->modifyCurriculumInfoByClassCode($subjectInitArr, $class_code);
				$mFeed = ClsFactory::Create('Model.mFeed');				
				$mFeed->addClassFeed(intval($class_code),intval($this->getCookieAccount()),intval($class_code),CLASS_FEED_CURRICULUM,FEED_UPD,time());
				
				echo "success";exit;
			}
		}else {
			$subjectInitArr = array(
				'class_code' =>$class_code,
				'upd_account' =>$this->getCookieAccount(),
				'upd_time' =>strtotime(date("Y-m-d H:i:s",time())),
				'subject_content' =>$subject,
			);	
			$returnData = $mCurriculum->addCurriculumInfo($subjectInitArr);
			if($returnData){
					echo "success";
			}else{
				echo "error";
			}
		}

	}
	
	/*删除课程科目名称 ajax post: */
	public function ajaxSubjectDelete(){
		$subjectName = trim(urldecode($this->objInput->postStr('subjectName')));
		$class_code = trim($this->objInput->postInt('classcode'));
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$subjectInfo_arr = $mCurriculum->getCurriculumInfoByClassCode($class_code);
		$subjectInfo_list = & $subjectInfo_arr[$class_code];
		$subjectInfo = array_shift($subjectInfo_list);
		
		if(!empty($subjectInfo)) { 
			$subject_content = $subjectInfo[subject_content];
			if(!empty($subject_content)){
				if(strpos($subject_content,$subjectName)!== false){
					$subject_content = trim(str_replace($subjectName,"",$subject_content));
					$lastChar = substr($subject_content,-1);
					if($lastChar==","){
						$subject_content = substr($subject_content,0,strlen($subject_content)-1);
					}
					$subject_content = str_replace(",,",",",$subject_content);
				}
				if(strpos($subject_content,$subjectName)==0){
					$subject_content = trim(str_replace($subjectName,"",$subject_content));
					$friestChar = substr( $subject_content, 0, 1 ); 
					if($friestChar==","){
						$subject_content = substr($subject_content,1,strlen($subject_content));
					}
					
				}
			}

			//更新字符串数据
			$subject_contentArr = array(
				'subject_content' =>$subject_content,
			);	

			$returnMod = $mCurriculum->modifyCurriculumInfoByClassCode($subject_contentArr, $class_code);
		}

	}
	
	/*课节调整 ajax post: */
	public function classCurriculumNums(){
		$numsCmd = $this->objInput->postStr('numsCmd');
		$arrnums = explode("|",$numsCmd);
		$nums = $arrnums[0];$cmd = $arrnums[1];$cmdFiled = $arrnums[2];
		$class_code = $arrnums[3];
		if(empty($class_code)) {
		    $class_code = key($this->user['class_info']);
		}
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$mCurriculumInfo_arr = $mCurriculum->getCurriculumInfoByClassCode($class_code);
		$mCurriculumInfo = & $mCurriculumInfo_arr[$class_code];
		
		if(!empty($mCurriculumInfo)) {
			$mCurriculumInfo = array_shift($mCurriculumInfo);
			$cmdFiled=="am" ? $curContent = unserialize($mCurriculumInfo[am_content]) : $curContent = unserialize($mCurriculumInfo[pm_content]);
			
			if($cmd=="less") { 
				$curContent = array_slice($curContent,0, $nums-5);
			} else {
				array_push($curContent,"空","空","空","空","空");
			}

			
			if($cmdFiled=="am") { 
				$curContentArr = array(
					'am_content' =>serialize($curContent),
				);	
			
			} else { 
				$curContentArr = array(
					'pm_content' =>serialize($curContent),
				);	
			}

			$mCurriculum->modifyCurriculumInfoByClassCode($curContentArr,$class_code);
			echo "success";exit;
			
		}
	

	}

	
	/**
	 * 检测当前的class_code参数是否正确
	 * @param $class_code
	 */
    private function checkclasscode($class_code = 0) {
	    if(empty($class_code)) {
	        $class_code = $this->objInput->getInt('class_code');
	        if(empty($class_code)) {
	            $class_code = $this->objInput->postInt('class_code');
	        }
	    }
	    $clientclasslist = $this->user['client_class'];
	    if(!empty($clientclasslist)) {
	        $class_code_list = array();
	        foreach($clientclasslist as $key=>$clientclass) {
	            $tmp_class_code = intval($clientclass['class_code']);
	            if($tmp_class_code > 0) {
	                $class_code_list[] = $tmp_class_code;
	            }
	        }
	    }
        if(!empty($class_code_list)) {
           $class_code_list = array_unique($class_code_list);
           $class_code = $class_code && in_array($class_code , $class_code_list) ? $class_code : array_shift($class_code_list);
        } else {
            $class_code = 0;
        }
	    return $class_code ? $class_code : false;
	}

	/**
	 * 初始化班级课程信息
	 * @param $class_code
	 */
	function InitCurriculum($class_code){
		$initData = array("0"=>"空","1"=>"空","2"=>"空","3"=>"空","4"=>"空","5"=>"空","6"=>"空","7"=>"空",
			"8"=>"空","9"=>"空","10"=>"空","11"=>"空","12"=>"空","13"=>"空","14"=>"空","15"=>"空","16"=>"空",
			"17"=>"空",	"18"=>"空","19"=>"空",
		);
		
		$CurriculumDataArr = array(
			'class_code' =>$class_code,
			'upd_account' =>$this->getCookieAccount(),
			'am_content' =>serialize($initData),
			'pm_content' =>serialize($initData),
			'upd_time' =>strtotime(date("Y-m-d H:i:s",time())),
			'subject_content'=>'',
		);	
		$mCurriculum = ClsFactory::Create('Model.mCurriculum');
		$res_id = $mCurriculum->addCurriculumInfo($CurriculumDataArr, true);
		
		return !empty($res_id) ? $CurriculumDataArr : false;
	}

}

?>
