(function($) {
	$.showError=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg || '操作失败!',
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'成功提示',
			content:msg || '操作成功!',
			icon:'succeed'
		}).lock().time(3);
	};
	
	//简单的数据渲染,支持反复渲染
	$.fn.renderHtml=function(datas) {
		datas = datas || {};
		//判断页面是否渲染过
		if(!this.data('is_rendered')) {
			this.data('tpl_html', this.html().toString());
			this.data('is_rendered', true);
		}
		var tpl_html = this.data('tpl_html');
		var html = tpl_html.toString().replace(/\{([^\}]+?)\}/ig, function(a, b) {
			return datas[b] || "";
		});
		this.html(html);
	};
	
})(jQuery);

function class_member_list() {
	this.delegateEvent();
	this.attachEvent();
}

class_member_list.prototype.delegateEvent=function() {
	//取消管理员
	$('body').delegate('.cancel_admin_a_selector', 'click', function() {
		var ancestorObj = $(this).parents('.ancestor_selector:first');
		var client_account = $('.client_account_selector', ancestorObj).val();
		var class_code = $("#class_code").val();
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/Index/cancelClassAdminAjax/class_code/' + class_code,
			dataType:'json',
			data:{'client_account':client_account},
			async:true,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				ancestorObj.remove();
				window.location.reload();
			}
		});
		//阻止事件冒泡和a元素的默认行为
		return false;
	});
	
	//设置管理
	$('body').delegate('.set_class_admin_selector_a', 'click', function() {
		var ancestorObj = $(this).parents('.ancestor_selector:first');
		var client_account = $('.client_account_selector', ancestorObj).val();
		var class_code = $("#class_code").val();
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/Index/setClassAdminAjax/class_code/' + class_code,
			dataType:'json',
			data:{'client_account':client_account},
			async:true,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				window.location.reload();
			}
		});
		return false;
	});
};

class_member_list.prototype.attachEvent=function() {
	$('.ancestor_selector').hover(function() {
		$('.float_div_selector', $(this)).show();
	}, function() {
		$('.float_div_selector', $(this)).hide();
	});
	
	//班级名称的编辑事件
	var contextClassInfo = $('#class_info_div');
	$('a', contextClassInfo).click(function() {
		var pObj = $('#class_name_p>span', contextClassInfo);
		var class_name = pObj.html();
		$('#edit_class_div').trigger('openEvent', [{
			datas:{'class_name' : class_name},
			callback:function(new_class_name) {
				pObj.html(new_class_name);
			}
		}]);
	});
};


function edit_class_div() {
	this.attachEventUserDefine();
	this.attachEvent();
}

edit_class_div.prototype.attachEventUserDefine=function() {
	var context = $('#edit_class_div');
	$('#edit_class_div').bind({
		openEvent:function(evt, options) {
			options = options || {};
			var divObj = $(this);
			divObj.data('options', options);
			art.dialog({
				id:'edit_class_dialog',
				title:'班级维护',
				content:divObj.get(0),
				init:function() {
					var datas = options.datas || {};
					$('#class_name', context).val(datas.class_name);
				}
			});
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['edit_class_dialog'];
			if(typeof dialogObj.close == 'function') {
				dialogObj.close();
			}
		}
	});
};

edit_class_div.prototype.attachEvent=function() {
	var context = $('#edit_class_div');
	//确认按钮
	$('#sure_btn', context).click(function() {
		//判断内容是否改变
		var divObj = $('#edit_class_div');
		var options = divObj.data('options') || {};
		var datas = options.datas || {};
		var new_class_name = $.trim($('#class_name', context).val());
		var class_code = $('#class_code').val();
		
		if(!new_class_name) {
			$.showError('班级名称不能为空!');
			return false;
		}
		
		//名称没有修改直接退出
		if(new_class_name == datas.class_name) {
			return false;
		}
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/Index/modifyClassInfoAjax/class_code/' + class_code,
			dataType:'json',
			data:{'class_name':new_class_name},
			success:function(json) {
				divObj.trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				if(typeof options.callback == 'function') {
					options.callback(new_class_name);
				}
			}
		});
	});
	
	//取消按钮
	$('#cancel_btn', context).click(function() {
		$('#edit_class_div').trigger('closeEvent');
	});
};

$(document).ready(function() {
	new class_member_list();
	new edit_class_div();
});


