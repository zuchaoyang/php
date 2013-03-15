<?php
class ClassnavigaAction extends OaController{
	const classnum = 2;
	public function _initialize() {
	    parent::_initialize();
	    
		import("@.Common_wmw.Constancearr");
		import("@.Common_wmw.Pathmanagement_sns");
		import("@.Common_wmw.WmwString");
		
		//判断用户是否登录
		$this->assign('uid', $this->user['client_account']);
	}
	public function index(){
		$school_info = $this->user['school_info'];
		if(empty($school_info)){
			echo "没有学校信息";
			return false;
		}
		
		$grade_id = $this->objInput->getInt('gradeid');

		$school_id = key($school_info);
		$grade_list = $this->gradelists($school_info[$school_id]['school_type'], $school_info[$school_id]['grade_type']);
		if(empty($grade_id)){
			$grade_id = key($grade_list);
		}
		$class_list = $this->classlist($school_id,$grade_id);
		
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$class_list1 = array_slice($class_list,0,self::classnum,true);
		foreach($class_list1 as $class_code=>&$val){
			$arrInfoData = $this->loadClassFeedData($val['class_code'], $lastUpdTime, 0, 3);
			$newarr_talkInfo[$val['class_code']] =$arrInfoData['feed'];
			$num[$val['class_code']] = $arrInfoData['num'];
			unset($arrInfoData);
		}
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('num',$num);
		$this->assign('nextLlimit',self::classnum);
		$this->assign('grade_id',$grade_id);
		$this->assign('gradelist',$grade_list);
		$this->assign('school_id',$school_id);
		$this->assign('classlist',$class_list);
		
		$this->display("classnaviga");
	}
	/*
	 * 2012-06-29 临时添加
	 */
	public function classfeed(){
		$school_info = $this->user['school_info'];
		if(empty($school_info)){
			echo "没有学校信息";
			return false;
		}
		$grade_id = $this->objInput->getInt('grade_id');
		$school_id = key($school_info);
		$grade_list = $this->gradelists($school_info[$school_id]['school_type'], $school_info[$school_id]['grade_type']);
		if(empty($grade_id)){
			$grade_id = key($grade_list);
		}
		$class_list = $this->classlist($school_id,$grade_id);
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$class_list1 = array_slice($class_list,0,self::classnum,true);
		foreach($class_list1 as $class_code=>&$val){
			$arrInfoData = $this->loadClassFeedData($val['class_code'], $lastUpdTime, 0, 3);
			$newarr_talkInfo[$val['class_code']] =$arrInfoData['feed'];
			$num[$val['class_code']] = $arrInfoData['num'];
			unset($arrInfoData);
		}
		$this->assign('Talk_ContentList',$newarr_talkInfo);
		$this->assign('num',$num);
		$this->assign('nextLlimit',self::classnum);
		$this->assign('grade_id',$grade_id);
		$this->assign('gradelist',$grade_list);
		$this->assign('school_id',$school_id);
		$this->assign('classlist',$class_list);
		
		$this->display("classfeed");
	}
	
	//年级列表
	private function gradelists($schooltype, $grade_type){
		$grade_lists = Constancearr::class_grade_id();
		if($schooltype ==1){
		    if($grade_type == 1) {
		        $grade_list = array_slice($grade_lists,0,6,true);
		    }else{
		        $grade_list = array_slice($grade_lists,0,5,true);
		    }
		}elseif($schooltype ==2){
			$grade_list = array_slice($grade_lists,6,3,true);
			if($grade_type == 2) {
			    $grade_list[13] = $grade_lists[13];
			}
		}elseif($schooltype ==3){
			$grade_list = array_slice($grade_lists,9,3,true);
		}
		return !empty($grade_list)?$grade_list:false;
	}
	
	//通过年级得到年级的所有班主任
	public function classlist($schoolid,$gradeid){
		$mUser = ClsFactory::Create('Model.mUser');	    
		$mClientInfo  = ClsFactory::Create('Model.mClassInfo');
        $filters = array(
            'grade_id'=>$gradeid
        );
        $clientClassInfo = $mClientInfo->getClassInfoBySchoolId($schoolid,$filters);
        foreach ($clientClassInfo[$schoolid] as $classCode=>$clientinfo) {
                $clientInfo[$clientinfo['class_code']] = $clientinfo;
                $uids[] = $clientinfo['headteacher_account'];
        }
        $tercherInfo = $mUser->getUserBaseByUid($uids);
        foreach ($tercherInfo as $uid=>$userinfo) {
            if(in_array($userinfo['client_account'],$uids)){
                $userName[$userinfo['client_account']] = $userinfo['client_name'];
                $head_url = Pathmanagement_sns::getHeadImg($userinfo['client_account']) . $userinfo['client_headimg'];
                if(!file_exists(WEB_ROOT_DIR.$head_url)){
                	$head_url = $userinfo['client_headimg_url'];
                }
                $head_pic[$userinfo['client_account']] = $head_url;
            }
        }
        foreach ($clientInfo as $classcode=>$classinfo) {
            $classInfom[$classinfo['class_code']] = array(
                'client_account'=>$classinfo['headteacher_account'],
                'headTercherName'=>$userName[$classinfo['headteacher_account']],
            	'headTercherPic'=>$head_pic[$classinfo['headteacher_account']],
                'class_name'=>$classinfo['class_name'],
	            'school_id'=>$classinfo['school_id'],
	            'class_code'=>$classinfo['class_code'],
	            'grade_id'=>$classinfo['grade_id']
            );
        }
        return !empty($classInfom)?$classInfom:false;
	}
	public function classmove(){
		$school_id = $this->objInput->getInt('school_id');
		$grade_id = $this->objInput->getInt('grade_id');
		$nextLlimit = $this->objInput->getInt('nextLlimit');
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$class_list = $this->classlist($school_id,$grade_id);
		$class_list1 = array_slice($class_list,$nextLlimit,2,true);
		foreach($class_list1 as $class_code=>&$val){
			$arrInfoData = $this->loadClassFeedData($val['class_code'], $lastUpdTime, 0, 3);
			$newarr_talkInfo[$val['class_code']] = $arrInfoData['feed'];
			$num[$val['class_code']] = $arrInfoData['num'];
			unset($arrInfoData);
		}
		$classmove_str = '';
		if(!empty($newarr_talkInfo)){
		    foreach($newarr_talkInfo as $key1=>&$val1){
			$classmove_str .= '<ul class="bjdh_ul"><a href="javascript:;" class="bjdt" onclick="move(\''.$key1.'\')" id="bjdt'.$key1.'">班级动态</a><a href="javascript:;" class="bjgg" id="bjgg'.$key1.'" onclick="ggao(\''.$key1.'\')">班级公告</a><a href="javascript:;" class="bjgg" id="bjzy'.$key1.'" onclick="work(\''.$key1.'\')">班级作业</a></ul>';
	        $classmove_str.='<!--bjdh_main-->
	        <div class="bjdh_main">
	          <h4>
	           <img src="'.IMG_SERVER.'/Public/oa/images/pic06.gif" /><a href="/Homeclass/Classspace/index/class_code/'.$key1.'" target="blank">访问班级</a>&nbsp;&nbsp;<span>'.$class_list[$key1]['class_name'].'&nbsp;&nbsp;人数：'.$num[$key1].'人&nbsp;&nbsp;班主任：'.$class_list[$key1]['headTercherName'].'</span>
	          </h4>';
	          $classmove_str.='<table border="0" cellspacing="0" cellpadding="0" class="bjdh_tab" id="move'.$key1.'">';
	          if($val1 != ''){
	          	foreach($val1 as $key2=>&$val2){
	          		$classmove_str.='<tr>
		            <td width="10%"><img ';
	          		if($val2['client_headimg_url'] != ''){
	          			$classmove_str.='src="'.$val2['client_headimg_url'].'"';
	          		}else{
	          			$classmove_str.='src="'.IMG_SERVER.'/Public/oa/images/pic05.jpg"';
	          		} 
	          		$classmove_str.='width="60" height="60"/></td>
		            <td width="10%"><span>'.$val2['client_name'].'</span></td>
		            <td width="12%">'.$val2['feed_title'].'</td>
		            <td width="50%"><div style="display:block;clear:both;width:350px;word-wrap:break-word;word-break:break-all;"><span>'.$val2['feed_name'].'</span></div></td>
		            <td width="18%" align="right"><span class="gray">'.$val2['feed_upd_time'].'</span></td>
		          	</tr>';
	          	}
	          	
	          }else{
	          	$classmove_str.='<tr>
	            	<td>本班没用动态</td>
	          		</tr>';
	          }
	          $classmove_str.='</table>
			   <div class="bjdh_bjgg" style="display:none;" id="ggao'.$key1.'"></div>
	           <div class="bjdh_bjgg" style="display:none;" id="work'.$key1.'"></div>
	           <input type="hidden" id="subid'.$key1.'"/>
        	   <input type="hidden" id="class'.$key1.'" value="'.$key1.'"/>
	           <div class="clear"></div></div>';
			}
		}

		
		$nextLlimitValue = $nextLlimit+self::classnum;
		
		if(!empty($classmove_str)) {
			$classmove_str .= "<script>document.getElementById('nextLlimit').value=".$nextLlimitValue.";</script>";
		}
		echo $classmove_str;
	}
	public function oneclassmove(){
		//上个月的时间戳
		$result['todayTime'] = time();
		$result['last30Times'] = $result['todayTime'] - 30*86400;
        $result['last30'] = date("Y-m-d H:i:s",$result['last30Times']);
		$lastUpdTime = time()-$result['last30Times'];
		$class_code = $this->objInput->getInt('class_code');
		$arrInfoData = $this->loadClassFeedData($class_code, $lastUpdTime, 0, 3);
		$newarr_talkInfo = $arrInfoData['feed'];
		$classmove_str = '';
        if($arrInfoData['feed'] != ''){
          foreach($arrInfoData['feed'] as $key2=>&$val2){
	          $classmove_str.='<tr><td width="10%"><img ';
	          if($val2['client_headimg_url'] != ''){
	          	$classmove_str.='src="'.$val2['client_headimg_url'].'"';
	          }else{
	          	$classmove_str.='src="'.IMG_SERVER.'/Public/oa/images/pic05.jpg"';
	          } 
	          $classmove_str.='width="60" height="60"/></td>
	            <td width="10%"><span>'.$val2['client_name'].'</span></td>
	            <td width="12%">'.$val2['feed_title'].'</td>
	            <td width="50%"><div style="display:block;clear:both;width:350px;word-wrap:break-word;word-break:break-all;"><span>'.$val2['feed_name'].'</span></div></td>
	            <td width="18%"align="right"><span class="gray">'.$val2['feed_upd_time'].'</span></td>
	          </tr>';
          }
        }else{
          $classmove_str.='<tr><td>本班没用动态</td></tr>';
        }
        $move_content = array('content'=>$classmove_str);
        $jsonstr = json_encode($move_content);
		echo $jsonstr;
	}
	public function loadClassFeedData($class_code, $lastUpdTime, $offset, $length){
		$mClassInfo = ClsFactory::Create('Model.mClassInfo');
		$loginid = $this->user['client_account'];

	    //有班级的话，跳转到班级首页，否则跳转到个人首页
        if(empty($class_code)){
            return false;
        }
	    //统计班级成员,client_type in(0,1)
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>array(CLIENT_TYPE_STUDENT , CLIENT_TYPE_TEACHER)));
	    $mClassInfo = ClsFactory::Create('Model.mClassInfo');
	    $clientclass_list = $clientclassarr[$class_code];
	    $classinfo = $mClassInfo->getClassInfoBaseById($class_code);
	    $stu_count = 0;
	    foreach($clientclassarr[$class_code] as $key3=>&$val3){
	    	if($val3['client_type'] == 0){
	    		$stu_count++;
	    	}
	    }
	    unset($clientclassarr);
	    
		$class_account = array();
		$class_account[$loginid] = $loginid;
		foreach($clientclass_list as $key=>&$val){
			$class_account[$val['client_account']] = intval($val['client_account']);
		} 
		
		$mFeed = ClsFactory::Create('Model.mFeed');
		$class_code = intval($class_code);
		$lastUpdTime = intval($lastUpdTime);
		$offset = intval($offset);
		$length = intval($length);
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
		$arrlist['num'] = $stu_count;
		return $arrlist;
	}
	//班级公告
	public function classggao(){
		$class_code = $this->objInput->getInt('class_code');
		//获取班级下面的公告信息
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJTG)));

	    //数据较大，引用传值，避免内存瞬间过大
	    $newsinfo_list = & $newsinfoarr[$class_code];
	    //分离班级的公告和作业信息
	    $classnoticelist = array();
	    if(!empty($newsinfo_list)) {
	        foreach($newsinfo_list as $news_id=>$news) {
				 $news['news_title'] = cutstr($news['news_title'],20);
				 $news['news_content'] = cutstr($news['news_content'],220);
                 $classnoticelist[$news_id] = $news;
	        }
	        //注意unset的顺序
	        unset($newsinfo_list , $newsinfoarr);
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
			$newnoticelist = array_splice($classnoticelist,0,1);
			$newnoticelist = array_shift($newnoticelist);
			
			$classnoticefirst = array(
				'news_title'=>$newnoticelist['news_title'],
				'news_content'=>WmwString::unhtmlspecialchars($newnoticelist['news_content']),
				'add_date'=>$newnoticelist['add_date'],
				'add_account_name'=>$newnoticelist['client_name'],
			);
			$jsonstr = json_encode($classnoticefirst);
			echo $jsonstr;
			return false;
		}
		return false;
	}
	//班级作业
	public function classwork(){
		$class_code = $this->objInput->getInt('class_code');
		//获取班级下面的作业信息
	    $mNewsInfo = ClsFactory::Create('Model.mNewsInfo');
	    $newsinfoarr = $mNewsInfo->getNewsInfoByClassCode($class_code , array("news_type" => array(NEWS_INFO_BJZY)));
	    $subjectinfolist = $this->getTeacherSubjectList($class_code);
	    $new_work_by_subid = array();
	    if(!empty($subjectinfolist)){
		    $subjectinfolist_str = '<p><select id="select'.$class_code.'" onchange="javascript:select(\''.$class_code.'\');">';
		    $sub_ids = array();
		    $new_work_content = '';
		    if(!empty($subjectinfolist)){
			    foreach($subjectinfolist as $key=>&$val){
			    	$subjectinfolist_str .= '<option value="'.$val['subject_id'].'">&nbsp;'.$val['subject_name'].'&nbsp;';
			    	$sub_ids[$val['subject_id']] = $val['subject_id'];
			    	foreach($newsinfoarr[$class_code] as $key1=>&$val1){
			    		if($val['subject_id'] == $val1['subject_id']){
			    			$sortkeys[$val['subject_id']][$val1['news_id']]=$val1['news_id'];
				    		$new_work_by_subid[$val['subject_id']][$val1['news_id']] = $val1;
				    		unset($newsinfoarr[$class_code][$key1]);
			    		}
			    	}
			    }
			    $subjectinfolist_str .='</select></p>';
			    unset($newsinfoarr,$subjectinfolist);
	    		foreach($sub_ids as $key3=>&$val3){
	    			if(empty($new_work_by_subid)){
	    				$new_work_content .= '<p class="bjzy_title" id="subcon'.$class_code.$key3.'" style="display:none;">暂无作业</p>';// style="display:none;"	
	    			}elseif(empty($new_work_by_subid[$val3])){
	    				$new_work_content .= '<p class="bjzy_title" id="subcon'.$class_code.$key3.'" style="display:none;">暂无作业</p>';// style="display:none;"	
	    			}else{
				    	array_multisort($sortkeys[$val3] , SORT_DESC , $new_work_by_subid[$val3]);
					    if(empty($new_work_by_subid[$val3][0]['news_content'])){
					    	$new_work_content .= '<p class="bjzy_title" id="subcon'.$class_code.$key3.'" style="display:none;">暂无作业</p>';// style="display:none;"	
					    }else{
					    	$new_work_content .= '<p class="bjzy_title" id="subcon'.$class_code.$key3.'" style="display:none;">'.WmwString::delhtml(WmwString::unhtmlspecialchars($new_work_by_subid[$val3][0]['news_content'])).'</p>';
					    }
	    			}
			    }
			    $sub_ids_str = implode(',',$sub_ids);
			    unset($sub_ids);
		    }else{
		    	$new_work_content = '<p style="color:blue;" id="subconno">本班还没有作业<p>';
		    	$sub_ids_str = 'no';
		    }
	    }else{
	    	$new_work_content = '<p style="color:blue;" id="subconno">本班还没有作业<p>';
		    $sub_ids_str = 'no';
	    }
	    $result = array('select'=>$subjectinfolist_str,'content'=>$new_work_content,'subids'=>$sub_ids_str);
	    echo json_encode($result);
	}
	/**
	 * 通过班级编号获取班级的科目信息
	 * 如果已经拿到了教师的账号信息就不要调用该方法
	 * @param $class_code
	 */
	private function getTeacherSubjectList($class_code) {
	    if(empty($class_code)) {
	        return false;
	    }
	     //获取班级老师的科目信息
	    $mClientClass = ClsFactory::Create('Model.mClientClass');
	    $clientclassarr = $mClientClass->getClientClassByClassCode($class_code , array('client_type'=>CLIENT_TYPE_TEACHER));
	    $clientclasslist = & $clientclassarr[$class_code];
	    if(!empty($clientclasslist)) {
	        $teacheruids  = array_unique(array_keys($clientclasslist));
	        $mSubjectInfo = ClsFactory::Create('Model.mSubjectInfo');
	        $subjectinfolist = $mSubjectInfo->getSubjectInfoByTeacherUidWithName($teacheruids, $class_code);
	    }

	    return !empty($subjectinfolist) ? $subjectinfolist : false;
	}
};
