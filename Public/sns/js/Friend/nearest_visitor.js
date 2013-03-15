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


function nearest_visitor(){
	this.max_length = 140;
	this.limitInterval = null;
	this.init();
	this.attachDelegateEvent();
	this.attachEvent();
	this.attachEventForLoadMore();
	this.delattachment();
}

nearest_visitor.prototype.init=function() {
	var me=this;
	$('#load_more_a').data('page', 2);
	$("#add_friend_div").hide();
	$("#msg_div").hide();
	//加载页面数据
	$.ajax({
		type:'get',
		url:'/Sns/Friend/Manage/person_vistior_list_ajax',
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
nearest_visitor.prototype.attachDelegateEvent=function() {
	var me = this;
	$("#add_friend",$("#friend_list_div")).live('click',function(){
		var self = $(this);
		var parentObj = self.parents(".per_main_tab");
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


nearest_visitor.prototype.attachEventForLoadMore=function() {
	var me = this;
	//加载更多
	$('#load_more_a').click(function() {
		//获取相关的页数设置
		var page = $(this).data('page');
		page = page > 1 ? page : 2;
		//处理相关的数据
		
		//分页
		$.ajax({
			type:'get',
			url:"/Sns/Friend/Manage/person_vistior_list_ajax/page/" + page,
			dataType:'json',
			success:function(json) {
				json = json || {};
				if(json.status < 0) {
					if($.isEmptyObject(json.data)) {
						$('#load_more_a').hide();
					}
					return false;
				}
				if($.isEmptyObject(json.data)) {
					$('#load_more_a').hide();
				}
				$('#load_more_a').data('page', page + 1);
				me.fillFriendList(json.data || {});
				
			}
		});
		
		return false;
	});
	
};

nearest_visitor.prototype.attachEvent=function() {
	var me = this;
	var context = $('#add_content_div');
	$('#content', context).focus(function() {
		me.limitInterval = setInterval(function() {
			var len = $.trim($('#content').val()).toString().length;
			if(len > me.max_length){
				return false;
			}
			$("#f_orange_id").html((me.max_length - len));
			return true;
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
	
};

nearest_visitor.prototype.delattachment=function() {
	var me = this;
	context = $('#friend_list_div');
	$("#del_vistior_btn",context).live('click',function(){
		var self = $(this);
		var parentObj = self.closest(".per_main_tab");
		var client_info = parentObj.data('data' || {});
		if(confirm('确定要删除该访客吗？')) {
			$.ajax({
				type:'get',
				url:'/Sns/Friend/Manage/del_vistior',
				dataType:'json',
				async:false,
				data:{id:client_info.id},
				success:function(json){
					if(json.status < 0) {
						$.showError(json.info);
					} else {
						$.showSuccess(json.info);
						parentObj.remove();
					}
					
					return false;
				}
			});
		}
	});
};


//填充好友列表
nearest_visitor.prototype.fillFriendList=function(user_list) {
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
	new nearest_visitor();
});

