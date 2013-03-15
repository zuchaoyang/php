<?php

class PersonMoodAction extends SnsController {
    /**
     * 班级说说的显示页面
     */
    public function show() {
        $client_account = $this->objInput->getStr('client_account');
        $mood_id        = $this->objInput->getStr('mood_id');
        
        if(empty($client_account)) {
            $client_account = $this->user['client_account'];
        }
        
        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_info = $MoodApi->getPersonMood($client_account, $mood_id);
        
        //dump($mood_info);
        
        
        if(empty($mood_info)) {
            //$this->showError('说说不存在或已删除!', '/Sns/');
        }
        
        $this->assign('client_account', $client_account);
        $this->assign('user', $this->user);
        $this->assign('mood_info', $mood_info);
        $this->display('person_show');
    }
    
    /**
     * 显示个人说说列表
     */
    public function mood_list() {
        
        $client_account = $this->user['client_account']; 
        $mMoodPersonRelation = ClsFactory::Create('Model.Mood.mMoodPersonRelation');
        $stat_list = $mMoodPersonRelation->statPersonMood($client_account);
        $mood_nums = & $stat_list[$client_account];
        
        $this->assign('mood_nums', $mood_nums);
        $this->display('person_list');
    }
    
    /**
     * 获取个人说说列表信息
     */
    public function getPersonMoodListAjax() {
        $page = $this->objInput->getInt('page');
        
        $page = max(1, $page);
        
        $perpage = 10;
        $offset = ($page - 1) * $perpage;
        
        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_list = $MoodApi->getPersonMoodList($this->user['client_account'], $offset, $perpage);
        
        //dump($mood_list);
        
        if(empty($mood_list)) {
            $this->ajaxReturn(null, '获取失败!', -1, 'json');
        }
        $this->ajaxReturn($mood_list, '获取成功!', 1, 'json');
    }
    
    
 	/**
     * 发表个人说说
     */
    public function publishAjax() {
        $content = $this->objInput->postStr('content');
        
        if(empty($content)) {
            $this->ajaxReturn(null, '说说内容不能为空!', -1, 'json');
        }
        
        $mood_datas = array(
            'content' => $content,
            'add_account' => $this->user['client_account'],
            'add_time' => time(),
            'comments' => 0,
        );

        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_id = $MoodApi->addPersonMood($this->user['client_account'], $mood_datas);
        if(empty($mood_id)) {
            $this->ajaxReturn(null, '说说发表失败!', -1, 'json');
        }
        
        //获取说说的相关信息
        $mood_info = $MoodApi->getPersonMood($this->user['client_account'], $mood_id);
        
        $this->ajaxReturn($mood_info, '说说发表成功!', 1, 'json');
    }
    
    /**
     * 删除个人说说
     * 权限控制：
     * 1. 在个人空间，只有说说的发布用户用全新删除，和班级管理员没有关系
     */
    public function deletePersonMoodAjax() {
        $mood_id = $this->objInput->getStr('mood_id');
        
        $client_account = $this->user['client_account'];
        
        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_info = $MoodApi->getPersonMood($client_account, $mood_id);
        
        //只有发表用户有权限权限删除个人说说信息
        if($this->user['client_account'] != $mood_info['add_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除个人说说!', -1, 'json');
        }
        
        //删除说说信息
        if(!$MoodApi->delPersonMood($client_account, $mood_id)) {
            $this->ajaxReturn(null, '系统繁忙，删除失败!', -1, 'json');
        }
        
        //获取班级的下一个说说信息
        $next_mood = $this->getNextPersonMood($client_account, $mood_id);
        if(!empty($next_mood)) {
            $redirect_url = "/Sns/Mood/PersonMood/show/mood_id/" . $next_mood['mood_id'];
        } else {
            //todolist 跳转到个人空间首页
            $redirect_url = "/Sns/ClassIndex/Index/index";
        }
        
        $this->ajaxReturn(array('redirect_url' => $redirect_url), '删除成功!', 1, 'json');
    }
    
    /**
     * 获取班级说说对应的mood_id之后的说说信息
     * @param $class_code
     * @param $mood_id
     */
    private function getNextPersonMood($client_account, $mood_id) {
        if(empty($client_account) || empty($mood_id)) {
            return false;
        }
        
        $mMoodPersonRelation = ClsFactory::Create('Model.Mood.mMoodPersonRelation');
        $mood_arr = $mMoodPersonRelation->getMoodPersonRelationByClientAccount($client_account, "mood_id>'$mood_id'", 0, 1);
        $mood_list = & $mood_arr[$client_account];
        if(!empty($mood_list)) {
            $next_mood = reset($mood_list);
        }
        
        return !empty($next_mood) ? $next_mood : false;
    }
    
}