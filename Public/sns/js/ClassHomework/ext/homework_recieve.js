//将事件的绑定和具体的实现函数分开
function homework_recieve() {
	this.class_list_cache = {};
	this.class_student_cache = {};
	this.callback = {};
	
	this.attachEvent();
	this.attachEventUserDefine();
}
//注册用户自定义事件
homework_recieve.prototype.attachEventUserDefine=function() {
	var me = this;
	//初始化函数
	$('#pop_div').bind('initPopDivEvent', function(evt, callback, subject_id, review_datas) {
		var class_list_datas = me.loadClassList(subject_id);
		me.fillClassList(class_list_datas);
		//加载需要回显的数据
		if(!$.isEmptyObject(review_datas)) {
			var class_code = (review_datas.class_info || {}).class_code;
			if(class_code) {
				$('#class_list_select option[value="' + class_code + '"]').attr('selected', true);
			}
		}
		var selected_class_code = $('#class_list_select').val();
		me.onClassSelected(selected_class_code);
		//打开弹出层
		me.openPopDiv();
		//注册回调函数
		me.callback = callback;
	});
};

/**
 * 绑定弹出相关的事件
 * @return
 */
homework_recieve.prototype.attachEvent=function() {
	var me = this;
	var context = $('#pop_div');
	//班级下拉框改变事件
	$('#class_list_select', context).change(function() {
		//获取已经选择的班级code
		var class_code = $(this).val();
		me.onClassSelected(class_code);
	});
	
	//全选按钮点击事件
	$('#check_all_btn', context).click(function() {
		$(':checkbox[id^="chkbox_"]').attr('checked', $(this).attr('checked'));
	});
	
	//确定按钮点击事件
	$('#sure_btn', context).click(function() {
		//回调函数
		if(typeof me.callback == 'function') {
			//数据收集
			var extract_datas = me.extractDatas();
			me.callback(extract_datas);
		}
		//关闭弹出层
		$('#pop_div').dialog('close');
	});
	
	//弹出右侧图片关闭按钮
	$('#close_pop_div_a', context).click(function() {
		$('#pop_div').dialog('close');
	});
	//绑定字符串的点击事件
	$('.chk_txt', context).live('click', function() {
		var uid = ($(this).attr('id').toString().match(/(\d+)/) || [])[1];
		uid && $('#chkbox_' + uid, context).attr('checked', true);
	}).live('mouseover', function() {
		$(this).css('cursor', 'pointer');
	});
};

homework_recieve.prototype.onClassSelected=function(class_code) {
	var me = this;
	//加载班级的成员选择列表
	var student_json = me.loadClassStudents(class_code);
	me.fillClassStudents(student_json);
	//修改班级的名称栏
	var context = $('#pop_div');
	$('#class_name', context).html($('option:selected', $('#class_list_select')).html());
	//取消全选按钮的选中状态
	$('#check_all_btn', context).attr('checked', false);
	//触发body的数据加载事件
	$('body').trigger('loadStudentDataEvent', [class_code, function(selected_students) {
		me.checkClassStudent(selected_students);
	}]);
};

//提取选中的数据
homework_recieve.prototype.extractDatas=function() {
	//获取当前选中的班级
	var class_code = $('#class_list_select').val();
	var class_name = $('option:selected', $('#class_list_select')).html();
	//回显数据到页面
	var selected_student = {};
	var contextTab = $('#pop_student_list_tab');
	$(':checkbox[id^="chkbox_"]', contextTab).filter(':checked').each(function() {
		var uid = $(this).attr('id').toString().match(/(\d+)/)[1];
		var user_name = $('#user_name_' + uid).text();
		selected_student[uid] = user_name;
	});
	return {
		'class_info' : {
			'class_code':class_code,
			'class_name':class_name
		},
		'selected_students':selected_student
	};
};

//远程加载班级列表
homework_recieve.prototype.loadClassList=function(subject_id) {
	var me = this;
	//数据加载
	var cache_key = "subject_id:" + subject_id;
	var json = me.class_list_cache[cache_key];
	if($.isEmptyObject(json)) {
		$.ajax({
			type:"post",
			data:{'subject_id':subject_id},
			dataType:"json",
			url:"/Sns/ClassHomework/Publish/class_info_json",
			async:false,
			success:function(_json) {
				json = me.class_list_cache[cache_key] = _json.data || {};
			}
		});
	}
	return json || {};
};

//数据填充
homework_recieve.prototype.fillClassList=function(datas) {
	//数据填充
	var datas = datas || {};
	var parentObject = $('#class_list_select');
	$('option:gt(0)', parentObject).remove();
	for(var i in datas) {
		$('<option value="' + datas[i].class_code + '">' + datas[i].class_name + '</option>').appendTo(parentObject);
	}
	//默认选择第一个班级
	$('option:eq(1)', parentObject).attr('selected', true);
};

//获取班级的学生信息
homework_recieve.prototype.loadClassStudents=function(class_code) {
	var me = this;
	var cache_key = "class_code:" + class_code;
	var json = me.class_student_cache[cache_key];
	if($.isEmptyObject(json)) {
		$.ajax({
			type:"post",
			data:{'class_code':class_code},
			dataType:"json",
			url:"/Sns/ClassHomework/Publish/student_info_json",
			async:false,
			success:function(_json) {
				json = me.class_student_cache[cache_key] = _json.data || {};
			}
		});
	}
	return json || {};
};

homework_recieve.prototype.fillClassStudents=function(datas) {
	datas = datas || {};
	//数据填充
	var parentObj = $('#pop_student_list_tab');
	//清空相应的数据
	$('tr:gt(0)', parentObj).remove();
	var trClone = $('.clone', parentObj);
	for(var i in datas) {
		var user = datas[i];
		var trObj = trClone.clone().removeClass('clone').appendTo(parentObj).show();
		$('.img', trObj).attr({
			id:'img_' + user.client_account,
			src:user.client_headimg
		});
		$('.user_name', trObj).attr({
			id:'user_name_' + user.client_account
		}).html(user.client_name);
		$('.chkbox', trObj).attr({
			id:'chkbox_' + user.client_account
		});
		$('.chk_txt', trObj).attr({
			id:'chk_txt_' + user.client_account
		});
	}
};

//勾选班级成员
homework_recieve.prototype.checkClassStudent=function(selected_students) {
	selected_students = selected_students || {};
	if($.isEmptyObject(selected_students)) {
		return false;
	}
	
	var context = $('#pop_student_list_tab');
	//将选中的成员勾选上
	for(var uid in selected_students) {
		//把相应的checkbox对应的复选框勾上
		$('#chkbox_' + uid, context).attr('checked', true);
	}
	return true;
};
//打开弹出层
homework_recieve.prototype.openPopDiv=function() {
	$('#pop_div').dialog({
		autoOpen:false,
		bgiframe:true,
		draggable:true,
		resizable:false,
		width:550,
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
	var title = $('#title', $('#pop_div')).val();
	$('#pop_div').dialog('option', 'title', title);
	$('#pop_div').dialog('open');
};

$(document).ready(function() {
	new homework_recieve();
});