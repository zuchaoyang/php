<?php
class PzonephotoAction extends SnsController{
   
	public function _initialize(){
	    parent::_initialize(); 
		import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Date");
		
		$this->assign('chanelid',"chanel1");
		
	}
	
	//进入我的相册
	public function photoindex(){
		$account = $this->getCookieAccount() ;
		$class_code = $this->checkclasscode($class_code);
		$pagecount = 24;
		
		$xiangce_result = $this->getMAlbumInfoModel($account);
		if(!empty($xiangce_result)){
    		foreach($xiangce_result as $key=>$val){
    		    $xiangce_result[$key]['album_name'] = htmlspecialchars_decode($xiangce_result[$key]['album_name']);
    		    $xiangce_result[$key]['album_explain'] = htmlspecialchars_decode($xiangce_result[$key]['album_name']);
    			$xiangce_result[$key]['xcimg'] = Pathmanagement_sns::getAlbum($account) . $xiangce_result[$key]['album_img'];
    		}
		}
		$this->assign('xiangce_list',$xiangce_result);
		$is_selfzone = $account && $account == $this->user['client_account'] ? true : false;

		$client_type = $this->user['client_type'];
		$this->assign('uid',$this->getCookieAccount());
		$this->assign('pagecount',$pagecount);
		$this->assign('class_code',$class_code);
		$this->assign('account',$account);
		$this->assign('log_account',$account);
		$this->assign('friendaccount',$this->getCookieAccount());
		$this->assign('ALBUM_SYS_CREATE' , ALBUM_SYS_CREATE);
		$this->assign('is_selfzone' , $is_selfzone);
		
		$this->display('personphoto');
	}


	//公用方法，个人相册列表统计 2012-3-21 by lyt:
	public function getMAlbumInfoModel($account){
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$xiangce_result = $mAlbuminfo->getAlbumInfoByaccount($account,0,100);
		if(!$xiangce_result){
			$this->adddefaultxc($account,ALBUM_SYS_CREATE);
			$xiangce_result = array_shift($xiangce_result);
		}

		return $xiangce_result;
	}


	
	//跳转到上传个人图片页面  2012-3-21 by lyt:
	public function uploadphoto(){
		$xcid=$this->objInput->getStr('xcid');
		$account = $this->getCookieAccount();
		$class_code = $this->checkclasscode($class_code);
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$albumlist = $mAlbuminfo->getAlbumListByaccount($account);
		
		$AlbumInfoData = array();
		foreach($albumlist as $key=>$list) {
			foreach ($list as $key1=>$val) {
				$AlbumInfoData[$key1] = $val;
			}
		}
		unset($albumlist);
		
		$this->assign('xiangce_list',$AlbumInfoData);
		$this->assign('account',$account);
		$this->assign('xcid',$xcid);
		$this->assign('class_code',$class_code);
		
		$this->display('uploadphoto');
	}

	
	//创建系统默认相册 2012-3-21 by lyt:
	function adddefaultxc($user_account,$xc_type){
		$adddate=time();
		$data['album_name']="系统个人相册";
		$data['album_explain']="系统默认个人相册";
		$data['add_date']=$adddate;
		$data['add_account']=$user_account;
		$data['upd_date']=$adddate;
		$data['upd_account']=$user_account;
		$data['album_create_type']=ALBUM_SYS_CREATE;
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		return $mAlbuminfo->addAlbuminfo($data);
	}
	
	//跳转创建我的相册 2012-3-21 by lyt:
	public function createxc(){
		$account = $this->getCookieAccount() ;
		$xcid = trim($this->objInput->getStr('xcid'));
		
		if($xcid != ""){
			$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
			$xcinfo = $mAlbuminfo->getAlbumListByAlbumid($xcid);
			$xcinfo = array_shift($xcinfo);
			$this->assign('xcinfo',$xcinfo);
		}

		$this->assign('account',$account);
		$this->assign('xcid',$xcid);
		$this->display('newxiangce');

	}
	
	//保存相册
	public function savexc(){
		$user_account = trim($this->getCookieAccount());
		$xcid = trim($this->objInput->postStr('xcid'));
		$xcname = trim($this->objInput->postStr('xcname'));
		$xcqx = trim($this->objInput->postStr('xcqx'));
		$xcqx=="" ? $xcqxvalue = 0 : $xcqxvalue = 1; //0:完全公开  1：仅好友可见  2：仅自己可见
		$xcms = trim($this->objInput->postStr('xcms'));
		$v_album_type= trim($this->objInput->postStr('album_type'));
		$class_code= trim($this->objInput->postStr('class_code'));
		$class_code = $this->checkclasscode($class_code);
		
		$adddate= time();
		
		if($xcid != ""){
			$data['album_name']=$xcname;
			$data['album_explain']=$xcms;
			$data['upd_date']=$adddate;
			$data['upd_account']=$user_account;

			$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
			$rs1 = $mAlbuminfo->modifyAlbuminfo($data,$xcid);

			if($rs1){
				echo "<div style='font-size:18px;color:#9C0D3F;float:center;margin-top:20px;text-align:center;font-weight:bold;'><img src='".IMG_SERVER."/Public/images/new/Ok.jpg'>相册修改成功</div>";
				echo "<script>setTimeout('parent.location.reload();parent.tb_remove();',1000);</script>";
			exit;
			}else{
				$error = "修改失败";
				$this->assign('error',$error);
			}
		}else{
			$data['album_name']=$xcname;
			$data['album_explain']=$xcms;
			$data['add_date']=time();
			$data['add_account']=$user_account;
			$data['upd_date']=time();
			$data['upd_account']=$user_account;
			$data['album_create_type']=ALBUM_USER_CREATE;
			
			$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
			$ReturnNewId = $mAlbuminfo->addAlbuminfo($data);
			if($ReturnNewId){
				$error = "添加成功";
				$this->assign('error',$error);
			echo "<div style='font-size:18px;color:#9C0D3F;float:center;margin-top:20px;text-align:center;font-weight:bold;'><img src='".IMG_SERVER."/Public/images/new/Ok.jpg'>&nbsp;相册创建成功</div>";
			echo "<script>setTimeout('parent.location.reload();parent.tb_remove();',1000);</script>";
			exit;

			}else{
				$error = "添加失败";
				$this->assign('error',$error);
			}
		}

		$this->assign('account',$user_account);
		
		$this->display('newxiangce');
		
	}
	

	//删除我的相册 2012-3-21 by lyt:
	public function deletexc(){
		$account = trim($this->getCookieAccount());
		$xcid = trim($this->objInput->getInt('xcid'));
		$class_code = trim($this->objInput->getInt('class_code'));
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
        if(empty($xcid)){
            $this->showError("相册信息错误", "/Homepzone/Pzonephoto/photoindex/class_code/$class_code"); 
        } else {
            //检测相册id是否合法
            $album_info = $mAlbuminfo->getAlbumListByAlbumid($xcid);
            if($album_info[$xcid]['add_account'] != $account) {
                $this->showError("相册信息错误", "/Homepzone/Pzonephoto/photoindex/class_code/$class_code");  
            }
        }
		
		//在删除相册时没有系统默认相册则创建一个默认相册
		$sysAlbumData =  $mAlbuminfo->getAlbuminfoByTowType(ALBUM_SYS_CREATE,$account);
		$sysAlbumData = array_shift($sysAlbumData);
		if(!$sysAlbumData){
			$sys_album_id = $this->adddefaultxc($account,ALBUM_SYS_CREATE);
		}else{
			$sys_album_id = $sysAlbumData['album_id'];
		}
	
		//删除相册时转移相册中的图片到默认相册
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$mPhotosInfo->movePhotoToNewAlbum($sys_album_id,$xcid);
		
		//删除相册
		$mAlbuminfo->deleteAlbuminfoById($xcid);

		//删除班级共享映射关系
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$Classlogstate = $mClassalbum->delClassAlbum($xcid);

		$mFeed = ClsFactory::Create('Model.mFeed');				
		$mFeed->addPersonFeed(intval($account),intval($xcid),PERSON_FEED_ALBUM,FEED_DEL,time());
		if($Classlogstate){
			$mFeed->addClassFeed(intval($class_code),intval($account),intval($xcid),CLASS_FEED_ALBUM,FEED_DEL,time());
		}

		$this->redirect('Pzonephoto/photoindex/class_code/'.$class_code);
	}
	
	
	//相册照片列表
	public function xcmanager(){
		$account = $this->objInput->getInt('user_account');
		$xcid = $this->objInput->getInt('xcid');
		$class_code = $this->checkclasscode($class_code);
		$pagecount = 24;
		
		//获取当前访问的相册的用户ID
		if(empty($account)) {
		    $account = $this->user['client_account'];
		}
		
		//我的相册列表
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		 $albumlist = $mAlbuminfo->getAlbumListByaccount($account);
		
		$xiangce_result = array();
		foreach($albumlist as $key=>$list) {
			foreach ($list as $key1=>$val) {
				$xiangce_result[$key1] = $val;
			}
		}
		
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo_result = $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_photoinfo_result = &$photoinfo_result[$xcid];
		unset($photoinfo_result,$albumlist);
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		if($new_photoinfo_result){
			$albumPhotos = array();
			foreach($new_photoinfo_result as $key=>$val){
				$val['photo_urlall'] = Pathmanagement_sns::getAlbum($account) . $val['photo_url'];
				$val['photo_min_urlall'] = Pathmanagement_sns::getAlbum($account) . $val['photo_min_url'];
				$val['photo_name'] = str_replace($account."_","",$val['photo_name']);
				$val['photo_min_url'] = trim($val['photo_min_url']);
				
				$plun_nums = $mPhotoplun->getPhotoPlunCountByPhotoId($val['photo_id']);
				$val['plunnums'] = "(" . max(intval($plun_nums), 0) . ")";
				
				$albumPhotos[] = $val;

			}
			unset($new_photoinfo_result);
		}
		
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$xcinfo = $mAlbuminfo->getAlbumListByAlbumid($xcid);
		$xcinfo = array_shift($xcinfo);
		if($xcinfo) {
		    $xcinfo['album_explain'] = htmlspecialchars_decode($xcinfo['album_explain']);
			$xcinfo['album_imgname']= trim($xcinfo['album_img']);
			$xcinfo['album_imgfm']= Pathmanagement_sns::getAlbum($account) . $xcinfo['album_img'];
			$xcinfo['add_date']= date('Y-m-d H:i:s',$xcinfo['add_date']);
			$xcinfo['upd_date']= date('Y-m-d H:i:s',$xcinfo['upd_date']);
		}
		

		//查找相册是否被分享
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$classablumdata = $mClassalbum->findAlbumexistsByAlbumid($xcid);
		if($classablumdata){
			$this->assign('sharcmd',0);
			$this->assign('shareTagValue',"取消相册分享");
		} else {
			$this->assign('shareTagValue',"将相册分享到班级");
			$this->assign('sharcmd',"1");
		}

		

		$newmyclasslist = &$this->user['class_info'];
		
		
		if (!empty($newmyclasslist)) {
			foreach($newmyclasslist as $key =>$val){
				$class_codes[$val['class_code']] = $val['class_code'];
			}
		}
		if (!empty($class_codes)) {
			$finddatas = $mClassalbum->findAlbumexistsByAlbumidclasscode($xcid,$class_codes);
			if (!empty($finddatas)) {
				foreach ($finddatas as $key1=>$val1) {
					if (in_array($val1['class_code'], $class_codes)) {
						$newmyclasslist[$val1['class_code']]['classcodechk'] = "checked";
					}
				}
			}
		}
 
		$this->assign('myclasslistnew',$newmyclasslist);
		$this->assign('photoinfo',$albumPhotos);
		$this->assign('xiangce_list',$xiangce_result);
		$this->assign('xcinfo',$xcinfo);
		$this->assign('client_type',$this->user['client_type']);

		$this->assign('account',$account);
		$this->assign('class_code',$class_code);
		
		$this->assign('photocount',count($albumPhotos));
		$this->assign('friendaccount',$this->getCookieAccount());
		$this->assign('xcid',$xcid);
		$this->assign('actionUrl','/Homeclass/Class/classalbum/');
		
		$this->display('xcmanager');
	}


	
	//设置相册封面
	public function setxcfm(){
		$xcid = trim($this->objInput->postInt('hxcid'));
		$xcfm = trim($this->objInput->postStr('xcfm'));
		$account = trim($this->objInput->postInt('haccount'));
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$data['album_img']=$xcfm;
		$rs1 = $mAlbuminfo->setAlbumCover($data,$xcid);
		$this->redirect('Pzonephoto/xcmanager/user_account/'.$account."/xcid/".$xcid);
	}
	

	//删除相册照片
	public function deletexcphoto(){
		$account = $this->objInput->postStr('haccount');
		$xcid = $this->objInput->postStr('hxcid');
		$class_code = $this->objInput->postInt('hclass_code');
		$delphotoid=explode(',',$this->objInput->postStr('delphoto'));
		array_pop($delphotoid);//去尾
		
		$delphotoname=explode(',',$this->objInput->postStr('delphoto_str'));
		array_shift($delphotoname);//去头
		array_pop($delphotoname);//去尾

		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		for($i=0;$i<count($delphotoid);$i++){
			$result = $mPhotosInfo->delPhotoInfo(intval($delphotoid[$i]));
		}
		
		$upload_path = Pathmanagement_sns::getAlbum($account);	//上传照片路径

		foreach($delphotoname as $key=>$photoname) {
			clear_file($upload_path,$photoname);	//删除文件中存储的照片
		}

		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');


		$retfminfo = $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_retfminfo = &$retfminfo[$xcid];
		unset($retfminfo);
		if(!$new_retfminfo){
			$arrdata = array(
				'album_img' =>'',
			);	

			$mAlbuminfo->modifyAlbuminfo($arrdata,$xcid);
		}


		$this->redirect('Pzonephoto/xcmanager/user_account/'.$account.'/xcid/'.$xcid."/class_code/".$class_code);
	}
	
	//删除照片评论(1:单个照片删除 2：多个照片删除)
	function deletephotoplun($photoid) {
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		$effect_rows = 0;
	    $plun_arr = $this->getPhotoPlunByPhotoId((array)$photoid);
	    if(!empty($plun_arr)) {
	        foreach($plun_arr as $photo_id=>$plun_list) {
                foreach($plun_list as $plun_id=>$plun) {
                    $mPhotoplun->delPhotoPlun($plun_id) && $effect_rows++;
                }
	        }
	    }
	    
	    return $effect_rows;
	}
	
	// 移动相册照片到另外的相册多个
	public function movephoto(){
		$account = trim($this->objInput->postStr('haccount'));
		$xcid = trim($this->objInput->postStr('hxcid'));
		$movexcid = trim($this->objInput->postStr('selxcid'));
		$class_code = trim($this->objInput->postInt('hclass_code'));
		$movephotoid=explode(',',trim($this->objInput->postStr('movephoto')));
		array_pop($movephotoid);//去尾
		
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$data['album_id']=$movexcid;
		for($i=0;$i<count($movephotoid);$i++){
			$mPhotosInfo->movePhotoInfo($movexcid,intval($movephotoid[$i]));
		}
		$this->redirect('Pzonephoto/xcmanager/user_account/'.$account.'/xcid/'.$xcid."/class_code/".$class_code);
	}
	
	//单个照片移动
	public function movephotopl(){
		
		$account = trim($this->objInput->getStr('user_account'));
		$xcid = trim($this->objInput->getStr('xcid'));
		$movexcid = trim($this->objInput->getStr('movexcid'));
		$movephoto = trim($this->objInput->postInt('delphoto'));

		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$data['album_id']=$movexcid;
		$mPhotosInfo->movePhotoInfo($movexcid,intval($movephoto));
		
		$this->redirect('Pzonephoto/xcmanager/user_account/'.$account.'/xcid/'.$xcid);
	}


	//修改照片名字
	public function updatephotoname(){
		$account = trim($this->objInput->getStr('user_account'));
		$xcid = trim($this->objInput->getStr('xcid'));
		$photoid = trim($this->objInput->postStr('delphoto'));
		$photoname=trim($this->objInput->postStr('updphotoname'));
		$data['photo_name']=$photoname;
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$mPhotosInfo->modifyphotosbyId($data,$photoid);
		
		$this->redirect('Pzonephoto/toxcphoto/user_account/'.$account.'/xcid/'.$xcid.'/photo_id/'.$photoid);
	}
	
	/*修改照片描述及名称**********************************************************/
	public function modifyNamePlain(){

		$account = trim($this->objInput->getStr('account'));
		$photoid = trim($this->objInput->getStr('photoid'));
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo=$mPhotosInfo->getPhotoInfoById($photoid);
		if($photoinfo){
		    $new_photoinfo = &$photoinfo[$photoid];
		    unset($photoinfo);
			$this->assign('account' , $account);
			$this->assign('photoid' , $photoid);
			$this->assign('photo_name' , $new_photoinfo['photo_name']);
			$this->assign('photo_explain' , $new_photoinfo['photo_explain']);
		}
		
		$this->display('modifyNamePlain');
	}

	public function updatephotoexplain(){
		$account = trim($this->objInput->getStr('user_account'));
		$xcid = trim($this->objInput->postStr('xcid'));

		$photoid = trim($this->objInput->postInt('photoid'));
		$updphotoname = trim(urldecode($this->objInput->postStr('photoname')));
		$photoexplain= trim(urldecode($this->objInput->postStr('photoexplain')));
		
	
		if(get_magic_quotes_gpc()){
			$photoexplain = stripslashes($photoexplain);
		}
		$photoexplain = htmlspecialchars($photoexplain);
		$photoexplain = str_replace("'", "&#039;", $photoexplain);
		
		if(!$photoexplain){
			echo "nomsg";exit();
		}	

		$data['photo_name']=$updphotoname;
		$data['photo_explain']=$photoexplain;
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$mPhotosInfo->modifyphotosbyId($data,$photoid);
		echo "success";exit;

	
	}
	/*修改照片描述及名称结束*******************************************************/

	//点击照片显示评论
	public function tophotoplun(){
		$account = trim($this->objInput->getStr('user_account'));
		$photoid = trim($this->objInput->getStr('delphoto'));
		$strphotoplun = $this->getphotopluncontent($account,$photoid);
		
		echo $strphotoplun;
	}	

	//用户查看照片评论
	function getphotopluncontent($account,$photoid){
		$LoginUserAccount = $this->getCookieAccount();
		$class_code = trim($this->objInput->getStr('class_code'));
		
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$photodata = $mPhotosInfo->getPhotoInfoById($photoid);
		if($photodata){
			$photodata = array_shift($photodata);
			$photo_add_account = $photodata['add_account'];
		}
		
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		$tmp_photoplun_result = $mPhotoplun->getPhotoPlunByPhotoId($photoid);
		$photoplun_result = $tmp_photoplun_result[$photoid];
		array_multisort($photoplun_result, SORT_DESC);
		$mUser = ClsFactory::Create('Model.mUser');
		if($photoplun_result){
			$strphotoplun.="<tr><td height='30' align='left' colspan='2' style='font-size:14px; font-weight:bold; color:#666;'>相片评论共（".count($photoplun_result)."）条";
			$strphotoplun.="</td></tr><tr><td colspan='4'><div style='border-bottom:1px solid  #333;'></div><br></td></tr>";//<a href='javascript:;' onclick='clientplun();' id='myplun' style='font-size:12px;font-weight:normal;margin-left:10px;color:#31447e;'>我也评一下</a>
			foreach($photoplun_result as $key=>$val){
				$ReturnClientinfo = $mUser->getUserBaseByUid($val['add_account']);
				if ($ReturnClientinfo) { 
					$ReturnClientinfo = array_shift($ReturnClientinfo);
					$client_headimg = $ReturnClientinfo['client_headimg'];
				}
				//<a href='javascript:void(0);' onclick=\"javascript:photoplunComback(".$photoplun_result[$i]['plun_id'].",".$photoplun_result[$i]['add_account'].",'GR');\">【回复】</a>&nbsp;&nbsp;
				if($photo_add_account==$LoginUserAccount){                                                             
					$tagControl = "&nbsp;&nbsp;<a href='javascript:void(0);' onclick=\"javascript:deletephotoplun('" . $val['plun_id'] . "');\" style='font-weight:normal;margin-left:10px;margin-top:5px;color:#31447e;'>删除</a>";
				}

			    $val['plun_content'] = htmlspecialchars_decode($val['plun_content']);
				$val['add_date'] =  Date::formatedateparams($val['add_date']);
				$val['client_headimg']=Pathmanagement_sns::getHeadImg($val['add_account']) . $client_headimg;
				$strphotoplun.='<tr><td width="10%" rowspan="2" valign="top" align="left"><img src="'.$val['client_headimg'].'"'.' onerror="this.src=\''.IMG_SERVER.'/Public/images/head_pic.jpg\'"'.' width="60px" height="60px"/></td>';
				$strphotoplun.="<td align='left' style='text-indent:12px;width:600px;word-wrap:break-word;word-break:break-all; line-height:22px;' width='90%' height='30px'><a href='/Homeuser/Index/spacehome/spaceid/".$val['add_account']."' target='_blank' style='color:#31447e;'> " . $ReturnClientinfo['client_name'] . "：</a>&nbsp;&nbsp;".$val['add_date'].$tagControl."</td></tr>";
				
				$strphotoplun.="<tr><td height='30px' align='left' colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;<div class='wordwrap' style='word-break:break-all;width:450px;padding-left:20px;'>".$val['plun_content']."</div>&nbsp;&nbsp;&nbsp; ";
				
				$strphotoplun.="</td></tr><tr><td colspan='2'>&nbsp;<div style='border-bottom:1px dashed  #cccccc;'></div><br></td></tr>";
			}
		}else{
			$strphotoplun.="<tr><td height='30' colspan='2' style='font-size:14px; font-weight:bold; color:#666;'>相片评论共（0）条</td></tr>";
		}
		return $strphotoplun;
	}

	
	//添加评论
	public function addpl(){
		$friendaccount = trim($this->objInput->postStr('friendaccount'));
		$photo_id = trim($this->objInput->postStr('photo_id'));
		$account = trim($this->objInput->postStr('user_account'));
		$plun_content = trim($this->objInput->postStr('photoplun'));

		$facelist = Constancearr::getfacelist();
		$faceSearch=$faceReplace=array();
		if($facelist){
			foreach($facelist as $key => $val){
				$alt = str_replace("/", "", $facelist[$key]);
				$faceSearch[] = $facelist[$key];
				$faceReplace[] = "<img src=\"".IMG_SERVER."/Public/images/face/$key.gif\" width=\"22\" height=\"22\" alt=\"".$alt."\" />";
			}
		}
		$plun_content = str_replace($faceSearch, $faceReplace, $plun_content);
		$adddate=date("Y-m-d H:i:s",time());
		$dateflag = strtotime(date('Y-m-d H:i:s'));
		$data['photo_id']=$photo_id;
		$data['plun_content']=$plun_content;
		$data['add_account']=$friendaccount;
		$data['add_date']=$adddate;
		$data['photo_account']=$account;
		
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		$mPhotoplun->addPhotoPlun($data, true);
		$strphotoplun = $this->getphotopluncontent($account,$photo_id);
		foreach($strphotoplun as $key=>$val){
			 $val['add_date'] =  Date::formatedateparams($val['add_date']);
			 $strphotoplun[$key] = $val;
		}
		echo $strphotoplun;
	}


	
	//删除评论
	public function delphotoplun(){
		$account = $this->getCookieAccount();
		$photo_id = trim($this->objInput->getStr('photo_id'));
		$plunid = trim($this->objInput->getStr('plun_id'));
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		$mPhotoplun->delPhotoPlun($plunid);
		$strphotoplun = $this->getphotopluncontent($account,$photo_id);
		echo $strphotoplun;
	}
	
	//从相册最新评论删除评论
	public function delphotonewplun(){
		$photo_id = trim($this->objInput->getStr('photo_id'));
		$account = trim($this->objInput->getStr('user_account'));
		$plunid = trim($this->objInput->getStr('plun_id'));
	
		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		
		//todolist 检测相关的业务，是否是以前的代码调用的时候参数传递有误
		$mPhotoplun->delPhotoPlun($plunid);
		
		$this->redirect('Pzonephoto/photoindex/user_account/'.$account);
	}
	
	//上传相册图片（js队列单张上传，多次调用）
    public function uploadApplication() { 
        $xiangce = $this->objInput->postStr('xcid');
        //$account = $this->getCookieAccount(); //bug:通过flash请求时，firefox无法获取cookie信息
        $account = $this->objInput->postStr('PHPSESSID');
        
        $max_width_bigpic = 500;   //没有对大图进行缩放,需要在页面加宽度限制
        $max_width_smllpic = $max_height_smllpic = 112;
		$attachements_path = Pathmanagement_sns::uploadAlbum($account); 
		
		$new_name =  $account.'_'.time().WmwString::rand_string(5,2,'1234567890'); //不包含扩展名
        
        $up_init = array (
			  'attachmentspath' => $attachements_path, 
              'renamed' => true,
              'newname' => $new_name,
			  'ifresize' => true,
              'max_size' => 8 * 1020, // 最大8M
			  'resize_width' => $max_width_smllpic,
              'resize_height' => $max_height_smllpic,
        	  'allow_type' => array('jpg', 'png', 'gif', 'bin'),
    	);
    	
		$uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
        $uploadObj->_set_options($up_init);
        $up_rs = $uploadObj->upfile('Filedata'); 
        
        if(!empty($up_rs)) {
            //重命名小图：xxx_small.jpg => xxx_s.jpg
            $small_pic_name = $new_name.'_s.'.array_pop(explode('.',$up_rs['getsmallfilename']));
            $rs =  rename($up_rs['getsmallfilename'], $attachements_path.'/'.$small_pic_name);
            $photo_name = $photo_url = $new_name.'.'.array_pop(explode('.',$up_rs['getfilename']));

            $data=array(
    			'album_id'      => $xiangce,  
    			'photo_name'    => $photo_name, // 类中返回的$up_r['filename']有误，同$up_r['getfilename']值相同
    			'photo_url'     => $photo_url,  //该字段可删除
    			'photo_min_url' => $small_pic_name,
    			'photo_explain' => "",
    			'add_date'      => time(),
    			'add_account'   => $account,
    			'upd_date'      => time(),
    			'upd_account'   => $account,
    		);
    		
    		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
    		$album_id=$mPhotosInfo->addphotos($data, true);
    		//上传相片时添加用户动态信息表 intval
    		$mFeed = ClsFactory::Create('Model.mFeed');
    		$mFeed->addPersonFeed(intval($account),intval($album_id),PERSON_FEED_ALBUM,FEED_UPD,time());
        }
        
		echo 'success:'.$up_rs['getfilename'].','.$_FILES['Filedata']['name'].','.$up_rs['size'].','.$up_rs['getsmallfilename'];
    }
    

	//保存上传照片
	function saveuploadphoto($large_image_location,$xiangce,$user_account,$PHPSESSID){
		$photoname = substr($large_image_location, strrpos($large_image_location, '/') + 1);//照片名称
		$photoid  = substr($photoname, 0,strrpos($photoname, '.'));//照片id
		$file_ext = strtolower(substr($photoname, strrpos($photoname, '.') + 1));//照片后缀
		$adddate= time();
		$data=array(
			'album_id'=>$xiangce,
			'photo_name'=>$photoname,
			'photo_url'=>$photoname,
			'photo_min_url'=>$photoid."_s.".$file_ext,
			'photo_explain'=>"",
			'add_date'=>$adddate,
			'add_account'=>$PHPSESSID,
			'upd_date'=>$adddate,
			'upd_account'=>$PHPSESSID,
			
		);
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$album_id=$mPhotosInfo->addphotos($data, true);
		//上传相片时添加用户动态信息表 intval
		$mFeed = ClsFactory::Create('Model.mFeed');
		$mFeed->addPersonFeed(intval($user_account),intval($album_id),PERSON_FEED_ALBUM,FEED_UPD,time());
	}

	//更新最新上传的相册ID
	function modifyAllbumId(){
		$xcid = trim($this->objInput->getInt('xcid'));
		$class_code = trim($this->objInput->getInt('class_code'));
		
		$account = $this->getCookieAccount();
		
		$mPhotoInfo = ClsFactory::Create('Model.mPhotosInfo');
		$data['album_id']=$xcid;
			
		$mPhotoInfo->modifyphotos($data,$this->getCookieAccount());
		$this->redirect('Pzonephoto/xcmanager/user_account/'.$this->getCookieAccount()."/xcid/".$xcid."/class_code/".$class_code);
	}


	

	function uploadAppthumbnail(){		
		$image_id = isset($_GET["id"]) ? $_GET["id"] : false;
		if ($image_id === false) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "No ID";
			exit(0);
		}
		echo $image_id;
		exit(0);	
	}


	//相册分享到班级
	function plushalbum() { 
		$albumid = trim($this->objInput->getInt('albumid'));
		$class_code = trim($this->objInput->getInt('class_code'));
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$class_code = $this->checkclasscode($class_code);
		$classInfoData = array(
			'album_id' =>$albumid,
			'class_code' =>$class_code,
			'add_time' =>time()
		);
		$is_exist = $mClassalbum->getAlbumInfoByalbumIdClassCode($albumid,$class_code);
		if(empty($is_exist)) {
			$returnData = $mClassalbum->addClassAlbumInfo($classInfoData);
		}	
		if(empty($returnData)) {
			echo "success";exit;
		} else {
			echo "error";exit;
		}
	}


	//相册分享-删除相册需取消分享映射
	public function thisAlbumShareDo(){
		
		$albumid = $this->objInput->postInt('albumid');
		$classcode = $this->objInput->postStr('classcode');
		$shareCmd = $this->objInput->postStr('cmd');
		//个人分享处理，模版页面标识，是否已经分享
		if(!empty($classcode)){
			$shareClass = explode(",",$classcode);
			$shareClass = array_unique($shareClass);
		}
		
		$mClassalbum = ClsFactory::Create('Model.mClassalbum');
		$client_type = $this->user['client_type'];
		$mFeed=ClsFactory::Create('Model.mFeed');
		switch($client_type){
			case 0 :
				switch($shareCmd){
					case 1 : 
						$client_class = array_shift($this->user['client_class']);
						$class_code = $client_class['class_code'];
						$classInfoData = array(
							'album_id' =>$albumid,
							'class_code' =>$class_code,
							'add_time' =>time()
						);	
						$is_exist = $mClassalbum->getAlbumInfoByalbumIdClassCode($albumid,$class_code);
						if(empty($is_exist)) {
							$mClassalbum->addClassAlbumInfo($classInfoData);
						}
						$mFeed->addClassFeed(intval($class_code),intval($this->user['client_account']),intval($albumid),CLASS_FEED_ALBUM,FEED_NEW,time());
						break;

					case 0 :
						$client_class = array_shift($this->user['client_class']);
						$class_code = $client_class['class_code'];
				        $class_album_arr = $mClassalbum->getAlbumInfoByalbumIdClassCode($albumid,$class_code);
						foreach($class_album_arr as $key=>$val) {
						    $mClassalbum->delClassAlbum($key);
						}
						break;
				}	
				$msg = "success";
				break;
			case 1 :
				switch($shareCmd){
					case 1 : 
						$msg = "cancel";
						if(is_array($shareClass)){
							foreach($shareClass as $Ckey=>$Cval){
								$classInfoData = array(
									'album_id' =>$albumid,
									'class_code' =>$Cval,
									'add_time' =>time()
								);	
								$sharedata = $mClassalbum->findAlbumexistsByAlbumidclasscode($albumid,$Cval);
								$msg = "success";
								if(!$sharedata){
									$is_exist = $mClassalbum->getAlbumInfoByalbumIdClassCode($albumid,$class_code);
									if(empty($is_exist)) {
										$returnData = $mClassalbum->addClassAlbumInfo($classInfoData);
									}	
									$mFeed->addClassFeed(intval($Cval),intval($this->user['client_account']),intval($albumid),CLASS_FEED_ALBUM,FEED_NEW,time());
								}
							}
							
						}
						break;

					case 0 :
						foreach($this->user['client_class'] as $Ckey1=>$Cval1){
    						$class_album_arr = $mClassalbum->getAlbumInfoByalbumIdClassCode($albumid,$Cval1['class_code']);
    						foreach($class_album_arr as $key=>$val) {
    						    $mClassalbum->delClassAlbum($key);
    						}
						}						
						$msg = "success";
						break;
				}				
			break;
		}
		echo $msg;exit;
	}



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
}
?>
