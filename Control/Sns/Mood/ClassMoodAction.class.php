<?php

class ClassMoodAction extends SnsController {
    /**
     * 班级说说的显示页面
     */
    public function show() {
        $class_code = $this->objInput->getInt('class_code');
        $mood_id    = $this->objInput->getStr('mood_id');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->showError('您暂时没有权限查看班级说说!', '/Sns/PersonIndex/Index/index');
        }
        
        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_info = $MoodApi->getClassMood($class_code, $mood_id);
        
        if(empty($mood_info)) {
            $this->showError('班级说说不存在或已删除!', '/Sns/ClassIndex/Index/index/class_code/' . $class_code);
        }
        
        $this->assign('class_code', $class_code);
        $this->assign('user', $this->user);
        $this->assign('mood_info', $mood_info);
        
        $this->display('class_show');
    }
    
 	/**
     * 发表班级说说
     */
    public function publishAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $content    = $this->objInput->postStr('content');
        
        if(empty($content)) {
            $this->ajaxReturn(null, '说说内容不能为空!', -1, 'json');
        }
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时不能在该班级发表说说!', -1, 'json');
        }
        
        $mood_datas = array(
            'content' => $content,
            'add_account' => $this->user['client_account'],
            'add_time' => time(),
            'comments' => 0,
        );

        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_id = $MoodApi->addClassMood($class_code, $mood_datas);
        if(empty($mood_id)) {
            $this->ajaxReturn(null, '班级说说发表失败!', -1, 'json');
        }
        
        import('@.Control.Api.FeedApi');
        $FeedApi = new FeedApi();
        $feed_id = $FeedApi->class_create($class_code, $this->user['client_account'], $mood_id, FEED_MOOD, FEED_ACTION_PUBLISH);
        
        $feed_info = $FeedApi->getFeedById($feed_id);        

        $this->ajaxReturn($feed_info, '班级说说发表成功!', 1, 'json');
    }
    
    /**
     * 删除班级说说
     */
    public function deleteClassMoodAjax() {
        $class_code = $this->objInput->getInt('class_code');
        $mood_id    = $this->objInput->getStr('mood_id');
        
        $class_code = $this->checkoutClassCode($class_code);
        if(empty($class_code)) {
            $this->ajaxReturn(null, '您暂时没有权限删除班级说说!', -1, 'json');
        }
        
        $MoodApi = ClsFactory::Create('@.Control.Api.MoodApi');
        $mood_info = $MoodApi->getClassMood($class_code, $mood_id);
        //班级管理有权限删除说说信息
        if(!$this->isClassAdmin($class_code) && $this->user['client_account'] != $mood_info['add_account']) {
            $this->ajaxReturn(null, '您暂时没有权限删除班级说说!', -1, 'json');
        }
        
        //删除说说信息
        if(!$MoodApi->delClassMood($class_code, $mood_id)) {
            $this->ajaxReturn(null, '系统繁忙，删除失败!', -1, 'json');
        }
        
        //获取班级的下一个说说信息
        $next_mood = $this->getNextClassMood($class_code, $mood_id);
        if(!empty($next_mood)) {
            $redirect_url = "/Sns/Mood/ClassMood/show/class_code/$class_code/mood_id/" . $next_mood['mood_id'];
        } else {
            $redirect_url = "/Sns/ClassIndex/Index/index/class_code/$class_code";
        }
        
        $this->ajaxReturn(array('redirect_url' => $redirect_url), '删除成功!', 1, 'json');
    }
    
    /**
     * 获取班级说说对应的mood_id之后的说说信息
     * @param $class_code
     * @param $mood_id
     */
    private function getNextClassMood($class_code, $mood_id) {
        if(empty($class_code) || empty($mood_id)) {
            return false;
        }
        
        $mMoodClassRelation = ClsFactory::Create('Model.Mood.mMoodClassRelation');
        $mood_arr = $mMoodClassRelation->getMoodClassRelationByClassCode($class_code, "mood_id>'$mood_id'", 0, 1);
        $mood_list = & $mood_arr[$class_code];
        if(!empty($mood_list)) {
            $next_mood = reset($mood_list);
        }
        
        return !empty($next_mood) ? $next_mood : false;
    }
    
}