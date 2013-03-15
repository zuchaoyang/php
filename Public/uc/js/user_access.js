function accessCls() {
	this.css_mark = "_Current";
	this.attachEvent();
	this.init();
}
accessCls.prototype.init=function() {
	var self = this;
	$('#show_access a').each(function(i) {
		var css_class = $(this).attr('class');
		if(css_class.indexOf(self.css_mark) >= 0) {
			$('#space_access').val(Math.abs(i));
			return false;
		}
	});
};
accessCls.prototype.attachEvent=function() {
	var self = this;
	$('#show_access a').each(function(i) {
		$(this). bind('click', function() {
			self.resetClass();
			var css_class = $(this).attr('class');
			$(this).removeClass().addClass(css_class + self.css_mark);
			$('#space_access').val(Math.abs(i));
		});
	});
	$('form:first').submit(function() {
		var space_access = $('#space_access').val();
		var access_id = $('#access_id').val();
		if(space_access == access_id){
			return false;
		}
		return true;
	});
	$('#save_btn').bind('click', function() {
		$('form:first').submit();
	});
};
accessCls.prototype.resetClass=function() {
	//将所有的样式重置
	$('#show_access a').each(function() {
		var css_class = $(this).attr('class').toString().split('_').shift();
		$(this).removeClass().addClass(css_class);
	});
};
$(document).ready(function() {
	new accessCls();
});