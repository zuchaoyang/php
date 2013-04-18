//将事件的绑定和具体的实现函数分开
function homework_preview() {
	this.attachEvent();
	this.attachEventUserDefine();
}

homework_preview.prototype.attachEventUserDefine=function() {
	var me = this;
	$('body').bind('initPreviewDivEvent', function(evt, datas) {
		me.initPreviewDiv(datas);
		me.openPopDiv();
	});
};

/**
 * 绑定预览相关的事件
 * @return
 */
homework_preview.prototype.attachEvent=function() {
	var self = this;
	var context = $('#preview_div');
	//发布作业按钮
	$('#pub_btn', context).click(function() {
		$('#is_sms').remove();
		$('form:first').submit();
		return false;
	});
	//发布作业+短信按钮
	$('#pub_with_sms_btn', context).click(function() {
		$('<input type="hidden" id="is_sms" name="is_sms" value="1"/>').appendTo($('form:first'));
		$('form:first').submit();
		return false;
	});
};

homework_preview.prototype.openPopDiv=function() {
	$('#preview_div').dialog({
		autoOpen:false,
		bgiframe:true,
		draggable:true,
		resizable:false,
		width:960,
		minHeight:300,
		modal:true,
		zIndex:9999,
		stack:true,
		position:'center',
		dialogClass: 'alert',
		beforeclose:function(event, ui) {
			return true;
		}
	});
	var title = $('#title', $('#preview_div')).val();
	$('#preview_div').dialog('option', 'title', title);
	$('#preview_div').dialog('open');
};

//初始化预览的相关数据
homework_preview.prototype.initPreviewDiv=function(datas) {
	datas = datas || {};
	var context = $('#preview_div');
	//科目信息
	$('#subject_name', context).html(datas.subject_name);
	//交付日期
	$('#end_time', context).html(datas.end_time);
	//作业内容
	$('#content', context).html(datas.content);
	//作业附件
	$('#upload_file_name', context).html(datas.upload_file_name);
	if(datas.upload_file_name == '') {
		$("#fjxz").remove();
	}
	//情况已经存在的成员列表信息
	var parentDiv = $('#accepters_list', context);
	var divClone = $('.clone', parentDiv);
	//清空已经存在的div
	$('.pv_list', parentDiv).remove();
	//学生成员信息的填充
	var accepters_list = datas.accepters_list || {};
	for(var i in accepters_list) {
		var class_info = accepters_list[i].class_info || {};
		var selected_students = accepters_list[i].selected_students || {};
		var divObj = divClone.clone().removeClass('clone').addClass('pv_list').appendTo(parentDiv).show();
		//班级名称
		$('#class_name', divObj).html(class_info.class_name);
		//班级成员
		var num_id = 1;
		var html_str = "<tr>";
		for(var uid in selected_students) {
			html_str += "<td>" + selected_students[uid] + "</td>";
			if(num_id++ % 8 == 0) {
				html_str += "</tr><tr>";
			}
		}
		var append_nums = 8 - (num_id - 1) % 8;
		for(var i=1; i<=append_nums; i++) {
			html_str += "<td>&nbsp;</td>";
		}
		html_str += '</tr>';
		$('#student_list', divObj).html(html_str);
	}
};

$(document).ready(function() {
	new homework_preview();
});