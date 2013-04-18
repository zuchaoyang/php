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
})(jQuery);

//去掉页面标签
function tripTag(str) {
	str = $.trim(str.toString() || '');
	return str.replace(/<(.+?)>/gm, '');
};

//将事件的绑定和具体的实现函数分开
function Publish() {
	this.limitInterval = null;
	this.max_length = 180;
	this.attachEvent();
	this.attachEventUserDefine();
}


//绑定用户回调事件
Publish.prototype.attachEventUserDefine=function() {
	var me = this;
	//班级选择的数据获取
	$('body').bind('loadStudentDataEvent', function(evt, class_code, callback) {
		if(typeof callback == 'function') {
			var datas = $('#accept_list_' + class_code).data('data') || {};
			callback(datas.selected_students || {});
		}
	});
};

Publish.prototype.popDivCallback=function(datas) {
	var me = this;
	if($.isEmptyObject(datas)) {
		return false;
		
	}
	var class_info = datas.class_info || {};
	var selected_students = datas.selected_students || {};
	//以class_code作为div的id
	var div_id = 'accept_list_' + class_info.class_code;
	var parentObj = $('#review_list_div');
	var cloneDiv = $('.clone', parentObj);
	if(!$.isEmptyObject(selected_students)) {
		if($('#' + div_id, parentObj).length == 0) {
			cloneDiv.clone().removeClass('clone').addClass('review').attr('id', div_id).appendTo(parentObj).show();
		}
		var divObj = $('#' + div_id, parentObj);
		//绑定数据,将选中的数据绑定到对应的div中的data属性上,
		divObj.data('data', datas || {});
		var num_id = 1;
		var html_str = "<tr>";
		for(var i in selected_students) {
			html_str += "<td>" + selected_students[i] + "</td>";
			if(num_id++ % 8 == 0) {
				html_str += "</tr><tr>";
			}
		}
		var append_nums = 8 - (num_id - 1) % 8;
		for(var i=1; i<=append_nums; i++) {
			html_str += "<td>&nbsp;</td>";
		}
		html_str += "</tr>";
		$('#student_list_tab', divObj).html(html_str);
		$('#class_name', divObj).html(class_info.class_name);
		//事件绑定
		me.attachEventForAcceptList(div_id);
	} else {
		$('#' + div_id).remove();
	}
};

/**
 * 绑定发布页面的基本事件
 * @return
 */
Publish.prototype.attachEvent=function() {
	var me = this;
	var context = $('#publish_main');
	//绑定科目点击事件,todolist
	$(':input[name="subject_id"]', context).change(function() {
		//情况所有的数据信息
		$('#end_time,#file_name', context).val('');
		//移除回显的div信息
		$('.review', context).remove();
	});
	//绑定交付时间
	$('#end_time', context).click(function() {
		WdatePicker({minDate:'%y-%M-%d'});
	});
	$('#end_time_img', context).click(function() {
		WdatePicker({el:'end_time', minDate:'%y-%M-%d'});
	});
	
	//接受对象选择按钮
	$('#add_accept_btn', context).click(function() {
		var subject_id = $(':input[name="subject_id"]:checked').val();
		$('#pop_div').trigger('initPopDivEvent', [function(datas) {
			me.popDivCallback(datas);
		}, subject_id, {}]);
	});
	//预览发布按钮
	$('#preview_btn').click(function() {
		if(me.validator()) {
			var packDatas = me.packFormDatas();
			$('body').trigger('initPreviewDivEvent', [packDatas]);
		}
	});
	//表单提交时间
	$('form:first').submit(function() {
		me.formDataCollect();
		return me.validator();
	});
	
	$('#content').keypress(function(evt) {
		var content = $.trim($('#content').val()).toString();
		if(content.length >= me.max_length) {
			var keyCode = evt.keyCode || evt.which;
			//字符超过限制后只有Backspace键能够按
			if(keyCode != 8) {
				$.showError('作业内容不能超过180字!');
				return false;
			}
		}
	}).focus(function() {
		me.limitInterval = setInterval(function() {
			me.reflushCounter();
		}, 1000);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
};

/**
 * 绑定选中对象回显事件
 * @return
 */
Publish.prototype.attachEventForAcceptList=function(div_id) {
	var me = this;
	var context = $('#' + div_id);
	if(context.length == 0) {
		return false;
	}
	var self = this;
	var class_code = context.attr('id').toString().match(/(\d+)/)[1];
	//回显信息的编辑按钮,以班级组织数据
	$('#edit_a', context).click(function() {
		var subject_id = $(':input[name="subject_id"]:checked', $('#publish_main')).val();
		$('#pop_div').trigger('initPopDivEvent', [function(datas) {
			me.popDivCallback(datas);
		}, subject_id, context.data('data') || {}]);
	});
	//回显信息的删除按钮
	$('#delete_a', context).click(function() {
		//删除改班级选择的用户信息
		$('#' + div_id).remove();
	});
};
//收集整个表单的数据
Publish.prototype.packFormDatas=function() {
	var me = this;
	
	var context = $('#publish_main');
	var subject_id = $(':input[name="subject_id"]:checked', context).val();
	var subject_name = $('#subject_name_' + subject_id, context).text();
	var end_time = $('#end_time', context).val();
	var content = $('#content').val();
	var upload_file_name = ($('#file_name', context).val().toString().split('/') || []).pop();
	//收集接受对象,以class_code为key进行整理
	var accepters_list = {};
	$('.review', $('#review_list_div')).each(function() {
		var class_code = ($(this).attr('id').toString().match(/(\d+)/) || [])[1];
		accepters_list[class_code] = $(this).data('data') || {};
	});
	return {
		'subject_id':subject_id,
		'subject_name':subject_name,
		'end_time':end_time,
		'content':content,
		'upload_file_name':upload_file_name,
		'accepters_list':accepters_list
	};
};
//表单数据收集函数
Publish.prototype.formDataCollect=function() {
	var parentObj = $('form:first');
	$('.accepters', parentObj).remove();
	//将当前活动的学生列表上的数据收集起来整理为数组格式或者以逗号分隔的字符串格式
	var packDatas = this.packFormDatas() || {};
	var accepters_list = packDatas.accepters_list || {};
	
	for(var class_code in accepters_list) {
		var selected_students = accepters_list[class_code].selected_students || {};
		var uids = [];
		for(var uid in selected_students) {
			uids.push(uid);
		}
		$('<textarea name="accept_list[' + class_code + ']" class="accepters"></textarea>').text(uids.join(',')).hide().appendTo(parentObj);
	};
};
//表单的审核
Publish.prototype.validator=function() {
	var me = this;
	if($(':input[name="subject_id"]').filter(':checked').length == 0) {
		$.showError('请选择科目!');
		return false;
	}
	if(!$.trim($('#end_time').val())) {
		$.showError('请交作业日期!');
		return false;
	}
	if(!$('#content').val()) {
		$.showError('请填写作业内容!');
		return false;
	}
	if(!me.isSelectAccepters()) {
		$.showError('请选择接受对象!');
		return false;
	}
	return true;
};
//判断是否选择了接受对象
Publish.prototype.isSelectAccepters=function() {
	var packDatas = this.packFormDatas() || {};
	return !$.isEmptyObject(packDatas.accepters_list) ? true : false;
};
Publish.prototype.reflushCounter=function() {
	var me = this;
	var len = $.trim($('#content').val()).toString().length;
	var show_nums = me.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$("#content_counter").html(show_nums);
};

$(document).ready(function() {
	var pub = new Publish();
	pub.reflushCounter();
});
