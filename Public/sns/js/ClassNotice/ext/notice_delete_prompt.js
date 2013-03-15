(function($) {
	$.fn.openDeletePromptDiv=function(options) {
		$('#delete_prompt_div').trigger('openEvent', [options || {}]);
	};
})(jQuery);

function notice_delete_prompt() {
	this.attachEventUserDefine();
	this.attachEvent();
}
//绑定自定义事件用于外界通讯的解耦
notice_delete_prompt.prototype.attachEventUserDefine=function() {
	$('#delete_prompt_div').bind({
		//弹出层得打开事件
		'openEvent':function(evt, options) {
			options = options || {};
			var divObj = $(this);
			divObj.data('options', options);
			var notice_datas = options.notice_datas || {};
			art.dialog({
				id:'delete_prompt_dialog',
				title:'',
				content:divObj.get(0),
				init:function() {
					$('#notice_id', divObj).val(notice_datas.notice_id);
				}
			});
		},
		//弹出层得关闭事件
		'closeEvent':function() {
			var dialogObj = art.dialog.list['delete_prompt_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};
//绑定相关按钮的响应事件
notice_delete_prompt.prototype.attachEvent=function() {
	var context = $('#delete_prompt_div');
	//确定按钮
	$('#sure_btn', context).click(function() {
		var notice_id = $('#notice_id', context).val();
		if(!notice_id) {
			return false;
		}
		$.ajax({
			type:'get',
			url:'/Sns/ClassNotice/Manage/deleteClassNoticeAjax/notice_id/' + notice_id,
			dataType:'json',
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				var options = $('#delete_prompt_div').data('options') || {};
				if(typeof options.callback == 'function') {
					options.callback();
				}
				//关闭弹出层
				$('#delete_prompt_div').trigger('closeEvent');
			}
		});
	});
	//取消按钮
	$('#cancel_btn', context).click(function() {
		$('#delete_prompt_div').trigger('closeEvent');
	});
};

$(document).ready(function() {
	new notice_delete_prompt();
});