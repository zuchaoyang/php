function friend() {
	this.init();
}

friend.prototype.init=function() {
	var me=this;
	var vuid = $("#vuid").val();
	//加载页面数据
	$.ajax({
		type:'get',
		url:'/Sns/PersonIndex/Index/get_friend_list_ajax',
		data:{vuid:vuid},
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


//填充好友列表
friend.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone', $('#friend_list_div')).clone().removeClass('clone').show();
		divObj.renderHtml({
			user_friend:user || {}
		
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
	new friend();
});