function del_album() {
	this.attachEvent();
	this.attachEventUserDefine();
};

del_album.prototype.attachEvent = function(){
	var me = this;
	var del_album_divObj = $("#del_album_div");
	$(".qd_btn", del_album_divObj).click(function() {
		del_album_divObj.trigger('closeEvent');
		var options = del_album_divObj.data('options') || {};
		if(typeof options.callback == 'function') {
			options.callback();
		}
		
	});
	$(".qx_btn", del_album_divObj).click(function() {
		del_album_divObj.trigger('closeEvent');
	});
};

del_album.prototype.attachEventUserDefine=function() {
	var me = this;
	var del_album_divObj = $("#del_album_div");
	del_album_divObj.bind({
		openEvent: function(evt, options) {
			options = options || {};
			//获取权限设置列表
			$(this).data('options', options);
			
			//表单提交的地址
			art.dialog({
				id:'del_album_dialog',
			    opacity: 0.5,	// 透明度
				title:'删除相册',
				content:$('#del_album_div').get(0),
				drag: false,
				fixed: true,	//固定定位 ie 支持不好回默认转成绝对定位
				init:function() {
					var count = options.album_obj.photo_num || 0;
					if(count != 0) {
						$("#no_del_album_selector",del_album_divObj).show();
						$(".qd_btn",del_album_divObj).hide();
					}else{
						$("#no_del_album_selector",del_album_divObj).hide();
						$(".qd_btn",del_album_divObj).show();
					}
					$("#count", del_album_divObj).text(count);
				}
			}).lock();
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['del_album_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

$(document).ready(function() {
	new del_album();
});