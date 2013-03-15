function ucHeaderCls() {
	this.init();
}
ucHeaderCls.prototype.init=function() {
	var pos = this.findSelectNavPos();
	$('ul a', $('.nav_main')).each(function(i) {
		if(i == pos) {
			$(this).addClass('first');
		} else {
			$(this).removeClass('first');
		}
	});
};
ucHeaderCls.prototype.findSelectNavPos=function() {
	var self = this;
	var max = 0,pos=0;
	$('ul a', $('.nav_main')).each(function(i) {
		var href = $(this).attr('href').toLowerCase();
		var same = self.getSameSubStringLength(href);
		if(same > max) {
			max = same;
			pos = i;
		}
	});
	return pos;
};
ucHeaderCls.prototype.getSameSubStringLength=function(str) {
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
$(document).ready(function() {
	new ucHeaderCls();
});