function type() {
	this.attachEventForUserDefine();
	this.delegateEvent();
}

//绑定用户自定义的事件
type.prototype.attachEventForUserDefine=function() {
	var self = this;
	$('#add_type_div').bind({
		//弹层打开事件
		openEvent:function(evt, options) {
			options = options || {};
			var divObj = $(this);
			divObj.data('options', options);
			art.dialog({
				id:'add_type_dialog',
				title:'添加分类',
				content:divObj.get(0)
			});
		},
		//关闭预览弹层
		closeEvent:function() {
			var dialogObj = art.dialog.list['add_type_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};


//添加分类  按钮弹层事件
type.prototype.delegateEvent=function() {
	var context = $('#add_type_div');
	//确定按钮
	$('#add_type_div').delegate('#confirm_btn', 'click', function() {
		var options = $('#add_type_div').data('options') || {};
		var name = $('#type_name').val();
		var datas = options.datas || {};
		datas.name = name;
		
		$("#error").html('(最多12个字母或6个汉字)');
		if (! $.trim(name)) {
			$("#error").html('&nbsp;&nbsp;分类名称不能为空！');
			return false;
		}
		
		//ajax 添加日志分类
		$.ajax({
			type:'post',
			url:'/Sns/Blog/PersonType/publishAjax',
			data:datas,
			dataType:'json',
			success:function(json) {
				if(typeof options.callback == 'function') {
					options.callback(json);
				}
				return false;
			}
				
		});

	});
	
	//取消按钮
	$('#add_type_div').delegate('#cancel_btn', 'click', function() {
		$('#add_type_div').trigger('closeEvent');
		return false;
	});
	
};


$(document).ready(function(){
	var pubObj = new type();
});




