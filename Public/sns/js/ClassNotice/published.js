(function($) {
	$.showError=function(msg) {
		art.dialog({
			id:'show_error_dialog',
			title:'错误提示',
			content:msg,
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			id:'show_success_dialog',
			title:'成功提示',
			content:msg,
			icon:'succeed'
		}).lock().time(3);
	};
})(jQuery);

function published() {
	this.attachEvent();
	this.init();
}

published.prototype.init=function() {
	this.loadNoticeList({
		page : 1
	});
};

published.prototype.attachEvent=function() {
	var me = this;
	var context = $('.publish_main');
	//开始时间
	$('#start_time', context).click(function() {
		WdatePicker();
	});
	
	//结束时间
	$('#end_time', context).click(function() {
		WdatePicker();
	});
	
	//搜索按钮
	$('#search_btn', context).click(function() {
		var start_time = $('#start_time').val();
		var end_time = $('#end_time').val();
		//将数据缓存到id为search_p的p标签上
		$('#search_p').data('search_options', {
			start_time:start_time,
			end_time:end_time
		});
		//加载数据
		me.loadNoticeList({
			page : 1
		});
		$('#notice_list_div *').remove();
		$('#more_a', context).data('page', 1);
	});
	
	//查看更多班级公告
	$('#more_a', context).click(function() {
		var page = $(this).data('page') || 1;
		me.loadNoticeList({
			page : page + 1
		}, true);
		$(this).data('page', page + 1);
		//阻止a元素的事件冒泡和默认行为，避免页面发生跳转
		return false;
	});
};

//加载班级对应的公告列表
published.prototype.loadNoticeList=function(append_options, show_error) {
	var me = this;
	var class_code = $('#class_code').val();
	var options = $('#search_p').data('search_options') || {};
	options = $.extend(options, append_options || {}, {class_code:class_code});
	$.ajax({
		type:'post',
		url:'/Sns/ClassNotice/Published/getNoticeListAjax',
		data:options,
		dataType:'json',
		success:function(json) {
			if(show_error && json.status < 0) {
				$.showError(json.info);
				return false;
			}
			me.fillNoticeList(json.data || {});
		}
	});
};

//填充响应的公告的信息
published.prototype.fillNoticeList=function(notice_list) {
	var me = this;
	notice_list = notice_list || {};
	
	var client_type = $('#client_type').val();
	
	var num = 1;
	client_type = parseInt(client_type);
	if(isNaN(client_type) || client_type < 0) {
		client_type = 0;
	}
	for(var i in notice_list) {
		var notice_info = notice_list[i];
		var class_name = num % 2==0 ? 'has_release_main01' :'has_release_main02';
		num++;
		if(client_type == 0) {
			me.createStudentDiv(notice_info,class_name);
		} else if(client_type == 1) {
			me.createTeacherDiv(notice_info,class_name);
		} else if(client_type == 2) {
			me.createFamilyDiv(notice_info,class_name);
		}
	}
};
//动态创建学生相应的div
published.prototype.createStudentDiv=function(notice_info,class_name) {
	notice_info = notice_info || {};
	$('#clone_student').trigger('cloneEvent', [{
		notice_datas:notice_info,
		callback:function(divObj) {
			divObj.addClass(class_name);
			$('#notice_list_div').append(divObj);
		}
	}]);
};
//动态创建老师相应的div
published.prototype.createTeacherDiv=function(notice_info,class_name) {
	notice_info = notice_info || {};
	$('#clone_teacher').trigger('cloneEvent', [{
		notice_datas:notice_info,
		callback:function(divObj) {
			divObj.addClass(class_name);
			$('#notice_list_div').append(divObj);
		}
	}]);
};
//动态创建家长相应的div
published.prototype.createFamilyDiv=function(notice_info,class_name) {
	notice_info = notice_info || {};
	$('#clone_family').trigger('cloneEvent', [{
		notice_datas:notice_info,
		callback:function(divObj) {
			divObj.addClass(class_name);
			$('#notice_list_div').append(divObj);
		}
	}]);
};

$(document).ready(function() {
	new published();
});