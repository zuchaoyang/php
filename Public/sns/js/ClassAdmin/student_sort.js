function student_sort() {
	this.attachEvent();
}

student_sort.prototype.attachEvent=function() {
	var me = this;
	var context = $('form:first');
	$('#sure_btn_a', context).click(function() {
		if(me.validator()) {
			$('form:first').submit();
		}
		return false;
	});
};

student_sort.prototype.validator=function() {
	var context = $('form:first');
	var is_pass = true;
	$('.sort_seq_selector', context).each(function() {
		var inputObj = $(this);
		var sort_seq = inputObj.val();
		if(sort_seq < 1 || sort_seq > 9999) {
			$('#prompt_div').trigger('openEvent', [{
				'afterClose':function() {
					inputObj.focus();
				}
			}]);
			is_pass = false;
			return false;
		}
	});
	
	return is_pass;
};

function student_prompt() {
	this.attachEventUserDefine();
	this.attachEvent();
}

student_prompt.prototype.attachEventUserDefine=function() {
	$('#prompt_div').bind({
		//打开弹出层
		'openEvent':function(evt, options) {
			var divObj = $(this);
			divObj.data('options', options || {});
			art.dialog({
				id:'student_prompt_dialog',
				title:'错误提示',
				content:divObj.get(0),
				close:function() {
					var divObj = $(this);
					var options = divObj.data('options') || {};
					if(typeof options.afterClose == 'function') {
						options.afterClose();
					}
				}
			}).lock();
		},
		//关闭弹出层
		'closeEvent':function() {
			var dialogObj = art.dialog.list['student_prompt_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

student_prompt.prototype.attachEvent=function() {
	var context = $('#prompt_div');
	$('#sure_btn', context).click(function() {
		$('#prompt_div').trigger('closeEvent');
	});
};

$(document).ready(function() {
	new student_sort();
	new student_prompt();
});