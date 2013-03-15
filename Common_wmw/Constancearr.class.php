<?php
class Constancearr{
	public static function stop_flag($key = false){
	    $dataarr =  array(
            -1 => '未激活',
             0 => '正常',
             //1 => '有效期冻结',
             2 => '永久冻结',
             3 => '班级冻结',
		);
		
		return self::get_name($dataarr, $key);
	}
	
	public static function phone_status($key = false){
	    $dataarr =  array(
             0 => '欠费',
             1 => '正常',
		);
		
		return self::get_name($dataarr, $key);
	}
	
	public static function business_enable($key = false){ 
	    $dataarr =  array(
             0 => '未去营业厅开通账号（手机号）',
             1 => '已开通手机',
             2 => '已取消手机业务',
		);
		
		return self::get_name($dataarr, $key);
	}
	

	/*评语类型*/
	public static function pytype($key = false){
	    $dataarr =  array(
	        4 => '存在问题',
	        5 => '特长',
			6 => '交际',
			7 => '班务',
			8 => '学习情况',
			9 => '作业情况',
			10 => '成绩情况',
			11 => '课堂表现',
			12 => '总体印象',
			13 => '希望',
	    );
	    
	    return self::get_name($dataarr, $key);
	}

	public static function pytypeatt($key = false){
	    $dataarr =  array(
	        1 => '优秀',
	        2 => '良好',
	        3 => '还需努力',
	    );
	    
	    return self::get_name($dataarr, $key);
	}


	/**
	 * 获取学校的类型信息
	 * @param $key
	 */
	public static function school_type($key = false) {
	    $dataarr = array(
    	    1 => '小学',
    	    2 => '初中',
    	    3 => '高中',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	
	/**
	 * 获取学校的类型信息
	 * @param $key
	 */
	public static function grade_type($key = false) {
	    $dataarr = array(
    	    1 => '六三制',
    	    2 => '五四制',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	public static function mbquestion($key = false) {
		$dataarr = array(
			0 => '您印象最深刻的老师名字是',
			1 => '您的小学校名是',
			2 => '您父亲的生日是',
			3 => '您母亲的生日是',
			4 => '您最重要纪念日子是',
			5 => '您最喜欢的运动员的名字是',
			6 => '您的宠物的名字是'
		);
		
		return self::get_name($dataarr, $key);
	}
	
    public static function client_type($key = false) {
		$dataarr = array(
			0 => '学生',
			1 => '老师',
			2 => '家长',
		);
		
		return self::get_name($dataarr, $key);
	}
	
	

	public static function classleader($key = false){
		$dataarr =  array(
			1 => '学生',
			2 => '班长',
			3 => '学委',
			4 => '体委',
			5 => '纪委'
		);
		
		return self::get_name($dataarr, $key);
	}
	
	public static function school_resource_advantage($key = false) {
	    $dataarr = array(
    	    1 => '市重点' ,
    	    2 => '区重点' ,
    	    3 => '普通校' ,
    	    4 => '其他' ,
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/**
	 * 教师职称
	 */
	public static function client_title($key = false){
	    $dataarr = array(
	        1 => '初级教师',
	        2 => '中级教师',
	        3 => '高级教师',
	        4 => '特级教师',
	    );
	    
	    return self::get_name($dataarr, $key);
	    
	}
	
	/**
	 * 教师职务
	 */
    public static function client_job($key = false){
	    $dataarr = array(
	        1 => '校长',
	        2 => '副校长',
	        3 => '教导主任',
	        4 => '教研组长',
	        5 => '无',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	
	/**
	 * 用户血型
	 */
	public static function client_bloodtype($key = false){
	    $dataarr = array(
	        1 => 'A型',
	        2 => 'B型',
	        3 => 'AB型',
	        4 => 'O型',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/**
	 * 家长行业
	 */
	public static function client_trade($key = false){
	    $dataarr = array(
	       1 => '计算机/互联网/通信/电子',
	       2 => '会计/金融/银行/保险',
	       3 => '贸易/消费/制造/营运',
	       4 => '制药/医疗',
	       5 => '广告/媒体',
	       6 => '房地产/建筑',
	       7 => '专业服务/教育/培训',
	       8 => '服务业',
	       9 => '物流/运输',
	       10 => '能源/原材料',
	       11 => '政府/非营利机构/其他',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/**
	 * 年级信息
	 */
	public static function class_grade_id($key = false){
	    $dataarr = array(
	       1 => '小学一年级',
	       2 => '小学二年级',
	       3 => '小学三年级',
	       4 => '小学四年级',
	       5 => '小学五年级',
	       6 => '小学六年级',
	       7 => '初中一年级',
	       8 => '初中二年级',
	       9 => '初中三年级',
	       10 => '高中一年级',
	       11 => '高中二年级',
	       12 => '高中三年级',
	       13 => '初中四年级',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/**
	 * 亲属关系类型
	 */
	public static function family_relationtype($key = false){
	    $dataarr = array(
	       1 => '父亲',
	       2 => '母亲',
	       3 => '爷爷',
	       4 => '奶奶',
	       5 => '外公',
	       6 => '外婆',
	       7 => '其他亲属',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/**
	 * 生肖
	 */
	public static function client_zodiac($key = false){
	     $dataarr = array(
	        1 => '鼠',
	        2 => '牛',
	        3 => '虎',
	        4 => '兔',
	        5 => '龙',
	        6 => '蛇',
	        7 => '马',
	        8 => '羊',
	        9 => '猴',
	        10 => '鸡',
	        11 => '狗',
	        12 => '猪',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	/***
	 扩展资料中的性格*/
	public static function clienttemperament($key = false){
	    $dataarr = array(
	        'ym' => '幽默',
	        'lg' => '乐观',
	        'nx' => '内向',
	        'wx' => '外向',
	        'js' => '谨慎',
	        'dd' => '胆大',
	        'lm' => '浪漫',
	        'ka' => '可爱',
	        'ps' => '朴实',
	        'mt' => '腼腆',
	        'cm' => '聪明',
	        'zy' => '正义',
	        'sl' => '善良',
	        'qt' => '其他'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	/***
	 扩展资料中的兴趣*/
	public static function clienthobby($key = false){
	    $dataarr = array(
    	    'sf' => '书法',
    	    'hh' => '绘画',
    	    'lq' => '乐器',
    	    'wyx' => '玩游戏',
    	    'kdhp' => '看动画片',
    	    'cg' => '唱歌',
    	    'tw' => '跳舞',
    	    'ds' => '读书',
    	    'kb' => '看报',
    	    'kdm' => '看动漫',
    	    'sw' => '上网',
    	    'sdj' => '睡大觉',
    	    'ycw' => '养宠物',
    	    'qt' => '其他'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	/***
	 我是班上的职务*/
	public static function studentjob($key = false){
	    $dataarr = array(
            'ptxs' => '普通学生',
            'xzz' => '小组长',
            'kdb' => '课代表',
            'tywy' => '体育委员',
            'xcwy' => '宣传委员',
            'wywy' => '文艺委员',
            'xxwy' => '学习委员',
            'ldwy' => '劳动委员',
            'bz' => '班长',
            'fbz' => '副班长',
            'xdz' => '小队长',
            'zdz' => '中队长',
            'ddz' => '大队长'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	/***
	喜欢的动漫*/
	public static function cartoon($key = false){
	    $dataarr = array(
	        'cslr' => '城市猎人',
	        'hzw' => '海贼王',
	        'dlam' => '多啦A梦',
	        'atm' => '奥特曼',
    	    'hyrz' => '火影忍者狼士',
    	    'xyy' => '喜洋洋与灰太狼',
    	    'hmbb' => '海绵宝宝',
    	    'kjys' => '铠甲勇士',
    	    'zzx' => '猪猪侠',
    	    'gfxm' => '功夫熊猫',
    	    'yjdwb' => '妖精的尾巴',
    	    'cwxjl' => '宠物小精灵',
    	    'qyc' => '犬夜叉',
	        'qt' => '其他'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	/***
	喜欢的游戏*/
	public static function game($key = false){
	    $dataarr = array(
	        'cf' => '穿越火线',
	        'qqfc' => 'QQ飞车',
	        'mssj' => '魔兽世界',
    	    'qqxw' => 'QQ炫舞',
    	    'dnf' => '地下城勇士',
    	    'lkwg' => '洛克王国',
    	    'seh' => '赛尔号',
    	    'abd' => '奥比岛',
    	    'ddt' => '弹弹堂',
    	    'qpl' => '棋牌类',
    	    'fndxn' => '愤怒的小鸟',
    	    'zwdzjs' => '植物大战僵尸',
    	    'qsg' => '切水果'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	/***
	喜欢的运动*/
	public static function sports($key = false){
	    $dataarr = array(
	        'zq' => '足球',
	        'lq' => '篮球',
    	    'pq' => '排球',
    	    'wq' => '网球',
    	    'ppq' => '乒乓球',
    	    'ymq' => '羽毛球',
    	    'ts' => '跳绳',
    	    'tj' => '踢毽',
    	    'pb' => '跑步',
    	    'sb' => '散步',
    	    'ps' => '爬山',
    	    'qt' => '其它'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
    public static function zscy_story_status($key = false){
    	$dataarr = array(
	        0 => '未审核',
	        1 => '未通过',
	        2 => '已审核'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	
	/**
	 * 星座
	 * @param   $id
	 */
    public static function client_constellation($key = false){
	    $dataarr = array(
	        1 => '白羊座',
	        2 => '金牛座',
	        3 => '双子座',
	        4 => '巨蟹座',
	        5 => '狮子座',
	        6 => '处女座',
	        7 => '天秤座',
	        8 => '天蝎座',
	        9 => '射手座',
	        10 => '魔蝎座',
	        11 => '水瓶座',
	        12 => '双鱼座',
	    );
	    
	    return self::get_name($dataarr, $key);
	}



	/*
	 * 课程表系统科目
	 */
	public static function curriculumSubject(){
	    return array(
	        array('id'=>1 , 'subjectName'=>'语文','subjectIco'=>'1.jpg'),
	        array('id'=>2 , 'subjectName'=>'数学','subjectIco'=>'2.jpg'),
	        array('id'=>3 , 'subjectName'=>'英语','subjectIco'=>'3.jpg'),
	        array('id'=>4 , 'subjectName'=>'地理','subjectIco'=>'4.jpg'),
	    );
	}
	/*OA权限对应模块*/
	public static function oaRoleAccessModel($key = false){
	    $dataarr = array(
	        0 => '布置工作',
	        1 => '局里工作',
	        2 => '学校工作',
	        3 => '重要通知',
	        4 => '校务公开',
	        5 => '班级导航',
	        6 => '部门导航'
	    );
	    
	    return self::get_name($dataarr, $key);
	} 
	
	public static function getfacelist($key = false){
		$dataarr = array(
				0 => "/惊讶",
				1 => "/撇嘴",
				2 => "/色",
				3 => "/发呆",
				4 => "/得意",
				5 => "/大哭",
				6 => "/害羞",
				7 => "/闭嘴",
				8 => "/睡",
				9 => "/流泪",
				10 => "/尴尬",
				11 => "/发怒",
				12 => "/调皮",
				13 => "/呲牙",
				14 => "/微笑",
				15 => "/难过",
				16 => "/酷",
				17 => "/冷汗",
				18 => "/抓狂",
				19 => "/吐",
				20 => "/偷笑",
				21 => "/可爱",
				22 => "/白眼",
				23 => "/傲慢",
				24 => "/饥饿",
				25 => "/困",
				26 => "/惊恐",
				27 => "/流汗",
				28 => "/憨笑",
				29 => "/大兵",	
				30 => "/奋斗",
				31 => "/咒骂",
				32 => "/疑问",
				33 => "/嘘",
				34 => "/晕",
				35 => "/折磨",
				36 => "/衰",
				37 => "/骷髅",
				38 => "/敲打",
				39 => "/再见",
				40 => "/擦汗",
				41 => "/抠鼻",
				42 => "/鼓掌",
				43 => "/糗大了",
				44 => "/坏笑",
				
				45 => "/左哼哼",
				46 => "/右哼哼",
				47 => "/哈欠",
				48 => "/鄙视",
				49 => "/委屈",
				50 => "/快哭了",
				51 => "/阴险",
				52 => "/亲亲",
				53 => "/吓",
				54 => "/可怜",
				55 => "/菜刀",
				56 => "/西瓜",
				57 => "/啤酒",
				58 => "/篮球",
				59 => "/乒乓",
		);	

		return self::get_name($dataarr, $key);
	
	}
	
	
	// 学习资源arr
	public static function learn_type($key = false){
		$dataarr = array(
				1=>'自主学习',
				2=>'反馈跟踪',
				3=>'能力延伸',
				4=>'知识拓展',
				5=>'情景字典',
				6=>'典型例题',
				7=>'同步练习',
				8=>'同步听力',
				9=>'反馈练习',
				10=>'本讲要点',
				11=>'同步课堂',
				12=>'重难点讲解',
				13=>'在线测试',
				14=>'情景对话',
				15=>'知识讲解',
				16=>'课外拓展',
				17=>'观察与思考',
				18=>'课外导读',
				19=>'学习目标',
				20=>'伴读锦囊',
				21=>'素材积累',
				22=>'课外阅读',
				23=>'单元要点',
				24=>'整合复习',
				25=>'单元测试',
				26=>'高考链接',
				27=>'作文链接',
				28=>'方法技巧',
				29=>'知识结构',
				30=>'整合提高',
				31=>'链接高考',
				32=>'词汇讲解',
				33=>'课文讲解',
				34=>'语法讲解',
				35=>'写作指导',
				36=>'水平测试',
				37=>'目标词汇',
				38=>'在线听力',
				39=>'单词学习',
				40=>'单词测试'
		);
			
		return self::get_name($dataarr, $key);
	}
	
	public static function file_type($key = false){
	    $dataarr = array (
	        1 => "课件", 
	        2 => "文本", 
	        3 => "网页", 
	        4 => "视频", 
	        5 => "音频", 
	        6 => "动画", 
	        7 => "其它",
	        8 => "图片",
	        9 => "动画",    
	    );
	        
	    return self::get_name($dataarr, $key);
	}
	
	
	
	private static function get_name($dataarr, $key){
		if($key === false) {
	        return $dataarr;
	    } elseif(is_numeric($key)) {
	        $key = intval($key);
	        $returnstr = '暂无';
	        if(isset($dataarr[$key])) {
	            $returnstr = $dataarr[$key];
	        }
	        return $returnstr ? $returnstr : "暂无";
	    }
	    
	    return false;
	}
	
//***************************资源*********************************************	
	
    public static function con_resource($key = false){
		$dataarr = array(
			1=>'同步资源',
			2=>'精品资源',
			3=>'精品网校'
		);
		
		return self::get_name($dataarr, $key);
	}
	
	public static function get_resource_grade($key = false) {
	    $dataarr = array(
	        1 => '小学一年级',
	        2 => '小学二年级',
	        3 => '小学三年级',
	        4 => '小学四年级',
	        5 => '小学五年级',
	        6 => '小学六年级',
	        7 => '初中一年级',
	        8 => '初中二年级',
	        9 => '初中三年级',
	        13 => '初中四年级',
	        10 => '高中一年级',
	        11 => '高中二年级',
	        12 => '高中三年级',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	public static function get_resource_term($key = false) {
	    $dataarr = array(
	        1 => '上册',
	        2 => '下册',
	        3 => '全一册',    
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
	public static function get_resource_subject($key = false) {
	    $dataarr = array(
	        1  => '语文',
            2  => '数学',
            3  => '英语',
            4  => '物理',
            5  => '化学',
            6  => '生物',
            7  => '历史',
            8  => '地理',
            9  => '政治',
            10 => '思想品德',
            11 => '科学',
            12 => '奥数',
            13 => '其他',
            14 => '奥化',
            15 => '奥物',
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
//***************************日志*********************************************		
	//班级日志权限
	public static function get_blog_class_grant($key = false) {
	    $dataarr = array(
            0=>'公开',
            1=>'本班'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	//个人日志 权限
	public static function get_blog_person_grant($key = false) {
	    $dataarr = array(
            0=>'公开',
            1=>'好友',
            2=>'仅主人'
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
    public static function get_resource_column_synchronization($key = false) {
	    $dataarr = array(
	    
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
    public static function get_resource_column_school($key = false) {
	    $dataarr = array(
	    
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
    public static function get_($key = false) {
	    $dataarr = array(
	    
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
    public static function get_resource_show_type($key = false) {
	    $dataarr = array(
	    
	    );
	    
	    return self::get_name($dataarr, $key);
	}
	
//    public static function get_($key = false) {
//	    $dataarr = array(
//	    
//	    );
//	    
//	    return self::get_name($dataarr, $key);
//	}
	
}