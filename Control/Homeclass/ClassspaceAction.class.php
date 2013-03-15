<?php
class ClassspaceAction extends SnsController{

    public function _initialize(){
        parent::_initialize();
        import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		
		if ($this->user['client_type'] == CLIENT_TYPE_TEACHER) {
    		$tab_class_list = true;
    		//获取当前用户的班级列表信息
    		$myclasslist = $this->user['class_info'];
    		$this->assign('myclasslist' , $myclasslist); 
    		$this->assign('tab_class_list' , $tab_class_list); 
	    }
	}

	/*加载班级空间内容*/
	function index(){
		//由当前用户登录ID获取班级信息
		$class_code = $this->objInput->getInt('class_code');
		if(empty($class_code)){
			$class_code = $this->checkclasscode($class_code);
			
			if(empty($class_code)){
	            $this->showError("您不属于任何班级不可操作，将返回!", "/Homeuser/Index/spacehome/spaceid/".$this->getCookieAccount());
	            exit;
       		}
		}
		
		//leftinfo
		$this->classleftinfo($class_code);
		//班级动态
		$loginid = $this->user['client_account'];
	
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
 		
		$lastUpdTime = time()-$result['last30Times'];
		$arrInfoData = $this->loadClassFeedData($class_code, $lastUpdTime, 0, 100);
		$newarr_talkInfo = array_slice($arrInfoData, 0,3);
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		
		//班级日志
		$log_account=$loginid;
		$pagecount = 8;
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');
		
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$mUser = ClsFactory::Create('Model.mUser');

		$classLogData = $mClasslog->getLogInfoByClassCode($class_code);
		$classLogData = $classLogData[$class_code];
		if($classLogData){
			$sortkeys = array();
			$newClassLogData = array();
			foreach ($classLogData as $classkey=>$classLogInfo) {
				$LogInfo = $mOjbectData->getPersonLogsById($classLogInfo['log_id']);
				if($LogInfo){
					$LogInfo = array_shift($LogInfo);
					$log_plun_count=$this->pluncontent($LogInfo['log_id']);
					$classLogInfo['log_name']=$LogInfo['log_name'];
					$classLogInfo['add_date']=$LogInfo['add_date'];
					$classLogInfo['add_account']=$LogInfo['add_account'];
					$classLogInfo['upd_date']=$LogInfo['upd_date'];
					$classLogInfo['read_count']=$LogInfo['read_count'];
					$classLogInfo['plun_count']=$log_plun_count;
					$logcontent = str_replace('&nbsp;','',WmwString::unhtmlspecialchars($LogInfo['log_content']));
					$logcontent = str_replace('<P></P>','',$logcontent);
					$classLogInfo['log_contentall']= strip_tags(cutstr(WmwString::unhtmlspecialchars($logcontent),100, true));
					$mcInfo = $mUser->getUserBaseByUid($LogInfo['add_account']);
					$mcInfo = array_shift($mcInfo);
					$classLogInfo['headimg']=Pathmanagement_sns::getHeadImg($LogInfo['add_account']) . $mcInfo['client_headimg'];
					$classLogInfo['client_name']=$mcInfo['client_name'];
					$newClassLogData[] = $classLogInfo;
				}
			}
			 foreach($newClassLogData as $key=>$value) {
	            $sortkeys[$key] = $classLogInfo['add_time'];
	        }

		}


		//排序日记
		array_multisort($sortkeys , SORT_DESC , $newClassLogData);
		$newClassLogDatas = array_slice($newClassLogData, 0,3);
		$this->assign('mylog_list',$newClassLogDatas);
		//班级相册
		//查找相册列表
	
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');	 
		$mClassAlbum = ClsFactory::Create('Model.mClassalbum');	 
		$classAlbumData = $mClassAlbum->getAlbumInfoByClassCode($class_code);
		if(!empty($classAlbumData)){
			$sortkeys = array();
			$newClassalbumData = array();
			foreach ($classAlbumData[$class_code] as $classkey=>$classalbumval) {
				$album_ids[$classalbumval['album_id']] = $classalbumval['album_id'];
			}
			
			$albumInfo = $mAlbuminfo->getAlbumListByAlbumid($album_ids);
			if(!empty($albumInfo)) {
				foreach($albumInfo as $key1=>$val1){
					$log_plun_count=$this->pluncontent($val1['album_id']);
					$classalbumInfo['album_id']=$val1['album_id'];
					$classalbumInfo['album_name']=$val1['album_name'];
					$classalbumInfo['album_img'] = Pathmanagement_sns::getAlbum($val1['add_account']) . $val1['album_img'];
					$classalbumInfo['plun_count']=$log_plun_count;
					$classalbumInfo['add_account'] = $val1['add_account'];
					$classalbumInfo['add_date'] = $val1['add_date'];
					$add_accounts[$val1['add_account']] = $val1['add_account'];
					$newClassalbumData[] = $classalbumInfo;
				}
				$mcInfos = $mUser->getUserBaseByUid($add_accounts);
				
				foreach($newClassalbumData as $key=>& $value) {
				 	$value['client_name']=$mcInfos[$value['add_account']]['client_name'];
		            $sortkeys[$key] = $value['add_time'];
		        }
			}
		}

		array_multisort($sortkeys , SORT_DESC , $newClassalbumData);
		$newClassalbumDatas = array_slice($newClassalbumData, 0,3);
		$this->assign('xiangce_list',$newClassalbumDatas);
		 $this->assign('class_code' , $class_code);

		$this->display('classspace');
	

	}
	public function morespacefeedlist(){
		$nextLlimit = $this->objInput->getInt('nextLlimit');
	    $class_code = $this->objInput->getInt('class_code');
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$arrInfoData = $this->loadClassFeedData($class_code, $lastUpdTime, 0, 500);
		$newarr_talkInfo = array_slice($arrInfoData, $nextLlimit, WMW_XXS_LIMIT);
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
	public function loadClassFeedData($class_code, $lastUpdTime, $offset, $length){
		
		$mClassInfo = ClsFactory::Create('Model.mClassInfo');
		$class_info = $mClassInfo->getClassInfoById($class_code);
		$mUser = ClsFactory::Create('Model.mUser');	    //dump($this->user);
		$loginid = $this->user['client_account'];
	    //有班级的话，跳转到班级首页，否则跳转到个人首页
        if(empty($class_code)){
            return false;
        }
	    //统计班级成员,client_type in(0,1)
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>array(CLIENT_TYPE_STUDENT , CLIENT_TYPE_TEACHER)));
	    $clientclass_list = $clientclassarr[$class_code];
	    unset($clientclassarr);
	    
		$class_account = array();
		$class_account[$loginid] = $loginid;
		foreach($clientclass_list as $key=>&$val){
			$class_account[$val['client_account']] = intval($val['client_account']);
		} 
		
		$mFeed = ClsFactory::Create('Model.mFeed');
		$arrlist = $mFeed->getClassFeedList($class_code, $lastUpdTime, $offset, $length);
		$arrlist_type = array();
		foreach($arrlist['feed'] as $key1=>&$val1){
			$arrlist_type[$val1['res_type']][] = $val1['res_id'];
		}
		$mUser = ClsFactory::Create('Model.mUser');
		$client_infos = $mUser->getUserBaseByUid($class_account);
		foreach($arrlist_type as $key2=>&$val2){
			if($key2==CLASS_FEED_NOTICE){
				//班级通告
				//获取班级下面的公告信息
			    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
			    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJTG)));
			    //数据较大，引用传值，避免内存瞬间过大
			    $feedlist = & $newsinfoarr[$class_code];
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>&$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['news_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['feed_type'] = '通告';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新通告';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了通告';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								//$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/AnnouncementView/newsid/{$talkval['news_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['news_title'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name']?$client_infos[$talkval['add_account']]['client_name']:"无名";
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_WORK){
				//班级作业
				//获取班级下面的作业信息
			    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
			    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJZY)));
			    //数据较大，引用传值，避免内存瞬间过大
			    $feedlist = & $newsinfoarr[$class_code];
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>&$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['news_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['feed_type'] = '作业';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新作业';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了作业';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								
								//$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/hoemworkview/workid/{$talkval['news_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['news_title'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name']?$client_infos[$talkval['add_account']]['client_name']:"无名";
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_MARK){
				//班级成绩
				$mExamInfo = ClsFactory::Create('Model.mExamInfo');
				$feedlist = $mExamInfo->getExamInfoBaseById($val2);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['exam_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '成绩';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新成绩';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了成绩';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								//$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/examdetailinfo/exam_id/{$talkval['exam_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['exam_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
						
					}
					unset($feedlist,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_CURRICULUM){
				//班级课程表
				$mCurriculum = ClsFactory::Create('Model.mCurriculum');
				$feedlist = $mCurriculum->getCurriculumInfoById($val2);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['curriculum_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '课程表';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了课程表';
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了课程表';
								}
								$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s',intval($talkval['upd_time']));
								$arrlist['feed'][$arrlist_key]['feed_name'] = $class_info[$class_code]['class_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['upd_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['upd_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['upd_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_LOG){
				//班级日志
				$mPersonlogs = ClsFactory::Create('Model.mPersonlogs');
				$feedlist = $mPersonlogs->getPersonLogsById($val2);
				
				$mLogplun = ClsFactory::Create('Model.mLogplun');
				//$commentlist = $mLogplun->getLogplunByLogids($val2);
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
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了日志';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeuser/Index/spacelogview/spaceid/{$talkval['add_account']}/log_id/{$talkval['log_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['log_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$commentlist,$CommentNums,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_ALBUM){
				//班级相册
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
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了相册';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeuser/Index/albumphotodetail/spaceid/{$talkval['add_account']}/xcid/{$talkval['album_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['album_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
						
					}
					unset($feedlist,$CommentNums,$arrlist_type[$key2]);
				}
			}elseif($key2==CLASS_FEED_TALK){
				//班级说说
				$mTalkcontentinfo = ClsFactory::Create('Model.mClasstalk');
				$feedlist = $mTalkcontentinfo->getTalkcontentinfoById($val2);
				//评论
				$mClasstalkcomment = ClsFactory::Create('Model.mClasstalkcomment');
				$tmp_commentlist = $mClasstalkcomment->getCommentListByTalkId($val2);
				$commentlist = $tmp_commentlist[$val2];
				unset($tmp_commentlist);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['talk_id'] && $arrlist_val['res_type']==$key2){
								$CommentNums = count($commentlist[$arrlist_val['res_id']]);
								$arrlist['feed'][$arrlist_key]['CommentNums'] = $CommentNums?$CommentNums:0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '说说';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新说说';
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了新说说';
								}
								
								$talkval['talk_content'] = str_replace("http://images.wm616.cn",IMG_SERVER,$talkval['talk_content']);
								if(!strpos($talkval['talk_content'],IMG_SERVER)){
                                    $talkval['talk_content'] = str_replace("/Public/images/face",IMG_SERVER."/Public/images/face",$talkval['talk_content']);
                                }
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['talk_content'];
								$arrlist['feed'][$arrlist_key]['feed_name'] = str_replace("[IMG]", "<br><Img class='XXXImgSize' src='".Pathmanagement_sns::getTalkIco(), $arrlist['feed'][$arrlist_key]['feed_name']);
								$arrlist['feed'][$arrlist_key]['feed_name'] = str_replace("[/IMG]", "'>", $arrlist['feed'][$arrlist_key]['feed_name']);
				
								$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s', $talkval['add_date']);
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$commentlist,$CommentNums,$arrlist_type[$key2]);
				}
			}
		}
		unset($client_infos,$feedlist,$arrlist_type);
		return $arrlist['feed'];
	
	}
	
	function classStudents(){
		$class_code = $this->objInput->getInt('class_code');
	    $teacherlist = $studentlist = $familylist = array();
	    $teacherlist = $studentlist = $familylist = array();
	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code);
	    $clientclasslist = $clientclassarr[$class_code];
	    unset($clientclassarr);
	    //获取老师和家长的相关信息
	    $schooluids = array();
	    if(!empty($clientclasslist)) {
	        foreach($clientclasslist as $clientclass) {
	            $clienttype = intval($clientclass['client_type']);
	            $uid = $clientclass['client_account'];
	            if($clienttype == CLIENT_TYPE_STUDENT) {
	                $studentlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_TEACHER) {
	                $teacherlist[$uid]['client_class'] = $clientclass;
	                $schooluids[] = $uid;
	            } elseif($clienttype == CLIENT_TYPE_FAMILY) {
	                $familylist[$uid] = $uid;
	            }
	        }
	        unset($clientclasslist);
	    }

	    //获取老师和学生的基本信息
	    if(!empty($schooluids)) {
	        $schooluids = array_unique($schooluids);
			$mUser = ClsFactory::Create('Model.mUser');
			$userlist = $mUser->getUserBaseByUid($schooluids);
	        if(!empty($userlist)) {
	            foreach($userlist as $uid=>$user) {
                    if(!empty($studentlist[$uid])) {
                        $studentlist[$uid] = array_merge($user , $studentlist[$uid]);
                    }elseif(!empty($teacherlist[$uid])) {
                        $teacherlist[$uid] = array_merge($user , $teacherlist[$uid]);
						//班主任信息
						if(in_array($teacherlist[$uid]['client_class']['teacher_class_role'],array(1,3))){
							$this->assign('teacher_class_role_name' , $teacherlist[$uid]['client_name']);
							$this->assign('teacher_class_role_img',Pathmanagement_sns::getHeadImg($teacherlist[$uid]['client_account']) . $teacherlist[$uid]['client_headimg']);
						}
                    }
	            }
	        }
			
	        unset($userlist , $schooluids);
	    }
		
	    $studentusers = array_keys($studentlist);
	    //获取学生的家长信息
	    if(!empty($studentusers)) {
	    	$stu_count = count($studentusers);
	    	if($stu_count){
	    		$stu_count=0;
	    	}
			$this->assign('studentcountnums' ,$stu_count);
			
	        $mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
	        $familyrelationlist = $mFamilyRelation->getFamilyRelationByUid($studentusers);
	        if(!empty($familyrelationlist)) {
	            foreach($familyrelationlist as $stu_uid=>$relationlist) {
	                $defaultfamilyaccount = 0;
	                if(isset($studentlist[$stu_uid]) && !empty($relationlist)) {
	                    foreach($relationlist as $relation) {
	                        $familyaccount = $relation['family_account'];
	                        //检测家长的账号是否存在
	                        if(!empty($familyaccount) && isset($familylist[$familyaccount])) {
                                $defaultfamilyaccount = $familyaccount;
	                            break;
	                        }
	                    }
	                }
					
	                $studentlist[$stu_uid]['family_account'] = $defaultfamilyaccount;
	            }
	        }
	    	$fam_count = count($familylist);
	    	if($fam_count){
	    		$fam_count=0;
	    	}
			$this->assign('familycountnums' , $fam_count);
	        unset($familylist);
	    }else{
	    	$this->assign('studentcountnums' ,0);
	    	$this->assign('familycountnums' , 0);
	    }
	    //获取教师对应的科目信息,$anlicheng教师科目一对多的修改
	    if(!empty($teacherlist)) {
	        $teacheruids = array_keys($teacherlist);
            //todochecked
	        $subjectinfolist = $this->getSubjectInfoByTeacherUid($teacheruids, $class_code, "、");
	        if(!empty($subjectinfolist)) {
    	        foreach($teacherlist as $uid=>$teacher) {
    	            $teacher['subject_info'] = isset($subjectinfolist[$uid]) ? $subjectinfolist[$uid] : false;
    	            $teacherlist[$uid] = $teacher;
    	        }
	        }
	    }
	    
	    if(empty($studentlist)){
	    	$studentlist = '';
	    }
	    $this->classleftinfo($class_code);
	    
	    $this->assign('studentlist' , $studentlist);
	    $this->assign('teacherlist' , $teacherlist);
	    $this->assign('leadertype' , Constancearr::classleader());
		$this->assign('class_code' , $class_code);

		$this->display('bjclassStudents');
	
	}


	/**
	 * 获取教师的科目信息并合并
	 * @param $uids
	 * @param $split
	 */
	private function getSubjectInfoByTeacherUid($uids, $class_code, $split = " ") {
	      if(empty($uids)) {
	          return false;
	      }
	      $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	      $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUid($uids, $class_code);
	      //合并教师科目信息
	      if(!empty($subjectinfolist)) {
	            $tmp_subjectinfolist = array();
                foreach($subjectinfolist as $uid=>$list) {
                    $tmp_arr = array();
                    $subject_name = $comma = "";
                    $flag = false;
                    foreach($list as $subject_id=>$subject) {
                        $subject_name .= $comma . $subject['subject_name'];
                        $comma = !empty($split) ? $split : " ";
                        if(!$flag) {
                            $tmp_arr = $subject;
                            $flag = true;
                        }
                    }
                    $subject_name && $tmp_arr['subject_name'] = $subject_name;
                    $tmp_subjectinfolist[$uid] = $tmp_arr;
            }
            $subjectinfolist = & $tmp_subjectinfolist;
	     }
	     
	     return !empty($subjectinfolist) ? $subjectinfolist : false;
	}



	//班级相册 获取分享到班级的相册
	function classAlbumbj(){

		$account = $this->getCookieAccount() ;
		$class_code = $this->objInput->getInt('class_code');
		//$class_code = $this->checkclasscode($class_code);
		$mUser = ClsFactory::Create('Model.mUser');
		//查询数据的总条数
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=20; //每页数量
		//查找相册列表
	
		$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');	 
		$mClassAlbum = ClsFactory::Create('Model.mClassalbum');	 
		$classAlbumData = $mClassAlbum->getAlbumInfoByClassCode($class_code);
		
		if(!empty($classAlbumData)){
			$sortkeys = array();
			$newClassalbumData = array();
			foreach ($classAlbumData[$class_code] as $classkey=>$classalbumval) {
				$album_ids[$classalbumval['album_id']] = $classalbumval['album_id'];
			}
			
			$albumInfo = $mAlbuminfo->getAlbumListByAlbumid($album_ids);
			if(!empty($albumInfo)) {
				foreach($albumInfo as $key1=>$val1){
					$log_plun_count=$this->pluncontent($val1['album_id']);
					$classalbumInfo['album_id']=$val1['album_id'];
					$classalbumInfo['album_name']=$val1['album_name'];
					$classalbumInfo['album_img'] = Pathmanagement_sns::getAlbum($val1['add_account']) . $val1['album_img'];
					$classalbumInfo['plun_count']=$log_plun_count;
					$classalbumInfo['add_account'] = $val1['add_account'];
					$classalbumInfo['add_date'] = $val1['add_date'];
					$add_accounts[$val1['add_account']] = $val1['add_account'];
					$newClassalbumData[] = $classalbumInfo;
				}
				unset($albumInfo);
				$mcInfos = $mUser->getUserBaseByUid($add_accounts);
				
				foreach($newClassalbumData as $key=>& $value) {
				 	$value['client_name']=$mcInfos[$value['add_account']]['client_name'];
		            $sortkeys[$key] = $value['add_date'];
		        }
			}
		}

		array_multisort($sortkeys , SORT_DESC , $newClassalbumData);
		
		$newarr_newLog = array_slice($newClassalbumData, ($page-1)*$pagesize, $pagesize);
		
		$webUrl = "/Homeclass/Classspace/classAlbumbj/class_code/".$class_code;
		$pageCount = ceil(count($newClassalbumData)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newClassalbumData) > $pagesize){
			$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
		}
		unset($newClassalbumData);
		$this->classleftinfo($class_code);
		$this->assign('xiangce_list',$newarr_newLog);
		$this->assign('class_code',$class_code);
		$this->assign('account',$account);
		$this->assign('log_account',$account);
		$this->assign('friendaccount',$this->getCookieAccount());
		$this->assign('ALBUM_SYS_CREATE' , ALBUM_SYS_CREATE);
		$this->assign('class_code',$class_code);
		
		$this->display('classAlbumbj');
	
	}

	//班级信息
	public function classleftinfo($class_code){
		
		 //获取当前班级信息
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    $classinfoarr = $mClassInfo->getClassInfoById($class_code);
	    $classinfo = $classinfoarr[$class_code];
	    $this->assign('tpl_grade_id_name',$classinfo['grade_id_name']);
	    $this->assign('tpl_gradeclass_Name',$classinfo['class_name']);
		//统计班级成员,client_type in(0,1)
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>array(CLIENT_TYPE_STUDENT , CLIENT_TYPE_FAMILY)));
	    $clientclass_list = $clientclassarr[$class_code];
	    $classMemberNums=0;
	    $classFamilyNums=0;
	    foreach($clientclass_list as $key=>$val){
	    	if($val['client_type'] == CLIENT_TYPE_STUDENT){
	    		$classMemberNums++;
	    	}elseif($val['client_type'] == CLIENT_TYPE_FAMILY){
	    		$classFamilyNums++;
	    	}
	    }
	    $classMemberNums = $classMemberNums ? $classMemberNums : 0;
	    $classFamilyNums = $classFamilyNums ? $classFamilyNums : 0;
	    $this->assign('classMemberNums',$classMemberNums);
	    $this->assign('classFamilyNums',$classFamilyNums);
	    
	    $mUser = ClsFactory::Create('Model.mUser');	    //dump($this->user);
		$headteacher_uid = $classinfo['headteacher_account'];
		
	    if(!empty($headteacher_uid)) {
	        $userlist = $mUser->getUserBaseByUid($headteacher_uid);
	        $classdata['headteacher'] = $userlist[$headteacher_uid];
	        unset($userlist);
	    } else {
	        $classdata['headteacher']['client_name'] = "暂无";
	    }

		 $this->assign('class_headteacher' , $classdata['headteacher']['client_name']);
	    unset($clientclassarr,$clientclass_list,$classinfoarr,$classinfo);
	}

	//班级动态
	function classsDynamic(){
		$class_code = $this->objInput->getInt('class_code');
		$loginid = $this->user['client_account'];

	    //有班级的话，跳转到班级首页，否则跳转到个人首页
        if(empty($class_code)){
            $this->showError("你还没有加入班级，请与班主任联系加入班级吧", "/Homeuser/Index/spacehome/spaceid/".$this->getCookieAccount());
            
        }
	
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
 		
		$lastUpdTime = time()-$result['last30Times'];
		$arrInfoData = $this->loadClassFeedData($class_code, $lastUpdTime, 0, 100);
		$newarr_talkInfo = array_slice($arrInfoData, 0,WMW_XXS_LIMIT);
		$this->classleftinfo($class_code);
		
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);
		$this->assign('class_code',$class_code);
		
		$this->display('classsDynamic');

	}

	//班级日志
	function classrz(){

		$log_account=$this->getCookieAccount();
		
		$class_code = $this->objInput->getInt('class_code');
	
			//查询数据的总条数
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=7; //每页数量

		
		$mOjbectData = ClsFactory::Create('Model.mPersonlogs');	 
		$mLogtypes = ClsFactory::Create('Model.mLogtypes');
		
		$mClasslog = ClsFactory::Create('Model.mClasslog');
		$mUser = ClsFactory::Create('Model.mUser');

		$classLogData = $mClasslog->getLogInfoByClassCode($class_code);
		$classLogData = $classLogData[$class_code];
		if($classLogData){
			$sortkeys = array();
			$newClassLogData = array();
			foreach ($classLogData as $classkey=>$classLogInfo) {
				$LogInfo = $mOjbectData->getPersonLogsById($classLogInfo[log_id]);
				if($LogInfo){
					$LogInfo = array_shift($LogInfo);
					$log_plun_count=$this->pluncontent($LogInfo['log_id']);
					$classLogInfo['log_name']=$LogInfo[log_name];
					$classLogInfo['add_date']=$LogInfo[add_date];
					$classLogInfo['upd_date']=$LogInfo[upd_date];
					$classLogInfo['read_count']=$LogInfo[read_count];
					$classLogInfo['plun_count']=$log_plun_count;
					
					$logcontent = str_replace('&nbsp;','',WmwString::unhtmlspecialchars($LogInfo['log_content']));
					$logcontent = str_replace('<P></P>','',$logcontent);
					$classLogInfo['log_contentall']= strip_tags(cutstr(WmwString::unhtmlspecialchars($logcontent), 100, true));
		
					
					
					
					$mcInfo = $mUser->getUserBaseByUid($LogInfo['add_account']);
				
					$mcInfo = array_shift($mcInfo);
					$classLogInfo['headimg']=Pathmanagement_sns::getHeadImg($LogInfo['add_account']) . $mcInfo['client_headimg'];
					$classLogInfo['client_name']=$mcInfo['client_name'];
					$classLogInfo['add_account']=$LogInfo['add_account'];
					$newClassLogData[] = $classLogInfo;
				}
			}

		}

		//排序日记
		array_multisort($newClassLogData ,SORT_DESC);
	
		$newarr_newLog = array_slice($newClassLogData, ($page-1)*$pagesize, $pagesize);	
		$webUrl = "/Homeclass/Classspace/classrz/class_code/".$class_code;
		$pageCount = ceil(count($newClassLogData)/$pagesize);
		intval($page) >= intval($pageCount) ? $nextpageno = $pageCount : $nextpageno = $page+1;
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newClassLogData) > $pagesize){
		$this->assign('pageinfohtml',"<div class='divpageinfoM'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");

		}



		$this->assign('mylog_list',$newarr_newLog);

		$this->classleftinfo($class_code);
		
		$this->assign('uid',$this->getCookieAccount());
		$this->assign('pagecount',$pagecount);
		$this->assign('class_code',$class_code);

		$this->assign('log_account',$log_account);
		$this->assign('client_name',$this->user[client_name]);
		$this->assign('account' , $log_account);

		$this->display('banji_classrz');
	
	}



	//查出关于每个日志的评论内容
	public function pluncontent($log_id){
		$mLogplun = ClsFactory::Create('Model.mLogplun');
		return $mLogplun->getLogplunCountByLogid($log_id);
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


}