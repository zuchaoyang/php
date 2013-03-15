(function($) {
	//展示删除弹出层
	$.showDeleteMood=function(options) {
		$('#mood_delete_div').trigger('openEvent', [options || {}]);
	};
})(jQuery);

function mood_delete() {
	this.attachEvent();
	this.attachEventUserDefine();
}

mood_delete.prototype = {
	default_msg:'您确定删除该记录信息吗?',
	
	attachEventUserDefine:function() {
		var me = this;
		var divObj = $('#mood_delete_div');
		$('#mood_delete_div').bind({
			//打开删除层
			openEvent:function(evt, options) {
				options = options || {};
				divObj.data('options', options);
				art.dialog({
					id:'mood_delete_dialog',
					title:'删除说说',
					content:divObj.get(0),
					follow:options.follow || null,
					lock:options.lock || false,
					init:function() {
						$('#msg', divObj).html(options.msg || me.default_msg);
					}
				});
			},
			
			//关闭删除层
			closeEvent:function() {
				var dialogObj = art.dialog.list['mood_delete_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
		});
	},
	
	//删除评论部分的事件绑定
	attachEvent:function() {
		var divObj = $('#mood_delete_div');
		//确定删除按钮
		$('#sure_btn', divObj).click(function() {
			var options = divObj.data('options') || {};
			$.ajax({
				type:'get',
				url:options.url || {},
				dataType:'json',
				success:function(json) {
					divObj.trigger('closeEvent');
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					$.showSuccess(json.info);
					if(typeof options.callback == 'function') {
						options.callback(json.data || {});
					}
				}
			});
		});
		
		//取消按钮
		$('#cancel_btn', divObj).click(function() {
			divObj.trigger('closeEvent');
		});
	}
};

$(document).ready(function() {
	new mood_delete();
});