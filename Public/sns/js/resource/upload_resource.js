var upload_settings = {};
//是否开始上传
upload_settings.start_upload = false;
//上传是否完成
upload_settings.upload_success = false;

/**
 * 文件上传完成后的回调函数
 * @param file
 * @param serverData
 * @return
 */
function uploadSuccessUserDefine(file, serverData) {
	$('#submit_btn').attr('disabled', '');
	
	$('#uploaded_files').show();
	$('<span>文件:' + file.name + ' 上传成功!</span>').appendTo($('#uploaded_files'));
	
	//添加上传成功的页面信息
	$("<textarea style='display:none;' id='upload_file' name='upload_file'>" + serverData + "</textarea>").appendTo($('form:first'));
	
	upload_settings.upload_success = true;
	
	//文件上传成功后自动关闭对话框
	$('#upload_file_text').val('文件:' + file.name + ', 成功上传!');
	$('#upload_file_div').dialog('close');
}

/**
 * 开始上传文件时的处理，提交按钮要变灰
 * @param file
 * @return
 */
function uploadStartUserDefine(file) {
	$('#submit_btn').attr('disabled', 'disabled');
}

function upload_resource() {
	//flash处理对象
	this.swfu = {};
	this.img_server = $('#img_server').val() || '/Public';
	
	this.uid = $('#uid').val();
	this.secret_key = $('#secret_key').val();
	this.allow_upload_types = $('#allow_upload_types').val();
	
	this.grade();
	this.initValidator();
	this.initUpload();
	
	this.attachEvent();
	this.attachEventForUpload();
};
upload_resource.prototype.initUpload=function() {
	var self = this;
	var settings = {
		flash_url : self.img_server + "/tool_flash/swfupload/swfupload.swf",
		upload_url: "/Sns/Resource/Upload",
		post_params: {"uid" : self.uid, 'secret_key' : self.secret_key},
		file_size_limit : "200 MB",
		file_types : self.allow_upload_types,
		file_types_description : "全部文件",
		file_upload_limit : 10,
		file_queue_limit : 1,
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
		
		// The event handler functions are defined in handlers.js
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess
	};
	self.swfu = new SWFUpload(settings);
	//绑定开始上传的事件
	$('#start_upload,#start_upload_btn').click(function() {
		self.clearUploadedFile();
		self.swfu.startUpload();
		upload_settings.start_upload = true;
	});
	$('#cancel_upload').click(function() {
		self.swfu.cancelQueue();
	});
};

upload_resource.prototype.attachEvent=function() {
	var self = this;
	$('form:first').submit(function() {
		if(!self.checkFileIsUploaded()) {
			alert('请选择要上传的附件!');
			return false;
		}
		
		if(!self.checkSelectElements()) {
			return false;
		}
		
		if(!$.formValidator.pageIsValid("1")) {
			return false;
		}
		
		return true;
	});
	
	$('#submit_btn').click(function() {
		$('form:first').submit();
	});
	
	$("#my_resource").click(function () {
		window.location.href = "/Sns/Resource/Resource/my_upload_resource_list";
	});
};

upload_resource.prototype.attachEventForUpload=function() {
	var self = this;
	$('#upload_file_text').click(function() {
		$('#upload_file_div').dialog({
			autoOpen:false,
			bgiframe:true,
			/*
			buttons:{
				'确定':function() {
					$(this).dialog('close');
				},
				'取消':function() {
					$(this).dialog('close');
				}
			},
			*/
			draggable:true,
			resizable:false,
			width:550,
			minHeight:300,
			modal:true,
			zIndex:9999,
			stack:true,
			position:'center',
			dialogClass: 'alert',
			beforeclose:function(event, ui) {
				//判断用户上传的文件是否完成
				if(upload_settings.start_upload && ! upload_settings.upload_success) {
					if(confirm('文件上传未完成，强制关闭会导致文件上传失败，您确定关闭吗?')) {
						self.swfu.cancelQueue();
						return true;
					} else {
						return false;
					}
				}
				return true;
			}
		});
		$('#upload_file_div').dialog('option', 'title', '添加附件');
		$('#upload_file_div').dialog('open');
	});
};
upload_resource.prototype.clearUploadedFile=function() {
	var file_attrs = $('#upload_file').text();
	//尝试清理远程文件
	if(file_attrs) {
		$.ajax({
			type:'post',
			url:'/Sns/Resource/Upload/clearUploadFile',
			data:{'file_attrs' : file_attrs},
			dataType:'json',
			async:false,
			success:function(json) {
			}
		});
	}
	$('#upload_file').text('');
	$('#upload_file_text').val('');
};

upload_resource.prototype.checkFileIsUploaded=function() {
	return $('#upload_file').length > 0 ? true : false;
};

upload_resource.prototype.checkSelectElements=function() {
	if($('#grade_id option:first').is(':selected')) {
		alert('请选择年级!');
		return false;
	}
	if($('#subject_id option:first').is(':selected')) {
		alert('请选择科目!');
		return false;
	}
	if($('#version_id option:first').is(':selected')) {
		alert('请选择版本!');
		return false;
	}
	if($('#chapter_id option:first').is(':selected')) {
		alert('请选择章!');
		return false;
	}
	if($('#section_id option:first').is(':selected')) {
		alert('请选择节!');
		return false;
	}
	return true;
};

upload_resource.prototype.initValidator=function() {
	$.formValidator.initConfig({
		debug:false,
		submitonce:true,
		onerror:function(msg,obj,errorlist) {}
	});
	
	$('#title').formValidator({
		onshow:'请填写资源名',
		onfocus:'资源名称在5~50个字符',
		oncorrect:'&nbsp;'
	}).inputValidator({
		min:5,
		max:50,
		onerror:"资源名称在5~50个字符!"
	});
	
	$('#description').formValidator({
		onshow:'请填写资源介绍',
		onfocus:'资源介绍在400个字以内!',
		oncorrect:'&nbsp;'
	}).inputValidator({
		min:0,
		max:400,
		onerror:"资源介绍在400个字以内!"
	});
};

//得到特定选中的ID值 function1
upload_resource.prototype.grade = function() {
	$("#grade_id").change(function() {
		var grade_id = this.value;
		var resource_type = $("#resource_type").val();
		var nav_str = resource_type+"_"+grade_id;
		$("#subject_id option:gt(0)").remove();
		$("#version_id option:gt(0)").remove();
		$("#chapter_id option:gt(0)").remove();
		$("#section_id option:gt(0)").remove();
		
		$.ajax({
			type:'get',
			url:'/Sns/Resource/Resource/sectionvallist',
			data:{
				'nav_str':nav_str
			},
			dataType:'json',
			success:function(json) {
				$.each(json.subject_list,function(i,item){
					$("#subject_id").append("<option value='" + nav_str + "_" + item.subject_id + "'>" + item.subject_name + "</option>");
				});
			}
		});
	});
	
	$("#subject_id").change(function(){
		var version_id = this.value;
		var nav_str = version_id;
		$("#version_id option:gt(0)").remove();
		$("#chapter_id option:gt(0)").remove();
		$("#section_id option:gt(0)").remove();
		$.ajax({
			type:'get',
			url:'/Sns/Resource/Resource/sectionvallist',
			data:{
				'nav_str':nav_str
			},
			dataType:'json',
			success:function(json) {
				$.each(json.version_list, function(i,item) {
					$("#version_id").append("<option value='" + nav_str + "_" + item.version_id + "'>" + item.version_name + "</option>");
				});
			}
		});
	});
	
	$("#version_id").change(function(){
		var subject_id = this.value;
		var nav_str = subject_id;
		$("#chapter_id option:gt(0)").remove();
		$("#section_id option:gt(0)").remove();
		$.ajax({
			type:'get',
			url:'/Sns/Resource/Resource/sectionvallist',
			data:{
				'nav_str':nav_str
			},
			dataType:'json',
			success:function(json) {
				$.each(json.chapter_list,function(i,item){
					$("#chapter_id").append("<option value='" + nav_str + "_0_" + item.chapter_id + "'>" + item.chapter_name + "</option>");
				});
					
			}
		});
	});
	
	$("#chapter_id").change(function(){
		var nav_str = this.value;
		$("#section_id option:gt(0)").remove();
		$.ajax({
			type:'get',
			url:'/Sns/Resource/Resource/section_json',
			data:{
				'nav_str':nav_str
			},
			dataType:'json',
			success:function(json) {
				$.each(json,function(i,item){
					$("#section_id").append("<option value='" + nav_str + "_" + item.section_id + "'>" + item.section_name + "</option>");
				});
			}
		});
	});
	
	$("#quxiao_btn").click(function () {
		window.location="/Sns/Resource/Resource/synchroresource";
	});
};

$(document).ready(function(){
	new upload_resource();
});