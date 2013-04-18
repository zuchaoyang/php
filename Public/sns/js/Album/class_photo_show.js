function photoShowCls() {
	this.limitInterval = null;
	this.max_length = 20;
	this.client_account = $("#client_account").val();
	this.login_account = $("#login_account").val();
	this.class_code = $("#class_code").val();
	this.album_id = $("#album_id").val();
	this.photo_id = $("#photo_id").val();
	this.is_edit = $("#is_edit").val();
	this.showSize = $("#photo_num").val();
	this.preloadSize = 10;
	this.delegateEvent();
	this.init();
};
photoShowCls.prototype.init=function() {
	
	var self = this;
	if(!self.is_edit) {
		$("#photo_edit").remove();
	}
	$('#xpxq_div').trigger('openEvent', [{
		galleria_config:{
			client_account:self.client_account,
			album_id:self.album_id,
			url:'/Sns/Album/Classphoto/getPhotosByAlbumId/js_page/1',
			showSize:self.showSize,
			preloadSize:self.preloadSize,
			photo_id: self.photo_id
		},
		param_data:{
			client_account:self.client_account,
			class_code:self.class_code,
			album_id:self.album_id
		},
		callback_url:'/Sns/Album/Classphoto/getPhotoInfoByPhotoId',
		is_edit:self.is_edit
		
	}]);
};
photoShowCls.prototype.delegateEvent=function() {
	var self = this;
	//移动相片
	$('#photo_edit').delegate("#move_evt", 'click', function() {
		var divObj = $(this).parents("div:first");
		$("#move_photo_div").data('photoData',divObj.data("datas"));
		self.getAlbumList();
		var btnObj = $(this);
		art.dialog({
			id:'move_photo_dialog',
			follow:btnObj.get(0),
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'移动照片',
			content:$("#move_photo_div").get(0),
			drag: false,
			fixed: false //固定定位 ie 支持不好回默认转成绝对定位
		});
		//$('.aui_close',$(".aui_titleBar")).hide();return false;
		
	});
	
	//相片详情
	$(".xpxq_a").toggle(
		  function () {
		   $("#icon_img").attr('class','icon_up');
		   var btnObj = $(this);
			art.dialog({
				id:'xpxq_dialog',
				follow:btnObj.get(0),
				//background: '#600', // 背景色
				opacity: 0.5,	// 透明度
				title:'相片详情',
				content:$("#xpxq_div").get(0),
				drag:false,
				fixed:false //固定定位 ie 支持不好回默认转成绝对定位
			});
			$('.aui_close',$(".aui_titleBar")).hide();return false;
		  },
		  function () {
			  $("#icon_img").attr('class','icon_down');
			  var dialogObj = art.dialog.list['xpxq_dialog'];
				if(!$.isEmptyObject(dialogObj)) {
					dialogObj.close();
				}
		  }
	);
	//移动相片
	$("#move_photo_div").delegate("a", 'click', function(){
		var me = $(this);
		var to_album_id = $(this).attr('id');
		art.dialog({
		    id: 'move',
		    content: '你确定要移动相片到〈'+me.text()+'〉',
		    button: [
		        {
		            name: '确定',
		            callback: function () {
		        		this.close();
			        	var parentObj = me.parents("div:first");
			        	var photoData = parentObj.data('photoData');
			        	self.movePhoto(to_album_id,photoData);
		                return false;
		            },
		            focus: true
		        },
		        {
		            name: '取消'
		        }
		    ]
		});
	});
	
	//删除相片
	$("#photo_edit").delegate('#del_evt','click',function() {
		$(".tcc_msg_center",$(".tcc_msg")).data('datas', $(this).parents("div:first").data('datas'));
		art.dialog({
			id:'del_photo_dialog',
		    //background: '#600', // 背景色
		    opacity: 0.5,	// 透明度
			title:'移动照片',
			content:$(".tcc_msg").get(0),
			drag: false,
			fixed: true //固定定位 ie 支持不好回默认转成绝对定位
		});
	});
	
	//删除相片 确定
	$(".tcc_msg").delegate('.qd_btn','click',function() {
		var obj = $(this).parents("div:first");
		self.delPhoto(obj);
	});
	//删除相片 取消
	$(".tcc_msg").delegate('.qx_btn','click',function() {
		var dialogObj = art.dialog.list['del_photo_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
	//设置相册封面
	$("#photo_edit").delegate('#set_img_evt','click',function() {
		var obj = $(this).parents("div:first");
		self.setAlbumImg(obj);
	});
	
	//添加相片描述
	$(".photo_name").delegate('#description', 'click', function(){
		var desscriptionObj = $(".description",$(".photo_name"));
		if(desscriptionObj.is(':hidden')) {
			$(".description",$(".photo_name")).show();
			return false;
		}
		$(".description",$(".photo_name")).hide();
	});
	//取消相片描述
	$(".photo_name").delegate('.gray_btn', 'click', function(){
		var desscriptionObj = $(".description",$(".photo_name"));
		if(!desscriptionObj.is(':hidden')) {
			$(".description",$(".photo_name")).hide();
			return false;
		}
	});
	//描述计算器
	$(".photo_name").delegate('.text', 'keypress', function(evt){
		var content = $.trim($(this).val()).toString();
		if(content.length >= self.max_length) {
			var keyCode = evt.keyCode || evt.which;
			//字符超过限制后只有Backspace键能够按
			if(keyCode != 8) {
				$.showError('相片描述不能超过20字!');
				return false;
			}
		}
	});
	$(".photo_name").delegate('.text', 'focus', function(evt){
		self.limitInterval = setInterval(function() {
			self.reflushCounter();
		}, 10);
	});
	$(".photo_name").delegate('.text', 'blur', function(evt){
		clearInterval(self.limitInterval);
	});
	//添加描述
	$(".photo_name").delegate('.green_btn', 'click', function() {
		if($('.text',$('.photo_name')).val() == '') {
			$.showError('描述内容不可为空！');
			return false;
		}
		self.adddescription();
		$(".description",$(".photo_name")).hide();
		$("#description").html('<p><span>描述：</span><font id="description_font">'+$('.text',$('.photo_name')).val()+'</font></p>');
	});
	
	//评论
	$('.comment_reply_selector').live('click', function(){
		$.sendBox();
	});
	
	//评论删除
	$('.comment_delete_selector'). live('click', function(){
		var parentObj = $(this).parents('div:first');
		var pl_info = parentObj.data('datas');
		var comment_id = pl_info.comment_id;
		$.ajax({
			type:"get",
			dataType:"json",
			url:"/Sns/Album/Photocomments/deletePhotoCommentsAjax/comment_id/"+comment_id,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
				}
				parentObj.remove();
				$.showSuccess(json.info);
			}
		});
	});
	//移除提示
	$('.efpClew'). live('click', function(){
		$(this).remove();
	});
};
//添加相片描述
photoShowCls.prototype.adddescription = function() {
	var description = $.trim($('.text',$('.photo_name')).val()).toString();
	var photo_info = $("#photo_edit").data('datas');
	var photo_id = photo_info.photo_id;
	$.ajax({
		type:"post",
		url:"/Sns/Album/Classphoto/addPhotoDescription",
		data:{'photo_id':photo_id,'description':description},
		dataType:"json",
		async:true,
		success:function(json) {
			if(json.status<0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
		}
	});
	
};
photoShowCls.prototype.reflushCounter=function() {
	var self = this;
	var context = $('.photo_name');
	
	var len = $.trim($('.text', context).val()).toString().length;
	var show_nums = this.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$("#span_count", context).html(show_nums);
};

//设为封面
photoShowCls.prototype.setAlbumImg=function(obj) {
	var self = this;
	var dlObj = obj || {};
	var photo_datas = dlObj.data('datas') || {};
	var album_img = photo_datas.file_small;
	album_img = album_img || {};
	$.ajax({
		type:"post",
		data:{'album_id':self.album_id,'album_img':album_img,'class_code':self.class_code},
		dataType:"json",
		async:false,
		url:'/Sns/Album/Classalbum/setAlbumImg',//"/Api/Album/setAlbumImgByClass",
		success:function(json) {
			if(json.status<0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
		}
	});
};
//删除相片
photoShowCls.prototype.delPhoto=function(obj) {
	var self = this;
	var ancestorOb = obj;
	var photo_datas = ancestorOb.data('datas') || {};
	var photo_id = photo_datas.photo_id;
	$.ajax({
		type:"get",
		dataType:"json",
		url:'/Sns/Album/Classphoto/delPhoto/class_code/'+self.class_code+"/photo_id/"+photo_id,//"/Api/Album/delPhotoByClass/class_code/" + self.class_code + "/photo_id/" + photo_id,
		async:true,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			if(typeof photo_datas.callback == 'function') {
				photo_datas.callback();
			}
			var dialogObj = art.dialog.list['del_photo_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
			$.showSuccess(json.info);
		}
	});
};

//照片移动//ok
photoShowCls.prototype.getAlbumList=function() {
	var self = this;
	var album_list_tmp = this.album_list || {};
	if($.isEmptyObject(album_list_tmp)) {
		$.ajax({
			type:"get",
			dataType:"json",
			url:"/Sns/Album/Classphoto/getAlbumList/class_code/"+self.class_code,
			async:false,
			success:function(json) {
				if(json.status < 0) {
					$.showError(json.info);
				}
				album_list_tmp = json.data;
				delete album_list_tmp[self.album_id];
			}
		});
	}
	self.fillAlbumList(album_list_tmp);
	
};
//ok
photoShowCls.prototype.fillAlbumList = function (album_list) {
	var self = this;
	var album_list = album_list || {};
	var move_obj = $("#move_photo_div");
	var a_str = "";
	for(var i in album_list) {
		var	album_info = album_list[i];
		a_str += '<a id="'+album_info.album_id+'" href="javascript:;"><span>'+album_info.album_name+'</span></a>';
	}
	$("p",move_obj).html('');
	$(a_str).appendTo($("p",move_obj));
};

//移动照片
photoShowCls.prototype.movePhoto=function(to_album_id, photoData) {
	var self = this;
	to_album_id = to_album_id || {};
	var photo_datas = photoData || {};
	var photo_id = photo_datas.photo_id || {};
	$.ajax({
		type:"post",
		data:{'to_album_id':to_album_id,'photo_id':photo_id},
		dataType:"json",
		url:"/Sns/Album/Classphoto/movePhoto",
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
        	if(typeof photo_datas.callback == 'function') {
				photo_datas.callback();
			}
			var dialogObj = art.dialog.list['move_photo_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
			$.showSuccess(json.info);
		}
	});
};

$(document).ready(function() {
	new photoShowCls();
});