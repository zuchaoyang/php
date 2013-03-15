function Search_Friend() {
	this.max_length = 140;
	this.limitInterval = null;
	this.init();
	this.attachEventForLoadMore();
	this.attachDelegateEvent();
	this.attachEventForSearch();
	this.attachEvent();
}

Search_Friend.prototype.init=function() {
	var me = this;
	var search_name = $("#search_name").val();
	var search_account = $("#search_account").val();
	$('#load_more_a').data('page', 2);
	$('#add_friend_div').hide();
	$('#request_send_div').hide();
	
	Search_Friend.registerFilters({
		type:'get',
		url:'/Sns/Friend/Manage/search_friend_json',
		data:{search_account:search_account,search_name:search_name},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				return false;
			}
			
			me.fillFriendList(json.data || {});
		}
	});
};

//加好友弹层
Search_Friend.prototype.attachDelegateEvent=function() {
	var me = this;
	$("#add_friend",$("#friend_list_div")).live('click',function(){
		var self = $(this);
		var parentObj = self.parents(".search_main_single");
		var client_info = parentObj.data('data') || {};
		$("#friend_name_span").html(client_info.client_name);
		$("#add_friend_div").data('data',client_info) || {};
		$("#add_friend_div").data('add_friend_id_obj',self);
		art.dialog({
			id:'add_friend_dialog',
			follow:self.get(0),
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'好友请求',
			content:$("#add_friend_div").get(0),
			drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		});
	});
	
	$('#queding_btn').click(function(){
		var self = $(this);
		var parentObj = self.parents("#add_friend_div");
		var add_friend_id_obj = parentObj.data('add_friend_id_obj');
		var client_info = parentObj.data('data') || {};
		var content = $('#content').val();
		
		$.ajax({
			type:'post',
			url:'/Sns/Friend/Manage/add_friend',
			data:{accept_account:client_info.client_account,content:content},
			dataType:'json',
			success:function(json) {
				if(json.status<0){
					alert('失败');
				}
				add_friend_id_obj.html('已发送');
				add_friend_id_obj.removeAttr('id');
				
				return false;
			}
		});
		var dialogObj = art.dialog.list['add_friend_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
	
	$("#quxiao_btn").click(function() {
		var dialogObj = art.dialog.list['add_friend_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
		
};

Search_Friend.prototype.attachEvent=function() {
	var me = this;
	var context = $('#add_content_div');
	$('#content', context).focus(function() {
		me.limitInterval = setInterval(function() {
			var len = $.trim($('#content').val()).toString().length;
			if(len > me.max_length){
				return false;
			}
			$(".f_orange").html((me.max_length - len));
			return true;
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
	
};

Search_Friend.prototype.attachEventForSearch=function() {
	var me = this;
	var context = $('#search_name_id');
	var context_account = $('#search_account_id');
	var context_search = $('#search_p');
	//搜索部分
	var default_search_name = "搜索姓名";
	var default_search_account = "搜索帐号";
	$('#search_name', context).focus(function() {
		var search_name = $.trim($(this).val());
		if(search_name == default_search_name) {
			$(this).val('');
		}
	}).blur(function() {
		var search_name = $.trim($(this).val());
		if(!search_name) {
			$(this).val(default_search_name);
		}
	});
	
	$('#search_account', context_account).focus(function() {
		var search_account = $.trim($(this).val());
		if(search_account == default_search_account) {
			$(this).val('');
		}
	}).blur(function() {
		var search_account = $.trim($(this).val());
		if(!search_account) {
			$(this).val(default_search_account);
		}
	});
	
	//搜索按钮
	$('#search_btn', context_search).click(function() {
		var search_name = $('#search_name', context).val();
		var search_account = $('#search_account',context_account).val();
		
		if(search_name == '搜索姓名') {
			search_name = '';
		}
		if(search_account == '搜索帐号') {
			search_account = '';
		}
		
		//搜索之前清空数据
	   $('#friend_list_div').children(':gt(0)').remove();
	   Search_Friend.registerFilters({
				type:'post',
				url:'/Sns/Friend/Manage/search_friend_json',
				dataType:'json',
				data:{search_account:search_account,search_name:search_name},
				async:false,
				success:function(json) {
					if(json.status < 0) {
						return false;
					}
					
					me.fillFriendList(json.data || {});
				}
			});
		
	});
};


Search_Friend.prototype.attachEventForLoadMore=function() {
	var me = this;
	//加载更多
	$('#load_more_a').click(function() {
		//获取相关的页数设置
		var page = $(this).data('page');
		page = page > 1 ? page : 1;
		//处理相关的数据
		var handler = $(this).data('handler') || $.noop;
		handler(page + 1);
		$(this).data('page', page + 1);
		
		return false;
	});
};

//注册想要的过了条件到加载更多按钮
Search_Friend.registerFilters=function(options) {
	options = options || {};
	$('#load_more_a').show();
	//清空已有的好友列表信息
	$('#friend_list_div').children(':gt(0)').remove();
	//将操作的句柄绑定到load_more_a元素上
	var handler = function(page) {
		page = page > 1 ? page : 1;
		$.ajax({
			type:options.type || 'get',
			url:options.url + "/page/" + page,
			data:options.data || {},
			dataType:options.dataType || 'json',
			success:function(json) {
				json = json || {};
				if(typeof options.success == 'function') {
					options.success(json);
				}
				if($.isEmptyObject(json.data)) {
					$('#load_more_a').hide();
				}
				
			}
		});
	};
	$('#load_more_a').data('handler', handler);
	$('#load_more_a').data('page', 1);
	//加载第一页信息
	handler(1);
};


//填充好友列表
Search_Friend.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone', $('#friend_list_div')).clone().removeClass('clone').show();
		
		divObj.renderHtml({
			user:user || {}
		});
		return divObj;
	};
	
	var parentObj = $('#friend_list_div');
	for(var i in user_list) {
		var user = user_list[i];
		var divObj = createDiv(user || {});
		divObj.data('data',user);
		divObj && parentObj.append(divObj);
	}
};

$(document).ready(function() { 	
	new Search_Friend();
});


