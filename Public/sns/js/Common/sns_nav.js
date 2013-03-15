function sns_nav() {
	this.init();
};

sns_nav.prototype.init = function(){
	var first_context = $("#sns_nav_list");
	var first_pos = this.findSelectedNavPos($("a", first_context));
	$('a', first_context).each(function(i) {
		var num_th = parseInt(i) + parseInt(1);
		if(i == first_pos) {
			$(this).addClass('nav_a'+ num_th +'_hover');
		} else {
			$(this).removeClass('nav_a'+ num_th +'_hover').addClass('nav_a' + num_th);
		}
	});
};

sns_nav.prototype.findSelectedNavPos=function(obj) {
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

sns_nav.prototype.getSameSubStringLength=function(str) {
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

sns_nav.prototype.getUrlParam=function(name) {
	var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg);  //匹配目标参数
	if (r!=null) return unescape(r[2]); return null; //返回参数值
};
$(function(){
	new sns_nav();
});