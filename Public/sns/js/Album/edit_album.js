
function initEdit() {
	this.grant_cache = {};
	this.upd_post_url = {};
	this.limitInterval = null;
	this.max_length = 180;
	this.attachEvent();
	this.attachEventEditAlbum();
};

initEdit.prototype.attachEvent=function(){
	var me = this;
	$("#album_name", $('#edit_album_div')).focus(function() {
		var defaultval = $(this).val();
		if(defaultval == "请输入名称，你还可以输入20字") {
			$(this).val('');
		}
	}).blur(function() {
		me.validatorAlbumInfo();
	});
	
	//确定按钮
	$('#sub_btn',  $('#edit_album_div')).click(function() {
		if($("#album_name", $('#edit_album_div')).val() == ''){
			me.validatorAlbumInfo();
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
			url:me.upd_post_url,
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

initEdit.prototype.reflushCounter=function() {
	var me = this;
	var context = $('#edit_comment_div');
	
	var len = $.trim($('#album_explain', context).val()).toString().length;
	var show_nums = me.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$("#span_count", context).html(show_nums);
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
				//post提交路径
					me.upd_post_url = options.upd_post_url;
					me.fillClassGrantList(options);
					me.fillClassAlbumData(options);
					
					me.initFormOptions(options);
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

initEdit.prototype.fillClassGrantList=function(options) {
	options = options || {};
	var me = this;
	var cache_key = "grant:" + options.class_code;
	var grant_list = me.grant_cache[cache_key] || {};
	if($.isEmptyObject(grant_list)) {
		$.ajax({
			type:'get',
			dataType:"json",
			url:options.get_grant_url,
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

initEdit.prototype.fillClassAlbumData=function(options) {
	options = options || {};
	var me = this;
	var album_data = {};
	$.ajax({
		type:'get',
		url:options.get_album_url,
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
	var me = this;
	var album_name = $('#album_name').val();
	if(!album_name || album_name==me.default_album_name) {
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
	for(var name in options.add_form_info) {
		$('<input type="hidden" class="options_selector" name="' + name + '" value="' + options[name] + '">').appendTo(formObj);
	}
};


$(function(){
	new initEdit();
});