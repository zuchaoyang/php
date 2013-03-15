
function initEdit() {
	this.grant_cache = {};
	this.attachEvent();
	this.attachEventEditAlbum();
};

initEdit.prototype.attachEvent=function(){
	var self = this;
	$("#album_name", $('#edit_album_div')).click(function() {
		var defaultval = $(this).val();
		if(defaultval == "请输入名称，你还可以输入20字") {
			$(this).val('');
		}
	}).blur(function() {
		self.validatorAlbumInfo();
	});
	
	//确定按钮
	$('#sub_btn',  $('#edit_album_div')).click(function() {
		if($("#album_name", $('#edit_album_div')).val() == ''){
			self.validatorAlbumInfo();
			return false;
		}
		var options =  $('#edit_album_div').data('options') || {};
		var params = {};
		$(".options_selector", $('#edit_album_div')).each(function() {
			var name = $(this).attr('name');
			var val = $(this).val();
			if(!name) {
				return true;
			}
			params[name] = val || "";
		});
		var tabObj = $("#edit_album_tab");
		
		params['album_name'] = $("#album_name", tabObj).val();
		params['album_explain'] = $("#album_explain",tabObj).val();
		params['grant'] = $("#grant_sel",tabObj).val();
		params['grant_name'] = $("#grant_sel",tabObj).find("option:selected").text(); ;

		$.ajax({
			type:"post",
			data:params,
			dataType:"json",
			url:"/Api/Album/updAlbumByClass",
			async:false,
			success:function(json) {
				$('#edit_album_div').trigger('closeEvent');
				if(json.status < 0) {
					$.showError(json.info);
					return false;
				}
				$.showSuccess(json.info);
				if(typeof options.callback == 'function') {
					options.callback(params);
				}
			}
		});
	});
	//取消按钮
	$('#cancel_btn',  $('#edit_album_div')).click(function() {
		var dialogObj = art.dialog.list['edit_album_dialog'];
		if(!$.isEmptyObject(dialogObj)) {
			dialogObj.close();
		}
	});
	
	$("#album_explain",  $('#edit_album_div')).keyup(function(){
		var span_count_obj = $("#span_count",$('#edit_album_div'));
		var span_count = parseInt(span_count_obj.html());
		var text_count = $(this).val().length;
		
		var count = 180-text_count;
		span_count_obj.html(count);
	});
};


initEdit.prototype.attachEventEditAlbum=function(){
	var me = this;
	$('#edit_album_div').unbind().bind({
		openEvent:function(evt, options) {
			//表单的其他参数设置
			options = options || {};
			$(this).data('options', options);
			/*
			 * options : {
			 * 	  album_id:album_id,
			 *    //注明class_code和client_account不同时出现
			 * 	  class_code:class_code,
			 *    client_account:client_account,
			 * }
			 */
			//表单提交的地址
			art.dialog({
				id:'edit_album_dialog',
			    //background: '#600', // 背景色
			    opacity: 0.5,	// 透明度
				title:'修改相册',
				content:$('#edit_album_div').get(0),
				drag: false,
				fixed: true, //固定定位 ie 支持不好回默认转成绝对定位
				init:function() {
					me.fillClassGrantList(options.class_code);
					me.initFormOptions(options);
					me.fillClassAlbumData(options.album_id, options.class_code);
				}
			}).lock();
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['edit_album_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

initEdit.prototype.fillClassGrantList=function(class_code) {
	var me = this;
	var cache_key = "grant:" + class_code;
	var grant_list = me.grant_cache[cache_key] || {};
	if($.isEmptyObject(grant_list)) {
		$.ajax({
			type:'get',
			dataType:"json",
			url:"/Api/Album/getClassGrantList/class_code/" + class_code,
			async:false,
			success:function(json) {
				grant_list = me.grant_cache[cache_key] = json.data || {};
			}
		});
	}
	//填充权限列表
	var grant_obj = $("#grant_sel");
	//清空对象
	grant_obj.html('');
	$.each(grant_list, function (n, v) {
		var option = "<option value='"+n+"'>"+v+"</option>";
		grant_obj.append(option);
	});
};

initEdit.prototype.fillClassAlbumData=function(album_id, class_code) {
	var me = this;
	var album_data = {};
	$.ajax({
		url:'get',
		url:'/Api/Album/getAlbumByClass/album_id/' + album_id + "/class_code/" + class_code,
		dataType:'json',
		async:false,
		success:function(json) {
			album_data = json.data || {};
		}
	});
	
	return me.fillAlbum(album_data);
};

initEdit.prototype.fillPersonAlbumData=function(album_id, client_account) {
	var me = this;
	var album_data = {};
	$.ajax({
		url:'get',
		url:'/Api/Album/getAlbumByPerson/album_id/' + album_id + '/client_account/' + client_account,
		dataType:'json',
		async:false,
		success:function(json) {
			album_data = json.data || {};
		}
	});
	
	return me.fillAlbum(album_data);
};

initEdit.prototype.fillAlbum=function(data) {
	data = data || {};
	//数据填充
	var tabObj = $("#edit_album_tab");
	$.each(data, function(n, v) {
		$("#album_name", tabObj).val(v.album_name);
		$("#album_explain", tabObj).html(v.album_explain);
		$('option[value="' + v.grant + '"]', $('#grant_sel', tabObj)).attr('selected', 'selected')
		var span_count_obj = $("#span_count",$('#edit_album_div'));
		var span_count = parseInt(span_count_obj.html());
		var text_count = $("#album_explain", tabObj).val().length;
		
		var count = 180-text_count;
		span_count_obj.html(count);
	});
	
};


//创建相册的弹出层
initEdit.prototype.popEditDiv=function() {
	var dialogObj = art.dialog.list['edit_album_dialog'];
	if(!$.isEmptyObject(dialogObj)) {
		dialogObj.close();
	}
	art.dialog({
		id:'edit_album_dialog',
		lock: true,
	    //background: '#600', // 背景色
	    opacity: 0.5,	// 透明度
		title:'修改相册',
		content:$('#edit_album_div').get(0),
		drag: false,
		fixed: true    //固定定位 ie 支持不好回默认转成绝对定位
	});
};

initEdit.prototype.validatorAlbumInfo=function() {
	var self = this;
	if(!$('#album_name').val()) {
		$.showError('请填写相册名称!');
		return false;
	}
	return true;
};

//增加form表单的选项信息
initEdit.prototype.initFormOptions=function(options) {
	var me = this;
	options = options || {};
	var formObj = $('form:first', $('#edit_album_div'));
	//清楚已有的自定义隐藏域
	$('.options_selector', formObj).remove();
	for(var name in options) {
		$('<input type="hidden" class="options_selector" name="' + name + '" value="' + options[name] + '">').appendTo(formObj);
	}
};


$(function(){
	new initEdit();
});