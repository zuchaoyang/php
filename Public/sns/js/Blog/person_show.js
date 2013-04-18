
function pserson_show(){
	this.hasNextPage = true;
	this.init();
	this.loadDatas(1);
	this.deleteBlog();
	this.nextPage();
	this.update_views();
};

pserson_show.create=function(comment_list, is_prepend) {
	comment_list = comment_list || {};
	var parentObj = $('#comment_list_div');
	for(var i in comment_list) {
		var comment = comment_list[i];
		var divObj = comment_1st_unit.create(comment).prependTo(parentObj);

		if(is_prepend == true){
			parentObj.prepend(divObj);
		}else{
			parentObj.append(divObj);
		}
		
	}
};

pserson_show.prototype = {
	loadDatas:function(page) {
		var me = this;
		page = page >= 1 ? page : 1;
		var blog_id = $("#blog_id").val();
		$.ajax({
			type:'get',
			url:"/Sns/Blog/PersonContent/getcommentjson/blog_id/" + blog_id +  "/page/" + page,
			dataType:'json',
			success:function(json) {
				if(!json.data) {
					me.hasNextPage = false;
					$("a","#more_comments").html("没有更多评论");
				}
				
				pserson_show.create(json.data || {});
			}
		});
	},
	
	nextPage:function(){
		var me = this;
		$("#more_comments").bind("click",function(){
			if(!me.hasNextPage) {
				return false;
			}
			
			var page = $("#page").val();
			me.loadDatas(page);
			$("#page").val(parseInt(page)+1);
		});
	},
	
	deleteBlog:function(){
		$("#del_blog").click(function(){
			$("#del_true").trigger("openEvent",[{}]);
		});
	},
	
	update_views:function(){
		var blog_id = $("#blog_id").val();
		$.ajax({
			type:"get",
			dataType:"json",
			url:"/Sns/Blog/PersonContent/update_blog_views/blog_id/" + blog_id,
			success:function(json){
				if(json.status>0){
					var num = $("#class_blog_views").html().replace(/[^0-9]/ig, "");
					var new_num = parseInt(num)+parseInt(1);
					$("#class_blog_views").html("阅读(" + new_num + ")");
				}
			}
		});
	},
	
	init:function(){
		var blog_id = $("#blog_id").val();
		var param = {};
		param.blog_id = blog_id;
		param.up_id = 0;
		var sendBoxObj = $('#1st_comments_content').sendBox({
			//加载工具条，多个选项之间使用逗号隔开，目前支持：表情：emoto，文件上传：upload(form表单提交的文件的名字为:pic)
			panels:'emote',
			//设置编辑框中的字符数限制
			chars:140,
			//限制文件上传大小,(单位是：m 兆)
			file_size:2,
//			//设置编辑框对应的样式,对应查看sendbox相应的目录对应的css文件目录下的css文件中的样式名的后缀,
//			skin:'default',
			//表单的提交类型，建议使用post的方式，支持(get, post)
			type:'post',
			//表单的post数据
			data:param,
			//表单提交到的位置
			url:'/Sns/Blog/Content/addcommentjson',
			//数据返回格式，支持：json,html等数据格式，于success回调函数的数据格式保持一致
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
				if(json.status > 0){
					pserson_show.create(json.data || {}, true);
					if($("#page").val() > 2) {
						$(".comment_1st_unit_selector:last", $("#comment_list_div")).remove();
					}
					var num = $("#comment_text_num").html().replace(/[^0-9]/ig, "");
					var new_num = parseInt(num)+parseInt(1);
					$("#comment_text_num").html("评论(" + new_num + ")");
				}else{
					$.showError("评论失败");
				}
			}
		});
	}
};

function comment_1st_unit() {
	this.delegateEvent();
	this.repalyEvent();
}

comment_1st_unit.prototype = {
	delegateEvent:function() {
	    $(".dele_1st_selector").live("click",function(){
	    	var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_1st_unit_selector');
			var comment_id = $("input[type='hidden']", ancestorObj).val();
			
			$('#is_del_comments').trigger('openEvent', [{
				data:{
					comment_id:comment_id
				},
				callback:function() {
					ancestorObj.remove();
					var num = $("#comment_text_num").html().replace(/[^0-9]/ig, "");
					var new_num = parseInt(num)-parseInt(1);
					$("#comment_text_num").html("评论(" + new_num + ")");
				}
			}]);
	    	
	    });
	},
	
	repalyEvent:function(){
		$('.reply_1st_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_1st_unit_selector');
			var click_nums = aObj.data('click_nums') || 1;
			
			var blog_id = $("#blog_id").val();
			var param = {};
			param.blog_id = blog_id;
			param.up_id = $("input[type='hidden']:last", ancestorObj).val();
			
			if(click_nums == 1) {
				aObj[0].sendBoxObj = $('textarea:first', ancestorObj).sendBox({
					panels:'emote',
					chars:140,
					file_size:2,
//					skin:'sendbox',
					type:'post',
					data:param,
					url:'/Sns/Blog/PersonContent/addcommentjson',
					dataType:'json',
					beforeSubmit:function() {
						if(sendBoxObj.getSource() == ""){
							$.showTip("请您输入评论内容");
							return false;
						}
						return true;
					},
					success:function(json) {
						if(json.status > 0) {
							for(var i in json.data){
								comment_2nd_unit.create(json.data[i]).prependTo($('#comment_2nd_list_div',ancestorObj));
							}
							var num = $("#comment_text_num").html().replace(/[^0-9]/ig, "");
							var new_num = parseInt(num)+parseInt(1);
							$("#comment_text_num").html("评论(" + new_num + ")");
						}
						aObj.data('click_nums', click_nums + 2);
						aObj[0].sendBoxObj.hide();
					}
				});
			}
			
			var sendBoxObj = aObj[0].sendBoxObj;
			if(click_nums % 2 == 0) {
				sendBoxObj.hide();
			} else {
				sendBoxObj.show();
			}
			aObj.data('click_nums', click_nums + 1);
		});
	}
};

comment_1st_unit.create=function(comment) {
	comment = comment || {};
	
	var divObj = $('#comment_1st_unit').clone().removeAttr('id');
	if(!$.isEmptyObject(comment.child_items)) {
		var parentObj = $('#comment_2nd_list_div', divObj);
		for(var i in comment.child_items) {
			comment_2nd_unit.create(comment.child_items[i]).prependTo(parentObj);
		}
	}
	divObj.renderHtml({
		comment:comment
	});
	
	return $(divObj).show();
};


function comment_2nd_unit() {
	this.delegateEvent();
	this.repalyEvent();
}

comment_2nd_unit.prototype = {
	//事件委托
	delegateEvent:function() {
	    $(".dele_2nd_selector").live("click",function(){
	    	var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_2nd_unit_selector');
			var comment_id = $("input[type='hidden']", ancestorObj).val();
			
	    	$('#is_del_comments').trigger('openEvent', [{
	    		data: {
	    			comment_id:comment_id
	    		},
	    		callback:function() {
	    			ancestorObj.remove();
	    			var num = $("#comment_text_num").html().replace(/[^0-9]/ig, "");
					var new_num = parseInt(num)-parseInt(1);
					$("#comment_text_num").html("评论(" + new_num + ")");
	    		}
	    	}]);
	    });
	},

	repalyEvent:function(){
		$('.reply_2nd_selector').live('click', function() {
			var aObj = $(this);
			var ancestorObj = aObj.closest('.comment_2nd_unit_selector');
			var ancestorObj_ = $(ancestorObj).closest('.comment_1st_unit_selector');
			var blog_id = $("#blog_id").val();
			var param = {};
			param.blog_id = blog_id;
			param.up_id = $("input[type='hidden']:last", ancestorObj_).val();
			var click_nums = aObj.data('click_nums') || 1;
			if(click_nums == 1) {
				aObj[0].sendBoxObj = $('textarea:first', ancestorObj).sendBox({
					panels:'emote',
					chars:140,
					file_size:2,
//					skin:'sendbox',
					type:'post',
					data:param,
					url:'/Sns/Blog/PersonContent/addcommentjson',
					dataType:'json',
					beforeSubmit:function() {
						if(sendBoxObj.getSource() == ""){
							$.showTip("请您输入评论内容");
							return false;
						}
						return true;
					},
					success:function(json) {
						if(json.status > 0) {
							for(var i in json.data){
								comment_2nd_unit.create(json.data[i]).prependTo($('#comment_2nd_list_div',ancestorObj_));
							}
							var num = $("#comment_text_num").html().replace(/[^0-9]/ig, "");
							var new_num = parseInt(num)+parseInt(1);
							$("#comment_text_num").html("评论(" + new_num + ")");
						}
						aObj.data('click_nums', click_nums + 2);
						aObj[0].sendBoxObj.hide();
					}
				});
			}
			
			var sendBoxObj = aObj[0].sendBoxObj;
			if(click_nums % 2 == 0) {
				sendBoxObj.hide();
			} else {
				sendBoxObj.show();
			}
			aObj.data('click_nums', click_nums + 1);
		});
	}
};

comment_2nd_unit.create=function(child_comment) {
	child_comment = child_comment || {};
	var divObj = $('#comment_2nd_unit').clone().removeAttr('id');
	divObj.renderHtml({
		child_comment:child_comment
	});
	return $(divObj).show();
};

function comment_delete() {
	this.attachEvent();
	this.attachEventUserDefine();
}

comment_delete.prototype = {
	attachEventUserDefine:function() {
	    $('#is_del_comments').bind({
	    	openEvent:function(evt, options) {
	    	    var divObj = $(this);
		    	options = options || {};
		    	$(this).data('options', options);
				art.dialog({
					id:'comment_delete_dialog',
					titile:'删除评论',
					content:$('#is_del_comments').get(0),
					init:function() {
						$(':input[name="comment_id"]', divObj).val(options.data.comment_id);
					}
				});
			},
			
			closeEvent:function() {
				var dialogObj = art.dialog.list['comment_delete_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
	    });
	},
	
	attachEvent:function() {
		var context = $('#is_del_comments');
		//确定删除按钮
		$("#del_true_comments", context).click(function() {
			var comment_id = $(':input[name="comment_id"]', context).val();
			var options = context.data('options') || {};
			$.ajax({
				type:'post',
				url:"/Sns/Blog/PersonContent/delcommentjson",
				async:false,
				data: {
					comment_id:comment_id
				},
				dataType:'json',
				success:function(json) {
					if(json.status < 0) {
						$.showError(json.info);
						return false;
					}
					if(typeof options.callback == 'function') {
						options.callback();
					}
					$("#del_false_comments").trigger("click");
				}
			});
		});
		//取消按钮
		$('#del_false_comments', context).click(function() {
			$('#is_del_comments').trigger('closeEvent');
		});
	}
};


function blog_delete(){
	this.attachEvent();
	this.attachEventUserDefine();
}

blog_delete.prototype = {
		attachEventUserDefine:function(){
			$("#is_del").bind({
				openEvent:function(evt, options) {
	    	    var divObj = $(this);
		    	options = options || {};
		    	$(this).data('options', options);
				art.dialog({
					id:'blog_delete_dialog',
					titile:'删除评论',
					content:$('#is_del').get(0)
				});
			},
			
			closeEvent:function(){
				var dialogObj = art.dialog.list['blog_delete_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
			}
			});
		},
		
		attachEvent:function(){
			var context = $('#is_del');
			//确定删除按钮
			$("#del_true", context).click(function() {
				var client_account = $("#client_account").val();
				var blog_id = $("#blog_id").val();
				$.ajax({
					type:'get',
					url:"/Sns/Blog/PersonPublish/deleteBlogAjax/blog_id/" + blog_id +  "/client_account/" + client_account,
					data:null,
					dataType:'json',
					success:function(json) {
						if(json.status < 0) {
							$.showError(json.info);
							return false;
						}
						
						//成功跳转到日志详情页
						window.location.href = "/Sns/Blog/PersonList/index/client_account/" + client_account;
					}
				});
			});
				
			//取消按钮
			$('#del_false', context).click(function() {
				$('#is_del').trigger('closeEvent');
			});
		}
};

$(function(){
	new pserson_show();
	new comment_1st_unit();
	new comment_2nd_unit();
	new comment_delete();
	new blog_delete();
});