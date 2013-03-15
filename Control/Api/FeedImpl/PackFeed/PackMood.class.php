<?php
import('@.Control.Api.FeedImpl.PackFeed.IPack', null, '.php');

class PackMood implements Ipack {
    /**
     * 从说说实体中获取动态的内容
     * @param $mood_id
     * @return array(
     * 		'feed_type'     ,
            'add_account'   ,
            'add_time'		,
            'title'			,
            'feed_content'  ,
            'from_id'		,
            'img_url'		,
            'timeline'		,
     * ) 
     */
    public function getFeedDatas($mood_id) {
        if(empty($mood_id)) {
            return false;
        }
        
        //获取说说实体信息
        $mMood = ClsFactory::Create('Model.Mood.mMood');
        $mood_list = $mMood->getMoodById($mood_id);
        $mood_info = & $mood_list[$mood_id];
        
        if(empty($mood_info)) return false;
        
        //从说说中获取相关的动态内容
        $content = $mood_info['content'];
        import('@.Common_wmw.WmwString');
        $content = WmwString::mbstrcut($content, 0, 255, 1, true);
        
        $feed_datas = array(
            'feed_type'     => FEED_MOOD,
            'add_account'   => $mood_info['add_account'],
            'add_time'		=> $mood_info['add_time'],
            'title'			=> "",
            'feed_content'  => $content,
            'from_id'		=> $mood_id,
            'img_url'		=> !empty($mood_info['img_url']) ? $mood_info['img_url'] : '',
            'timeline'		=> time(),
        );
        
        return $feed_datas;
    }

}