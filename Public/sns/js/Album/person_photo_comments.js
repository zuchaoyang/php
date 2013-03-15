function sendCommentBox(sendOptions){
	sendOptions = sendOptions || {};
	var login_account = sendOptions.login_account || '';
	var photo_id = sendOptions.photo_id || '';
	var add_uid = login_account || '';
	var up_id = sendOptions.up_id || '';
	var paramData = {"photo_id":photo_id,"add_uid":login_account,"up_id":up_id};
	/*for(var i in paramData) {
		alert(i + "" + paramData[i]);
	}*/
	var commnetTextareaObj = sendOptions.textareaObj || {};
	var sendBoxObj = commnetTextareaObj.sendBox({
		//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
		panels:'emote',
		//设置编辑框中的字符数限制
		chars:120,
		//限制文件上传大小,(单位是：m 兆)
		file_size:2,
		//设置编辑框对应的样式,对应查看sendbox相应的目录对应的css文件目录下的css文件中的样式名的后缀,
		skin:'default',
		//表单的提交类型，建议使用post的方式，支持(get, post)
		type:'post',
		//表单提交到的位置
		url:'/Api/Album/addCommentByPerson',
		//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
		data:paramData,
		dataType:'json',
		//表单提交前验证信息，返回false表示验证失败，表单不提交；返回true表示通过验证；
		beforeSubmit:function() {
			if(sendBoxObj.getSource() == ""){
				$.showTip("请您输入评论内容");
				return false;
			}
			return true;
		},
		//服务器返回数据后的回调函数
		success:function(json) {
			if(typeof sendOptions.callback == "function") {
				sendOptions.callback(json);
			}
		}
	}, true);
	
	return sendBoxObj;
}

function class_show(){
	this.login_account = $("#login_account").val();
	this.client_account = $("#client_account").val();
	this.album_id = $("#album_id").val();
	this.is_edit = $("#is_edit").val();
	this.img_server = $("#img_server").val();
	this.head_img = $("#head_img").val();
	$('#comment_list_div').data('is_edit',this.is_edit);
	this.cacheSendBox = {};
	this.attachEventDefine();
	this.delegateEvent();
};
class_show.create=function(comment_list,sort_key) {
	comment_list = comment_list || {};
	var parentObj = $('#comment_list_div');
	var sort_obj = sort_key || {};
	if($.isEmptyObject(sort_key)) {
		sort_obj = function(comment_list){
			parentObj.append(comment_list);
		};
	}else{
		sort_obj = function(comment_list){
			parentObj.prepend(comment_list);
		};
	}
	for(var i in comment_list) {
		var comment = comment_list[i];
		var divObj = comment_1st_unit.create(comment);
		sort_obj(divObj);
	}
};
//删除评论
class_show.delComment=function(obj) {
	obj = obj || {};
	var delObj = obj.delObj || {};
	var comment_id = obj.comment_id || {};
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/Api/Album/delPhotoCommentByPerson/comment_id/"+comment_id,
		success:function(json) {
			if(json.status < 0) {
				$.showError('操作失败');
				return false;
			}
			delObj.remove();
			$.showSuccess('删除成功');
		}	
	});
};

class_show.prototype = {
	delegateEvent:function() {
		var me = this;
		$("#morePage").live('click',function() {
			
			var page = $(this).data('page') || 1;
			var photo_id = $(this).data('photo_id') || {};
			//加载更多相册
			var is_success = me.loadDatas(photo_id, page + 1);
			if(is_success) {
				$(this).data('page', page + 1);
			} else {
				$(this).html("没有评论了！");
			}
			return false;
		});

	},
	attachEventDefine:function() {
		var me = this;
		$('#comment_list_div').bind({
			loadEvent:function(evt, options) {
				//清空评论
				var comment_list_div_obj = $('#comment_list_div');
				comment_list_div_obj.html('');
				//表单的其他参数设置
				options = options || {};
				
				var photo_id = options.data.photo_id || {};
				var login_account = options.data.photo_id || {};
				var up_id = photo_id;
				sendCommentBox({
						textareaObj:$("#photo_comments"),
						up_id:up_id,
						photo_id:photo_id,
						client_account:me.client_account || {},
						login_account:me.login_account || {},
						callback:function(json){
							if(json.status < 0){
								$.showError('发表失败');
							}
							//添加一条评论
							var comment_info = json.data;
							var user_info = {
									client_name:$("#client_name").val(),
									client_head_img:$("#head_img").val()
							};
							
							comment_info = $.extend(comment_info, user_info);
							var comment_id = comment_info.comment_id;
							class_show.create({comment_id:comment_info},'preapend');
							$.showSuccess('发表成功');
						}
				});
				me.loadDatas(photo_id, 1);
				$('#morePage').data('page', 1);
				$('#morePage').data('photo_id', photo_id);
			}
		});
	},
	loadDatas:function(up_id,page) {
		var me = this;
		page = page || 1;
		up_id = up_id || {};
		var is_success = true;
		$.ajax({
			type:'get',
			url:"/Api/Album/getPhotoCommentListByPerson/up_id/"+up_id+"/page/"+page,
			dataType:'json',
			async:false,
			success:function(json) {
				if(json.status < 0) {
					is_success = false;
				}else{
					class_show.create(json.data || {});
				}
			}
		});
		return is_success;
	}
};

function comment_1st_unit() {
	this.login_account = $("#login_account").val();
	this.client_account = $("#client_account").val();
	this.client_account = $("#client_account").val();
	this.album_id = $("#album_id").val();
	this.head_img = $("#head_img").val();
	
	this.delegateEvent();
}

comment_1st_unit.prototype = {
	delegateEvent:function() {
		var me=this;
		$('.reply_1st_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_1st_unit_selector');
			var click_nums = aObj.data('click_nums') || 1;
			var commentObj = aObj.parents(".comment_1st_unit_selector");
			var commentData = commentObj.data('datas') || {};
			var up_id = commentData.comment_id || {};
			var photo_id = commentData.photo_id || {};
			if(click_nums == 1) {
				aObj[0].sendBoxObj = sendCommentBox({
						textareaObj:$('textarea:first', ancestorObj),
						up_id:up_id,
						photo_id:photo_id,
						client_account:me.client_account || {},
						login_account:me.login_account || {},
						callback:function(json){
							if(json.status < 0){
								$.showError('发表失败');
								return false;
							}
							//添加一条评论
							var comment_info = json.data;
							var user_info = {
									client_name:$("#client_name").val(),
									client_head_img:$("#head_img").val()
							};
							var list_1st_obj = aObj.parents('.list_comment_main');
							var list_2nd_obj = $('#comment_2nd_list_div',list_1st_obj);
							comment_info = $.extend(comment_info, user_info);
							comment_2nd_unit.create(comment_info).prependTo(list_2nd_obj);
							aObj[0].sendBoxObj.hide();
							click_nums = aObj.data('click_nums');
							aObj.data('click_nums', click_nums + 1);
							$.showSuccess('发表成功');
						}
				});
			}
			if(click_nums % 2 == 0) {
				aObj[0].sendBoxObj.hide();
			}else if(click_nums % 2 == 1){
				aObj[0].sendBoxObj.show();
			}
			aObj.data('click_nums', click_nums + 1);
		});
		
		$(".del_1st_comment").live('click',function() {
			var parent1stObj = $(this).parents('.comment_1st_unit_selector');
			var comment_id = $(':input[name="comment_id"]', parent1stObj).val();
			var photo_id = $(':input[name="photo_id"]', parent1stObj).val();
			var dataObj = {
					comment_id:comment_id,
					photo_id:photo_id,
					leve:1,
					delObj:parent1stObj
			};
			class_show.delComment(dataObj);
		});
	}
};

comment_1st_unit.create=function(comment) {
	var me = this;
	comment = comment || {};
	var div1stObj = $('#comment_1st_unit').clone().removeAttr('id');
	if(!$("#comment_list_div").data('is_edit')) {
		$("#is_edit_del",divObj).remove();
	}
	div1stObj.data('datas',comment);
	if(!$.isEmptyObject(comment.child_items)) {
		var parentObj = $('#comment_2nd_list_div', div1stObj);
		for(var i in comment.child_items) {
			comment_2nd_unit.create(comment.child_items[i]).appendTo(parentObj);
		}
	}
	div1stObj.renderHtml({
		comment:comment
	});
	return $(div1stObj).show();
};


function comment_2nd_unit() {
	this.login_account = $("#login_account").val();
	this.client_account = $("#client_account").val();
	this.client_account = $("#client_account").val();
	this.album_id = $("#album_id").val();
	this.head_img = $("#head_img").val();
	$(".del_ids").remove();
	this.delegateEvent();
}

comment_2nd_unit.prototype = {
	//事件委托
	delegateEvent:function() {
		var me = this;
		$(".del_2nd_comment").live('click',function() {
			var parent2ndObj = $(this).parents('.comment_2nd_unit_selector');
			var child_comment_id = $(':input[name="child_comment_id"]', parent2ndObj).val();
			var child_photo_id = $(':input[name="child_photo_id"]', parent2ndObj).val();
			var dataObj = {
					comment_id:child_comment_id,
					photo_id:child_photo_id,
					leve:2,
					delObj:parent2ndObj
			};
			class_show.delComment(dataObj);
		});
	}
};

comment_2nd_unit.create=function(child_comment) {
	var me = this;
	child_comment = child_comment || {};
	var div2ndObj = $('#comment_2nd_unit').clone().removeAttr('id');
	if(!$("#comment_list_div").data('is_edit')) {
		$("#is_edit_del",div2ndObj).remove();
	}
	div2ndObj.renderHtml({
		child_comment:child_comment
	});
	return $(div2ndObj).show();
};

$(function(){
	new class_show();
	new comment_1st_unit();
	new comment_2nd_unit();
});