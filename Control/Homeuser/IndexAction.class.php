<?php
class IndexAction extends SnsController {
	public function _initialize(){
	    parent::_initialize();

		import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Date");
		import("ORG.Util.Page");
		
		$this->getSpaceInfo();
		
	}


	public function index(){
		$this->redirect('/Homepage/Homepage/index');
	}	
	
	public function getnewscount($is_not_ajax){
		$uid = $this->getCookieAccount();
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByToUid($uid , array('news_type' => array(NEWS_INFO_XT_FRIEND,NEWS_INFO_HY) , 'upd_account' => null));
	    $newsinfolist = $newsinfoarr[$uid];
	    unset($newsinfoarr);
		$rscount = count($newsinfolist);
		if($rscount > 0){
			$echostr = "(+".$rscount.")";
		}else{
			$echostr = '';
		}
		if(empty($is_not_ajax)){
		    echo $echostr;
		}else{
		    return !empty($echostr) ? $echostr : '(0)';
		}
	}

	//第一视角加载空间主页
	function spacehome(){
		$spaceid_account = trim($this->objInput->getInt('spaceid'));
		$this->chkThisSpaceId($spaceid_account);
		$loginid = $this->user['client_account'];
		
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$newarr_talkInfo = $this->loadPersonFeedData($spaceid_account,$lastUpdTime, 0, WMW_XXS_LIMIT);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);
		$this->assign('account',$spaceid_account);
		$this->assign('loginid',$loginid);
		$this->assign('arrInfoData',$newarr_talkInfo);
		$this->assign('class_code',key($this->user['class_info']));
		
		$this->getSpaceCount($spaceid_account);
		if($spaceid_account == $loginid){
			$this->display('spacehome');
		}else{
			$this->display('spaceIndex');
		}
		

	}
	public function morefeedlist(){
		$nextLlimit = $this->objInput->getInt('nextLlimit');
	    $spaceid_account = $this->objInput->getInt('account');
	    
		$mUser = ClsFactory::Create('Model.mUser');
		if(!$this->_isLoginCheck){//todolist
		   $this->assign('falg_login',0);
		} else {
			$this->assign('falg_login',1);
		}
		$loginid = $this->user['client_account'];
		if($spaceid_account == $loginid){
			
			$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
			$tmp_firendinfos = $mAccountrelation->getAccountRelationByAddAccount($loginid);
			$firendinfos = $tmp_firendinfos[$loginid];
			$firend_ids = array();
			$firend_ids[$loginid] = $loginid;
			foreach($firendinfos as $key=>&$val){
				$firend_ids[$val['friend_account']] = $val['friend_account'];
			} 
			$this->assign('falg_delete',1);
		}else{
			$flag = true;
			$firend_ids[$spaceid_account] = $spaceid_account;
			$this->assign('keyUserType',$this->user['client_type']);
			$this->chkThisSpaceId($spaceid_account);
			$this->assign('falg_delete',0);
		}
	    
		
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$arrInfoData = $this->loadPersonFeedData($spaceid_account, $lastUpdTime, 0, 20);
		$newarr_talkInfo = array_slice($arrInfoData, $nextLlimit, WMW_XXS_LIMIT);
		unset($arrInfoData);
		$Thtmldata = "";
		if ($newarr_talkInfo) { 
			foreach($newarr_talkInfo as $key=>$val){

			$htmldata .= "<div class=\"m_message\">";
				$htmldata .= "<div class=\"m_message_l\">";
					$htmldata .= "<a href='/Homeuser/Index/spacehome/spaceid/{$val['client_account']}' title='访问Ta的空间' target='_blank'><img src='{$val['client_headimg_url']}' width=\"60\" height=\"60\" onerror=\"this.src='".IMG_SERVER."/Public/images/head_pics.jpg'\"/></a>"; 
				$htmldata .= "</div>";
				$htmldata .= "<div class=\"m_message_r\">";
					$htmldata .= "<h3 style=\"padding-left:10px;\"><strong>";
					$htmldata .= "{$val['client_name']}<br/>";
					$htmldata .= "{$val['feed_title']}：<br/>";
					if(isset($val['feed_url'])){
						$htmldata .= "<a href=".$val['feed_url'].">{$val['feed_name']}</a>";
					}else{
						$htmldata .= "{$val['feed_name']}";
					}
					
					$htmldata .= "</strong>";
					$htmldata .= "</h3>";
					$htmldata .= "<div class=\"m_message_rt\">";
					$htmldata .= "<p>";
					$htmldata .=$val['feed_upd_time'];
					$htmldata .= "</p>";
					$htmldata .= "</div>";

				$htmldata .= "</div>";
				$htmldata .= "<div class=\"kong\"></div>";
			$htmldata .= "</div>";


			}
			if($Thtmldata == "") {
				$Thtmldata = $htmldata ;
			} else {
				$Thtmldata = $Thtmldata.$htmldata ;
			}
			
			$nextLlimitValue = $nextLlimit+WMW_XXS_LIMIT;

			echo $Thtmldata;
			if(!empty($nextLlimitValue)) {
				echo "<script>document.getElementById('nextLlimit').value=".$nextLlimitValue.";</script>";
			}

		}
		
		
	}

	public function loadPersonFeedData($spaceid_account, $lastUpdTime, $offset, $length){
		
		$flag = false;
		if(!$this->_isLoginCheck){//todolist
		   $this->assign('falg_login',0);
		} else {
			$this->assign('falg_login',1);
		}
		$loginid = $this->user['client_account'];
		if($spaceid_account == $loginid){
			$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
			$tmp_firendinfos = $mAccountrelation->getAccountRelationByAddAccount($loginid);
			$firendinfos = $tmp_firendinfos[$loginid];
			$firend_ids = array();
			$firend_ids[$loginid] = $loginid;
			foreach($firendinfos as $key=>&$val){
				$firend_ids[$val['friend_account']] = $val['friend_account'];
			} 
			$this->assign('falg_delete',1);
		}else{
			$flag = true;
			$firend_ids[$spaceid_account] = $spaceid_account;
			$this->assign('keyUserType',$this->user['client_type']);
			$this->chkThisSpaceId($spaceid_account);
			$this->assign('falg_delete',0);
		}
		
		$mFeed = ClsFactory::Create('Model.mFeed');
		$arrlist = $mFeed->getPersonFeedList($firend_ids, $lastUpdTime, $offset, $length);
		$arrlist_type = array();
		foreach($arrlist['feed'] as $key1=>&$val1){
			$arrlist_type[$val1['res_type']][] = $val1['res_id'];
		}
		$mUser = ClsFactory::Create('Model.mUser');
		$client_infos = $mUser->getUserBaseByUid($firend_ids);
		foreach($arrlist_type as $key2=>&$val2){
			if($key2==PERSON_FEED_TALK){
				//个人新鲜事
				$mPersonTalk = ClsFactory::Create('Model.mPersonTalk');
				$feedlist = $mPersonTalk->getPersonTalkById($val2);
				//评论
				$mPersontalkcomment = ClsFactory::Create('Model.mPersontalkcomment');
				$tmp_commentlist = $mPersontalkcomment->getPersonTalkCommentBySignId($val2);
				$commentlist = $tmp_commentlist[$val2];
				unset($tmp_commentlist);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['sign_id'] && $arrlist_val['res_type']==$key2){
								$CommentNums = count($commentlist[$arrlist_val['res_id']]);
								$arrlist['feed'][$arrlist_key]['CommentNums'] = $CommentNums?$CommentNums:0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '新鲜事';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新鲜事';
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了新鲜事';
								}
								
								$talkval['sign_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$talkval['sign_content']);
								if(!strpos($talkval['sign_content'],IMG_SERVER)){
   									$talkval['sign_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$talkval['sign_content']);
 								}
 								
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['sign_content'];
								$arrlist['feed'][$arrlist_key]['feed_name'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrlist['feed'][$arrlist_key]['feed_name']);
								$arrlist['feed'][$arrlist_key]['feed_name'] = str_replace("[/IMG]", "'>", $arrlist['feed'][$arrlist_key]['feed_name']);
								if($flag){
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams(date('Y-m-d H:i:s', $talkval['add_date']));
								}else{
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s', $talkval['add_date']);
								}
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$commentlist,$CommentNums);
				}
			}elseif($key2==PERSON_FEED_LOG){
				//个人动态日志类型
				$mPersonlogs = ClsFactory::Create('Model.mPersonlogs');
				$feedlist = $mPersonlogs->getPersonLogsById($val2);
				$mLogplun = ClsFactory::Create('Model.mLogplun');
				$commentlist = $mLogplun->getLogplunByLogid($val2);
				
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['log_id'] && $arrlist_val['res_type']==$key2){
								$CommentNums = count($commentlist[$arrlist_val['res_id']]);
								$arrlist['feed'][$arrlist_key]['CommentNums'] = $CommentNums?$CommentNums:0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '日志';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新日志';
									if($flag){
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams($talkval['add_date']);
									}else{
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
									}
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了日志';
									if($flag){
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams($talkval['add_date']);
									}else{
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
									}
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeuser/Index/spacelogview/spaceid/{$talkval['add_account']}/log_id/{$talkval['log_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['log_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$commentlist,$CommentNums);
				}
			}elseif($key2==PERSON_FEED_ALBUM){
				//个人动态相册类型
				$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
				$feedlist = $mPhotosInfo->getPhotoInfoById($val2);
				foreach($feedlist as $feedkey=>&$feedval){
					$album_id[$feedval['album_id']] =  $feedval['album_id'];
				}
				$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
				$albuminfo = $mAlbuminfo->getAlbumListByAlbumid($album_id);
				foreach($feedlist as $feedkey1=>&$feedval1){
					foreach($albuminfo as $albkey=>&$albval){
						if($albval['album_id'] == $feedval1['album_id']){
							$feedval1['album_name'] = $albval['album_name'];
						}
					}
				}
				unset($album_id,$albuminfo);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['photo_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '相册';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新相册';
									if($flag){
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams($talkval['add_date']);
									}else{
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s', $talkval['add_date']);
									}
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了相册';
									if($flag){
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams($talkval['add_date']);
									}else{
										$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s', $talkval['upd_date']);
									}
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeuser/Index/albumphotodetail/spaceid/{$talkval['add_account']}/xcid/{$talkval['album_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['album_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
						
					}
					unset($feedlist,$CommentNums);
				}
			}elseif($key2==PERSON_FEED_LEAVE){
				//个人动态留言板类型
				$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
				$feedlist = $mGuestbookInfo->getGuestbookInfoById($val2);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['guestbook_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = $CommentNums?$CommentNums:0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '留言';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '新留言';
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了留言';
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeuser/Index/guestbook/spaceid/{$talkval['add_account']}";
								if($flag){
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = Date::formatedateparams($talkval['add_date']);
								}else{
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['add_date'];
								}
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['guestbook_content'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$CommentNums);
				}
			}
		}
		unset($client_infos,$feedlist,$arrlist_type,$firendinfos,$firend_ids);
		return $arrlist['feed'];
	
	}
	
	//新鲜事
	public function spacetalk(){
		$spaceid_account = trim($this->objInput->getInt('spaceid'));
		$this->chkThisSpaceId($spaceid_account);

		if($this->getCookieAccount()==$spaceid_account){
			$this->assign('falg_delete',1);
		}else{
			$this->assign('falg_delete',0);
		}
		$mUser = ClsFactory::Create('Model.mUser');
		if(!$this->_isLoginCheck){//todolist
		   $this->assign('falg_login',0);
		} else {
			$this->assign('falg_login',1);
		}
		
		$mData = ClsFactory::Create('Model.mPersonTalk');	
		$arrInfoData = $mData->getPersonTalkByAddAccount($spaceid_account);
		//数据维度处理 $arrInfoData 降为二维
		$new_arrInfoData = array();
		if(!empty($arrInfoData)){
			foreach ($arrInfoData as $key=>$val) {
				foreach ($val as $k=>$v) {
					$new_arrInfoData[] = $v;
				}
			}
		}
		$arrInfoData = $new_arrInfoData;
		unset($new_arrInfoData);
		
		$faceSearch=$faceReplace=array();
		$facelist = Constancearr::getfacelist();
		$mUser = ClsFactory::Create('Model.mUser');
		if($arrInfoData){
			$sortkeys = array();
			for($i=0;$i<count($arrInfoData);$i++){
				$userlist = $mUser->getUserBaseByUid($arrInfoData[$i]['add_account']);
				$arrInfoData[$i]['account_headpic_path'] = $userlist[$arrInfoData[$i]['add_account']]['client_headimg_url'];
				$arrInfoData[$i]['client_name']= $userlist[$arrInfoData[$i]['add_account']]['client_name'];
				
				$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
				
				$arrInfoData[$i]['sign_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$arrInfoData[$i]['sign_content']);
				if(!strpos($arrInfoData[$i]['sign_content'],IMG_SERVER)){
   					$arrInfoData[$i]['sign_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$arrInfoData[$i]['sign_content']);
				} 
				
				$arrInfoData[$i]['sign_content'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrInfoData[$i]['sign_content']);
				$arrInfoData[$i]['sign_content'] = str_replace("[/IMG]", "'>", $arrInfoData[$i]['sign_content']);
				$newKey = date('Y-m-d H:i:s',$arrInfoData[$i]['add_date']);
				if(empty($arrInfoData[$i]['comment_nums'])){
					$arrInfoData[$i]['comment_nums']=0;
				}
				$arrInfoData[$i]['add_datetime']=Date::formatedateparams($newKey);

				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$arrInfoData[$i]['sign_content'] = str_replace($faceSearch, $faceReplace, $arrInfoData[$i]['sign_content']);
				}
				

			}//for

			foreach($arrInfoData as $key=>$value) {
	            $sortkeys[$key] = $value['sign_id'];
	        }
			array_multisort($sortkeys , SORT_DESC , $arrInfoData);
			$newarr_talkInfo = array_slice($arrInfoData, 0, WMW_XXS_LIMIT);	
		}//if
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);
		$this->getSpaceCount($spaceid_account);
		$this->assign('spaceid_account',$spaceid_account);
		$this->assign('curr_account',$this->getCookieAccount());
		
		$this->display('spacetalk');

	}
	
	public function morexxxlist(){
		$nextLlimit = $this->objInput->getInt('nextLlimit');
		$spaceid_account = $account = $this->objInput->getInt('account');
		$mUser = ClsFactory::Create('Model.mUser');

		$mData = ClsFactory::Create('Model.mPersonTalk');	
		$arrInfoData = $mData->getPersonTalkByAddAccount($account);
		//$arrInfoData 降为二维
		$new_arrInfoData = array();
		if(!empty($arrInfoData)){
			foreach ($arrInfoData as $key=>$val) {
				foreach ($val as $k=>$v) {
					$new_arrInfoData[] = $v;
				}
			}
		}
		$arrInfoData = $new_arrInfoData;
		unset($new_arrInfoData);
		
		$faceSearch=$faceReplace=array();
		$facelist = Constancearr::getfacelist();
		

		if($arrInfoData){
			$sortkeys = array();
			for($i=0;$i<count($arrInfoData);$i++){
				$userlist = $mUser->getUserBaseByUid($arrInfoData[$i]['add_account']);
				$arrInfoData[$i]['account_headpic_path'] = $userlist[$arrInfoData[$i]['add_account']]['client_headimg_url'];
				$arrInfoData[$i]['client_name']= $userlist[$arrInfoData[$i]['add_account']]['client_name'];
				$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
				
				$arrInfoData[$i]['sign_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$arrInfoData[$i]['sign_content']);
				if(!strpos($arrInfoData[$i]['sign_content'],IMG_SERVER)){
   					$arrInfoData[$i]['sign_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$arrInfoData[$i]['sign_content']);
				} 
				
				$arrInfoData[$i]['sign_content'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrInfoData[$i]['sign_content']);
				$arrInfoData[$i]['sign_content'] = str_replace("[/IMG]", "'>", $arrInfoData[$i]['sign_content']);
				$newKey = date('Y-m-d H:i:s',$arrInfoData[$i]['add_date']);
				$arrInfoData[$i]['add_datetime']=Date::formatedateparams($newKey);

				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$arrInfoData[$i]['sign_content'] = str_replace($faceSearch, $faceReplace, $arrInfoData[$i]['sign_content']);
				}
			}//for
	
	
			foreach($arrInfoData as $key=>&$value) {
	            $sortkeys[$key] = $value['sign_id'];
				if(empty($value['comment_nums'])){
	            	$value['comment_nums']=0;
	            }
	        }

			array_multisort($sortkeys , SORT_DESC , $arrInfoData);
			$newarr_talkInfo = array_slice($arrInfoData, $nextLlimit, WMW_XXS_LIMIT+$nextLlimit);
			if($newarr_talkInfo){
				foreach($newarr_talkInfo as $key => $value){
					$htmldata ="<div class=\"m_message\">";
						$htmldata =$htmldata."<div class=\"m_message_l\">";
							$htmldata =$htmldata."<a href='/Homeuser/Index/spacehome/spaceid/".$value['add_account']."' title='访问Ta的空间' target='_blank'><img src=\"".$value['account_headpic_path']."\" width=\"60\" height=\"60\" onerror=\"this.src='".IMG_SERVER."/Public/images/head_pics.jpg'\"/></a> ";
						$htmldata =$htmldata."</div>";

						$htmldata =$htmldata."<div class=\"m_message_r\">";
							$htmldata =$htmldata."<h3><strong>";
							$htmldata =$htmldata.$value['client_name'];
							$htmldata =$htmldata.$value['add_datetime']."：刚刚发布了新鲜事</strong></h3>";
							
							$htmldata =$htmldata."<div class=\"m_message_rt\">";
							$htmldata =$htmldata."<p>".$value['sign_content']."</p>";

							$htmldata =$htmldata."<div class=\"m_message_rm\">";
							if($this->getCookieAccount()==$spaceid_account) { 
								$htmldata = $htmldata."<span><a href=\"#\" title=\"删除\" onclick=\"return deleteSay('".$value['sign_id']."','space');\" ><font color='#889DB6'>删除</font></a></span>";
							}			
							$htmldata =$htmldata."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							if ($this->getCookieAccount()!="") {
								$htmldata = $htmldata."<a href=\"#\" title=\"评论\" onclick=\"return commentXXX(this, '".$value['sign_id']."','".$value['add_account']."');\" ><font color='#889DB6'>发表评论</font></a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" class='link1' title=\"查看评论\"  onclick=\"return listcomment(this, '".$value['sign_id']."');\">评论</a>(<span id=\"pcount_".$value['sign_id']."\">".$value['comment_nums']."</span>)↓";
							} else {
								$htmldata = $htmldata."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"评论\" onclick=\"javascript:needtoLogTip('请登录后发表评论内容');\" ><font color='#889DB6'>发表评论</font></a>&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" class='link1' title=\"查看评论\"  onclick=\"javascript:needtoLogTip('请登录后查看评论内容');\">评论</a>(<span id=\"pcount_".$value['sign_id']."\">".$value['comment_nums']."</span>)↓";
							}
							
							$htmldata = $htmldata."<div id=\"plist_".$value['sign_id']."\" class=\"plist\" style='display:none'></div>";

								$htmldata = $htmldata."</div>";
							$htmldata = $htmldata."</div>";
						$htmldata = $htmldata."</div>";
						$htmldata = $htmldata."<div class=\"kong\"></div>";
					$htmldata = $htmldata."</div>";
					

					if($Thtmldata == "") {
						$Thtmldata = $htmldata ;
					} else {
						$Thtmldata = $Thtmldata.$htmldata ;
					}
				}

				$nextLlimitValue = $nextLlimit+WMW_XXS_LIMIT;
				}
			}
		
			echo $Thtmldata;
			if(!empty($nextLlimitValue)) {
				echo "<script>document.getElementById('nextLlimit').value=".$nextLlimitValue.";</script>";
			}
		}
	
	// 公告方法 统计数量
	function getSpaceCount($spaceid_account) {
		$mData = ClsFactory::Create('Model.mPersonTalk');	    
		$DataRows = $mData->getDataRows($spaceid_account);
		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');	    
		$friendNums = $mAccountrelation->getAccountRelationCountByClientAccount($spaceid_account);
		
       $friendqqNum =  $this->getnewscount(true);
		$this->assign('friendqqNum',$friendqqNum);
		$this->assign('DataRows',$DataRows);
		$this->assign('friendNums',$friendNums);
		$this->assign('datacount',$DataRows);

	}


	//个人空间日记
	//todolist 日志类型的获取函数调用不合理
	function spacelogindex(){
		$friendaccount=$this->getCookieAccount();
		$spaceid_account=trim($this->objInput->getStr('spaceid'));
		$this->chkThisSpaceId($spaceid_account);

		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=10; //每页数量

		$log_type = trim($this->objInput->getStr('logtype'));
		$mPersonlogs = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');	 

		$filters = array(
		    'log_status' => 1,
			'log_type' => $log_type,
		);
		
		$log_arr = $mPersonlogs->getPersonLogsByAddaccount($spaceid_account, $filters);
		$LogInfoArrData = & $log_arr[$spaceid_account];
		
		if($LogInfoArrData){
			$sortkeys = array();
			$newLog = array();
			foreach($LogInfoArrData as $key=>$val){
				$log_plun_count=$this->pluncontent($val['log_id']);
				$val['plun_count']=$log_plun_count;

				$logcontent = str_replace('&nbsp;','',WmwString::unhtmlspecialchars($val['log_content']));
				$logcontent = str_replace('<P></P>','',$logcontent);
				$val['log_contentall']= strip_tags(cutstr(WmwString::unhtmlspecialchars($logcontent), 100, true));


				$RsmLogtypes = $mLogtypes->getLogTypesById($val['log_type']);
				$RsmLogtypes = array_shift($RsmLogtypes);
				$val['logtype_name'] = $RsmLogtypes['logtype_name'];
				$newLog[] = $val;
			}
			unset($val);

			 foreach($newLog as $key=>$value) {
	            $sortkeys[$key] = $value['add_date'];
	        }

			//排序日记
			array_multisort($sortkeys , SORT_DESC , $newLog);
		}
		


		$newarr_newLog = array_slice($newLog, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeuser/Index/spacelogindex/spaceid/".$spaceid_account;
		$pageCount = round(count($newLog)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		
		$this->assign('pageinfohtml',"<a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a>");
		$this->assign('pageno',$pageno);
		
		$this->assign('mylog_list',$newarr_newLog);
		$this->assign('uid',$this->getCookieAccount());

		$this->assign('friendaccount',$friendaccount);
		$this->assign('log_account',$spaceid_account);
		$this->assign('client_name',$this->user['client_name']);
		$this->assign('account' , $spaceid_account);

		$this->getSpaceCount($spaceid_account);
		$this->display('spacelogindex');
	
	}

	function spacelogview(){
		$log_account = $this->objInput->getInt('spaceid');
		$log_id = $this->objInput->getInt('log_id');
		$this->chkThisSpaceId($log_account);
		$log_id = $log_id > 0 ? $log_id : 0;
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');

		//获取日志的基本信息
		//tododel$client_info =D('client_info');
		$mUser = ClsFactory::Create('Model.mUser');
		if($log_id) {
			$log_list = $mOjbectData->getPersonLogsById($log_id);
		    if(!empty($log_list) && is_array($log_list)) {
		        foreach($log_list as $log) {
		            if($log['log_id'] == $log_id) {
		                $log_info = $log;
						//tododel$client_infos = $client_info->find($log_info['add_account']);
						$client_infos = $mUser->getUserBaseByUid($log_info['add_account']);
						$client_infos = &$client_infos[$log_info['add_account']];
						$log_info['log_contentall']=WmwString::unhtmlspecialchars($log_info['log_content']);
						$log_info['headimg']=Pathmanagement_sns::getHeadImg($log_info['add_account']) . $client_infos['client_headimg'];
						$log_info['client_name']=$client_infos['client_name'];
		                break;
		            }
		        }
		        unset($log_list);
		    }
		}
		
   		//日志存在则该值重新赋值，保证数据的一致性
    	if(!empty($log_info)) {
			
    	    $log_account = intval($log_info['add_account']);
    	    $datas = array(
			    'read_count'=>"%read_count+1%"
			);
			$mOjbectData->modifyPersonLogs($datas, $log_id); //日志浏览 数加1
			
            //获取日志评论信息
			$mLogplun = ClsFactory::Create('Model.mLogplun');
			
			//$new_plun_list = $mLogplun->getLogplunByLogid($log_id);
			$new_plun_arr = $mLogplun->getLogplunByLogid($log_id);
    		$new_plun_list = & $new_plun_arr[$log_id];
			
    		$icount = !empty($new_plun_list) ? count($new_plun_list) : 0;
			//追加评论的用户信息
    		if(!empty($new_plun_list)) {
    		    $uidarr = array();
    		    foreach($new_plun_list as $plun) {
    		        $uidarr[] = $plun['add_account'];
    		    }
    		    $uidarr = array_unique($uidarr);
    		    $mUser = ClsFactory::Create('Model.mUser');
    		    $userlist = $mUser->getUserBaseByUid($uidarr);
    		    foreach($new_plun_list as $key=>$plun) {
    		        $uid = $plun['add_account'];
    		        if(isset($userlist[$uid])) { 
    		            $plun['client_name'] = $userlist[$uid]['client_name'];
						$plun['add_date_sec']=Date::formatedateparams($plun['add_date']);
						$plun['plunheadimg']=Pathmanagement_sns::getHeadImg($uid) . $userlist[$uid]['client_headimg'];
    		        }
    		        $new_plun_list[$key] = $plun;
    		    }
    		    unset($userlist , $uidarr);
				$faceSearch=$faceReplace=array();
				$facelist = Constancearr::getfacelist();

				foreach($new_plun_list as $plun_id => $log_plun){
					if($facelist){
						foreach($facelist as $key => $val){
							$alt = str_replace("/", "", $facelist[$key]);
							$faceSearch[] = $facelist[$key];
							$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
						}
						$new_plun_list[$plun_id]['plun_content'] = str_replace($faceSearch, $faceReplace, $new_plun_list[$plun_id]['plun_content']);
					}	
				}
    		}
        } else {
            //如果存在则退回到相应的好友日志列表
            $backto_account = $log_account ? $log_account : $this->user['client_account'];
        	$this->showError("您访问的日志不存在或者已经删除", "/Homeuser/Index/spacehome/spaceid/$log_account");
        }
        
		$this->assign('plun_list' , $new_plun_list);
		$this->assign('log_id' , $log_id);
		$this->assign('upload_latterbg' , IMG_SERVER.'/Public/latterbg/');
		$this->getSpaceCount($log_account);

		$this->assign('log_info' , $log_info);
		$this->assign('count' , $icount);
		$this->assign('head_img' , $this->user['client_headimg_url']);
		$this->assign('friendaccount' , $this->user['client_account']);
		$this->assign('log_account' , $log_account);
		
		$this->display('space_logview');
	}

	//查出关于每个日志的评论内容
	public function pluncontent($log_id){
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		
		return $mLogplun->getLogplunCountByLogid($log_id);
	}

	//留言列表
	public function guestbook(){
		
		$spaceid_account = trim($this->objInput->getStr('spaceid'));
		$this->chkThisSpaceId($spaceid_account);
		
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=5; //每页数量

		$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
		$mUser = ClsFactory::Create('Model.mUser');	

		$guestbook_info = $mGuestbookInfo->getGuestbookInfoByToUid($spaceid_account);
		if($guestbook_info){
			$faceSearch=$faceReplace=array();
			$facelist = Constancearr::getfacelist();
	
			$guestbook_info = array_shift($guestbook_info);
			$sm_bh =array();
			$newGinfo = array();
			$sortkeys = array();
			foreach($guestbook_info as $keys=>$valInfo){
			if(empty($valInfo[upid])){
				$login_info = $mUser->getUserByUid($valInfo[add_account]);
				$valInfo['add_date']=Date::formatedateparams($valInfo['add_date']);
			    $valInfo['plunheadimg'] = Pathmanagement_sns::getHeadImg($valInfo['add_account']) . $login_info[$valInfo['add_account']]['client_headimg'];
				$valInfo['client_name'] = $login_info[$valInfo['add_account']]['client_name'];
				$valInfo['back_client_name'] = $login_info[$valInfo['to_account']]['client_name'];
				$valInfo['account'] = $spaceid_account;

				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$valInfo['guestbook_content'] = str_replace($faceSearch, $faceReplace, $valInfo['guestbook_content']);
				}


				$hfData = $mGuestbookInfo->getGuestbookHfInfoByToUid($valInfo['guestbook_id']);
				if($hfData){
					
					foreach($hfData as $hfkey=>&$hfval){
						$login_info2 = $mUser->getUserByUid($hfval[add_account]);
						$hfval['add_date']=Date::formatedateparams($hfval['add_date']);
						$hfval['plunheadimg2'] = Pathmanagement_sns::getHeadImg($hfval['add_account']) . $login_info2[$hfval['add_account']]['client_headimg'];
						$hfval['client_name'] = $login_info2[$hfval['add_account']]['client_name'];
					}

				}
				$valInfo['hfkey'] = $hfData;
				unset($hfData);
				$newGinfo[] = $valInfo;
			}
			}
		
			 foreach($newGinfo as $key=>$value) {
	            $sortkeys[$key] = $value['guestbook_id'];
	        }
			array_multisort($sortkeys , SORT_DESC , $newGinfo);
			unset($valInfo);
		}
		
		$newarr_newGinfo = array_slice($newGinfo, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeuser/Index/guestbook/spaceid/".$spaceid_account;
		
		$pageCount = ceil(count($newGinfo)/$pagesize);
			
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		

		if(count($newGinfo) > $pagesize){
			$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
		}
		$this->assign('pageno',$pageno);


		$this->assign('spaceid_account',$spaceid_account);
		$this->assign('guestbook_info',$newarr_newGinfo);
		$this->assign('spaceid_glaccount',$this->getCookieAccount());
	
		$this->display('guestbook');
	}


	//获取最新留言
	function newguestbook(){
		$spaceid=$this->objInput->postInt('spaceid');
		$log_account = $this->getCookieAccount();
	
		$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
		$result= $mGuestbookInfo->getGuestbookInfoByToAccount($spaceid);
		$guestbook_info = array_slice($result[$spaceid],0,1,true);
		$mUser = ClsFactory::Create('Model.mUser');	
		if($guestbook_info){
			foreach($guestbook_info as $keys=>$valInfo){
				$login_info = $mUser->getUserByUid($valInfo['add_account']);
				$login_info1 = $mUser->getUserByUid($valInfo['to_account']);
				$valInfo['add_date']=Date::formatedateparams($valInfo['add_date']);
				//留言者头像
    			$valInfo['plunheadimg'] = Pathmanagement_sns::getHeadImg($valInfo[add_account]) . $login_info[$valInfo[add_account]]['client_headimg'];
				//留言都姓名
				$valInfo['client_name'] = $login_info[$valInfo[add_account]]['client_name'];
				$valInfo['back_client_name'] = $login_info1[$valInfo[to_account]]['client_name'];
			
				$facelist = Constancearr::getfacelist();
				if($facelist){
					foreach($facelist as $key => $val){
						$alt = str_replace("/", "", $facelist[$key]);
						$faceSearch[] = $facelist[$key];
						$faceReplace[] = "<img src='".IMG_SERVER."/Public/images/face/".$key.".gif' width=22 height=22>";
					}
					$valInfo['guestbook_content'] = str_replace($faceSearch, $faceReplace, $valInfo['guestbook_content']);
				}
			
			}
		}	
		
		echo "<data>";
		echo "<guestbook_id>".$valInfo['guestbook_id']."<guestbook_id>";
		echo "<guestbook_type>".$valInfo['guestbook_type']."<guestbook_type>";
		echo "<class_code><class_code>";
		echo "<to_account>".$valInfo['to_account']."<to_account>";
		echo "<guestbook_content>".$valInfo['guestbook_content']."<guestbook_content>";
		echo "<upid>".$valInfo['upid']."<upid>";
		echo "<add_account>".$valInfo['add_account']."<add_account>";
		echo "<add_date>".$valInfo['add_date']."<add_date>";

		echo "<plunheadimg>".$valInfo['plunheadimg']."<plunheadimg>";
		echo "<client_name>".$valInfo['client_name']."<client_name>";
		echo "<data>";
		
		exit();
	}
	
	//删除留言信息
	function ajaxguestbookDel() { 
		$spaceid_account=trim($this->objInput->postInt('spaceId'));
		$guestbook_id=trim($this->objInput->postInt('guestbook_id'));
		$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
		$reurnState = $mGuestbookInfo->delGuestbookInfo($guestbook_id);
		if($reurnState) {
			$mFeed = ClsFactory::Create('Model.mFeed');				
			$mFeed->addPersonFeed(intval($spaceid_account),intval($guestbook_id),PERSON_FEED_LEAVE,FEED_DEL,time());
			echo "success";
		}else { 
			echo "fail";
		}
	}


	//添加留言
	public function addGuestbook(){
		if($this->getCookieAccount()==""){
			echo "nologin";exit;
		}else{
			
			$LoginUserAccount = $this->getCookieAccount();
			$spaceid_account=trim($this->objInput->postInt('spaceid_account'));

			$content=trim($_POST['msgcontent']);
			$date = date("Y-m-d H:i:s");
			$data['to_account']=$spaceid_account;
			$data['guestbook_content']= $content;
			$data['add_account']=$LoginUserAccount;
			$data['add_date']=$date;
			
			if($content!=''){
				$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
				$returnData = $mGuestbookInfo->addGuestbookInfo($data, true);
				if($returnData){
					//添加用户动态信息表
					$mFeed = ClsFactory::Create('Model.mFeed');				
					$mFeed->addPersonFeed(intval($LoginUserAccount),intval($returnData),PERSON_FEED_LEAVE,FEED_NEW,time());

					echo "success: return data.";
				}else{
					echo "error: return data empty.";
				}
			}else{
				echo "error: content.";
			}
		}

	}

	//提交回复留言
	function ajaxbackguestbook(){
		$add_account = $this->getCookieAccount(); //回复人当前用户
		$guestbookid = trim($this->objInput->postStr('guestbookid'));//回复留言主题
		$curhfuser = trim($this->objInput->postStr('curhfuser'));//回复目标用户
		$ReplyContent = trim($this->objInput->postStr('msg'));//回复内容
		$ReplyContent = urldecode($ReplyContent);
		$adddate=date("Y-m-d H:i:s",time());
		$dateflag = strtotime(date('Y-m-d H:i:s'));
		
		$data['to_account']=$curhfuser;
		$data['guestbook_content']=$ReplyContent;
		$data['upid']=$guestbookid;
		$data['add_account']=$add_account;
		$data['add_date']=$adddate;
		
		$mGuestbookInfo = ClsFactory::Create('Model.mGuestbookInfo');
		$returnData = $mGuestbookInfo->addGuestbookInfo($data);
		if($returnData){
			echo "success";
		}else{
			echo "fail";
		}

	}

	//好友
	public function friend(){
		$spaceid_account = $this->objInput->getInt('spaceid');
		$this->chkThisSpaceId($spaceid_account);

		$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');	
		$tmp_friend_info = $mAccountrelation->getAccountRelationByAddAccount($spaceid_account);
		$friend_info = $tmp_friend_info[$spaceid_account];
		if (!empty($friend_info)) {
			foreach($friend_info as $key1=>$val1){
				$friend_accounts_list[$val1['friend_account']] = $val1['friend_account'];
			}
		}
		
		$mUser = ClsFactory::Create('Model.mUser');
		$friend_info_list = $mUser->getUserByUid($friend_accounts_list);
		if(!empty($friend_info)){
    		foreach($friend_info as $key=>$val){
    			$val['client_headimg'] = Pathmanagement_sns::getHeadImg($val[friend_account]) . $friend_info_list[$val[friend_account]]['client_headimg'];
				$val['client_name'] = $friend_info_list[$val[friend_account]]['client_name'];
				$friend_info[$key] = $val;
    		}		
		}
		if(empty($friend_info)){
			$num = 0;
		}else{
			$num = count($friend_info);
		}
		$this->assign('num',$num);
		$this->assign('friend_info',$friend_info);
		$this->getSpaceCount($spaceid_account);
		
		$this->display('friend');
	}

	//资料
	public function basemessage(){
		$spaceid_account = trim($_REQUEST['spaceid']);
		$mUser = ClsFactory::Create('Model.mUser');	
		$login_info = $mUser->getUserByUid($spaceid_account);
		$texta = substr($login_info[$spaceid_account]['client_email'],0,strpos($login_info[$spaceid_account]['client_email'],"@"));
		$textb = substr($login_info[$spaceid_account]['client_email'],strpos($login_info[$spaceid_account]['client_email'],"@"));
		
		if(strlen($texta)>2){
			$login_info[$spaceid_account]['client_email'] = substr($texta,0,2)."******".$textb;
		}else{
			$login_info[$spaceid_account]['client_email'] = $texta."******".$textb;
		}
		//dump($login_info[$spaceid_account]['class_info']);
		foreach($login_info[$spaceid_account]['school_info'] as $value)
		foreach($login_info[$spaceid_account]['class_info'] as $value1)
		
		$client_info = $mUser->getClientInfoById($spaceid_account);
		$login_info[$spaceid_account]['client_birthday'] = $client_info[$spaceid_account]['client_birthday'];
		$this->assign('class_info',$value1);
		$this->assign('person_info',$login_info[$spaceid_account]);
		$this->assign('school_info',$value);

		$this->getSpaceCount($spaceid_account);
		
		$this->display('basemessage');
	}
	


	//相册
	public function album(){
		$spaceid_account = trim($_REQUEST['spaceid']);
		$this->chkThisSpaceId($spaceid_account);

		$pagecount = 30;
		$xiangce_result = $this->getMAlbumInfoModel($spaceid_account,$pagecount);
		

		if($xiangce_result){
			$newAlbumarr = array();
			foreach($xiangce_result as $key => $val){
    		    $val['album_name'] = htmlspecialchars_decode($val['album_name']);
    			$val['album_explain'] = htmlspecialchars_decode($val['album_name']);
    			$val['xcimg'] = Pathmanagement_sns::getAlbum($spaceid_account) . $val['album_img'];
				$newAlbumarr[] = $val;
			}
		}
	
		$this->getSpaceCount($spaceid_account);
		$this->assign('xiangce_list',$newAlbumarr);
		$this->assign('account',$spaceid_account);
		
		$this->display('album');
	}


	//公用方法，个人相册列表统计 2012-3-21 by lyt:
	public function getMAlbumInfoModel($account,$pagecount){
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$xiangce_result = $mAlbuminfo->getAlbumInfoByaccount($account,0,100);
		$this->assign('pagecount',$pagecount);

		return $xiangce_result;
	}

	public function albumphotodetail(){
		$account = $this->objInput->getInt('spaceid');
		$this->chkThisSpaceId($account);

		$xcid = $this->objInput->getInt('xcid');
		$class_code = $this->objInput->getInt('class_code');
		//获取当前访问的相册的用户ID
		if(empty($account)) {
		    $account = $this->user['client_account'];
		}

		$pagecount = 20;
		
		//获取当前访问的相册的用户ID
		if(empty($account)) {
		    $account = $this->user['client_account'];
		}
		
		//我的相册列表
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$xiangce_result = $mAlbuminfo->getAlbumListByaccount($account);
		
		//指定相册ID下照片信息\并进行分页处理
		//$photoinfo_result = $this->getPhotoInfoByXcId($account,$xcid,$pagecount);
		$mPhotosInfo	= ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo_result = $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_photoinfo_result = &$photoinfo_result[$xcid];
		unset($photoinfo_result);

		$mPhotoplun = ClsFactory::Create('Model.mPhotoplun');
		if($new_photoinfo_result){
			foreach($new_photoinfo_result as $key=>$photo_info){
				$photo_info['photo_urlall']=Pathmanagement_sns::getAlbum($account) . $photo_info['photo_url'];
				$photo_info['photo_min_urlall']=Pathmanagement_sns::getAlbum($account) . $photo_info['photo_min_url'];
				$photo_info['photo_name']= str_replace($account."_","",$photo_info['photo_name']);
				$photo_info['photo_min_url']=trim($photo_info['photo_min_url']);

				$plun_nums = $mPhotoplun->getPhotoPlunCountByPhotoId($photo_info['photo_id']);
				$photo_info['plunnums'] = "(" . max(intval($plun_nums), 0) . ")";
				
				$new_photoinfo_result[$key] = $photo_info; 
			}
		}
		
		$xcinfo = $mAlbuminfo->getAlbumListByAlbumid($xcid);
		$xcinfo = array_shift($xcinfo);
		if($xcinfo) {
		    $xcinfo['album_explain'] = htmlspecialchars_decode($xcinfo['album_explain']);
			$xcinfo['album_imgname']= trim($xcinfo['album_img']);
			$xcinfo['album_imgfm']= Pathmanagement_sns::getAlbum($account) . $xcinfo['album_img'];
			$xcinfo['add_date']=date("Y-m-d",$xcinfo['add_date']);
			$xcinfo['upd_date']=date("Y-m-d",$xcinfo['upd_date']);
		}
		if($photocount == 0 && $account != $this->getCookieAccount()){
		    $photocount=-1;
		}
		
		$this->assign('xiangce_list',$xiangce_result);
		$this->assign('account',$account);
		$this->assign('xcinfo',$xcinfo);
		$this->assign('class_code',$class_code);
		$this->assign('photoinfo',$new_photoinfo_result);
		$this->assign('photocount',count($new_photoinfo_result));
		$this->assign('friendaccount',$this->getCookieAccount());
		$this->assign('xcid',$xcid);
		$this->getSpaceCount($account);

		$this->display('space_albumphoto');	

	}


	//公用方法，指定ID下照片信息
	public function getPhotoInfoByXcId($account,$xcid,$pagecount){
		$mPhotoinfo = ClsFactory::Create('Model.mPhotosInfo');
		$photoCountData = $mPhotoinfo->getPhotoInfoCountByXcid($account,$xcid);
		$photoCountData = array_shift($photoCountData);
		$photoCountDataNums = $photoCountData[photoCount];
	
		$this->assign('pageinfo_count',$photoCountDataNums);
		$this->assign('pageinfo_pagesize',$pagecount);		
		$Page=new Page($photoCountDataNums,$pagecount);
		$show=$Page->show("/Homeuser/Index/albumphotodetail/spaceid/".$account."/xcid/".$xcid."?");//显示分页
		$this->assign('pageinfo',$show);
		return $mPhotoinfo->getPhotoInfoByXcId($account,$photoCountDataNums,$xcid,$pagecount);
		
	}

	function albumphotoview(){
		$account = trim($this->objInput->getStr('spaceid'));
		$this->chkThisSpaceId($account);

		$friendaccount = $this->getCookieAccount();
		$xcid = trim($this->objInput->getStr('xcid'));
		$photo_id = trim($this->objInput->getStr('photo_id'));
		$class_code= trim($this->objInput->getStr('class_code'));
		//当前照片ID信息
		$mPhotosInfo = ClsFactory::Create('Model.mPhotosInfo');
		$arrphotoData = $mPhotosInfo->getPhotoInfoById($photo_id);
		
		if($arrphotoData){
		    $new_arrphotoData = &$arrphotoData[$photo_id];
		    unset($arrphotoData);
			$new_arrphotoData['photo_urlbig']=$new_arrphotoData['photo_url'];
			$new_arrphotoData['photo_urlfm']=$new_arrphotoData['photo_min_url'];
			$new_arrphotoData['photo_url']=Pathmanagement_sns::getAlbum($account) . $new_arrphotoData['photo_url'];
			$this->assign('arrphotoData',$new_arrphotoData);
		}
		
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
		$xcinfo=$mAlbuminfo->getAlbumListByAlbumid($xcid);
		
		$xiangce_result=$mAlbuminfo->getAlbumListByaccount($account);
		foreach($xiangce_result as $key=>$val) {
			$xiangce_result[$key]['xcimg']=Pathmanagement_sns::getAlbum($account) . $xiangce_result[$key]['album_img'];
		}	
		
		//相册内所有照片
		
		$mPhotosInfo	= ClsFactory::Create('Model.mPhotosInfo');
		$photoinfo	= $mPhotosInfo->getPhotoInfoByAlbumId($xcid);
		$new_photoinfo = &$photoinfo[$xcid];
		unset($photoinfo);
		
		$click_photo_num = 0;
		$photoCount = count($new_photoinfo);
		
		foreach($new_photoinfo as $photo_id => $photo_info){
			if($photo_info['photo_id'] == $photo_id){
				$click_photo_num = $i;
			}
			$photo_info['photo_urlall']=Pathmanagement_sns::getAlbum($account) . $photo_info['photo_url'];
			$photo_info['photo_min_urlall']=Pathmanagement_sns::getAlbum($account) . $photo_info['photo_min_url'];
			if(empty($photo_info['photo_id'])){
                unset($new_photoinfo[$photo_id]);
                continue;			    
			}
			$new_photoinfo[$photo_id] = $photo_info;
		}
		if(!count($new_photoinfo)){
		    $this->photoindex();
		    die;
		}

		$mUser = ClsFactory::Create('Model.mUser');
		$client_infos = $mUser->getUserBaseByUid($friendaccount);
		$client_infos = &$client_infos[$friendaccount];
		$this->assign('friendaccount',$friendaccount);
		$this->assign('account',$account);
		$this->assign('photonameurl',Pathmanagement_sns::getHeadImg($friendaccount) . $client_infos['client_headimg']);
		$this->assign('client_info',$client_infos);
		$this->assign('photoinfo',$new_photoinfo);
		$this->assign('xcid',$xcid);
		$this->assign('photo_id',$photo_id);
		$this->assign('class_code',$class_code);
		$this->assign('xiangce_list',$xiangce_result);
	
		
		$this->getSpaceCount($account);


		$this->assign('xcinfo',$xcinfo);

		$this->assign('click_photo_num',$click_photo_num);
		$this->assign('photocount',$photoCount);
		
		$this->display('space_albumphotoview');
	}

	
	//设置空间名称	
	function setUpdateSpaceName() { 
		$account = $this->getCookieAccount();
		$spancename = trim(urldecode($this->objInput->postStr('spancename')));
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		$personConfigArr = array(
			'client_account' =>$account,
			'space_name' =>$spancename,
		);	
		$PersonConfig_list = $mPersonconfig->getPersonConfigByaccount($account);
		if(!empty($PersonConfig_list)) {
		    $returndata = $mPersonconfig->modifyPersonConfig($personConfigArr,$account);
		} else {
		    $returndata = $mPersonconfig->addPersonConfig($personConfigArr);
		}
		
		
		if($returndata) { 
			echo "success";exit;
		} else { 
			echo "fail";exit;
		}
	}



	//设置空间皮肤背景
	function styleChange(){
		if(!$this->_isLoginCheck){//todolist
			echo "nologin";
			exit();
		}
		$styleid = trim($this->objInput->postStr('styleid'));
		$account = $this->getCookieAccount();
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		$personConfigArr = array(
			'client_account' =>$account,
			'space_skin_id' =>$styleid,
		);	
		$PersonConfig_list = $mPersonconfig->getPersonConfigByaccount($account);
		if(!empty($PersonConfig_list)) {
		    $returndata = $mPersonconfig->modifyPersonConfig($personConfigArr,$account);
		} else {
		    $returndata = $mPersonconfig->addPersonConfig($personConfigArr);
		}
		
		if($returndata) { 
			echo "success";exit;
		} else { 
			echo "fail";exit;
		}	

	}

	//空间验证；
	function chkThisSpaceId($spaceId){
		$LoginUserId = $this->getCookieAccount();
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');
		$spaceConfig = $mPersonconfig->getPersonConfigByaccount($spaceId);
		if($spaceConfig){
			$spaceConfig = array_shift($spaceConfig);
			$space_access = $spaceConfig['space_access'];
			if($space_access!=''){
				
				switch($space_access){
					case 0 : 
						break;
					case 1 :
					//验证当前用户是否为好友
					if($LoginUserId!=$spaceId){
						$mAccountrelation = ClsFactory::Create('Model.mAccountrelation');
						$tmp_myfrienddata = $mAccountrelation->getAccountRelationByAddAccount($spaceId);
						$myfrienddata = $tmp_myfrienddata[$spaceId];
						if($myfrienddata){
							foreach($myfrienddata as $key => $val){
								if ($val['friend_account'] == $LoginUserId){
									$isaccess= true;
									break;
								}
							}
						}
						if($isaccess){
						}else{
							$this->showError("您不是好友，无权访问!", "/Homeuser/Index/spacehome/spaceid/$LoginUserId");
						}
					}
					
						break;
					case 2 :
						if($LoginUserId!=$spaceId){
							$this->showError("无权限操作!", "/Homeuser/Index/spacehome/spaceid/$LoginUserId");
						}
						break;
				}
			}
		}

		return true;

	}

	
	//基本信息
	function getSpaceInfo($spaceid_account) {
		$spaceid_account = trim($_REQUEST['spaceid']);
		$mUser = ClsFactory::Create('Model.mUser');	
		$inserinfo=$loigin_info = $mUser->getUserByUid($spaceid_account);
		$client_type = ($loigin_info[$spaceid_account]['client_type']);
			  
		
		//加载默认空间皮肤
		
		if($client_type==1){
			$defautl_bg = "tbg1";
		}elseif($client_type==0){
			$defautl_bg = "bg0";
		}elseif($client_type==2){
			$defautl_bg = "fbg1";
		}else{
			exit();
		}
		
		$space_name = $loigin_info[$spaceid_account]['client_name']."的个人空间";
		$mPersonconfig = ClsFactory::Create('Model.mPersonconfig');	  
		
		$InitSpaceInfo = $mPersonconfig->getPersonConfigByaccount($spaceid_account);
		if($InitSpaceInfo) { 
			$InitSpaceInfo = array_shift($InitSpaceInfo);
			$user_space_skin_id = $InitSpaceInfo[space_skin_id];
			if(!empty($user_space_skin_id)) { 
				$mPersonspaceskin = ClsFactory::Create('Model.mPersonspaceskin');
				$spaceSkinInfo = $mPersonspaceskin->getPersonSpaceSkinById($user_space_skin_id);
				if($spaceSkinInfo){
					$spaceSkinInfo = array_shift($spaceSkinInfo);
					$defautl_bg = $spaceSkinInfo[skin_value];
				}
			}
			if(!empty($InitSpaceInfo[space_name])) { 
				$space_name  = $InitSpaceInfo[space_name];
			}
		}
		$loginuser = $this->user;
		$loginid = $this->user['client_account'];
		$mAccountrelation = ClsFactory::create('Model.mAccountrelation');
		$RSmRelation = $mAccountrelation->getaccountrelationbyuid($loginid, null, null, array('friend_account'=>$spaceid_account));
		$flag = 'false';
		if($loginuser['client_type'] == CLIENT_TYPE_STUDENT && $inserinfo[$spaceid_account]['client_type'] == CLIENT_TYPE_FAMILY){
			$flag = 'false';
		}elseif($loginuser['client_type'] == CLIENT_TYPE_FAMILY && $inserinfo[$spaceid_account]['client_type'] == CLIENT_TYPE_STUDENT){
			$flag = 'false';
		}elseif(!empty($RSmRelation)){
			$flag = 'false';
		}else{
			$flag = 'true';
		}
		
		$this->assign('flag',$flag);
		
		
		$this->assign('space_client_skin',$defautl_bg);
		$this->assign('space_client_space_name',$space_name);

		$this->assign('space_client_name',$loigin_info[$spaceid_account]['client_name']);
		$this->assign('space_constellation_nam',$loigin_info[$spaceid_account]['client_constellation_name']);
		$this->assign('space_client_birthday',$loigin_info[$spaceid_account]['client_birthday']);
		$this->assign('space_blood_type_name',$loigin_info[$spaceid_account]['client_blood_type_name']);

		$class_code = key($loigin_info[$spaceid_account]['client_class']);
		$school_id = $loigin_info[$spaceid_account]['class_info'][key($loigin_info[$spaceid_account]['client_class'])]['school_id'];
		$school_name = $loigin_info[$spaceid_account]['school_info'][$school_id]['school_name'];
		
		$this->assign('space_url',$_SERVER['HTTP_HOST']."/Homeuser/Index/spacehome/spaceid/".$spaceid_account);
		$this->assign('login_space_url',$_SERVER['HTTP_HOST']."/Homeuser/Index/spacehome/spaceid/".$this->getCookieAccount());

		$this->assign('space_school_name',$school_name);
		$this->assign('tpl_gradeclass_Name',$loigin_info[$spaceid_account]['class_info'][$class_code]['class_name']);
		$this->assign('tpl_grade_id_name',$loigin_info[$spaceid_account]['class_info'][$class_code]['grade_id_name']);
		$this->assign('tpl_headteacher_account',$loigin_info[$spaceid_account]['class_info'][$class_code]['headteacher_account']);
		$this->assign('space_current_user',$spaceid_account);
		$this->assign('space_Login_user',$loginid);
		$this->assign('spaceid',$spaceid_account);

		$this->assign('uploada_talk_img',Pathmanagement_sns::getTalkIco());
		$this->assign('user_head_img',Pathmanagement_sns::getHeadImg($spaceid_account) . $loigin_info[$spaceid_account]['client_headimg']);
		
		$this->assign('space_current_type',$client_type);
		$this->assign('chanelid',"chanel2");	
	
	}






}
