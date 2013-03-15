(function($) {
	$.showError=function(msg) {
		$('#tip_error_div').trigger('openEvent', [msg]);
	};
	$.showSuccess = function(msg){
		$('#tip_success_div').trigger('openEvent', [msg]);
	};
	$.showTip = function(msg){
		$('#tip_tip_div').trigger('openEvent', [msg]);
	};
})(jQuery);

function tip() {
	this.loadTemplate();
	this.attachEventUserDefine();
}

tip.prototype.attachEventUserDefine=function() {
	var context = $('#tip_div');
	$('#tip_error_div').bind({
		'openEvent':function(evt, msg) {
			var divObj = $(this);
			art.dialog({
				id:'tip_dialog',
				content:divObj.get(0),
				init:function() {
					$('#msg', divObj).html(msg).show();
				}
			}).lock().time(2);
		}
	});
	$('#tip_success_div').bind({
		'openEvent':function(evt, msg) {
			var divObj = $(this);
			art.dialog({
				id:'tip_dialog',
				content:divObj.get(0),
				init:function() {
					$('#msg', divObj).html(msg).show();
				}
			}).lock().time(2);
		}
	});
	$('#tip_tip_div').bind({
		'openEvent':function(evt, msg) {
			var divObj = $(this);
			art.dialog({
				id:'tip_dialog',
				content:divObj.get(0),
				init:function() {
					$('#msg', divObj).html(msg).show();
				}
			}).lock().time(2);
		}
	});
};

tip.prototype.loadTemplate=function() {
	$.ajax({
		type:'get',
		url:'/Sns/Index/getTemplate',
		dataType:'html',
		async:false,
		success:function(html) {
			$('<div id="tip_div"></div>').appendTo($('body')).html(html).hide();
		}
	});
};

$(document).ready(function() {
	new tip();
});