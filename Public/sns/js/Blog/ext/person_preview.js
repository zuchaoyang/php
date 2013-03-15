function blog_preview() {
	this.delegateEvent();
	this.attachUserDefineEvent();
}
//绑定自定义事件
blog_preview.prototype.attachUserDefineEvent=function() {
	var self = this;
	$('#preview_div').bind({
		//打开预览弹层
		openEvent:function(evt, options) {
			options = options || {};
			var datas = options.datas || {};
			var divObj = $(this);
			divObj.data('options', options);
			art.dialog({
				id:'preview_dialog',
				title:'发布预览',
				content:divObj.get(0),
				init:function() {
					divObj.renderHtml(datas);
				}
			}).lock();
		},
		//关闭预览弹层
		closeEvent:function() {
			var dialogObj = art.dialog.list['preview_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
	
};

//预览发布相关的事件
blog_preview.prototype.delegateEvent=function() {
	var context = $('#preview_div');
	
	//返回修改按钮
	$('#preview_div').delegate('#go_back_btn', 'click', function() {
		$('#preview_div').trigger('closeEvent');
		return false;
	});
	
	//发布按钮
	$('#preview_div').delegate('#publish_btn', 'click', function() {
		$('#preview_div').trigger('closeEvent');
		var options = $('#preview_div').data('options') || {};
		if(typeof options.callback == 'function') {
			options.callback();
		}
		return false;
	});
};

$(document).ready(function() {
	new blog_preview();
});