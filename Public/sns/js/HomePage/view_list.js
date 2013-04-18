
function view_list() {
	this.init();
}
view_list.prototype.init=function() {
	var me=this;
	var vuid = $("#client_account").val();
	//加载页面数据
	$.ajax({
		type:'get',
		url:'/Sns/PersonIndex/Index/get_vistior_list_ajax',
		data:{vuid:vuid},
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$("#vistior_list_tab").html('暂无访客');
				return false;
			}
			
			me.fillFriendList(json.data || {});
		}
	});
};

//填充好友列表
view_list.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone', $('#vistior_list_tab')).clone().removeClass('clone').show();
		divObj.renderHtml({
			user:user || {}
		});
		return divObj;
	};
	
	var parentObj = $('#vistior_list_tab');
	for(var i in user_list) {
		var user = user_list[i];
		user.client_headimg_url_obj = '<img src="' + user.client_headimg_url + '"  class="zjfk_tx" />';
		var divObj = createDiv(user || {});
		divObj.data('data',user);
		divObj && parentObj.append(divObj);
		
	}
};

$(document).ready(function() { 	
	new view_list();
});



