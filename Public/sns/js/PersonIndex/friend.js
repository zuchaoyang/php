function friend() {
	this.init();
}

friend.prototype.init=function() {
	var me=this;
	var vuid =$("#vuid").val();
	$("#active_id").hide();
	$("#del_dynamic_id").hide();
	$("#dynamic_id").hide();
	$("#div_id").hide();
	$("#share_id").hide();
	
	//显示和隐藏头像更换头像按钮
	$(".banner").mouseover(function() {
		
		$("#head_pic_id").show();
	});
	
	$(".banner").mouseout(function() {
		$("#head_pic_id").hide();
	});
	//加载页面数据
	$.ajax({
		type:'get',
		url:'/Sns/PersonIndex/Index/get_friend_list_ajax',
		data:{vuid:vuid},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$("#friend_list_div").html('暂无好友');
				return false;
			}
			
			me.fillFriendList(json.data || {});
		}
	});
};


//填充好友列表
friend.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone_selector', $('.my_friend')).clone().removeClass('clone_selector').show();
		
		divObj.renderHtml({
			user_friend:user || {}
		});
		
		return divObj;
	};
	
	var parentObj = $('#friend_list_div');
	for(var i in user_list) {
		var user = user_list[i];
		var divObj = createDiv(user || {});
		var aObj = {};
		aObj = $('a:first',divObj);
		divObj.remove();
		aObj.data('data',user);
		aObj && parentObj.append(aObj);
		
	}
};

$(document).ready(function() {
	new friend();
});