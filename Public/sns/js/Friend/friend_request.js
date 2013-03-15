(function($){
	$.showSuccess=function(obj, msg) {
		art.dialog({
			id:'show_succeed_dialog',
			title:'成功提示',
			content:obj || '操作失败!',
			fixed: true ,  //固定定位 ie 支持不好回默认转成绝对定位
			lock : true,
			init:function(){
				$('#msg_td', $(obj)).html(msg);
			}
		}).time(2);
	};
})(jQuery);

function friend_request() {
	this.init();
	this.attachEventForLoadMore();
//	this.dedal_request();
	this.dynamicAttachEvent();
}

friend_request.prototype.init=function() {
	var me = this;
	$('#agree_div').hide();
	
	var page = $('#load_more_a').data('page') || 1;
	var is_success = me.loadMore(page);
	if(is_success) {
		$('#load_more_a').data('page', page + 1);
	}else{
		$('#load_more_a').data('page', 1);
	}
	
};
friend_request.prototype.loadMore = function(page) {
	var me = this;
	var is_success = true;
	$.ajax({
		type:'get',
		url:'/Sns/Friend/Manage/friend_request_list/page/'+page,
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				is_success = false;
			}else{
				me.fillFriendList(json.data || {});
			}
		}
	});
	
	return is_success;
};
friend_request.prototype.attachEventForLoadMore=function() {
	var me = this;
	//加载更多
	$('#load_more_a').click(function() {
		//获取相关的页数设置
		var page = $(this).data('page') || 1;
		var list = me.loadMore(page);
		if(!list) {
			$(this).parents('.more_active:first').remove();
			return false;
		}
		$(this).data('page', page + 1);
		
		return false;
	});
	
};


//填充好友请求列表
friend_request.prototype.fillFriendList=function(user_list) {
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

//动态绑定事件
friend_request.prototype.dynamicAttachEvent=function() {
//处理同意好友请求

$('#friend_list_div').delegate('#quding_btn', 'click', function() {
	var context = $(this).closest('.per_main_tab');
	var friend_account = $('#friend_account_id', context).val();
	var friend_name = $('#client_name_p',context).html();
	var req_id = $("#req_id",context).val();
	var msg_div = $('#agree_div').get(0);
	$.ajax({
		type:'post',
		url:'/Sns/Friend/Manage/do_friend_response',
		data:{friend_account:friend_account,friend_name:friend_name,req_id:req_id},
		dataType:'json',
		success:function(json) {
			msg = " 你同意了" + friend_name + "的好友请求";
			$.showSuccess(msg_div, msg);
			context.remove();
		}
	});
});

//处理忽略好友请求
$('#friend_list_div').delegate('#ignore_btn', 'click', function() {
	var context = $(this).closest('.per_main_tab');
	var req_id = $("#req_id",context).val();
	var friend_name = $('#client_name_p',context).html();
	var msg_div = $('#agree_div').get(0);
	
	$.ajax({
		type:'post',
		url:'/Sns/Friend/Manage/do_friend_response',
		data:{req_id:req_id},
		dataType:'json',
		success:function(json) {
			msg = " 你忽略了" + friend_name + "的好友请求";
			$.showSuccess(msg_div, msg);
			context.remove();
		}
	});
});
};

$(document).ready(function() { 	
	new friend_request();
});



