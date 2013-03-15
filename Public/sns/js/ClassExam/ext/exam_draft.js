function exam_draft() {
	this.draft_cache = {};
	this.attachEventForUserDefine();
}

//绑定用户的自定义事件
exam_draft.prototype.attachEventForUserDefine=function() {
	var self = this;
	//触发exam_draft_div的数据加载事件
	$('#exam_draft_div').bind('loadEvent', function(evt, page) {
		var datas = self.loadDatas(page);
		self.fillDatas(datas);
		self.dynamicAttachEvent();
	});
};
//ajax加载远程数据信息
exam_draft.prototype.loadDatas=function(page) {
	var self = this;
	
	page = parseInt(page);
	if(isNaN(page) || page <= 0) {
		page = 1;
	}
	var json_datas = self.draft_cache[page];
	if($.isEmptyObject(json_datas)) {
		$.ajax({
			type:'get',
			url:'/Sns/ClassExam/Publish/getDraftListAjax',
			data:{
				class_code:$('#class_code').val(),
				page:page
			},
			async:false,
			dataType:'json',
			success:function(json) {
				json_datas = self.draft_cache[page] = json;
			}
		});
	}
	return json_datas || {};
};
//数据填充
exam_draft.prototype.fillDatas=function(json_datas) {
	json_datas = json_datas || {};
	
	//数据出错的时候需要提示
	if(json_datas.status < 0) {
		art.dialog({
			title:'错误提示',
			content:json_datas.info
		}).lock().time(3);
	}
	
	var self = this;
	var contextTab = $('#exam_draft_tab');
	//清理已有的数据
	$('tr:gt(1)', contextTab).remove();
	
	var data = json_datas.data || {};
	var current_page = data.current_page || 1;
	var has_nextpage = data.has_nextpage || 0;
	var draft_list = data.draft_list || {};
	
	//将是否有下一页的数据进行绑定
	$('#exam_draft_div').data({
		'current_page' : current_page,
		'has_nextpage' : has_nextpage
	});
	
	var cloneObj = $('.clone', contextTab);
	for(var i in draft_list) {
		var draft = draft_list[i];
		var trObj = cloneObj.clone().removeClass('clone').appendTo(contextTab).show();
		//数据绑定
		trObj.data('data', draft || {});
		//添加数据信息
		var child_set = trObj.children('td');
		child_set.eq(0).children('a').text(draft.exam_name);
		child_set.eq(1).text(draft.add_time);
	}
};
//动态绑定事件
exam_draft.prototype.dynamicAttachEvent=function() {
	var self = this;
	$('tr:gt(1)', $('#exam_draft_tab')).each(function() {
		var data = $(this).data('data') || {};
		var exam_id = data.exam_id || 0;
		if(!exam_id) return true;
		var trContext = $(this);
		//选择草稿
		$('.choice_a', trContext).unbind('click').click(function() {
			window.location.href="/Sns/ClassExam/Publish/index/exam_id/" + exam_id + "/is_draft/1";
		});
		//删除草稿
		$('.delete_a', trContext).unbind('click').click(function() {
			$.ajax({
				type:'get',
				url:'/Sns/ClassExam/Publish/deleteDraftAjax/exam_id/' + exam_id,
				dataType:'json',
				success:function(json) {
					//操作失败时的处理
					if(json.status < 0) {
						art.dialog({
							title:'操作失败',
							content:json.info,
							icon:'error'
						}).lock().time(3);
						return false;
					}
					//操作成功时的处理
					art.dialog({
						title:'操作成功',
						content:json.info,
						icon:'succeed'
					}).lock().time(3);
					
					//删除当前的tr元素
					trContext.remove();
					self.draft_cache = {};  //删除缓存
					return true;
				}
			});
		});
	});
	
	var divContext = $('#exam_draft_div');
	var current_page = divContext.data('current_page') || 1;
	var has_nextpage = divContext.data('has_nextpage');
	
	$('#next_page_btn, #pre_page_btn').unbind('click');
	if(current_page > 1) {
		//上一页事件绑定
		$('#pre_page_btn', divContext).click(function() {
			divContext.trigger('loadEvent', [current_page - 1]);
		}).show();
	} else {
		$('#pre_page_btn', divContext).hide();
	}
	if(has_nextpage) {
		//下一页按钮事件绑定
		$('#next_page_btn', divContext).click(function() {
			divContext.trigger('loadEvent', [current_page + 1]);
		}).show();
	} else {
		$('#next_page_btn', divContext).hide();
	}
};

$(document).ready(function() {
	new exam_draft();
});
