<?php
/**
 * 说说部分的实现的管理，支持单个和批量处理
 * 注明：
 * 1. 承接C层和M层得部分功能，API对应的文件只是分担调度的问题，即API是调用的上下文;
 * 2. ClassMood和PersonMood与MoodInfo的关系是：持有的关系(不是继承);
 * 3. 业务覆盖的范围是:发布、删除、获取(单个获取和批量获取)
 * 4. 针对共享主键段的不同实体的数据库设计，实现的时候继承不是好的解决方案；继承是用来解决同类问题的不同
 *    实现方式的方式；此处涉及到的是不同的实体，并且是持有关系
 * 5. 结合关系和实体的负责业务的数据获取不在该类得考虑范围内，英爱
 * 6. 需要尝试去找到更好的解决办法；？
 * @author Administrator
 *
 */
class MoodInfo {
    private $mMood;
    
    public function __construct() {
        $this->mMood = ClsFactory::Create('Model.Mood.mMood');        
    }
    
    /**
     * 获取单个说说的相关信息
     * @param $mood_id
     */
    public function getMood($mood_id) {
        if(empty($mood_id)) {
            return false;
        }
        
        $mood_list = $this->getMoodBat($mood_id);
        $mood_info = & $mood_list[$mood_id];
        
        return !empty($mood_info) ? $mood_info : false;
    }
    
    /**
     * 批量获取说说的相关信息
     * @param $mood_ids
     */
    public function getMoodBat($mood_ids) {
        if(empty($mood_ids)) {
            return false;
        }
        
        $mood_list = $this->mMood->getMoodById($mood_ids);
        
        return $this->parseMood($mood_list);
    }
    
    /**
     * 解析mood相关的信息
     * @param $mood_list
     */
    public function parseMood($mood_list) {
        if(empty($mood_list)) {
            return false;
        }
        
        $mood_list = $this->appendUserInfo($mood_list);
        $mood_list = $this->formatMoodInfo($mood_list);
        
        return $mood_list;
    }
    
    
    /**
     * 发表说说信息
     * @param $mood_datas
     */
    public function addMood($mood_datas) {
        if(empty($mood_datas)) {
            return false;
        }
        
        $img_file = '';
        if(!empty($_FILES['pic']['name'])) {
            $img_file = $this->uploadPic('pic');            
        }
        $mood_datas['img_url'] = $img_file;
        
        return $this->mMood->addMood($mood_datas, true);
    }
    
    /**
     * 删除说说信息
     * @param $mood_id
     */
    public function delMood($mood_id) {
        if(empty($mood_id)) {
            return false;
        }
        
        //获取说说实体的相关信息
        $mood_list = $this->mMood->getMoodById($mood_id);
        $mood_info = & $mood_list[$mood_id];
        
        //删除说说实体
        if(!$this->mMood->delMood($mood_id)) {
            return false;
        }
        
        //清除说说的图片信息
        $img_url = $mood_info['img_url'];
        if(!empty($img_url)) {
            import('@.Common_wmw.Pathmanagement_sns');
            $file_name = Pathmanagement_sns::uploadMood() . pathinfo($img_url, PATHINFO_BASENAME);
            if(is_file($file_name)) {
                @ unlink($file_name);
            }
        }
        
        //清除说说的评论信息
        $mMoodComments = ClsFactory::Create('Model.Mood.mMoodComments');
        //因为函数的默认参数不是null，因此要获取全部的评论信息需要将limit参数设置为：null
        $comment_arr = $mMoodComments->getMoodCommentsByMoodId($mood_id, null, null, null);
        $comment_list = & $comment_arr[$mood_id];
        foreach($comment_list as $comment_id=>$comment) {
            $mMoodComments->delMoodComments($comment_id);
            unset($comment_list[$comment_id]);
        }
        
        return true;
    }
    
    /**
     * 文件上传
     */
    private function uploadPic($field = 'pic') {
        if(empty($field)) {
            $field = 'pic';
        }
        $options = array(
            'allow_type' => array(
                'jpg',
                'gif',
                'png'
            ),
            'renamed' => true,
            'attachmentspath' => Pathmanagement_sns::uploadMood(),
            'newname' => 'mood_' . time() . "" . rand(),
        );
        $upload = ClsFactory::Create('@.Common_wmw.WmwUpload');
        $file_attrs = $upload->upfile($field, $options);
        if(empty($file_attrs)) {
            return '';
        }
        
        return pathinfo($file_attrs['filename'], PATHINFO_BASENAME);
    }
    
	/**
     * 追加说说的个人信息
     * @param $mood_info
     */
    private function appendUserInfo($mood_list) {
        if(empty($mood_list)) {
            return false;
        }
        
        $uids = array();
        foreach($mood_list as $mood_id=>$mood) {
            $uid = $mood['add_account'];
            $uids[$uid] = $uid;
        }
        
        $mUser = ClsFactory::Create('Model.mUser');
        $user_list = $mUser->getUserBaseByUid($uids);
        if(!empty($user_list)) {
            foreach($mood_list as $mood_id=>$mood) {
                $uid = $mood['add_account'];
                $mood['user_info'] = isset($user_list[$uid]) ? $user_list[$uid] : false;
                $mood_list[$mood_id] = $mood;
            }
        }
        
        return $mood_list;
    }
    
    /**
     * 格式化说说的信息
     * @param $mood_info
     */
    private function formatMoodInfo($mood_list) {
        if(empty($mood_list)) {
            return false;
        }
        
        foreach($mood_list as $mood_id=>$mood_info) {
            //格式化时间
            $mood_info['add_time_format'] = date('Y.m.d', $mood_info['add_time']);
            //解析表情
            import('@.Common_wmw.WmwFace');
            $mood_info['content'] = WmwFace::parseFace($mood_info['content']);
            //解析图片信息,并将图片信息追加到内容的后边
            $photo_mark = '#分享照片#';
            $mood_info['content'] = str_replace($photo_mark, '', $mood_info['content']);
            
            $mood_list[$mood_id] = $mood_info;
        }
        
        return $mood_list;
    }
    
}