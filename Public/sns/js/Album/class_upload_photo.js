/**
 * 文件上传完成后的回调函数
 * @param file
 * @param serverData
 * @return
 */
var upload_nums = 0;
function uploadSuccessUserDefine(file, serverData) {
	upload_nums = upload_nums+1;
	//文件上传成功后自动关闭对话框
	$('#divStatus').html("成功上传文件数量："+upload_nums);
}

//所选文件的个数
var select_upload_nums = 0;
function selectUploadUserDefine() {
	select_upload_nums = select_upload_nums+1;
	//文件上传成功后自动关闭对话框
	$('#divStatus').html("已选择文件数量："+select_upload_nums);
}

function class_photo_upload() {
	this.client_account = $("#client_account").val();
	this.class_code = $("#class_code").val();
	this.album_id = $("#album_id").val();
	this.img_server = $('#img_server').val() || '/Public';
	this.is_edit = $("#is_edit").val();
	this.album_list_cache = {};
	this.initUpload();
	this.attachEvent();
	this.fillClassAlbumList();
};

//所选文件的个数
function filesUploadComplete() {
	art.dialog({
		id:'upload_end',
	    //background: '#600', // 背景色
	    opacity: 0.5,	// 透明度
		title:'上传结束',
		content:$(".finishupload").get(0),
		drag: false,
		fixed: true //固定定位 ie 支持不好回默认转成绝对定位
	}).lock();
	$('.aui_close',$(".aui_titleBar")).hide();
	var album_id = $("#xcid").val();
	var client_account = $("#client_account").val();
	var class_code = $("#class_code").val();
	$("#upload_photo_count").html(upload_nums);
	$("#gotoAlbum").attr('href',"/Sns/Album/Classphoto/photolist/album_id/"+album_id+"/class_code/"+class_code+"/client_account/"+client_account);
}
class_photo_upload.prototype.attachEvent=function() {
	var me = this;
	var class_code = $("#class_code").val();
	var album_id = $("#xcid").val();
	//创建相册按钮
	$("#create_album_btn").click(function() {
		//打开创建弹出层
		me.createAlbum();
	});
	$("#upload_finish_close",$(".finishupload")).click(function() {
		var dialogObj = art.dialog.list['upload_end'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		window.location.href='/Sns/Album/Classphoto/uplaodPhoto/class_code/'+class_code+'/album_id/'+album_id;
	});
	//绑定开始上传的事件
	$("#start_upload").click(function() {
		var secret_key = $("#secret_key").val();
		var xcid = $("#xcid").val() || '';
		if(xcid == '') {
			me.no_album_tip();
			return false;
		}
		var postobj = { "secret_key" : secret_key, "client_account" : me.client_account, "album_id" : xcid,"class_code":me.class_code};
		me.swfu.setPostParams(postobj);
		me.swfu.startUpload();
	});
	//创建相册
	$("#upload_create_album").click(function() {
		//关闭弹出层
		var dialogObj = art.dialog.list['no_album_tips'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
		//打开创建相册层
		me.createAlbum();
	});
};

class_photo_upload.prototype.initUpload=function() {
	var me = this;
	var settings = {
		flash_url : me.img_server + "/tool_flash/swfupload/swfupload.swf",
		upload_url: "/Sns/Album/Photoupload/index",
		post_params:{
			
		},
		file_size_limit : "8 MB",
		file_types : "*.jpg;*.gif;*.png",
		file_types_description : "全部文件",
		file_upload_limit : 10,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: false,

		// Button settings
		button_image_url: self.img_server + "/tool_flash/swfupload/images/SmallSpyGlassWithTransperancy_17x18.png",
		button_width: "200",
		button_height: "18",
		button_placeholder_id: "spanButtonPlaceHolder",
		button_text: '<span class="button">请点击这里选择要上传的文件 <span class="buttonSmall"></span></span>',
		button_text_style: '.button{font-family: Helvetica, Arial, sans-serif; font-size: 12pt;} .buttonSmall{font-size: 10pt;}',
		button_text_left_padding: 18,
		button_text_top_padding: 0,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,

		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		queue_complete_handler : queueComplete	// Queue plugin event
	};
	me.swfu = new SWFUpload(settings);
};

class_photo_upload.prototype.fillClassAlbumList=function() {
	var me = this;
	var cache_key = "album_list:" + me.class_code;
	var album_list = me.album_list_cache[cache_key] || {};
	if($.isEmptyObject(album_list)) {
		$.ajax({
			type:'get',
			url:"/Sns/Album/Classphoto/getAlbumList/class_code/" + me.class_code,
			dataType:"json",
			async:false,
			success:function(json) {
				if(json.status<0) {
					me.no_album_tip();
					return false;
				}
				album_list = json.data || {};
				for(var i in album_list) {
					var album_info = album_list[i];
					if(album_info.upd_account != me.client_account){
						delete album_list[i];
					}
				}
				if($.isEmptyObject(album_list)) {
					me.no_album_tip();
					return false;
				}
				album_list = me.album_list_cache[cache_key] = album_list || {};
			}
		});
	}
	//填充权限列表
	var xcselect_obj = $("#xcid");
	//清空对象
	xcselect_obj.html('');
	for(var i in album_list) {
		$('<option value="' + i + '">' + album_list[i]['album_name'] + '</option>').appendTo(xcselect_obj);
	}
	var album_id = $("#album_id").val();
	if(album_id != '') {
		xcselect_obj.val(album_id);
	}
	
};
class_photo_upload.prototype.no_album_tip = function() {
	var me = this;
	art.dialog({
		id:'no_album_tips',
	    //background: '#600', // 背景色
	    opacity: 0.5,	// 透明度
		title:'提示信息',
		content:$(".upload_create_album").get(0),
		drag: false,
		fixed: true //固定定位 ie 支持不好回默认转成绝对定位
	}).lock();
	//$('.aui_close',$(".aui_titleBar")).hide();
};
class_photo_upload.prototype.createAlbum = function () {
	var me = this;
	//打开创建弹出层
	$('#create_album_div').trigger('openEvent', [{
		add_post_url:'/Sns/Album/Classalbum/createAlbum',//添加相册路径
		get_grant_url:'/Sns/Album/Classalbum/getClassGrantList',//获取相册权限路径
		get_album_url:'/Sns/Album/Classalbum/getAlbum/class_code/'+me.class_code,
		add_form_info:{class_code:me.class_code,client_account:me.client_account},
		class_code:me.class_code,
		client_account:me.client_account,
		callback:function(album_list) {
			var xcselect_obj = $("#xcid");
			var album_id = {};
			for(var i in album_list) {
				$('<option value="' + i + '">' + album_list[i]['album_name'] + '</option>').prependTo(xcselect_obj);
				album_id = i; 
			}
			if(album_id != '') {
				me.album_id = album_id;
				xcselect_obj.val(album_id);
			}
		}
	}]);
};

$(document).ready(function(){
	new class_photo_upload();
});