<?php
class LabelclassAction extends OaController {
	const LENGTH = 10;
	const LABEL_LENGTH = 42;
	public function _initialize() {
	    parent::_initialize();
	    
		import("@.Common_wmw.WmwString");
		
		$this->assign('uid', $this->user['client_account']);
	}
	
	//通过学校$school_id得到这个学校的标签列表信息
	public function taglist(){
        $userId = $this->user['client_account'];
        $schoolModel = ClsFactory::Create('Model.mSchoolInfo');
        $schoolInfo = $schoolModel->getSchoolInfoByNetManagerAccount($userId);
        if(empty($schoolInfo)){
            $school_info = $this->user['school_info'];
        }
		$page = $this->objInput->getInt('page');
		$page = max($page,1);
		$offset = ($page-1)*self::LABEL_LENGTH;
		$prepage = max(1,$page-1);
		if(empty($school_info)){
			echo "您不属于任何学校，系统即将推出";
			return false;
		}
		$school_id = key($school_info);
		$mTaskTag=ClsFactory::Create('Model.mTaskTags');
		$taglist = $mTaskTag->getTaskTagBySchoolId($school_id);
		array_multisort($taglist[$school_id] , SORT_DESC);
		$newtaglist = array_slice($taglist[$school_id], $offset,self::LABEL_LENGTH+1);
		if(!empty($newtaglist)){
			$task_tag_rel_count = count($newtaglist);
			if($task_tag_rel_count>self::LABEL_LENGTH){
				array_pop($newtaglist);
				$endpage = max($page,$page+1);
			}else{
				$endpage = $page;
			}
		}
		$this->assign('prepage',$prepage);
		$this->assign('currentpage',$page);
		$this->assign('endpage',$endpage);
		$this->assign('taglist',$newtaglist);
		
		$this->display("taglist");
	}
	//通过标签$tag_id得到关于此表签的文章
	public function tagtasklist(){
		$tag_id = $this->objInput->getInt('tag_id');
		$page = $this->objInput->getInt('page');
		$page = max($page,1);
		$offset = ($page-1)*self::LENGTH;
		if(empty($tag_id)){
			echo '标签tag_id为空，不可操作';
			return false;
		}
		$prepage = max(1,$page-1);
		//得到标签信息
		$mTaskTag=ClsFactory::Create('Model.mTaskTags');
		$taginfo = $mTaskTag->getTaskTagById($tag_id);
		//通过标签得到工作id及task_id
		$mTaskTagRelation = ClsFactory::Create("Model.mTaskTagRelation");
		$task_tag_rel_list = $mTaskTagRelation->getTaskTagRelationByTagId($tag_id, $offset, self::LENGTH+1);

		if(!empty($task_tag_rel_list[$tag_id])){
			$task_tag_rel_count = count($task_tag_rel_list[$tag_id]);
			if($task_tag_rel_count>self::LENGTH){
				array_pop($task_tag_rel_list[$tag_id]);
				$endpage = max($page, $page+1);
			}else{
				$endpage = $page;
			}
		}
		
		if(!empty($task_tag_rel_list)){
			foreach($task_tag_rel_list[$tag_id] as $relkey=>&$relval){
				$task_ids[] = $relval['task_id'];
			}
			if(!empty($task_ids)){
				//通过工作id及task_id得到工作信息task_info
				$mTask = ClsFactory::Create("Model.mTask");
				$task_list = $mTask->getTaskById($task_ids);
				//得到所有系统工作类型$task_type_list
				$mTaskType = ClsFactory::Create("Model.mTaskType");
				$task_type_list = $mTaskType->getTaskTypeSystemAll();
				//通过工作id及task_id得到回复内容
				$mTaskPush = ClsFactory::Create("Model.mTaskPush");
				$task_reply_list = $mTaskPush->getTaskPushByTaskId($task_ids);
				foreach($task_reply_list as $key=>&$val){
					if(!empty($val)){
						foreach($val as $key1=>&$val1){
							if($val1['is_replied'] == 1){
								$task_list[$key]['task_reply_con'] = 1;//代表已回复
							}elseif($val1['is_replied'] == 0){
								$task_list[$key]['task_reply_con'] = 2;//代表未回复
							}
						}
					}else{
						$task_list[$key]['task_reply_con'] = 2;//代表未回复
					}
				}
				unset($task_ids);
				//将工作内容进行处理，标识处理，排序，分页
				foreach($task_list as $taskkye=>&$taskval){
					$task_conten = WmwString::unhtmlspecialchars($taskval['task_content']);
                    $task_conten = WmwString::delhtml($task_conten);
                    $task_conten = addslashes ($task_conten);
                    $task_title = WmwString::unhtmlspecialchars($taskval['task_title']);
                    $task_title = WmwString::delhtml($task_title);
                    $task_title = addslashes ($task_title);
					$taskval['sub_task_content']= strip_tags(cutstr(WmwString::unhtmlspecialchars($task_conten),200,true));
					$taskval['task_title']= strip_tags(cutstr(WmwString::unhtmlspecialchars($task_title),10,true));
					$taskval['add_date'] = date('Y年m月d日',$taskval['add_time']);
					$taskval['task_type_name'] = $task_type_list[$taskval['task_type']]['type_name'];
				}
			}else{
				$no = "此学校的标签的工作id不存在";
				$this->assign('no',$no);
			}
		}else{
			$no = "此表签的工作关系表中无数据";
			$this->assign('no',$no);
		}
		$this->assign('prepage',$prepage);
		$this->assign('currentpage',$page);
		$this->assign('endpage',$endpage);
		$this->assign('taginfo',$taginfo[$tag_id]);
		$this->assign('taskinfo',$task_list);
		
		$this->display('tagtasklist');
	}
//按照个人标签分类
    public function persontaglist(){
    	$page = $this->objInput->getInt('page');
		$page = max($page,1);
		$offset = ($page-1)*self::LABEL_LENGTH;
		$prepage = max(1,$page-1);
        $mTaskPush = ClsFactory::Create("Model.mTaskPush");
        $taskpush_arr = $mTaskPush->getTaskPushByUid($this->uid, 'push_id desc');
        $taskpush_list = & $taskpush_arr[$this->uid];
        $task_ids = array();
        if(!empty($taskpush_list)) {
            foreach($taskpush_list as $key=>$val){
                $task_id = intval($val['task_id']);
                if($task_id > 0) {
                   $task_ids[] = $task_id;
                }
                unset($taskpush_list[$key]);
            }
        }
        $mTask = ClsFactory::Create("Model.mTask");
        $tasklist = $mTask->getTaskById($task_ids);
        if(!empty($tasklist)){
            foreach($tasklist as $task_id=>$task){
               if(!empty($task["tag_ids"]))
               		$tag_ids .= $task['tag_ids'].",";
            }
        }
        $new_tag_arr = array_unique(explode(",",$tag_ids));
        
        array_multisort($new_tag_arr , SORT_DESC);
		$newtaglist = array_slice($new_tag_arr, $offset,self::LABEL_LENGTH+1);
		$mTag = ClsFactory::Create('Model.mTaskTags');
        $tag_list = $mTag->getTaskTagById($newtaglist);
		if(!empty($tag_list)){
			$task_tag_rel_count = count($tag_list);
			if($task_tag_rel_count>self::LABEL_LENGTH){
				array_pop($tag_list);
				$endpage = max($page,$page+1);
			}else{
				$endpage = $page;
			}
		}
		$this->assign('person','/person/person');
		$this->assign('prepage',$prepage);
		$this->assign('currentpage',$page);
		$this->assign('endpage',$endpage);
        $this->assign('taglist',$tag_list);
        
		$this->display("taglist");
    }
	//通过标签$tag_id得到关于此表签的个人的文章
	public function persontagtasklist(){
		$tag_id = $this->objInput->getInt('tag_id');
		$page = $this->objInput->getInt('page');
		$page = max($page,1);
		$offset = ($page-1)*self::LENGTH;
		if(empty($tag_id)){
			echo '标签tag_id为空，不可操作';
			return false;
		}
		$prepage = max(1,$page-1);
		//得到标签信息
		$mTaskTag=ClsFactory::Create('Model.mTaskTags');
		$taginfo = $mTaskTag->getTaskTagById($tag_id);
		
		$mTaskPush = ClsFactory::Create("Model.mTaskPush");
        $taskpush_arr = $mTaskPush->getTaskPushByUid($this->uid, 'push_id desc');
        $taskpush_list = & $taskpush_arr[$this->uid];
        $task_ids = array();
        if(!empty($taskpush_list)) {
            foreach($taskpush_list as $key=>$val){
                $task_id = intval($val['task_id']);
                if($task_id > 0) {
                   $task_ids[] = $task_id;
                }
                unset($taskpush_list[$key]);
            }
        }
        $mTask = ClsFactory::Create("Model.mTask");
        $task_tag_rel_list = $mTask->getTaskById($task_ids);
        $new_task_list = array();
        if(!empty($task_tag_rel_list)){
            foreach($task_tag_rel_list as $task_id=>$task){
               if(!empty($task["tag_ids"])){
               		$tag_ids = $task['tag_ids'];
               		$new_tag_arr = array_unique(explode(",",$tag_ids));
               		if(in_array($tag_id,$new_tag_arr)){
               			$new_task_list[$task_id] = $task;
               		}
               }
			   unset($task_tag_rel_list[$task_id]);
            }
        }
        array_multisort($new_task_list, SORT_DESC, true);
		$new_task_list1 = array_slice($new_task_list, $offset,self::LENGTH+1,true);
		if(!empty($new_task_list1)){
			$task_tag_rel_count = count($new_task_list1);
			if($task_tag_rel_count>self::LENGTH){
				array_pop($new_task_list1);
				$endpage = max($page,$page+1);
			}else{
				$endpage = $page;
			}
		}
		if(!empty($new_task_list1)){
			unset($task_ids);
			foreach($new_task_list1 as $relkey=>&$relval){
				$task_ids[] = $relval['task_id'];
			}
			if(!empty($task_ids)){
				$task_list = & $new_task_list1;
				//得到所有系统工作类型$task_type_list
				$mTaskType = ClsFactory::Create("Model.mTaskType");
				$task_type_list = $mTaskType->getTaskTypeSystemAll();
				//通过工作id及task_id得到回复内容
				$mTaskPush = ClsFactory::Create("Model.mTaskPush");
				$task_reply_list = $mTaskPush->getTaskPushByTaskId($task_ids);
				foreach($task_reply_list as $key=>&$val){
					if(!empty($val)){
						foreach($val as $key1=>&$val1){
							if($val1['is_replied'] == 1){
								$task_list[$key]['task_reply_con'] = 1;//代表已回复
							}elseif($val1['is_replied'] == 0){
								$task_list[$key]['task_reply_con'] = 2;//代表未回复
							}
						}
					}else{
						$task_list[$key]['task_reply_con'] = 2;//代表未回复
					}
				}
				unset($task_ids);
				//将工作内容进行处理，标识处理，排序，分页
				foreach($task_list as $taskkye=>&$taskval){
					$task_conten = WmwString::unhtmlspecialchars($taskval['task_content']);
                    $task_conten = WmwString::delhtml($task_conten);
                    $task_conten = addslashes ($task_conten);
                    $task_title = WmwString::unhtmlspecialchars($taskval['task_title']);
                    $task_title = WmwString::delhtml($task_title);
                    $task_title = addslashes ($task_title);
					$taskval['sub_task_content']= strip_tags(cutstr(WmwString::unhtmlspecialchars($task_conten),200,true));
					$taskval['task_title']= strip_tags(cutstr(WmwString::unhtmlspecialchars($task_title),10,true));
					$taskval['add_date'] = date('Y年m月d日',$taskval['add_time']);
					$taskval['task_type_name'] = $task_type_list[$taskval['task_type']]['type_name'];
				}
			}else{
				$no = "此学校的标签的工作id不存在";
				$this->assign('no',$no);
			}
		}else{
			$no = "此表签的工作关系表中无数据";
			$this->assign('no',$no);
		}
		$this->assign('person','/person/person');
		$this->assign('prepage',$prepage);
		$this->assign('currentpage',$page);
		$this->assign('endpage',$endpage);
		$this->assign('taginfo',$taginfo[$tag_id]);
		$this->assign('taskinfo',$task_list);
		
		$this->display('tagtasklist');
	}
}