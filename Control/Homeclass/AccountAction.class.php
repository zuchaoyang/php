<?php
class AccountAction extends SnsController{

    public function _initialize(){
        parent::_initialize();
		import("@.Common_wmw.Pathmanagement_sns");
		
		$this->assign('chanelid',"chanel1");
	}
	
	
	/*
	 *新功能引导页面学生
	 */
	public function index_guide() {
	    $class_code = $this->objInput->getStr('class_code');
	    if(empty($class_code)) {
	        $class_code = key($this->user['class_info']);
	    }
	    
	    $this->assign('class_code',$class_code);
	    
	    $this->display(WEB_ROOT_DIR . "/View/Template/Public/zuoye/student/student_work.html");
	}
	
	
	public function index(){
		$class_code = key($this->user['class_info']);
		if(empty($class_code)){
			$this->redirect('../Homepage/Homepage/index');
			exit;
		}
		$mClassInfo = ClsFactory::Create('Model.mClassInfo');
		$class_info = $mClassInfo->getClassInfoById($class_code);
		$mUser = ClsFactory::Create('Model.mUser');	    
		$loginid = $this->user['client_account'];

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
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
 		
		$lastUpdTime = time()-$result['last30Times'];
		$offset = 0;
		$length = 100;
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
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/hoemworkview/workid/{$talkval['news_id']}";
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
								
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/classJournalview/log_id/{$talkval['log_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['log_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
					}
					unset($feedlist,$commentlist,$CommentNums);
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
				if(!empty($albuminfo)){
					foreach($albuminfo as $talkkey=>&$talkval){
						foreach($arrlist['feed'] as $arrlist_key=>$arrlist_val){
							if($arrlist_val['res_id'] == $talkval['album_id'] && $arrlist_val['res_type']==$key2){
								$arrlist['feed'][$arrlist_key]['CommentNums'] = 0;
								$arrlist['feed'][$arrlist_key]['feed_type'] = '相册';
								if($arrlist_val['res_stats'] == FEED_NEW){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '添加了新相册';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] = date('Y-m-d H:i:s',$talkval['add_date']);
								}elseif($arrlist_val['res_stats'] == FEED_UPD){
									$arrlist['feed'][$arrlist_key]['feed_title'] = '修改了相册';
									$arrlist['feed'][$arrlist_key]['feed_upd_time'] =  date('Y-m-d H:i:s',$talkval['upd_date']);
								}
								$arrlist['feed'][$arrlist_key]['feed_url'] = "/Homeclass/Class/xcmanager/class_code/{$class_code}/xcid/{$talkval['album_id']}";
								$arrlist['feed'][$arrlist_key]['feed_name'] = $talkval['album_name'];
								$arrlist['feed'][$arrlist_key]['client_name'] = $client_infos[$talkval['add_account']]['client_name'];
								$arrlist['feed'][$arrlist_key]['client_account'] = $client_infos[$talkval['add_account']]['client_account'];
								$arrlist['feed'][$arrlist_key]['client_headimg_url'] = $client_infos[$talkval['add_account']]['client_headimg_url'];
							}
						}
						
					}
					unset($feedlist,$CommentNums);
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
					unset($feedlist,$commentlist,$CommentNums);
				}
			}
		}
		unset($client_infos,$feedlist,$arrlist_type);
		$newarr_talkInfo = array_slice($arrlist['feed'], 0,WMW_XXS_LIMIT);
		$this->assign('Talk_ContentList',$newarr_talkInfo);



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
					 $news[news_title] = cutstr($news['news_title'],20);
					 $news[news_content] = cutstr($news['news_content'],220);

	                $classnoticelist[$news_id] = $news;
	            } elseif($news['news_type'] == NEWS_INFO_BJZY) {
	                //作业信息的时间过滤
	                if(!empty($add_date)) {
	                    if(date("Y-m-d" , strtotime($add_date)) >= date("Y-m-d" , strtotime($news['add_date']))&& date("Y-m-d" , strtotime($add_date)) <= date('Y-m-d',strtotime($news['expiration_date']))){
	                        $news["news_content"] = $news["news_content"]."（到期时间：".date('Y-m-d',strtotime($news['expiration_date']))."）";
	                        $homeworklist[$news_id] = $news;
	                    }
	                } else {
	                    $homeworklist[$news_id] = $news;
	                }
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
				$homeworklist[$news_id][expiration_date] = date("Y-m-d",strtotime($homeworklist[$news_id]['expiration_date']));

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
			$this->assign('news_title' , $newnoticelist[0][news_title]);
			$this->assign('news_content' , $newnoticelist[0][news_content]);
			$this->assign('add_date' , $newnoticelist[0][add_date]);
			$this->assign('add_account_name' , $newnoticelist[0][client_name]);
		}
		$this->assign('class_code',$class_code);
		$this->assign('nextLlimit',WMW_XXS_LIMIT);
	    $this->assign('homeworklist' , $homeworklist) ;        //班级作业
	    
		$this->display('accountIndex');

	}
	





}