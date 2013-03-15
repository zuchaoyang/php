<?php 
class MyclassAction extends SnsController{
	public $user;
	const PYCOUNT=30;
    public function _initialize() {
        parent::_initialize();
		import("@.Common_wmw.WmwString");
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Pathmanagement_sns");

		$this->assign('chanelid',"chanel1");
		
	}
	
	/*
	 *新功能引导页面老师
	 */
	public function index_guide() {
	    $class_code = $this->objInput->getStr('class_code');
	    if(empty($class_code)) {
	        $class_code = key($this->user['class_info']);
	    }
	    
	    $this->assign('class_code',$class_code);
	    
	    $this->display(WEB_ROOT_DIR . "/View/Template/Public/zuoye/teacher/teacher_works.html");
	}
	
	/**
	 * 可以考虑将由于时间的改变而引起的作业信息的改变的实现采用ajax	
	 */
	public function index() {
		$class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
	     
		if ($this->user['client_type']!=CLIENT_TYPE_TEACHER || empty($class_code)){
			$this->redirect('../Homepage/Homepage/index');
			exit;
		}

	
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
 		
		$lastUpdTime = time()-$result['last30Times'];
		$limit = constant("WMW_XXS_LIMIT");
		$arrInfoData = $this->loadClassFeedData($class_code, $lastUpdTime, 0, $limit);
		$newarr_talkInfo = array_slice($arrInfoData, 0,WMW_XXS_LIMIT);
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);

	    
	    //获取班级下面的公告信息
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJTG , NEWS_INFO_BJZY)));
	    //数据较大，引用传值，避免内存瞬间过大
	    $newsinfo_list = & $newsinfoarr[$class_code];
	    //分离班级的公告和作业信息
	    $homeworklist = $classnoticelist = array();
	    if(!empty($newsinfo_list)) {
	        foreach($newsinfo_list as $news_id=>$news) {

	            if($news['news_type'] == NEWS_INFO_BJTG) {
					 $news['news_title'] = cutstr($news['news_title'],20);
					 $news['news_content'] = cutstr($news['news_content'],220);

	                $classnoticelist[$news_id] = $news;
	            } elseif($news['news_type'] == NEWS_INFO_BJZY) {
					$homeworklist[$news_id] = $news;
	            }
	        }
	        //注意unset的顺序
	        unset($newsinfo_list , $newsinfoarr);
	    }
		
	    //追加作业的科目信息
	    if(!empty($homeworklist)) {
	        $subjectids = array();
	        foreach($homeworklist as $homework) {
	            $subjectids[] = $homework['subject_id'];

	        }
	        $subjectids = array_unique($subjectids);
			
	        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subjectinfo_list = $mSubjectInfo->getSubjectInfoById($subjectids);
	        foreach($homeworklist as $news_id=>$homework) {
	            $subject_id = $homework['subject_id'];
	            if(isset($subjectinfo_list[$subject_id])) {
	                $homework = array_merge($subjectinfo_list[$subject_id] , $homework);
	            }
	            $homeworklist[$news_id] = $homework;
				$homeworklist[$news_id]['expiration_date'] = date("Y-m-d",strtotime($homeworklist[$news_id]['expiration_date']));

	        }
	        unset($subjectinfo_list);
	    }
		
	    //作业信息的排序问题
	    if(!empty($homeworklist)) {
	        $sortkeys = array();
	        foreach($homeworklist as $news_id=>$homework) {
	            $sortkeys[$news_id] = $homework['add_date'];
	        }
	        array_multisort($sortkeys , SORT_DESC , $homeworklist);
	        if(count($homeworklist) > 6) {
	            $homeworklist = array_slice($homeworklist , 0 , 6);
	        }
		}
		
		

	    //班级公告的排序问题
	    if(!empty($classnoticelist)) {
	        $sortkeys = array();
	        foreach($classnoticelist as $news_id=>$notice) {
	            $sortkeys[$news_id] = $notice['add_date'];
	        }
	        array_multisort($sortkeys , SORT_DESC , $classnoticelist);
	        if(count($classnoticelist) > 10) {
	            $classnoticelist = array_slice($classnoticelist , 0 , 10);
	        }
	    }
		if($classnoticelist){
			//$classnoticelist = array_shift($classnoticelist);
			$newnoticelist[] = array_splice($classnoticelist,0,1);
			$newnoticelist = array_shift($newnoticelist);
			$this->assign('news_title' , $newnoticelist[0]['news_title']);
			$this->assign('news_content' , $newnoticelist[0]['news_content']);
			$this->assign('add_date' , $newnoticelist[0]['add_date']);
			$this->assign('add_account_name' , $newnoticelist[0]['client_name']);
		}
	

	    $this->assign('class_code' , $class_code);
	    $this->assign('current_user' , $this->user);                           //当前用户基本信息
	    $this->assign('client_type' , intval($this->user['client_type']));    //当前用户类型
	    $this->assign('classnoticelist' , $classnoticelist);   //班级下的公告信息
	    $this->assign('homeworklist' , $homeworklist) ;        //班级作业
        
	    
	    $this->display('myclass');
	}
	
	
	public function morefeedlist(){
		$nextLlimit = $this->objInput->getInt('nextLlimit');
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
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
		$mUser = ClsFactory::Create('Model.mUser');	    
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
								$arrlist['feed'][$arrlist_key]['feed_type'] = '公告';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新公告';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =$talkval['add_date'];
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了公告';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = $talkval['upd_date'];
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/AnnouncementView/class_code/{$class_code}/newsid/{$talkval['news_id']}";
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
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/hoemworkview/workid/{$talkval['news_id']}/class_code/{$class_code}";
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

								if($client_infos[$loginid]['client_type'] == CLIENT_TYPE_TEACHER){
									$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/examdetailinfo/exam_id/{$talkval['exam_id']}";
								}else{
									$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/achievement/class_code/{$class_code}";
								}
								
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
				$feedlist = $mCurriculum->getCurriculumInfoByClassCode($val2);
				$feedlist = array_shift($feedlist);
				if($feedlist){
					foreach($feedlist as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['class_code'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '课程表';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了课程表';
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了课程表';
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Curriculum/index/class_code/{$class_code}";
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
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/classJournalview/log_id/{$talkval['log_id']}";
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
					$album_id[$feedval['photo_id']] =  $feedval['photo_id'];
				}
				$mAlbuminfo = ClsFactory::Create('Model.mAlbuminfo');
				$albuminfo = $mAlbuminfo->getAlbumListByAlbumid($album_id);
				
				unset($album_id);
				if($albuminfo){
					foreach($albuminfo as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['album_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '相册';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新相册';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =date('Y-m-d H:i:s',$talkval['add_date']);
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了相册';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s',$talkval['upd_date']);
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homepzone/Pzonephoto/xcmanager/user_account/{$talkval['add_account']}/xcid/{$talkval['album_id']}";
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


	//发布公告
	public function publishggao(){
	    $class_code = $this->objInput->getInt('class_code');
	    $class_code = $this->checkclasscode($class_code);
	    $classinfolist = $this->user['class_info'];
	    $schoolinfo = array_shift($this->user['school_info']);
	    $schoolid = $schoolinfo['school_id'];
		$operationStrategy = intval($schoolinfo['operation_strategy']);//获取该学校的运营策略
	    $this->assign('school_id' , $schoolid);
        $this->assign('operationStrategy' , $operationStrategy);
		$this->assign('class_code' , $class_code);
		$this->assign('classinfolist' , $classinfolist);
		
		$this->display('publishggao');
	}
	
	/**
	 * 获取当前的作业信息,通过ajax进行获取
	 * @param  $class_code
	 */
	public function homeworklist(){
	    $class_code = $this->objInput->getInt('class_code');
	    $nowdate = $this->objInput->getStr('date');
	    //检测当前班级class_code的正确性
	    $checked_class_code = $this->checkclasscode($class_code);
	    $flag = $class_code && $checked_class_code == $class_code ? true : false;

	    if(!empty($nowdate) && strtotime($nowdate) !== false) {
	        $nowdate = date('Y-m-d' , strtotime($nowdate));
	    } else {
	        $nowdate = date('Y-m-d' , time());
	    }
	    //设置过滤器
	    $filters = array(
	        'news_type' => NEWS_INFO_BJZY,
	    );

	    $taskstr = "";
	    if($flag) {
    	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
    	    $homeworkarr = $mNewsInfo->getNewsInfoByClassCode($class_code , $filters);
            $homeworklist = $homeworkarr[$class_code];
    	    unset($homeworkarr);

    	    //追加作业的科目信息
    	    if(!empty($homeworklist)) {
    	        $subjectids = array();
    	        foreach($homeworklist as $homework) {
    	            $subjectids[] = $homework['subject_id'];
    	        }
    	        $subjectids = array_unique($subjectids);

    	        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
    	        $subjectinfo_list = $mSubjectInfo->getSubjectInfoById($subjectids);
    	        foreach($homeworklist as $news_id=>$homework) {
    	            $subject_id = $homework['subject_id'];
    	            if(isset($subjectinfo_list[$subject_id])) {
    	                $homework = array_merge($subjectinfo_list[$subject_id] , $homework);
    	            }
    	            if(date("Y-m-d" , strtotime($nowdate)) >= date("Y-m-d" , strtotime($homework['add_date'])) && date("Y-m-d" , strtotime($nowdate)) <= date('Y-m-d',strtotime($homework['expiration_date']))){
                        $homework['news_content'] = $homework['news_content']."（到期时间：".date('Y-m-d',strtotime($homework['expiration_date']))."）";
                        $homeworklist[$news_id] = $homework;
    	            }else{
                        unset($homeworklist[$news_id]);
    	            }
    	        }
    	        unset($subjectinfo_list);
    	    }

    	    $new_homeworklist = array();
    	    if(!empty($homeworklist)) {
    	        foreach($homeworklist as $key=>$task) {
    	            $new_homeworklist[] = "<table width='100%' border='0' cellspacing='0' cellpadding='0'>
                              <tr>
                                <td>{$task['subject_name']}作业（{$task['client_name']}）</td>
                              </tr>
                              <tr>
                                <td><p>{$task['news_content']}</p></td>
                              </tr>
                            </table>";
    	        }
    	        $taskstr = implode(" " , $new_homeworklist);
    	    } else {
    	        $taskstr = "<table width='100%' border='0' cellspacing='0' cellpadding='0'>
                              <tr>
                                <td colspan='2'><p>{$nowdate}&nbsp;暂无作业内容 </p></td>
                              </tr>
                            </table>";
    	    }
	    } else {
	        $taskstr = "<p>您没有权限查看相关信息!</p>";
	    }

	    echo $taskstr;
	}
	

	

//成长沟通部分使用-------------------------------------------------------------------------
	
/*公告首页	 	 //获取班级下面的公告信息*/
	function Announcement(){
		$class_code = $this->objInput->getInt('class_code');
		$pageno = trim($this->objInput->getInt('pageno'));
		empty($pageno) ? $page = 1 : $page = $pageno;//页码
		$pagesize=15; //每页数量
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJTG)));
	    $newsinfo_list = & $newsinfoarr[$class_code];
		 foreach($newsinfo_list as $news_id=>$news) {
				$news['news_title'] = cutstr($news['news_title'], 60, true);
				$classnoticelist[$news_id] = $news;
				$dates[$news_id] = $news['add_date'];
		 }
		array_multisort($dates,SORT_DESC,$classnoticelist);
		
		 
	    $newarr_newLog = array_slice($classnoticelist, ($page-1)*$pagesize, $pagesize+1);	
		$webUrl = "/Homeclass/Myclass/Announcement/class_code/{$class_code}";
		$pageCount = ceil(count($newsinfo_list)/$pagesize);
		
		intval($page) ==1 ? $prvpageno = 1 : $prvpageno = $page-1;
		if(count($newarr_newLog) > $pagesize){
			array_pop($newarr_newLog);
			$nextpageno = $page+1;
		}else{
			$nextpageno = $page;
		}
		$this->assign('pageinfohtml',"<div class='divpageinfo'><a href='".$webUrl."/pageno/".$prvpageno."'>上一页</a> | <a href='".$webUrl."/pageno/".($nextpageno)."'>下一页</a></div>");
		$this->assign('pageno',$pageno);
		
		$this->assign('classnoticelist',$newarr_newLog);
		$this->assign('class_code',$class_code);
		$this->assign('actionUrl',"/Homeclass/Myclass/Announcement/");
		
		$this->display('myclassAnnouncement');
	}
	
//公告预览
	function saveggaoPreview(){
	    $news_title = $this->objInput->postStr('news_title');
		$news_content = $this->objInput->postStr('ggao_content');
	    $class_code   = $this->objInput->postInt('class_code');
	    $sendMessage  = $this->objInput->postStr('sendMessage'); //短信发送checkbox是否勾选
	    $schoolid     = $this->objInput->postStr('schoolid');
	    $class_code   = $this->checkclasscode($class_code);
		$sendMessage == "" ? $x_sendMessage = "no" : $x_sendMessage = $sendMessage;
	    $operationStrategy = $this->objInput->postStr('operationStrategy');
	    if (empty($class_code)) {
	        return false;
	    }
		
        $this->assign('news_title' , $news_title);
        $this->assign('news_content' , $news_content);
        $this->assign('class_code' , $class_code);
        $this->assign('schoolid' , $schoolid);
        $this->assign('x_sendMessage' , $x_sendMessage);
        
		$this->display('saveggaoPreview');

	}
//保存公告
	public function saveggao(){
	    $news_title = $this->objInput->postStr('news_title');
		$news_content = $this->objInput->postStr('ggao_content');
	    $class_code   = $this->objInput->postInt('class_code');
	    $sendMessage  = $this->objInput->postStr('sendMessage'); //短信发送checkbox是否勾选
	    $schoolid     = $this->objInput->postStr('schoolid');
	    $class_code   = $this->checkclasscode($class_code);
		$sendMessage == "" ? $x_sendMessage = "no" : $x_sendMessage = $sendMessage;
	    $operationStrategy = $this->objInput->postStr('operationStrategy');
	    if (empty($class_code)) {
	        return false;
	    }
	     /*
		 * 2011-09-07增加短信通知家长的功能
		 */
	    if ($x_sendMessage == 'on') {//checkbox勾选上了
	        $mBusinessphone  = ClsFactory::Create('Model.mBusinessphone'); 
	        $phone_arr = $mBusinessphone->getParentBPhoneByClassCode($class_code);
            if (!empty($phone_arr)) {
                $userInfo = $this->user;
                $school_info = array_shift($userInfo['school_info']);
                $schoolName = $userInfo['school_info'][$schoolid]['school_name'];
                $operationStrategy = $school_info['operation_strategy'];
                $className = $userInfo['class_info'][$class_code]['class_name'];
                $news_content = WmwString::unhtmlspecialchars($news_content);   
	   			$news_content = WmwString::delhtml($news_content);
                $message = $schoolName.'-'.$className.'-公告：'.$news_content;
                import('@.Control.Api.Smssend.Smssendapi');//upd smssend
                $smssendapi_obj = new Smssendapi();
                //70859265//18620456699
                $addSmsSendResult = $smssendapi_obj->send($phone_arr, $message, $operationStrategy);
	            if (!$addSmsSendResult) {//如果通知家长失败则跳转，将不发表该公告
					$msg = "短信通知家长失败！ 3   秒    钟后自动跳转... ";
                    $this->showError($msg, "/Homeclass/MyClass/index/class_code/$class_code");
	        	}
            }
		}
		$dataarr = array(
			'news_type' => NEWS_INFO_BJTG,
			'news_title'=> $news_title,
			'news_content' => $news_content,
			'class_code' => $class_code,
			'add_account' => $this->user['client_account'],
			'add_date' => date('Y-m-d H:i:s',time()),
			'upd_account' => $this->user['client_account'],
			'sendMessage' => $x_sendMessage
			
		);
		$mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
		$res_id = $mNewsInfo->addNewsInfo($dataarr, true);
		if (!empty($res_id)) {
			/*添加动态信息-操作开始*/
			$mFeed = ClsFactory::Create('Model.mFeed');
			$resultData = $mFeed->addClassFeed(intval($class_code), intval($this->getCookieAccount()), intval($res_id), intval(CLASS_FEED_NOTICE), intval(FEED_NEW), time());
			/*添加动态信息-操作结束*/
        	$this->showSuccess("公告添加成功!", "/Homepage/Homepage/index/class_code/$class_code");
		}else if (empty($resultData)) {
			$this->showError("添加公告失败!", "/Homepage/Homepage/index/class_code/$class_code");
		}
	}
	//添加作业预览
	public function addworkcontPreview(){
	    $class_code = $this->objInput->postInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		
	    $news_content = $this->objInput->postStr('news_content');
	    $subject_id = $this->objInput->postInt('subject_id');
	    $sendMessage  = $this->objInput->postStr('sendMessage'); //短信发送标志位
		$sendMessage ? $sendMessage : $sendMessage="no";
		
		$schoolid     = $this->objInput->postStr('schoolid');
	    $expiration_date = $this->objInput->postStr('expiration_date');
	    $operationStrategy = $this->objInput->postInt('operationStrategy');
		$news_contenthidden = htmlspecialchars($news_content);
		$news_content = WmwString::unhtmlspecialchars($this->objInput->postStr('news_content'));     

		$mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
		$schoolSubjectInfo = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);
		$schoolSubjectInfo = & $schoolSubjectInfo[$schoolid];
		$subjectName = $schoolSubjectInfo[$subject_id]['subject_name'];
		
		if($_FILES['workattachment']['type']!=''){
		    $up_init = array(
                'max_size' => 900,
                'attachmentspath' => Pathmanagement_sns::uploadHomeWork(),
            	'renamed' => true,
                'allow_type' => array('xls', 'xlsx','jpg','gif','png','doc','txt')
            );
            
            $uploadObj = ClsFactory::Create('@.Common_wmw.WmwUpload');  
            $uploadObj->_set_options($up_init);
            $up_rs = $uploadObj->upfile('workattachment');
            $strSaveFile1Url1 = end(explode('/',$up_rs['getfilename']));
			$this->assign('strSaveFile1Url1' , $strSaveFile1Url1);
		}

		$this->assign('class_code' , $class_code);
		$this->assign('news_content' , $news_content);
		$this->assign('news_contenthidden' , $news_contenthidden);
		$this->assign('strSaveFile1Url1' , $strSaveFile1Url1);

		$this->assign('school_id' , $schoolid);
		$this->assign('subject_id' , $subject_id);
		$this->assign('subjectName' , $subjectName);
		$this->assign('client_name' , $this->user['client_name']);
	
		$this->assign('sendMessage' , $sendMessage);
		$this->assign('expiration_date' , $expiration_date);
		$this->assign('add_date' , date("Y-m-d" , time()));

		$this->display('workcontPreview');

	}



	//添加作业
	public function addworkcont(){
	    $class_code = $this->objInput->postInt('class_code');
		if(!$class_code){
			$class_code = $this->objInput->getInt('class_code');
		}
		
	    $news_content = $this->objInput->postStr('news_content');
	    $subject_id = $this->objInput->postInt('subject_id');
	    $sendMessage  = $this->objInput->postStr('sendMessage'); //短信发送标志位
		$sendMessage == "" ? $x_sendMessage = "no" : $x_sendMessage = $sendMessage;
	    $schoolid     = $this->objInput->postStr('schoolid');
	    $subjectName  = $this->objInput->postStr('subjectName');
	    $expiration_date = $this->objInput->postStr('expiration_date');
	    $operationStrategy = $this->objInput->postInt('operationStrategy');
		$strSaveFile1Url1 = $this->objInput->postStr('strSaveFile1Url1');
	    $class_code = $this->checkclasscode($class_code);
	    $news_content = htmlspecialchars($news_content);

	    if(empty($news_content)) {
	        echo '<script type="text/javascript">alert("内容不能为空!");history.back(-1);</script>';
	        exit;
	    }

	    //当前用户的权限判断
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
		$clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type' => CLIENT_TYPE_TEACHER));
		$clientclasslist = $clientclassarr[$class_code];
		unset($clientclassarr);
		//老师科目
		$teacheruids = array_keys($clientclasslist);
		//判断当前用户的权限
		if(empty($teacheruids) || (!empty($teacheruids) && !in_array($this->user['client_account'] , $teacheruids))) {
		    $this->showError("您不是该班的任课教师,不能添加作业信息", "/Homepage/Homepage/index/class_code/$class_code");
		    exit;
		}
	 	/*
		 * 2011-09-07增加短信通知家长的功能
		 */
	    if ($x_sendMessage == 'on') {//checkbox勾选上了
	        $mBusinessphone  = ClsFactory::Create('Model.mBusinessphone');
	        $phone_arr = $mBusinessphone->getParentBPhoneByClassCode($class_code);
            if (!empty($phone_arr)) {
                $userInfo = $this->user;
                $schoolName = $userInfo['school_info'][$schoolid]['school_name'];
                $className = $userInfo['class_info'][$class_code]['grade_id_name'];
                $school_info = array_shift($userInfo['school_info']);
                if(!$schoolid){
                	$schoolid = $school_info['school_id'];
                }
                $operationStrategy = $school_info['operation_strategy'];
                $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
                $schoolSubjectInfo = $mSubjectInfo->getSubjectInfoBySchoolid($schoolid);
                $schoolSubjectInfo = & $schoolSubjectInfo[$schoolid];
                $subjectName = $schoolSubjectInfo[$subject_id]['subject_name'];
                $message = $schoolName.'-'.$className.'-'.$subjectName.'-作业：'.$news_content;
                $message = WmwString::unhtmlspecialchars($message); 
                $message = WmwString::delhtml($message);
                import('@.Control.Api.Smssend.Smssendapi');//upd smssend
                $smssendapi_obj = new Smssendapi();
                //70859265//18620456699
                $addSmsSendResult = $smssendapi_obj->send($phone_arr, $message, $operationStrategy);
                if (empty($addSmsSendResult)) {//如果通知家长失败则跳转，将不发表该作业
                    $this->showError("短信通知家长失败!", "/Homepage/Homepage/index/class_code/$class_code");
        		}
        		$message = ",短信通知家长成功!";
            }
		}
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $dataarr = array(
	    	'news_type' => NEWS_INFO_BJZY,
    	    'news_toaccount' => 0,
    	    'news_content' => $news_content,
	    	'news_title' => $subjectName,
    	    'class_code' => $class_code,
	        'subject_id' => $subject_id,
    	    'add_account' => $this->user['client_account'],
    	    'add_date' => date("Y-m-d H:i:s" , time()),
    	    'upd_account' => $this->user['client_account'],
    	    'upd_date' => date("Y-m-d H:i:s" , time()),
	        'expiration_date' => $expiration_date && strtotime($expiration_date) !== false && $expiration_date > date("Y-m-d" , time()) ? $expiration_date : date('Y-m-d' , time() + 3600 *24),
			'attachment' => $strSaveFile1Url1,
			'sendMessage' => $x_sendMessage,
	    );
	    $res_id= $mNewsInfo->addNewsInfo($dataarr, true);
		if ($res_id) {
			/*添加动态信息-操作开始*/
			$mFeed = ClsFactory::Create('Model.mFeed');
			$resultData = $mFeed->addClassFeed(intval($class_code), intval($this->getCookieAccount()), intval($res_id), intval(CLASS_FEED_WORK), intval(FEED_NEW), time());
            
			$this->showSuccess("添加作业成功".$message, "/Homepage/Homepage/index/class_code/$class_code");
		} else if (!$res_id) {
            $this->showError("添加作业失败", "/Homepage/Homepage/index/class_code/$class_code");
		}
	}
	
	//成长沟通老师
	function Communicate(){
		$inarray = array();
		$client_type = $this->user['client_type'];
		$client_type==1 || $client_type==2 ? $blnJurisdiction = true : $blnJurisdiction = false;
		//homepageAction::chkUserJurisdiction($blnJurisdiction,"submit");
		$mCommunicateInfo = ClsFactory::Create('Model.mCommunicateInfo');
		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
		$mUser = ClsFactory::Create('Model.mUser');
		$mClientClass = ClsFactory::Create('Model.mClientClass');
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		
		if($this->user['client_type'] == CLIENT_TYPE_TEACHER){
			//老师登录时
			//得到班级学生成员
			$class_user_list = $mClientClass->getClientClassByClassCode($class_code);
			foreach($class_user_list[$class_code] as $userkey=>&$userlist){
				if($userlist['client_type'] == CLIENT_TYPE_STUDENT){
					$studentlist[$userkey] = $userkey; 
				}
			}
			unset($class_user_list);
			
			//得到班级学生成员信息
			$client_class_list = $mUser->getUserBaseByUid($studentlist);
			$ComcountInfo = $mCommunicateInfo->getCommunicateByChildId($studentlist);
			if(!empty($ComcountInfo)){
				foreach($client_class_list as $client_key=>&$client_val){
					if(empty($ComcountInfo[$client_key])){
						$client_class_list[$client_key]['max_add_date'] = '0000-00-00';
						$client_class_list[$client_key]['msgnums'] = 0;
					}
				}
				foreach($ComcountInfo as $key3=>&$val3){
					$count = 0;
					foreach($val3 as $key4=>&$val4){
						if($val4['to_account'] == $this->user['client_account']){
							$strarr[$key4] = $val4;
							$count++;
						}
					}
					if($strarr){
						$lastarr = array_pop($strarr);
						$client_class_list[$key3]['max_add_date'] = date('Y-m-d H:i:s', $lastarr['add_time']);
					}else{
						$client_class_list[$key3]['max_add_date'] = '0000-00-00';
					}
					if(!$count){
						$count = 0;
					}
					$client_class_list[$key3]['msgnums'] = $count;
					
					unset($lastarr);
				}
			}else{
				foreach($client_class_list as $key3=>&$val3){
					$client_class_list[$key3]['max_add_date'] = '0000-00-00';
					$client_class_list[$key3]['msgnums'] = 0;
				}
			}
			
			unset($studentlist,$ComcountInfo);
		}elseif($this->user['client_type'] == CLIENT_TYPE_FAMILY){
			//家长登录时
			$loginerid = intval($this->user['client_account']);
			$relationList1 = $mFamilyRelation->getFamilyRelationByFamilyUid($loginerid);//家长登录账号
			$account_info = array_shift($relationList1[$loginerid]);
			$childid=$account_info['client_account'];
			
			//得到班级老师成员
			$class_user_list = $mClientClass->getClientClassByClassCode($class_code);
			foreach($class_user_list[$class_code] as $userkey=>&$userlist){
				if($userlist['client_type'] == CLIENT_TYPE_TEACHER){
					$teacherlist[$userkey] = $userkey; 
				}
			}
			unset($class_user_list);
			
			//得到班级老师信息
			$client_class_list = $mUser->getUserBaseByUid($teacherlist);
			$ComcountInfo = $mCommunicateInfo->getCommunicateByChildId($childid);
			foreach($client_class_list as $key6=>$val6){
				$count = 0;
				foreach($ComcountInfo[$childid] as $key5=>&$val5){
					if($val5['add_account'] == $key6){
						$teaarr[$key5]= $val5;
						$count++;
					}
				}
				if($teaarr){
					$lastarr = array_pop($teaarr);
					$client_class_list[$key6]['max_add_date'] = date('Y-m-d H:i:s', $lastarr['add_time']);
				}else{
					$client_class_list[$key6]['max_add_date'] = '0000-00-00';
				}
				if(!$count){
					$count = 0;
				}
				$client_class_list[$key6]['msgnums'] = $count;
				unset($teaarr,$lastarr);
			}
			unset($teacherlist,$ComcountInfo);
		}
		if($this->objInput->getInt('account')){
			$defaultuser = $this->objInput->getInt('account');
		}else{
			foreach($client_class_list as $firstkey=>&$firstval){
			$defaultuser = $firstkey;
			break;
		}
		}
		$this->assign('clientclasslist',$client_class_list);
		$this->assign('class_code',$class_code);
		$this->assign('defaultuser',$defaultuser);
		$this->assign('actionUrl' , "/Homeclass/Myclass/Communicate/");
		if($this->user['client_type'] == CLIENT_TYPE_FAMILY){
			$this->display('fmailyCommunicate');
		}elseif($this->user['client_type'] == CLIENT_TYPE_TEACHER){
			$this->display('Communicate');
		}
	}
	//wmw3.0end
	//老师获得沟通信息
	function CommunicateData(){
		//3.0
		$inarray = array();
		$account = $this->objInput->getInt('account');
		$mFamilyRelation = ClsFactory::Create('Model.mFamilyRelation');
		if($this->user['client_type'] == CLIENT_TYPE_FAMILY){
			$loginerid = $this->user['client_account'];
			$inarray[] = $account;
			$teacherid = $account;
			$relationList1 = $mFamilyRelation->getFamilyRelationByFamilyUid($loginerid);//家长登录账号
			$account_info = array_shift($relationList1[$loginerid]);
			$inarray[] = $childid = $account_info['client_account'];
		}elseif($this->user['client_type'] == CLIENT_TYPE_TEACHER){
			$inarray[] = $this->user['client_account'];
			$inarray[] = $childid = $account;
			$teacherid = $this->user['client_account'];
		}
		$mUser = ClsFactory::Create('Model.mUser');
		$client_info = $mUser->getUserBaseByUid($account);
		$client_name = $client_info[$account]['client_name'];
		$child_info = $mUser->getUserBaseByUid($childid);
		$child_name = $child_info[$childid]['client_name'];
		$mCommunicateInfo = ClsFactory::Create('Model.mCommunicateInfo');
		//得到老师与家长的沟通内容
		$communicateList = $mCommunicateInfo->getCommunicateByChildId($childid);
		$relationList = $mFamilyRelation->getFamilyRelationByUid($childid);
		
		foreach($relationList[$childid] as $familykey1=>&$familyval){
			$inarray[] = $familyval['family_account'];
		}
		$new_inarray = array_unique($inarray);
		$contentlist = array();
		foreach($communicateList as $childkey2=>&$comlist){
			foreach($comlist as $idkey=>&$vallist){
				if(in_array($vallist['add_account'],$new_inarray) && in_array($vallist['to_account'],$new_inarray)){
					foreach($relationList[$childid] as $typekey=>&$typeval){
						if($typekey == $vallist['add_account']){
							$vallist['family_type'] =$typeval['family_type'];
						}
					}
					$vallist['add_date'] = date('Y-m-d H:i:s',$vallist['add_time']);
					$contentlist[$idkey] = $vallist;
					
				}
			}
		}
		unset($inarray,$new_inarray);
		if($this->user['client_type'] == CLIENT_TYPE_TEACHER){
			$htmldata = $account."??<div class='grow_trt'>与".$child_name."家长进行沟通</div>";
		}elseif($this->user['client_type'] == CLIENT_TYPE_FAMILY){
			$htmldata = $account."??<div class='grow_trt'>与".$client_name."老师进行沟通</div>";
		}
			if($contentlist){
			
			$htmldata = $htmldata."<div class='grow_trm'>";
				foreach($contentlist as &$contentval){
					if (in_array($contentval['family_type'],array(1,2))){
						
						if($contentval['family_type']){
							switch($contentval['family_type']){
								case 1 :
										$tipname = $child_name."的母亲";
									break;
								case 2 :
										$tipname = $child_name."的父亲";
									break;
							}
						}
					

						$htmldata = $htmldata."<div class='grow_trml'>";
							$htmldata = $htmldata."<div class='grow_time'>".$contentval['add_date']."</div>";
							$htmldata = $htmldata."<div class='grow_trmlt'></div>";
							$htmldata = $htmldata."<div class='grow_trmlm'>";
								$htmldata = $htmldata."<p>".$contentval['communicate_content']."<br>".$tipname."</p>";
								$htmldata = $htmldata."<div class='kong'></div>";
							$htmldata = $htmldata."</div>";
							$htmldata = $htmldata."<div class='grow_trmlb'></div>";
						$htmldata = $htmldata."</div>";
					
					}else{


						$htmldata = $htmldata."<div class='grow_trmr'>";
							$htmldata = $htmldata."<div class='grow_time'>".$contentval['add_date']."</div>";
							$htmldata = $htmldata."<div class='grow_trmrt'></div>";
							$htmldata = $htmldata."<div class='grow_trmrm'>";
								$htmldata = $htmldata."<p>".$contentval['communicate_content']."</p>";
								$htmldata = $htmldata."<div class='kong'></div>";
							$htmldata = $htmldata."</div>";
							$htmldata = $htmldata."<div class='grow_trmrb'></div>";
						$htmldata = $htmldata."</div>";
					}

				}

				$htmldata = $htmldata.'</div>';
			}
			
		echo $htmldata;

	}
	
	//老师发送
	function CommunicateSave(){
		$client_type = $this->user['client_type'];
		$client_type==1 || $client_type==2 ? $blnJurisdiction = true : $blnJurisdiction = false;
		
		$msg = trim(urldecode($this->objInput->postStr('msg')));
		$Receive_account = $this->objInput->postInt('account');
		$class_code =$this->objInput->postInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$sendtype= $this->objInput->postStr('sendtype');
		$mCommunicate = ClsFactory::Create('Model.mCommunicateInfo');
		
		if($sendtype=="more"){
			$msgid = trim(urldecode($this->objInput->postStr('msgid')));
			$msgid = substr($msgid,0,strlen($msgid)-1);
			$arr_msgid = explode(",",$msgid);
			$data = array();
			for($i=0;$i<count($arr_msgid);$i++){
				$data[$i]['child_account']=$arr_msgid[$i];
				$data[$i]['add_account']=$this->user['client_account'];
				$data[$i]['to_account']=$arr_msgid[$i];
				$data[$i]['add_time']=time();
				$data[$i]['communicate_content']=$msg;
			}
			$RsCommunicate = $mCommunicate->addCommunicateBat($data);
		}else{
			$data['child_account']=$Receive_account;
			$data['add_account']=$this->user['client_account'];
			$data['to_account']=$Receive_account;
			$data['add_time']=time();
			$data['communicate_content']=$msg;
			$RsCommunicate = $mCommunicate->addCommunicate($data, true);
		}
		if($RsCommunicate){
			echo "success";    
		}else{
		    echo "fail";
		}
		
	}
	

	//家长发送
	function familyCommunicateSave(){
		$mFamilyRelation =ClsFactory::Create('Model.mFamilyRelation');
		
		$client_type = $this->user['client_type'];
		$to_account= $this->objInput->postInt('account');
		$client_type==2 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$msg = trim(urldecode($this->objInput->postStr('msg')));
		$loginerid = $this->user['client_account'];
		$RsmRelation = $mFamilyRelation->getFamilyRelationByFamilyUid($loginerid);//家长登录账号
		
		$class_code = $this->objInput->postInt('class_code');
		
		if($RsmRelation){
		    $account_info = array_shift($RsmRelation[$loginerid]);
            $childid = $account_info['client_account'];
			
		}else{
			echo "fail";
			exit;
		}
		$mCommunicate = ClsFactory::Create('Model.mCommunicateInfo');
		$data['child_account']=$childid;
		$data['add_account']=$loginerid;
		$data['to_account']=$to_account;
		$data['add_time']=time();
		$data['communicate_content']=$msg;
		$RsCommunicate = $mCommunicate->addCommunicate($data,true);
		if($RsCommunicate){
			echo "success";
		}
	}

	//多个家长沟通页面
	function Communicatemore(){
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		$this->assign('class_code',$class_code);
		
		$this->display('Communicatemore');
	}


	//多个家长沟通页面
	function getstudent_listbycommunicate(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$class_code = $this->objInput->getInt('class_code');
		$class_code = $this->checkclasscode($class_code);
		
		$mUser = ClsFactory::Create('Model.mUser');
		$mCommunicate = ClsFactory::Create('Model.mCommunicateInfo');

	    //获取班级的成员列表
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    
	    //得到班级学生成员
		$class_user_list = $mClientClass->getClientClassByClassCode($class_code);
		foreach($class_user_list[$class_code] as $userkey=>&$userlist){
			if($userlist['client_type'] == CLIENT_TYPE_STUDENT){
				$studentlist[$userkey] = $userkey; 
			}
		}
		unset($class_user_list);
		
		$svaue = $this->objInput->getStr('svaue');
		$svaue = ",".$svaue;
		
		//得到班级学生成员信息
		$clientclasslist = $mUser->getUserBaseByUid($studentlist);
		unset($studentlist);
		if(!empty($clientclasslist)){
			foreach($clientclasslist as $ukey=>& $uval){
				if($uval['client_sex']!=""){
					switch($uval['client_sex']){
						case 0 :
							$uval['client_sex'] = "女";
							break;
						case 1 :
							$uval['client_sex'] = "男";
							break;
					}
				}
				else{
					$uval['client_sex'] = "不详";
				}
				
				$uval['client_num']= $uval['client_account'];
				if(strchr($svaue,$uval['client_account'])>0){
					$uval['client_check']= "checked";
				}else{
					$uval['client_check']= "";
				}
			}
		}
	
		$this->assign('class_code',$class_code);
		$this->assign('DataSlist',$clientclasslist);
		
		$this->display('studentlistbycommunicate');
	}
	
//成长沟通部分使用结束-------------------------------------------------------------------------

/*评语大师部分应用*******************************************************************************/

	//评语大师教师访问权限
	function pyComment(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$arrpytype = Constancearr::pytype();
		$pytypeatt = Constancearr::pytypeatt();
        $arrOut=array();
        $pytypeattarr=array();
        $p=0;
        foreach($arrpytype as $key=>&$value){
			  $arrOut[$p]['id']=$key;
			  $arrOut[$p]['name']=$value;
          $p++;
       }
	   $p=0;
        foreach($pytypeatt as $key1=>&$value1){
			  $pytypeattarr[$p]['id']=$key1;
			  $pytypeattarr[$p]['name']=$value1;
          $p++;
       }
       
		$class_code = $this->objInput->getInt('class_code');
		$this->assign('class_code',$class_code);
		$this->assign('arrpytype',$arrOut);
		$this->assign('pytypeatt',$pytypeattarr);
		
		$this->display('pyComment');
	}
	
	//评语大师
	function pyCommentOpen(){
		$rowsid = $this->objInput->getStr('rowsid');
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;

		$arrpytype = Constancearr::pytype();
        $arrOut=array();
        $p=0;
        foreach($arrpytype as $key=>$value){
			  $arrOut[$p]['id']=$key;
			  $arrOut[$p]['name']=$value;
          $p++;
       }

		$this->assign('rowsid',$rowsid);
		$this->assign('arrpytype',$arrOut);
		
		$this->display('pyCommentOpen');
	}


	//获取评语内容
	function showpyContentData(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$url = $this->objInput->getStr('url'); //成绩发布时选择来源
		$url == "cj" ?  $tag = "选择"  : $tag = "复制";
		$pytype = $this->objInput->getInt('pytype');
		$pyatt = $this->objInput->getInt('pyatt');
		$mPyInfo = ClsFactory::Create('Model.mPyInfo');	    
		$rsmpy_info = $mPyInfo->getpyCollectBypytypeatt($pytype, $pyatt);
		if($rsmpy_info){
		    foreach($rsmpy_info as $key=>$val){
				$outdata = "<li><span style='color:#FF3300'><a href=\"javascript:scpy('".$val['py_id']."');\">收藏</a></span><span><a href=\"javascript:copyText('".$val['py_content']."');\">".$tag."</a></span>".$val['py_content']."</li>";
				$Toutdata == "" ? $Toutdata = $outdata : $Toutdata=$Toutdata.$outdata;
			}
		}else{
			$Toutdata = "<li style='color:#FF3300'>抱歉，没有内容哦！！！</li>";
		}
		echo "<div class='commenbrbb'><ul>".$Toutdata."</ul></div>";
	}


	//获取评语内容 按搜索词
	function showpyContentDataKey(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		//homepageAction::chkUserJurisdiction($blnJurisdiction,"ajax");

		$pytxt = trim(urldecode($this->objInput->postStr('pytxt')));
		if(get_magic_quotes_gpc()){
			$pytxt = stripslashes($pytxt);
		}
		$pytxt = htmlspecialchars($pytxt);
		$pytxt = str_replace("'", "&#039;", $pytxt);
		$mPyInfo = ClsFactory::Create('Model.mPyInfo');	    
		$rsmpy_info = $mPyInfo->getPycontentLikekey($pytxt);

		if($rsmpy_info){
		    foreach($rsmpy_info as $py_id => $py){
				$outdata = "<li><span style='color:#FF3300'><a href=\"javascript:scpy('".$py['py_id']."');\">收藏</a></span><span><a href=\"javascript:copyText('".$py['py_content']."');\">".$tag."</a></span>".$py['py_content']."</li>";
				$Toutdata == "" ? $Toutdata = $outdata : $Toutdata=$Toutdata.$outdata;
			}
		}else{
			$Toutdata = "<li style='color:#FF3300'>没有找到您要搜索的内容哦！！！</li>";
		}
		echo "<div class='commenbrbb'><ul>".$Toutdata."</ul></div>";
	}

	
	//按评语属性查看
	function showpybytypeatt(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$url = $this->objInput->getStr('url'); //成绩发布时选择来源
		$url == "cj" ?  $tag = "选择"  :$tag = "复制";
		$pytypeatt = $this->objInput->getInt('pytypeatt');
		
		$mPyInfo = ClsFactory::Create('Model.mPyInfo');	  
		$rsmpy_info = $mPyInfo->getpyCollectBypyatt($pytypeatt);
		if($rsmpy_info){
			$sortkeys = array();
			foreach($rsmpy_info as $key=>$value) {
	            $sortkeys[$key] = $value['py_id'];
	        }
			array_multisort($sortkeys , SORT_DESC , $rsmpy_info);

		    for($i=0;$i<count($rsmpy_info);$i++){
				$outdata = "<li><span style='color:#FF3300'><a href=\"javascript:scpy('".$rsmpy_info[$i]['py_id']."');\">收藏</a></span><span><a href=\"javascript:copyText('".$rsmpy_info[$i]['py_content']."');\">".$tag."</a></span>".$rsmpy_info[$i]['py_content']."</li>";
				$Toutdata == "" ? $Toutdata = $outdata : $Toutdata=$Toutdata.$outdata;
			}


		}else{
			$Toutdata = "<li style='color:#FF3300'>抱歉，没有内容哦！！！</li>";
		}
		echo "<div class='commenbrbb'><ul>".$Toutdata."</ul></div>";
	}


	//我的评语库信息查看
	function mypyComment(){
		$client_type = $this->user['client_type'];
		$client_type==1? $blnJurisdiction = true : $blnJurisdiction = false;
		$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');	    
		$uid = $this->getCookieAccount();
		$rsmpy_info = $mMypyCollect->getMyPycollectByaccount($uid);
		$new_rsmpy_info = &$rsmpy_info[$uid];
		unset($rsmpy_info);

		$class_code = $this->objInput->getInt('class_code');
		$this->assign('class_code',$class_code);
		foreach($new_rsmpy_info as $key=>&$val){
			$val['add_date'] = date('Y-m-d H:i:s',$val['add_time']);
		}
		$this->assign('rsmpy_info',$new_rsmpy_info);
		
		$this->display('mypyComment');
	}

	//删除我的评语
	function mypydelete(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$pyid = $this->objInput->getInt('pyid');
		if(!empty($pyid)){
			$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');	    
			$mMypyCollect->delMyCollect($pyid);
			echo "suucess";exit;
			
		}else{
			echo "fail";exit;
		}
	}


	//我的评语库成绩使用
	function mypyCommentOpen(){
		$client_type = $this->user['client_type'];
		$rowsid = $this->objInput->getInt('rowsid');
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');	
		$uid = $this->getCookieAccount();    
		$rsmpy_info = $mMypyCollect->getMyPycollectByaccount($uid);
		$new_rsmpy_info = &$rsmpy_info[$uid];
		unset($rsmpy_info);
		$this->assign('rowsid',$rowsid);
		$this->assign('rsmpy_info',$new_rsmpy_info);
		
		$this->display('mypyCommentOpen');
	}


	//收藏系统评语到我的评语库
	function scpyContentData(){
		$client_type = $this->user['client_type'];
		$client_type==1 ? $blnJurisdiction = true : $blnJurisdiction = false;
		$pyid = $this->objInput->getInt('pyid');
		$mMypyCollect = ClsFactory::Create('Model.mMypyCollect');
		$uid = $this->getCookieAccount();
		$rsmpy_info = $mMypyCollect->getMyPycollectByaccount($uid);
		$new_rsmpy_info = &$rsmpy_info[$uid];
		if(!empty($new_rsmpy_info)){
			if(count($new_rsmpy_info)>=self::PYCOUNT){
				echo "moreerror";exit;//最多收藏30个评语
			}
		}
		if(!empty($pyid)){
			$mPyInfo = ClsFactory::Create('Model.mPyInfo');	    
			$new_rsmpy_info = $mPyInfo->getPyInfoById($pyid);
			if(!empty($new_rsmpy_info)) {
			    $py_content = array_shift($new_rsmpy_info);
				$data['py_content']=$py_content['py_content'];
				$data['add_time']= time();
				$data['client_account']=$uid;
				$mMypyInfo = $mMypyCollect->addMyPyCollect($data, true);
				if($mMypyInfo){
					echo "suucess";exit;
				}else{
					echo "fail";exit;
				}
			}else{
				echo "fail";exit;
			}
		}else{
			echo "fail";exit;
		}
	}
/*评语大师部分应用*******************************************************************************/
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