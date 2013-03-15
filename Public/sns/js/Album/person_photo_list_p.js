(function($) {
	$.sendCommentBox = function(sendOptions){
		var photo_id = sendOptions.photo_id || {};
		var login_account = sendOptions.login_account || {};
		var client_account = sendOptions.client_account || {};
		var up_id = photo_id || {};
		var paramData = {"photo_id":photo_id,"add_uid":login_account,"up_id":up_id,"client_account":client_account};
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
	this.albumObj = $("#album_list_json").val();
	this.is_edit = $("#is_edit").val();
	this.img_server = $("#img_server").val();
	
	this.delegateEvent();
	this.init();
	this.attachEvent();
	
};


photo_list.prototype.init=function() {
	this.loadMorePhoto({
		page:1
	});
	$('#more').data('page', 1);
};


photo_list.prototype.attachEvent = function(){
	var me = this;
	
	//更多相片
	$("#more", $(".list_photo_left")).click(function() {
		var page = $(this).data('page') || 1;
		//加载更多相册
		var is_success = me.loadMorePhoto({
			page : page + 1
		});
		if(is_success) {
			$(this).data('page', page + 1);
		} else {
			$(this).parents('p:first').hide();
		}
		return false;
	});
	
	//修改相册按钮
	$("#upd_album_btn", $(".list_photo_right")).click(function() {
		var album_datas = me.albumObj || {};
		$('#edit_album_div').trigger('openEvent',[{
			client_account:me.client_account,
			album_id:me.album_id,
			client_account:me.client_account,
			callback:function(datas) {
				datas = datas || {};
				me.albumObj = me.albumObj || {};
				
				me.albumObj = $.extend(me.albumObj, datas, {'upd_date':me.formatDate()});
				var list_photo_rightObj = $(".list_photo_right");
				$('.album_name', list_photo_rightObj).html(me.albumObj.album_name);
				$('.upd_date', list_photo_rightObj).html(me.albumObj.upd_date);
				$('.album_explain', list_photo_rightObj).html(me.albumObj.album_explain);
				$('.grant_name', list_photo_rightObj).html(me.albumObj.grant_name);
				$('.photo_count', list_photo_rightObj).html(me.albumObj.count);
			}
		}]);
	});
	
	//删除相册按钮
	$("#del_album_btn",$(".list_photo_right")).click(function() {
		art.dialog({
			id:'photo_del_album_dialog',
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'删除相册',
			content:$('#photo_del_album_div').get(0),
			drag: false,
			fixed: true, //固定定位 ie 支持不好回默认转成绝对定位
			init:function() {
				//初始值
			}
		}).lock();
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

photo_list.prototype.delegateEvent=function() {
	var me = this;
	//显示设为封面，删除，移动操作
	$('.publish_main').delegate('dl', 'mouseover', function() {
		if(me.is_edit) {
			$('.float_main', $(this)).show();
		}else{
			$('.float_main', $(this)).remove();
		}
		
	});
	
	//隐藏设为封面，删除，移动操作
	$('.publish_main').delegate('dl', 'mouseleave', function() {
		if(!$.isEmptyObject($('.float_main', $(this)))) {
			$('.float_main', $(this)).hide();
		}
		
	});
	
	//评论
	$(".publish_main").delegate('.comments', 'click', function(){
		var div_obj = $(this).parents('.list_photo_single');
		var photo_data = div_obj.data("datas") || {};
		var click_nums = div_obj.data('click_nums') || 1;
		var up_id = 0;
		var sendOptions = {
				textareaObj:$("#comment_area"),
				photo_id:photo_data.photo_id || {},
				login_account:me.login_account || {},
				client_account:me.client_account || {},
				up_id:up_id,
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
					$pl_count = parseInt($(".pl_count", div_obj).text());
					$pl_count = $pl_count+1;
					$(".pl_count", div_obj).text($pl_count);
				}
		};
		if(click_nums == 1) {
			div_obj[0].sendBoxObj = $.sendCommentBox(sendOptions);
		}
		div_obj.data('click_nums', click_nums + 1);
		art.dialog({
		    id: 'edit_comment_div_dialog',
		    opacity: 0.5,	// 透明度
		    content: $("#edit_comment_div").get(0),
		    drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		}).lock();
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
	var parentObj = $('.publish_main');
	var divClone = $('.clone_selector', parentObj);
	
	var insertPosDivObj = $('.insert_pos_div', parentObj);
	for(var i in photo_list) {
		var photo_datas = photo_list[i] || {};
		photo_datas = $.extend(photo_datas,{'client_account':me.client_account});
		if(!photo_datas.small_img) {
			photo_datas.small_img = img_server + "sns/images/Album/class_list_photo_n/pic01.jpg";
		}
		var dlObj = divClone.clone().removeClass('clone_selector').insertBefore(insertPosDivObj).show();
		dlObj.data('datas', photo_datas).renderHtml(photo_datas);
	}
};

photo_list.prototype._renderItem=function(data) {
	var me = this;
	var img_server = me.img_server || {};
	var parentObj = $('.publish_main');
	var divClone = $('.clone_selector', parentObj);
	
	var insertPosDivObj = $('.insert_pos_div', parentObj);

	var photo_datas = data || {};
	photo_datas = $.extend(photo_datas,{'client_account':me.client_account});
	if(!photo_datas.small_img) {
		photo_datas.small_img = img_server + "sns/images/Album/class_list_photo_n/pic01.jpg";
	}
	var dlObj = divClone.clone().removeClass('clone_selector').insertBefore(insertPosDivObj).show();
	dlObj.data('datas', photo_datas).renderHtml(photo_datas);
	
	return dlObj;
};



$(document).ready(function() {
	var object = new photo_list();
    var $container = $('#container');
    
    $container.imagesLoaded(function(){
      $container.masonry({
        itemSelector: '.list_photo_single'
        //columnWidth: 100
      });
    });
    
    //翻页插件加载
	$container.infinitescroll({
	
		// callback		: function () { console.log('using opts.callback'); },
		navSelector  : '#more',    // selector for the paged navigation 
		nextSelector : '#more a',  // selector for the NEXT link (to page 2)
		itemSelector 	: ".list_photo_single",
		animate : true,
		debug		 	: false,
		dataType	 	: 'json',
		appendCallback	: false

    }, function( response ) {
    	var jsonData = $.parseJSON(response) || {};
    	var datas = jsonData.data || {};
    	var status = jsonData.status || '-1';
    	
    	if (status == '-1') {
    	// 如果最后一页，则取消绑定事件，不在加载
    		$(window).unbind('.infscr');
    	}
    	setTimeout(function() {
    		for(var i in datas) {
            	var item = object._renderItem(datas[i]);
            	$container.masonry( 'appended', item, true ); 
    		}
    	},2000);
		
	 });

    //返回顶部相关的代码
	$(window).scroll(function(){
		if($(window).scrollTop() > 600) {
			$("#gotopbtn").css('display','').click(function(){
				$(window).scrollTop(0);
			});
		} else {
			$("#gotopbtn").css('display','none');
		}
		
	});
});