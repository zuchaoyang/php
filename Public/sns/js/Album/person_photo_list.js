(function($) {
	$.sendCommentBox = function(sendOptions){
		var photo_id = sendOptions.photo_id || {};
		var login_account = sendOptions.login_account || {};
		var up_id = photo_id || {};
		var paramData = {"photo_id":photo_id,"add_uid":login_account,"up_id":up_id};
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
		},true);
		return sendBoxObj;
	};
})(jQuery);

function photo_list() {
	this.limitInterval = null;
	this.max_length = 140;
	
	this.login_account = $("#login_account").val();
	this.client_account = $("#client_account").val();
	this.album_id = $("#album_id").val();
	this.photo_num = $("#photo_num").val();
	$("#photo_num").remove();
	this.is_edit = $("#is_edit").val();
	this.img_server = $("#img_server").val();
	this.delegateEvent();
	this.init();
	this.attachEvent();
	
};


photo_list.prototype.init=function() {
	var me = this;
	if(!me.is_edit) {
		$(".is_edit",$(".list_photo_right")).remove();
	}
	var is_success = this.loadMorePhoto({
		page:1
	});
	if(!is_success) {
		$("#more_a",$(".see_homework")).html("没有照片了！");
	}
	$('#more_a').data('page', 1);
};


photo_list.prototype.attachEvent = function(){
	var me = this;
	
	//更多相片
	$("#more_a", $(".list_photo_left")).click(function() {
		var page = $(this).data('page') || 1;
		//加载更多相册
		var is_success = me.loadMorePhoto({
			page : page + 1
		});
		if(is_success) {
			$(this).data('page', page + 1);
		} else {
			$(this).html("没有照片了！");
		}
		return false;
	});
	
	//修改相册按钮
	$("#upd_album_btn", $(".list_photo_right")).click(function() {
		$('#edit_album_div').trigger('openEvent',[{
			album_id:me.album_id,
			client_account:me.client_account,
			callback:function(datas) {
				datas = datas || {};
				
				var albumObj = $.extend(datas, {'upd_date':me.formatDate()});
				var list_photo_rightObj = $(".list_photo_right");
				
				$('.album_name', list_photo_rightObj).html(albumObj.album_name);
				$('.upd_date', list_photo_rightObj).html(albumObj.upd_date);
				$('.album_explain', list_photo_rightObj).html(albumObj.album_explain);
				$('.grant_name', list_photo_rightObj).html(albumObj.grant_name);
				$('.photo_count', list_photo_rightObj).html(albumObj.count);
			}
		}]);
	});
	
	//删除相册按钮
	$("#del_album_btn",$(".list_photo_right")).click(function() {
		var album_datas = {photo_num:me.photo_num};
		$('#del_album_div').trigger('openEvent',[{
			client_account:me.client_account,
			album_id:me.album_id,
			album_obj:album_datas,
			callback:function() {
				me.deleteAlbum(ancestorOb);
			}
		}]);
	});
	//删除相册 确定按钮
	$(".qd_btn",$("#photo_del_album_div")).click(function() {
		var dialogObj = art.dialog.list['photo_del_album_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		var album_datas = me.albumObj || {};
		me.deleteAlbum(album_datas);
	});
	
	//删除相册  取消按钮
	$(".qx_btn",$("#photo_del_album_div")).click(function() {
		var dialogObj = art.dialog.list['photo_del_album_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
	
	//计算器 
	$('.textarea', $("#edit_comment_div")).keypress(function(evt) {
		var content = $.trim($(this).val()).toString();
		if(content.length >= me.max_length) {
			var keyCode = evt.keyCode || evt.which;
			//字符超过限制后只有Backspace键能够按
			if(keyCode != 8) {
				$.showError('评论不能超过140字!');
				return false;
			}
		}
	}).focus(function() {
		me.limitInterval = setInterval(function() {
			me.reflushCounter();
		}, 10);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});

};

//添加评论数
//渲染页面
photo_list.prototype.reflushCounter=function() {
	var me = this;
	var context = $('#edit_comment_div');
	
	var len = $.trim($('.textarea', context).val()).toString().length;
	var show_nums = me.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$(".f_orange", context).html(show_nums);
};
//格式化时间
photo_list.prototype.formatDate=function () {
	var currentDate = new Date();
	var   year=currentDate.getFullYear();     
    var   month=currentDate.getMonth()+1;     
    var   date=currentDate.getDate();     
     
    return   year+"-"+month+"-"+date;     
};

photo_list.prototype.delegateEvent=function() {
	var me = this;
	//相册详情
	$(".xcxq_zk").toggle(
	  function () {
		  $("#icon_img").attr('class','icon_up');
		  var btnObj = $(this);
			art.dialog({
				id:'xcxq_zk_dialog',
				follow:btnObj.get(0),
			    //background: '#600', // 背景色
			    opacity: 0.5,	// 透明度
				title:'相片详情',
				content:$("#xcxq_div").get(0),
				drag: false,
				fixed: false //固定定位 ie 支持不好回默认转成绝对定位
			});
			$('.aui_close',$(".aui_titleBar")).hide();return false;
	  },
	  function () {
		  $("#icon_img").attr('class','icon_down');
		  var dialogObj = art.dialog.list['xcxq_zk_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
	  }
	);
	
	//显示设为封面，删除，移动操作
	$('.list_photo_left').delegate('dl', 'mouseover', function() {
		if(me.is_edit) {
			$('.float_main', $(this)).show();
		}else{
			$('.float_main', $(this)).remove();
		}
		
	});
	
	//隐藏设为评论操作
	$('.list_photo_left').delegate('dl', 'mouseleave', function() {
		if(!$.isEmptyObject($('.float_main', $(this)))) {
			$('.float_main', $(this)).hide();
		}
		
	});
	

	//评论
	$(".list_photo_left").delegate('.comments', 'click', function(){
		var dl_obj = $(this).parents('dl:first');
		var photo_data = dl_obj.data('datas') || {};
		var click_nums = dl_obj.data('click_nums') || 1;
		var sendOptions = {
				textareaObj:$("#comment_area"),
				photo_id:photo_data.photo_id || {},
				login_account:me.login_account || {},
				client_account:me.client_account || {},
				callback:function(jsonData){
					var dialogObj = art.dialog.list['edit_comment_div_dialog'];
					if(!$.isEmptyObject(dialogObj)) {
						dialogObj.close();
					}
					if(jsonData.status < 0) {
						$.showError(jsonData.info);
						return false;
					}
					$.showSuccess(jsonData.info);
					$pl_count = parseInt($(".pl_count", dl_obj).text());
					$pl_count = $pl_count+1;
					$(".pl_count", dl_obj).text($pl_count);
				}
		};
		if(click_nums == 1) {
			dl_obj[0].sendBoxObj = $.sendCommentBox(sendOptions);
		}
		dl_obj.data('click_nums', click_nums + 1);
		art.dialog({
		    id: 'edit_comment_div_dialog',
		    opacity: 0.5,	// 透明度
		    content: $("#edit_comment_div").get(0),
		    drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		}).lock();
	});
	//修改照片名称
	$('.list_photo_left').delegate('.photo_name','click',function() {
		var photo_name = $(this).html();
		var parentObj = $(this).parents('div:first');
		$('span',parentObj).hide();
		$("<input type='text' name='photo_name' class='upd_photo_name text_boder' maxlength=15 value='"+photo_name+"'/>").appendTo(parentObj).focus();
	});
	$('.list_photo_left').delegate('.upd_photo_name','focusout',function() {
		var parentObj = $(this).parents('div:first');
		var parentDlObj = $(this).parents('dl:first');
		var photo_id = parentDlObj.attr('id') || {};
		var photo_name = $('.upd_photo_name',parentObj).val() || {};
		if($.isEmptyObject(photo_name)) {
			$.showError('相片名称不为空');
			return false;
		}
		if(me.updPhotoName(photo_id, photo_name)) {
			$('.photo_name',parentObj).text(photo_name);
			$.showSuccess('更新成功');
		}else{
			$.showError('更新失败');
		}
		$('.upd_photo_name',parentObj).remove();
		$('span',parentObj).show();
	});
};
//修改相片名称
photo_list.prototype.updPhotoName=function(photo_id,photo_name){
	photo_id = photo_id || {};
	photo_name = photo_name || {};
	var isTrue = true;
	$.ajax({
		type:"post",
		data:{'photo_id':photo_id,'photo_name':photo_name},
		dataType:"json",
		url:"/Api/Album/updPhotoNameByPerson",
		success:function(json) {
			if(json.status < 0) {
				isTrue = false;
			}
		}
	});
	
	return isTrue;
};

photo_list.prototype.fillAlbumList = function (album_list) {
	var me = this;
	var album_list = album_list || {};
	var move_obj = $("#move_photo_div");
	var a_str = "";
	for(var i in album_list) {
		var album_info = album_list[i];
		a_str += '<a id="'+album_info.album_id+'" href="javascript:;"><span>'+album_info.album_name+'</span></a>';
	}
	$("p",move_obj).html('');
	$(a_str).appendTo($("p",move_obj));
};

//删除相册
photo_list.prototype.deleteAlbum=function() {
	var me = this;
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/Api/Album/delAlbumByPerson/client_account/" + me.client_account + "/album_id/" + me.album_id,
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
			window.location.href = "/Sns/Album/Person/albumlist/client_account/" + me.client_account;
		}
	});
};

photo_list.prototype.loadMorePhoto=function(options) {
	var me = this;
	options = options || {};
	//serilize
	var serilize_params = "";
	for(var name in options) {
		if(!options[name]) {
			continue;
		}
		serilize_params += "/" + name + "/" + options[name];
	}
	var is_success = true;
	$.ajax({
		type:"get",
		url:"/Api/Album/getPersonPhotoListByAlbumId/client_account/" + me.client_account + '/album_id/'+ me.album_id + '/client_account/' + me.client_account + serilize_params,
		dataType:"json",
		async:false,
		success:function(json) {
			if(json.status < 0) {
				is_success = false;
				return false;
			}
			me.fillPhotoList(json.data || {});
		}
	});
	return is_success;
};

photo_list.prototype.fillPhotoList=function(photo_list) {
	var me = this;
	photo_list = photo_list || {};
	var img_server = me.img_server || {};
	var parentObj = $('.list_photo_left');
	var dlClone = $('.clone_selector', parentObj);

	var insertPosDivObj = $('.insert_pos_div', parentObj);
	for(var i in photo_list) {
		var photo_datas = photo_list[i] || {};
		photo_datas = $.extend(photo_datas,{'client_account':me.client_account,'client_account':me.client_account});
		if(!photo_datas.small_img) {
			photo_datas.small_img = img_server + "sns/images/Album/class_list_photo_n/pic01.jpg";
		}
		var dlObj = dlClone.clone();
		
		dlObj.removeClass('clone_selector').insertBefore(insertPosDivObj).show();
		dlObj.attr('id',i);
		dlObj.data('datas', photo_datas).renderHtml(photo_datas);
	}
};

$(document).ready(function() {
	new photo_list();
});