function header() {
	this.init();
	this.msg();
	this.account_settings();
	this.show_msg();
	this.close_msg();
};

header.prototype.init = function(){
	$("#small_head_pic").error(function(){
		this.src = '/Public/images/head_pic.jpg';
	});
	
	$("#show_msg_on_load,#account_settings,#msg").hide();
	
	var first_context = $("#header_list");
	var first_pos = this.findSelectedNavPos($("a", first_context));
	$('a', first_context).each(function(i) {
		var num_th = parseInt(i) + parseInt(1);
		if(i == first_pos) {
			$(this).addClass('a_'+ num_th);
		}
	});
};

header.prototype.msg = function(){
	var self = this;
	$("#msg_dispaly,#msg").mouseover(function(){
		self.set_position_show('msg_dispaly', 'msg');
		$("#show_msg_on_load").trigger('click');
		$("#msg").show();
	});
	
	$("#msg_dispaly,#msg").mouseleave(function(){
		self.set_position_show('msg_dispaly', 'msg');
		$("#msg").hide();
	});
};

header.prototype.account_settings = function(){
	var self = this;
	$("#account_settings_dispaly,#account_settings").mouseover(function(){
		self.set_position_show('account_settings_dispaly', 'account_settings');
		$("#account_settings").show();
	});
	
	$("#account_settings_dispaly,#account_settings").mouseleave(function(){
		self.set_position_show('account_settings_dispaly', 'account_settings');
		$("#account_settings").hide();
	});
};

header.prototype.show_msg = function(){
	var self = this;
	
};

header.prototype.close_msg = function(){
	$("#show_msg_on_load").click(function(){
		$("#show_msg_on_load").hide();
	});
};

header.prototype.set_position_show = function(id,show_id) {
    var show_x = $("#" + id).outerHeight() + $("#" + id).position().top;
    var show_y = $("#" + id).position().left;
    
    $("#" + show_id).css({
    	"position":"absolute", 
    	"left":show_y + "px", 
    	'top':show_x + "px",
    	'z-index':999
    	}); 
};

header.prototype.findSelectedNavPos=function(obj) {
	var self = this;
	var max = 0,pos=0;
	obj.each(function(i) {
		var href = $(this).attr('href').toLowerCase();
		var same = self.getSameSubStringLength(href);
		if(same > max) {
			max = same;
			pos = i;
		}
	});
	return pos;
};

header.prototype.getSameSubStringLength=function(str) {
	if(!str) {
		return 0;
	}
	var str_compare = (window.location.pathname + window.location.search).toLowerCase();
	for(var i=0; i<str.length; i++) {
		if(str.charAt(i) != str_compare.charAt(i)) break;
	}
	//求相似单词数在比较的2个串中的比重值
	return 2 * i / (str.length + str_compare.length);
};

header.prototype.getUrlParam=function(name) {
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg);  //匹配目标参数
	if (r!=null) return unescape(r[2]); return null; //返回参数值
};

$(document).ready(function(){
	new header();
});