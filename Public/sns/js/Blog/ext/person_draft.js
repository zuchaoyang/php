function draft() {
	this.attachEvent();
	this.attachEventForUserDefine();
	this.delegateEvent();
}

//绑定事件
draft.prototype.attachEvent=function() {
	var me = this;
	var centex = $('#pager_div');

	$('#pre_page_btn', centex).click(function(){
		var pager = centex.data('pager') || {};
		var page = pager.page;
		if(pager.page == 1) {
			me.refreshPagerStatus();
			return false;
		}
		
		//调用方法
		me.loadDatas(page-1);
	});
	
	$('#next_page_btn', centex).click(function(){
		var pager = centex.data('pager') || {};
		var page = pager.page;
		
		if(!pager.has_next_page) {
			me.refreshPagerStatus();
			return false;
		}
		
		//调用方法
		me.loadDatas(page+1);
	});
	
};

draft.prototype.refreshPagerStatus=function() {
	var contex = $('#pager_div');
	var pager = $('#pager_div').data('pager');
	$('#pre_page_btn', contex).removeClass('no_dj').addClass('f_green');
	$('#next_page_btn', contex).removeClass('no_dj').addClass('f_green');

	if(pager.page == 1) {
		$('#pre_page_btn', contex).removeClass('f_green').addClass('no_dj');
	}
	
	if(!pager.has_next_page) {
		$('#next_page_btn', contex).removeClass('f_green').addClass('no_dj');
	}
};

//绑定用户的自定义事件
draft.prototype.attachEventForUserDefine=function() {
	var me = this;
	//触发draft_div的数据加载事件
	$('#draft_div').bind('openEvent', function(evt) {
		var divObj = $(this);
		divObj.trigger('closeEvent');
		art.dialog({
			id:'draft_dialog',
			titile:'读取草稿',
			content:divObj.get(0),
			
			init:function() {
				me.loadDatas(1);
			}
		});
	}).bind('closeEvent', function(evt) {
		//关闭弹层
		var dialogObj = art.dialog.list['draft_dialog'] || {};
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
};

//ajax加载远程数据信息
draft.prototype.loadDatas=function(page) {
	var me = this;
	page = page > 1 ? page : 1;

	$.ajax({
		type:'get',
		url:'/Sns/Blog/Publish/getDraftListAjax/page/' + page,
		data:{
			class_code:$('#class_code').val()
		},
		async:false,
		dataType:'json',
		success:function(json) {
			var draft_list = json.data.draft_list || {};
			
			$('#pager_div').data('pager', json.data.pager);
			me.refreshPagerStatus();
			
			me.fillDatas(draft_list);
		}
	});
	
};


//草稿列表数据填充
draft.prototype.fillDatas=function(draft_list) {
	draft_list = draft_list || {};
	var contextTab = $('#draft_tab');
	//清理已有的数据除了供克隆用的其他全部删除
	$('tr:gt(1)', contextTab).remove();
	var cloneObj = $('.clone', contextTab);
	
	var num = 1;
	for(var i in draft_list) {
		var draft = draft_list[i] || {};
		var trObj = cloneObj.clone().removeClass('clone').appendTo(contextTab).show();
		//数据绑定并添加数据信息
		if(num++ % 2 == 0) {
			trObj.addClass('back_color');
		}
		trObj.data('data', draft).renderHtml(draft);
	}
};


//草稿详情数据填充（读取草稿）
draft.prototype.fillDratfInfo=function(dratf_info) {
	//页面赋值
	var parent = $('#blog_info_div');
	//当前最新数据的获取
	$('#title', parent).val(dratf_info.title || '');
	if(!$.isEmptyObject($editor)) {
		$editor.setSource(dratf_info.content || '');
	}
	$('#grant', parent).val(dratf_info.grant);
	$('#type_id', parent).val(dratf_info.type_id);
	$('#contentbg', parent).val(dratf_info.contentbg);
	
	//关闭弹层
	$('#draft_div').trigger('closeEvent');
};

draft.prototype.delegateEvent=function() {
	var me = this;
	//选择草稿
	$('#draft_div').delegate('.choice_td', 'click', function() {
		var trObj = $(this).closest('tr');
		var data = trObj.data('data') || {};
		var blog_id = data.blog_id;
		if(!blog_id) {
			return false;
		}
		$.ajax({
			type:'get',
			url:'/Sns/Blog/Publish/readDraftAjax/blog_id/' + blog_id,
			data:{
				class_code:$('#class_code').val()
			},
			dataType:'json',
			success:function(json_datas) {
				// 读取草稿后 清空修改标示
				$('#blog_id,#draft_id').val('');
				me.fillDratfInfo(json_datas.data || {});
			}
		});
	});
	
	//删除草稿
	$('#draft_div').delegate('.delete_a', 'click', function() {
		var trObj = $(this).closest('tr');
		var data = trObj.data('data') || {};
		var blog_id = data.blog_id;
		if(!blog_id) {
			return false;
		}
		$.ajax({
			type:'get',
			url:'/Sns/Blog/Publish/deleteDraftAjax',
			data:{
				blog_id: blog_id,
				class_code:$('#class_code').val()
			},
			dataType:'json',
			success:function(json) {
				//操作失败时的处理
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				
				//删除当前的tr元素
				trObj.remove();
		
				//提示成功
				$.showSuccess(json.info);
				return true;
			}
		});
	});
};

$(document).ready(function() {
	new draft();
});
