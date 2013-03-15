<?php
import('@.Control.Api.FeedImpl.PackFeed.IPack', null, '.php');

class PackBlog implements IPack {
    /**
     * 
     * @param int $blog_id
 	 * @return 
     * array(
            'feed_type'     => FEED_BLog,
            'add_account'   => $album_datas['add_account'],
            'timeline'		=> $album_datas['add_time'],
            'feed_content'  => $this->formatContent($album_datas),
            'img_url'		=> $this->getAlbumFirstPhoto($album_datas['album_id']),
        );
     */
    public function getFeedDatas($blog_id) {
        if(empty($blog_id)) {
            return false;
        }
        
        $mBlog = ClsFactory::Create("Model.Blog.mBlog");
        $blog_info = $mBlog->getBlogById($blog_id);
        if(empty($blog_info)) return false;
        $blog_info = $blog_info[$blog_id];
        $feed_datas = array(
            'feed_type'     => FEED_BLOG,
            'add_account'   => $blog_info['add_account'],
            'add_time'		=> $blog_info['add_time'],
            'feed_content'  => strip_tags(htmlspecialchars_decode($blog_info['summary'])),
            'from_id'       => $blog_id,
            'img_url'		=> $this->getBlogFirstPhotoFromSummary($blog_info['summary']),
            'title'			=> $blog_info['title'],
        	'timeline'		=> time(),
        );
        
        return $feed_datas;
    }
    
    /**
     * 获取日志中的第一张照片
     * @param $album_id
     * 
     * todolist
     */
    private function getBlogFirstPhotoFromSummary($summary) {
        if(empty($summary)) {
            return false;
        }
        
        $summary = htmlspecialchars_decode($summary);
        
        import("@.Common_wmw.HtmlParser");
        $HtmlParser = new HtmlParser($summary);
        
        $img = $HtmlParser->getElementByTagName('img');
        
        $ImgParser = HtmlParser::createTagParser("img", $img);
        
        $img_src = $ImgParser->attr('src');
        
        return $img_src;
    }
}
