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
		return this;
	};
	
})(jQuery);

function student_list() {
	this.attachEvent();
}

//绑定学生列表相关的事件
student_list.prototype.attachEvent=function() {
	var me = this;
	//编辑按钮的点击事件的绑定
	var context = $('table:first tr');
	$('.edit_selector', context).click(function() {
		var trContext = $(this).parents('tr:first');
		//触发编辑框的打开事件
		$('#student_edit_div').trigger('openEvent', [{
			'datas':me.extractTrDatas(trContext),
			'callback':function(edited_datas) {
				me.fillTrDatas(trContext, edited_datas);
			}
		}]);
	});
	//移除班级按钮的点击事件的绑定
	$('.remove_selector', context).click(function() {
		var trContext = $(this).parents('tr:first');
		$('#student_delete_div').trigger('openEvent', [{
			'datas': me.extractTrDatas(trContext),
			'callback':function() {
				trContext.remove();
			}
		}]);
	});
};

//获取tr的数据信息
student_list.prototype.extractTrDatas=function(trContext) {
	if($.isEmptyObject(trContext)) {
		return {};
	}
	return {
		'client_name':$('td:eq(1)', trContext).html(),
		'client_class_role_name':$('td:eq(2)', trContext).html(),
		'client_account':$('td:eq(3)', trContext).html(),
		'client_class_role':$('.client_class_role_selector', trContext).val()
	};
};

//填充tr的数据信息
student_list.prototype.fillTrDatas=function(trContext, datas) {
	if($.isEmptyObject(trContext)) {
		return false;
	}
	datas = datas || {};
	$('td:eq(1)', trContext).html(datas.client_name);
	$('td:eq(2)', trContext).html(datas.client_class_role_name);
	$('.client_class_role_selector', trContext).val(datas.client_class_role);
};

function student_edit() {
	this.attachEvent();
	this.attachEventUserDefine();
}

student_edit.prototype.attachEventUserDefine=function() {
	$('#student_edit_div').bind({
		'openEvent':function(evt, options) {
			options = options || {};
			//将数据绑定到对应的div上
			$('#student_edit_div').data('options', options);
			var datas = options.datas || {};
			art.dialog({
				id:'student_edit_dialog',
				title:'编辑学生信息',
				content:$('#student_edit_div').get(0),
				init:function() {
					var context = $('#student_edit_div');
					$('#client_name', context).val(datas.client_name);
					$('option[value="' + datas.client_class_role + '"]', $('#client_class_role_select', context)).attr('selected', true);
				}
			}).lock();
		},
		'closeEvent':function() {
			var dialogObj = art.dialog.list['student_edit_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

student_edit.prototype.attachEvent=function() {
	var context = $('#student_edit_div');
	//点击取消按钮
	$('#cancel_btn', context).click(function() {
		$('#student_edit_div').trigger('closeEvent');
	});
	
	//点击确定按钮
	$('#sure_btn', context).click(function() {
		var options = $('#student_edit_div').data('options') || {};
		var datas = options.datas || {};
		var selectObj = $('#client_class_role_select', context);
		
		var client_account = datas.client_account;
		var new_client_name = $.trim($('#client_name', context).val());
		var new_client_class_role = selectObj.val();
		var new_client_class_role_name = $('option:selected', selectObj).text();
		//用户名不能为空
		if(!new_client_name) {
			$.showError('学生姓名不能为空!');
			return false;
		}
		//判断数据是否做了调整
		if(datas.client_name == new_client_name && datas.client_class_role == new_client_class_role) {
			//数据没有做任何修改，关闭当前弹层
			$('#student_edit_div').trigger('closeEvent');
			return false;
		}
		
		//远程保存相关信息
		var new_datas = {
			'client_account':client_account,
			'client_name':new_client_name,
			'client_class_role':new_client_class_role
		};
		var class_code = $('#class_code').val();
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/StudentList/editStudentAjax/class_code/' + class_code,
			data:new_datas,
			dataType:'json',
			success:function(json) {
				//关闭当前对话框
				$('#student_edit_div').trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//将数据回写到页面
				if(typeof options.callback == 'function') {
					options.callback({
						client_name:new_client_name,
						client_class_role:new_client_class_role,
						client_class_role_name:new_client_class_role_name
					});
				}
			}
		});
	});
};

function student_delete() {
	this.attachEventUserDefine();
	this.attachEvent();
}

student_delete.prototype.attachEventUserDefine=function() {
	$('#student_delete_div').bind({
		'openEvent':function(evt, options) {
			var divObj = $(this); 
			$(this).data('options', options || {});
			art.dialog({
				id:'student_delete_dialog',
				title:'编辑学生信息',
				content:$('#student_delete_div').get(0),
				init:function() {
					$('#prompt_p', divObj).renderHtml(options.datas || {});
				}
			}).lock();
		},
		'closeEvent':function() {
			var dialogObj = art.dialog.list['student_delete_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

student_delete.prototype.attachEvent=function() {
	var context = $('#student_delete_div');
	//打开删除层
	$('#sure_delete_btn', context).click(function() {
		var options = context.data('options') || {};
		var datas = options.datas || {};
		var class_code = $('#class_code').val();
		$.ajax({
			type:'post',
			url:'/Sns/ClassAdmin/StudentList/removeStudentAjax/class_code/' + class_code,
			data:{
				'client_account':datas.client_account
			},
			dataType:'json',
			success:function(json) {
				//关闭当前对话框
				$('#student_delete_div').trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				//删除当前单元格
				if(typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	});
	//关闭删除层
	$('#cancel_delete_btn', context).click(function(){
		$('#student_delete_div').trigger('closeEvent');
	});
};

$(document).ready(function() {
	new student_list();
	new student_edit();
	new student_delete();
});