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

//初始化页面的相关数据
published.prototype.init=function() {
	var context = $('.list_main');
	$('#more_a', context).data('page', 1);
	this.loadHomework({
		page : 1
	}, true);
};

published.prototype.attachEvent=function() {
	var me = this;
	var context = $('.list_main');
	//时间选择
	$("#start_time,#end_time", context).click(function() {
		WdatePicker();
	});
	//当前页面只负责查询按钮和查看更多作业的相关事件
	//查询按钮在点击的时候，收集当时的查询条件并记录下来,
	//用户改变搜索条件并重新点击的时候才改变
	$('#search_a', context).click(function() {
		var divContext = $('#search_div');
		var subject_id = $('#subject_id', divContext).val();
		var search_type = $('#search_type', divContext).val();
		var start_time = $('#start_time', divContext).val();
		var end_time = $('#end_time', divContext).val();
		divContext.data('search_options', {
			'subject_id':subject_id,
			'search_type':search_type,
			'start_time':start_time,
			'end_time':end_time
		});
		//重新加载数据
		$('#homework_list_div *', context).remove();
		$('#more_a', context).data('page', 1);
		me.loadHomework();
	});
	//加载更多按钮的点击事件
	$('#more_a', context).click(function() {
		var page = $(this).data('page') || 1;
		me.loadHomework({
			'page' : page + 1
		});
		$(this).data('page', page + 1);
		//阻止a元素的事件冒泡和默认行为,防止页面跳动
		return false;
	});
};

//根据页面查询条件加载作业数据,无缓存
published.prototype.loadHomework=function(append_options, no_show_error) {
	var me = this;
	var search_options = $('#search_div').data('search_options') || {};
	search_options = $.extend(search_options, append_options || {});
	//获取班级class_code
	var class_code = $('#class_code').val();
	$.ajax({
		type:'post',
		url:"/Sns/ClassHomework/Published/getHomeworklistAjax/class_code/" + class_code,
		data:search_options,
		dataType:'json',
		success:function(json) {
			if(json.status < 0 && !no_show_error) {
				$.showError(json.info);
				return false;
			}
			me.fillHomework(json.data || {});
		}
	});
};
//填充作业列表
published.prototype.fillHomework=function(homework_list) {
	homework_list = homework_list || {};
	if($.isEmptyObject(homework_list)) {
		return false;
	}
	var parentObj = $('#homework_list_div');
	var client_type = $('#client_type').val();
	//填充作业列表
	for(var i in homework_list) {
		var homework = homework_list[i];
		if(client_type == 0) {
			$('#clone_student').trigger('cloneEvent', [{
				'homework_datas':homework || {},
				'callback':function(cloneDiv) {
					cloneDiv.appendTo(parentObj);
				}
			}]);
		} else if(client_type == 1) {
			$('#clone_teacher').trigger('cloneEvent', [{
				'homework_datas':homework || {},
				'callback':function(cloneDiv) {
					cloneDiv.appendTo(parentObj);
				}
			}]);
		} else if(client_type == 2) {
			$('#clone_family').trigger('cloneEvent', [{
				'homework_datas':homework || {},
				'callback':function(cloneDiv) {
					cloneDiv.appendTo(parentObj);
				}
			}]);
		}
	}
};

$(document).ready(function() {
	new published();
});