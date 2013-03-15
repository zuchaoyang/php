function show(){
	this.show_send_box();
	this.show_del_private_msg();
};

show.prototype.show_del_private_msg = function(){
	var me = this;
	var context = $("#del_msg_tip");
	$(".icon_close").click(function(){
		var session_id = this.id;
		var del_private_msg = context;
		del_private_msg.bind({
			openEvent: function(evt, options) {
				options = options || {};
				//获取权限设置列表
				$(this).data('options', options);
				
				//表单提交的地址
				art.dialog({
					id:this.id,
				    opacity: 0.5,	// 透明度
					title:'删除私信',
					content:$('#del_msg_tip').get(0),
					drag: false,
					fixed: true,	//固定定位 ie 支持不好回默认转成绝对定位
					init:function() {
					}
				}).lock();
			},
			closeEvent:function() {
				var dialogObj = art.dialog.list['del_msg_tip'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
		});
		del_private_msg.trigger('openEvent');
		$("input[class='qx_btn']",context).click(function(){
			del_private_msg.trigger('closeEvent');
		});
		
		$("input[class='qd_btn']",context).click(function(){
			me.del_private_msg(session_id,del_private_msg);
		});
		
	});
};

show.prototype.del_private_msg = function(session_id, obj){
	$.ajax({
		type:'get',
		url:"/Sns/PrivateMsg/PrivateMsg/del_private_msg/session_id/" + session_id,
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status>0){
				$("#" + session_id).parent().parent().remove();
				$.showSuccess(json.info);
				obj.trigger('closeEvent');
			}else{
				$.showError(json.info);
			}
		}
	});
};

show.prototype.show_send_box = function() {
	var to_uid = $("#to_uid").val();
	var me = this;
	//返回值为sendbox对象
	var sendBoxObj = $("#say_textarea").sendBox({
		//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
		panels:'emote,upload',
		//表单的提交类型，建议使用post的方式，支持(get, post)
		type:'post',
		//表单提交到的位置
		url:'/Sns/PrivateMsg/PrivateMsg/add_private_msg/to_uid/' + to_uid,
		//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
		dataType:'json',
		//表单提交前验证信息，
		beforeSubmit:function() {
			if(sendBoxObj.getSource() == ""){
				$.showTip("请您输入私信内容");
				return false;
			}
			return true;
		},
		//服务器返回数据后的回调函数
		success:function(json) {
			me.success();
		}
	});
};

show.prototype.success = function(){
	$.showSuccess("发送成功");
	window.location.reload();
};

$(function(){
	new show();
});