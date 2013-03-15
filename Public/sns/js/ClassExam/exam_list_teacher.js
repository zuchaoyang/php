(function($) {
	$.showError=function(msg) {
		art.dialog({
			title:'错误提示',
			content:msg,
			icon:'error'
		}).lock().time(3);
	};
	$.showSuccess=function(msg) {
		art.dialog({
			title:'成功提示',
			content:msg,
			icon:'succeed'
		}).lock().time(3);
	};
})(jQuery);

function examCls() {
	this.attachEvent();
	this.delegateEventForPage();
	this.delegateEventForExamList();
	
	this.init();
}

examCls.prototype.init=function() {
	var me = this;
	me.loadExamDatas(1);
	$('#page_list_p').data('page', 1);
};

examCls.prototype.attachEvent=function(){
	var me = this;
	var context = $('#search_tab');
	//搜索按钮
	$('#search_btn_a', context).click(function() {
		//收集当前的查询条件
		var subject_id = $('#subject_id', context).val();
		var exam_name = $('#exam_name', context).val();
		var start_time = $('#start_time', context).val();
		var end_time = $('#end_time', context).val();
		var search_options = {
			subject_id : subject_id || 0,
			exam_name : $.trim(exam_name),
			start_time : $.trim(start_time),
			end_time : $.trim(end_time)
		};
		$('#search_tab').data('search_options', search_options);
		$('#page_list_p').data('page', 1);
		me.loadExamDatas(1);
		//阻止事件冒泡和页面跳转
		return false;
	});
	//开始时间
	$('#start_time', context).click(function() {
		WdatePicker({el:'start_time'});
	});
	//结束时间
	$('#end_time', context).click(function() {
		WdatePicker({el:'end_time'});
	});
};

//委托翻页的相关事件
examCls.prototype.delegateEventForPage=function() {
	var me = this;
	//上一页按钮
	$('#page_list_p').delegate('#prev_page', 'click', function() {
		var ancestorObj = $('#page_list_p');
		var page_list = ancestorObj.data('page_list') || {};
		var page = ancestorObj.data('page') || 1;
		//加载数据
		ancestorObj.data('page', page - 1);
		me.loadExamDatas(page - 1);
	});
	//下一页按钮
	$('#page_list_p').delegate('#next_page', 'click', function() {
		var ancestorObj = $('#page_list_p');
		var page_list = ancestorObj.data('page_list') || {};
		var page = ancestorObj.data('page') || 1;
		//加载数据
		ancestorObj.data('page', page + 1);
		me.loadExamDatas(page + 1);
	});
};

examCls.prototype.delegateEventForExamList=function() {
	//绑定补发短信按钮事件
	$('#exam_tab').delegate('.sms_reissue', 'click', function() {
		var ancestorTrObj = $(this).parents('tr:first');
		var exam_datas = ancestorTrObj.data('exam_datas') || {};
		var exam_id = exam_datas.exam_id;
		if(!exam_id) {
			$.showError('您没有权限进行该操作!');
			return false;
		}
		var ancestorTdObj = $(this).parents('td:first');
		art.dialog({
			title: '发送短信',
		    content: '您确认要发送短信吗？',
		    icon: 'succeed',
		    //取消按钮
		    cancel:$.noop,
		    ok:function() {
				$.ajax({
					type: "get",
					url: '/Sns/ClassExam/View/examSmsReissueAjax/exam_id/' + exam_id,
					dataType:'json',
					success:function(json){
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						$.showSuccess(json.info);
						//修改按钮状态,todolist
						ancestorTdObj.html($('#sended_icon').html());
						return true;
				    }
				});
			}
		});
	});
	//绑定发送短信按钮事件
	$('#exam_tab').delegate('.sms_all', 'click', function() {
		var ancestorTrObj = $(this).parents('tr:first');
		var exam_datas = ancestorTrObj.data('exam_datas') || {};
		var exam_id = exam_datas.exam_id;
		if(!exam_id) {
			$.showError('您没有权限进行该操作!');
			return false;
		}
		var ancestorTdObj = $(this).parents('td:first');
		art.dialog({
			title: '发送短信',
		    content: '您确认要发送短信吗？',
		    icon: 'succeed',
		    cancel:$.noop, //取消按钮
		    ok:function() {
				$.ajax({
					type: "get",
					url: '/Sns/ClassExam/View/examSmsAllAjax/exam_id/' + exam_id,
					dataType:'json',
					success:function(json) {
						if(json.status == -1) {
							$.showError(json.info);
							return false;
						} else if(json.status == -2) {
							$.showError(json.info);
							//修改按钮状态
							ancestorTdObj.html($('#reissue_icon').html());
							return false;
						}
						$.showSuccess(json.info);
						//修改按钮状态
						ancestorTdObj.html($('#sended_icon').html());
						return true;
				    }
				});
			}
		});
	});
	//绑定发送短信按钮事件
	$('#exam_tab').delegate('.del_but', 'click', function() {
		var ancestorTrObj = $(this).parents('tr:first');
		var exam_datas = ancestorTrObj.data('exam_datas') || {};
		var exam_id = exam_datas.exam_id;
		art.dialog({
			title: '删除成绩',
		    content: '您确认要删除成绩信息吗？',
		    icon: 'succeed',
		    //取消按钮
		    cancel:$.noop,
		    //确定按钮
		    ok:function() {
				$.ajax({
					type: "get",
					url: '/Sns/ClassExam/Exam/delExamAjax/exam_id/' + exam_id,
					dataType:'json',
					success:function(json){
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						$.showSuccess(json.info);
						//删除改行记录
						ancestorTrObj.remove();
						return true;
				    }
				});
			}
		});
		return false;
	});
	
	//查看按钮
	$('#exam_tab').delegate('.view_a', 'click', function() {
		var ancestorTrObj = $(this).parents('tr:first');
		var exam_datas = ancestorTrObj.data('exam_datas') || {};
		var exam_id = exam_datas.exam_id;
		window.location.href = "/Sns/ClassExam/View/index/exam_id/" + exam_id;
	});
};

//加载考试的相关信息
examCls.prototype.loadExamDatas=function(page) {
	var me = this;
	var options = $('#search_tab').data('search_options') || {};
	var class_code = $('#class_code').val();
	var page = page >= 1 ? page : 1;
	$.ajax({
		type:'post',
		url:'/Sns/ClassExam/Exam/getTeacherExamListAjax/class_code/' + class_code + "/page/" + page,
		data:options,
		dataType:'json',
		success:function(json) {
			var data = json.data || {};
			me.fillPage(data.page_list);
			me.fillExamList(data.exam_list || {});
		}
	});
};

//处理上下页的相关信息
examCls.prototype.fillPage=function(page_list) {
	page_list = page_list || {};
	//缓存数据
	var ancestorObj = $('#page_list_p');
	ancestorObj.data('page_list', page_list || {});
	//更改当前显示的页码
	var page = ancestorObj.data('page') || 1;
	$('#current_page').html(page);
	//判断按钮是否显示
	if(page_list.has_prev_page) {
		$('#prev_page', ancestorObj).show();
		$('#first_page', ancestorObj).hide();
	} else {
		$('#first_page', ancestorObj).show();
		$('#prev_page', ancestorObj).hide();
	}
	if(page_list.has_next_page) {
		$('#next_page', ancestorObj).show();
		$('#last_page', ancestorObj).hide();
	} else {
		$('#next_page', ancestorObj).hide();
		$('#last_page', ancestorObj).show();
	}
};
//处理成绩的列表信息
examCls.prototype.fillExamList=function(exam_list) {
	exam_list = exam_list || {};
	var tabContext = $('#exam_tab');
	$('tr:gt(2)', tabContext).remove();
	//考试信息不存在
	if($.isEmptyObject(exam_list)) {
		$('<tr><td colspan="5">没有相应的考试信息!</td></tr>').appendTo(tabContext);
		return false;
	}
	//填充考试列表信息
	var trClone = $('#tr_clone', tabContext);
	for(var i in exam_list) {
		var exam = exam_list[i] || {};
		var trObj = trClone.clone().attr('id', '').appendTo(tabContext).show();
		//将数据绑定到对应的tr元素上
		trObj.data('exam_datas', exam);
		$('td:eq(0)', trObj).html(exam.exam_name);
		$('td:eq(1)', trObj).html(exam.subject_name);
		$('td:eq(2)', trObj).html(exam.exam_time);
		//处理短信状态
		var html = $('#send_icon').html();
		if(exam.is_sms == 1) {
			html = $('#sended_icon').html();
		} else if(exam.is_sms == 2) {
			html = $('#reissue_icon').html();
		}
		$('td:eq(3)', trObj).html(html);
		//处理操作按钮,如果不能删除去掉删除按钮
		if(!exam.can_del) {
			var tdObj = $('td:eq(4)', trObj);
			tdObj.html($('a:first', trObj).get(0).outerHTML);
		}
	}
};

$(document).ready(function(){
	new examCls();
});