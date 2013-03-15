function list(){
	this.del_private_relation_show();
	this.reply_msg_show();
};

list.prototype.del_private_relation_show = function(){
	var context = $("#private_msg_relation");
	var me = this;
	$(".icon_close").click(function(){
		var relation_id = (this.id.toString().match(/(\d+)/ig) || [])[0];
		var to_uid = (this.id.toString().match(/(\d+)/ig) || [])[1];
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
					content:$('#private_msg_relation').get(0),
					drag: false,
					fixed: true,	//固定定位 ie 支持不好回默认转成绝对定位
					init:function() {
					}
				}).lock();
			},
			closeEvent:function() {
				var dialogObj = art.dialog.list['private_msg_relation'];
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
			me.del_private_relation(relation_id,to_uid,del_private_msg);
		});
	});
};

list.prototype.del_private_relation = function(relation_id,to_uid,Obj){
	$.ajax({
		type:'get',
		url:"/Sns/PrivateMsg/PrivateMsg/del_private_msg_session/id/" + relation_id + "/to_uid/" + to_uid,
		dataType:'json',
		async:false,
		success:function(json) {
			if(json.status>0){
				$("#private_msg_" + relation_id + "_" + to_uid).parent().parent().remove();
				$.showSuccess(json.info);
				Obj.trigger('closeEvent');
			}else{
				$.showError(json.info);
			}
		}
	});
};

list.prototype.reply_msg_show = function(){
	var me = this;
	$("a[class='replay_msg']").click(function(){
		var to_uid = (this.id.toString().match(/(\d+)/ig) || [])[0];
		var context = $("#replay_content");
		var replay_private_msg = context;
		replay_private_msg.bind({
			openEvent: function(evt, options) {
				options = options || {};
				//获取权限设置列表
				$(this).data('options', options);
				
				//表单提交的地址
				art.dialog({
					id:this.id,
				    opacity: 0.5,	// 透明度
					title:'回复私信',
					content:$('#replay_content').get(0),
					drag: false,
					fixed: true,	//固定定位 ie 支持不好回默认转成绝对定位
					init:function() {
					}
				}).lock();
			},
			closeEvent:function() {
				var dialogObj = art.dialog.list['replay_content'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
		});
		$("h1",context).html("回复：" + $("#replay_name_"+to_uid).val());
		me.show_send_box(to_uid);
		replay_private_msg.trigger('openEvent');
	});
};

list.prototype.show_send_box = function(to_uid) {
	var me = this;
	//返回值为sendbox对象
	var sendBoxObj = $("#replay_textarea").sendBox({
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

list.prototype.success = function(){
	$.showSuccess("发送成功");
	window.location.reload();
};



$(function(){
	new list();
});