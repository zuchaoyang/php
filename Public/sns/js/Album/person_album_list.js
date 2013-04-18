function albumlist() {
	this.client_account = $("#client_account").val();
	this.is_edit = $("#is_edit").val();
	this.no_photo_img = $("#no_photo_img").val();
	this.attachEvent();
	this.delegateEvent();
	
	this.init();
};

albumlist.prototype.init=function() {
	var is_success = this.loadMoreAlbum({
		page:1
	});
	if(!is_success) {
		//$("#insert_pos_div").hide();
		$('#more_a').html('亲，还没相册哦！');
	}
	$('#more_a').data('page', 1);
};

albumlist.prototype.attachEvent = function(){
	var me = this;
	
	$("#create_album_btn").click(function() {
		var parentObj = $(this).parents("div:first");
		//打开创建弹出层
		$('#create_album_div').trigger('openEvent', [{
			add_post_url:'/Sns/Album/Personalbum/createAlbum',//添加相册路径
			get_grant_url:'/Sns/Album/Personalbum/getGrantList',//获取相册权限路径
			get_album_url:'/Sns/Album/Personalbum/getAlbum/client_account/'+me.client_account,
			add_form_info:{client_account:me.client_account},
			client_account:me.client_account,
			callback:function(album_list) {
				me.fillAlbum(album_list);
			}
		}]);
	});
	
	//更多相册
	$("#more_a").click(function() {
		var page = $(this).data('page') || 1;
		//加载更多相册
		var is_success = me.loadMoreAlbum({
			page : page + 1
		});
		if(is_success) {
			$(this).data('page', page + 1);
		} else {
			//$(this).parents('p:first').hide();
			$(this).html('亲，没有了！');
		}
		return false;
	});
};

albumlist.prototype.delegateEvent=function() {
	var me = this;
	
	$('#dl_list').delegate('dl', 'mouseover', function() {
		if($("#is_edit").val()) {
			$('.hide_main', $(this)).show();
			$('#photo_num_dd',$(this)).hide();
		}else{
			$('.hide_main', $(this)).remove();
		}
		
	});
	
	$('#dl_list').delegate('dl', 'mouseleave', function() {
		if(!$.isEmptyObject($('.hide_main', $(this)))) {
			$('.hide_main', $(this)).hide();
			$('#photo_num_dd',$(this)).show();
		}
		
	});
	
	$('#dl_list').delegate('.edit_a_selector', 'click', function() {
		var ancestorOb = $(this).parents('dl:first');
		var album_datas = ancestorOb.data('datas') || {};
		var album_id = album_datas.album_id;
		$('#edit_album_div').trigger('openEvent',[{
			upd_post_url:'/Sns/Album/Personalbum/updAlbum',//添加相册路径
			get_grant_url:'/Sns/Album/Personalbum/getGrantList',//获取相册权限路径
			get_album_url:'/Sns/Album/Personalbum/getAlbum/client_account/'+me.client_account+'/album_id/'+album_id,
			add_form_info:{client_account:me.client_account,album_id:album_id},
			client_account:me.client_account,
			album_id:album_id,
			callback:function(datas) {
				datas = datas || {};
				$('.album_name_selector', ancestorOb).html(datas.album_name);
			}
		}]);
	});
	
	//
	$('#dl_list').delegate('.delete_a_selector', 'click', function() {
		var ancestorOb = $(this).parents('dl:first');
		var album_datas = ancestorOb.data('datas') || {};
		
		var album_id = album_datas.album_id;
		$('#del_album_div').trigger('openEvent',[{
			client_account:me.client_account,
			album_id:album_id,
			album_obj:album_datas,
			callback:function() {
				me.deleteAlbum(ancestorOb);
			}
		}]);
	});
	
};

albumlist.prototype.deleteAlbum=function(obj) {
	var me = this;
	var ancestorOb = obj;
	var album_datas = ancestorOb.data('datas') || {};
	
	var album_id = album_datas.album_id;
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/Sns/Album/Personalbum/delAlbum/client_account/" + me.client_account + "/album_id/" + album_id,
		async:false,
		success:function(json) {
			if(json.status < 0) {
				$.showError(json.info);
				return false;
			}
			$.showSuccess(json.info);
			ancestorOb.remove();
		}
	});
};

albumlist.prototype.loadMoreAlbum=function(options) {
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
		url:"/Sns/Album/Personalbum/loadMoreAlbum/client_account/" + me.client_account + serilize_params,
		dataType:"json",
		async:false,
		success:function(json) {
			if(json.status < 0) {
				is_success = false;
				return false;
			}
			me.fillAlbum(json.data || {});
		}
	});
	return is_success;
};

albumlist.prototype.fillAlbum=function(album_list) {
	var me = this;
	album_list = album_list || {};
	var img_server = $('#img_server').val();
	var parentObj = $('#dl_list');
	var dlClone = $('.clone_selector', parentObj);
	var insertPosDivObj = $('#insert_pos_div', parentObj);
	for(var i in album_list) {
		var album_datas = album_list[i] || {};
		
		album_datas = $.extend(album_datas, {'client_account':me.client_account});
		if(album_datas.album_img_path == '') {
			album_datas.album_img_path = me.no_photo_img;
		}
		var dlObj = dlClone.clone().removeClass('clone_selector').insertBefore(insertPosDivObj).show();
		dlObj.data('datas', album_datas).renderHtml(album_datas);
	}
};

$(document).ready(function() {
	new albumlist();
});