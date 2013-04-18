function Message() {
	this.is_pull_homework = true;
	this.is_pull_notice = true;
	//this.is_pull_comments = true;
	this.is_pull_exam = true;
	this.socket;
	this.on_load();
	this.pushmsg();
	this.pullmsg();
	this.clear_msg();
	this.on_resize();
};

/**
 * 加载socket.io
 * @return
 */
Message.prototype.on_load = function(){
	var timeout = 2000;
	try{
	this.socket = io.connect('http://node.wmw.cn/msg',{
				'port':3000,
				'connect timeout': timeout,
		        'reconnect': true,
		        'reconnection delay': 2000,
		        'max reconnection attempts': 5,
		        'force new connection':false
	        });
			this.init();
	}catch(e){
		$('script[src*="message.js"]:first').remove();
	}
};

/**
 * 通知服务器用户要订阅
 * @return
 */
Message.prototype.init = function(){
	var me = this;
	var uid = $("#uid").val();
	if($.isEmptyObject(this.socket)){
		return false;
	}
	this.socket.emit('on_load', uid);
	this.socket.emit('sub_msg', uid);
	$("#msg_homework_list,#msg_notice_list,#msg_homework_list,#msg_comments_list").each(function(i){
		var class_name = $(this).attr('class');
		if(class_name == 'main_nav_a1 f16') { 
			var arr = this.id.split('_');
			var uid = $("#uid").val();
			me.socket.emit('clear_msg', uid , arr[1]);
			var obj_str = 'is_pull_' + arr[1];
			me[obj_str] = false;
		}
	});
};

/**
 * 消息推送的方法
 */
Message.prototype.pushmsg = function() {
	var self = this;
	if($.isEmptyObject(this.socket)){
		return false;
	}
	this.socket.on('message',function(data){
		
		var msg_num = !!$("#" + data + '_num').html() ?$("#" + data + '_num').html().replace(/[^0-9]/ig, "") : 0 ;
		var new_msg_num = parseInt(msg_num) + parseInt(1);
		$("#" + data + '_num').html("(" + new_msg_num + "新)");
		$("#new_news_num").html(parseInt($("#new_news_num").html()) + 1);
		self.show_message();
		$("#show_msg_on_load").css('z-index',100).show();
	});
};

Message.prototype.clear_msg = function(){
	var me = this;
	$("#homework,#exam,#comments,#notice,#privatemsg").live('click', function(){
		var uid = $("#uid").val();
		me.socket.emit('clear_msg', uid , this.id);
	});
};

/**
 * 消息拉去的方法
 */
Message.prototype.pullmsg = function() {
	var self = this;
	if($.isEmptyObject(this.socket)){
		return false;
	}
	this.socket.on('get_msg', function (data){
		!!data.homework && self.is_pull_homework ? $("#homework_num").html("(" + data.homework + "新)") : data.homework = 0;
		!!data.exam && self.is_pull_exam ? $("#exam_num").html("(" + data.exam + "新)") : data.exam = 0;
		!!data.req && data.req != 0 ? $("#req_num").html("(" + data.req + "新)") : data.req = 0;
		//!!data.comments && self.is_pull_comments ? $("#comments_num").html("(" + data.comments + "新)") : data.comments = 0;
		!!data.notice && self.is_pull_notice ? $("#notice_num").html("(" + data.notice + "新)") : data.notice = 0;
		//!!data.res && data.res != 0 ? $("#res_num").html("(" + data.res + "新)") : data.res = 0;
		!!data.privatemsg ? $("#privatemsg_num").html("(" + data.privatemsg + "新)") : data.privatemsg = 0;
		
		
		//$("#new_news_num").html(parseInt(data.privatemsg) + parseInt(data.notice) + parseInt(data.comments) + parseInt(data.req) + parseInt(data.exam) + parseInt(data.homework) + parseInt(data.res));
		$("#new_news_num").html(parseInt(data.privatemsg) + parseInt(data.notice) +  parseInt(data.req) + parseInt(data.exam) + parseInt(data.homework));
		self.show_message();
	});
};



Message.prototype.show_message=function(){
	var self = this;
	
	var class_code = $("#class_code").val();
	$("#show_msg_load_main").html('<ul><a href="javascript:;" class="news_close"></a></ul>');

	var homework_num = $("#homework_num").html().replace(/[^0-9]/ig, "") != 0  && self.is_pull_homework ? $("#show_msg_load_main").append('<p><span>' + $("#homework_num").html().replace(/[^0-9]/ig, "") + '条</span>班级作业<a href="/Sns/ClassHomework/Published/index/class_code/' + class_code +'" class="f_orange" id="homework">查看</a></p>') : 0;
	var exam_num = $("#exam_num").html().replace(/[^0-9]/ig, "") != 0  && self.is_pull_exam ? $("#show_msg_load_main").append('<p><span>' + $("#exam_num").html().replace(/[^0-9]/ig, "")  + '条</span>班级考试<a href="/Sns/ClassExam/Exam/index/class_code/' + class_code +'" class="f_orange" id="exam">查看</a></p>') : 0;
	var req_num = $("#req_num").html().replace(/[^0-9]/ig, "") != 0  ? $("#show_msg_load_main").append('<p><span>' + $("#req_num").html().replace(/[^0-9]/ig, "")  + '条</span>好友请求<a href="/Sns/Friend/Manage/friend_request" class="f_orange" id="req">查看</a></p>') : 0;
	//var res_num = $("#res_num").html().replace(/[^0-9]/ig, "") != 0  ? $("#show_msg_load_main").append('<p><span>' + $("#res_num").html().replace(/[^0-9]/ig, "")  + '条</span>好友回复<a href="#" class="f_orange" id="res">查看</a></p>') : 0;
	//var comments_num = $("#comments_num").html().replace(/[^0-9]/ig, "") != 0  && self.is_pull_comments ? $("#show_msg_load_main").append('<p><span>' + $("#comments_num").html().replace(/[^0-9]/ig, "")  + '条</span>评论<a href="#" class="f_orange" id="comments">查看</a></p>') : 0;
	var notice_num = $("#notice_num").html().replace(/[^0-9]/ig, "") != 0  && self.is_pull_notice ? $("#show_msg_load_main").append('<p><span>' + $("#notice_num").html().replace(/[^0-9]/ig, "")  + '条</span>班级公告<a href="/Sns/ClassNotice/Published/index/class_code/' + class_code +'" id="notice" class="f_orange" id="notice">查看</a></p>') : 0;
	var private_num = $("#privatemsg_num").html().replace(/[^0-9]/ig, "") != 0 ? $("#show_msg_load_main").append('<p><span>' + $("#privatemsg_num").html().replace(/[^0-9]/ig, "")  + '条</span>私信<a href="/Sns/PrivateMsg/PrivateMsg/index" class="f_orange" id="privatemsg">查看</a></p>') : 0;
	
	if(this.is_obj(private_num) || 
	   this.is_obj(homework_num) || 
	   this.is_obj(exam_num) || 
	   this.is_obj(req_num) || 
	   this.is_obj(notice_num)){
		self.set_position_show('msg_dispaly', 'show_msg_on_load',30 , 30);
		$("#show_msg_on_load").css('z-index',1000).show();
	}
};

Message.prototype.on_resize = function(){
	var self = this;
	$(window).resize(function() {
		self.set_position_show('msg_dispaly', 'show_msg_on_load',-85,0);
	});
};

Message.prototype.is_obj = function(obj){
	for ( var name in obj ) { 
		return true; 
	} 
	return false; 
};


Message.prototype.set_position_show = function(id,show_id, y, x) {
    var show_x = $("#" + id).outerHeight() + $("#" + id).position().top + x;
    var show_y = $("#" + id).position().left + y;
    $("#" + show_id).css("position","absolute"); 
    $("#" + show_id).css("left",show_y + "px"); 
	$('#' + show_id).css('top',show_x + "px");
};

/**
 * 消息channl
 */
Message.prototype.channl = function() {
	var channl = {};
	channl.homework = 'homework';
	channl.exam = 'exam';
	channl.req = 'req';
	channl.comments = 'comments';
	channl.notice = 'notice';
	channl.res = 'res';
	channl.privatemsg = 'privatemsg';
};

$(document).ready(function(){
	new Message();
});