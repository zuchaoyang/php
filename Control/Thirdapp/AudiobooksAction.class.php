<?php
class AudiobooksAction extends SnsController {
    public $_isLoginCheck = false;
    
    public function _initialize() {
        parent::_initialize();
        import("@.Common_wmw.Constancearr");
    }
     
    
    private function  AudiobooksArr($key = false) {
        $dataarr =  array(
	        1 => '童话名著',
	        2 => '人类与昆虫',
			3 => '成语故事',
			4 => '世界著名童话',
			5 => '中外文学',
			6 => '鲁迅小说之彷徨',
			7 => '秘密.朗达·拜恩小说',
			8 => '元朝帝王史话',
			9 => '唐朝帝王史话',
			10 => '宋朝帝王史话',
			11 => '明朝帝王史话',
			12 => '汉朝帝王史话',
			13 => '清朝帝王史话',
			14 => '秦朝帝王史话',
			15 => '隋朝帝王史话',
			16 => '英文数字儿歌',
			17 => '十万个为什么',
			18 => '爸爸讲故事',
			19 => '格林童话全集',
			20 => '豪夫童话',
			21 => '安徒生童话全集',
			22 => '列那狐的故事',
			23 => '伊索寓言',
			24 => '中外童话故事',
			25 => '一千零一夜',
			26 => '金鸡报春渡华年',
			27 => '十二生肖之鼠',
			28 => '十二生肖之牛',
			29 => '十二生肖之虎',
			30 => '十二生肖之兔',
			31 => '十二生肖之龙',
			32 => '十二生肖之蛇',
			33 => '十二生肖之马',
			34 => '十二生肖之羊',
			35 => '十二生肖之猴',
			36 => '十二生肖之鸡',
			37 => '十二生肖之狗',
			38 => '十二生肖之猪',
			39 => '春秋',
			40 => '郭德纲相声',
			
	    );
	    
	    return !empty($key) ? $dataarr[$key] : $dataarr; 
    }
    
    
    //获取所有类型列表
    public function getAudiobooksByType() {
        $mAudiobooks = ClsFactory::Create('Model.mAudiobooks');
        $audiobookslist = $mAudiobooks->getAudiobooksByType();
        
        $audiobooksarr = $this->AudiobooksArr();
        
        $audiobookslistarr = array();
        foreach($audiobooksarr as $id=>$val) {
             $audiobookslistarr[$id]['category'] = $val;
        }
        
        foreach($audiobookslist as $id=>$val) {
            $audiobookslistarr[$val['category']]['pic_url'] = $val['pic_url'];
            $audiobookslistarr[$val['category']]['category_id'] = $val['category'];
        }
        
        $this->assign('audiobookslistarr',$audiobookslistarr);
        
        $this->display('audio_books');
    }
    
    
    //获取类型下的数据列表
    public function getAudiobooksByTypeid() {
        $limit = 10;
        $page = $this->objInput->getInt('page');
        $page = max($page,1);
        
        $typeid = $this->objInput->getInt('typeid');
        $typeid = empty($typeid) ? 1 : $typeid;
        
        $typename = $this->AudiobooksArr($typeid);
        
        $offset = ($page-1) * $limit;
        
        $mAudiobooks = ClsFactory::Create('Model.mAudiobooks');
        $audiobookslist = $mAudiobooks->getAudiobooksByTypeid($typeid,$offset,$limit);
        $audiobookslist_shift = current($audiobookslist);
        $pic_url = $audiobookslist_shift['pic_url'];
        $url = $audiobookslist_shift['url'];
        $title = $audiobookslist_shift['title'];
            
        if(count($audiobookslist) < $limit) {
            $flag = 'end';
        }
        
        $this->assign('page',$page);
        $this->assign('flag',$flag);
        $this->assign('typeid',$typeid);
        $this->assign('typename',$typename);
        $this->assign('title',$title);
        $this->assign('url',$url);
        $this->assign('pic_url',$pic_url);
        $this->assign('audiobookslist',$audiobookslist);
        
        $this->display('audio_books_list');
    }
    
    
}