function vistior() {
	this.init();
	this.delattachEvent();
}

vistior.prototype.init=function() {
	var me=this;
	var vuid = $("#vuid").val();
	//加载页面数据
	$.ajax({
		type:'get',
		url:'/Sns/PersonIndex/Index/get_vistior_list_ajax',
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
vistior.prototype.fillFriendList=function(user_list) {
	user_list = user_list || {};
	
	//创建一个div对象
	function createDiv(user) {
		if($.isEmptyObject(user)) {
			return false;
		}
		var divObj = $('.clone', $('#vistior_list_div')).clone().removeClass('clone').show();
		divObj.renderHtml({
			user:user || {}
		});
		return divObj;
	};
	
	var parentObj = $('#vistior_list_div');
	for(var i in user_list) {
		var user = user_list[i];
		var divObj = createDiv(user || {});
		divObj.data('data',user);
		divObj && parentObj.append(divObj);
		
	}
};

//删除访客
vistior.prototype.delattachEvent = function(){
	//隐藏和展示删除按钮
	$(".visitor_main_tab").live('mouseover',function() {
		$("#show_del_btn",$(this)).show();
	});
	
	$(".visitor_main_tab").live('mouseout',function() {
		$("#show_del_btn",$(this)).hide();
	});
	
	var me = this;
	context = $('#friend_list_div');
	$("#del_btn",context).live('click',function(){
		var self = $(this);
		var parentObj = self.closest(".visitor_main_tab");
		var client_info = parentObj.data('data' || {});
		
		if(client_info.client_account != client_info.vuid){
			$("#show_del_btn",$(this)).hide();
		}
		
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

$(document).ready(function() {
	new vistior();
});
