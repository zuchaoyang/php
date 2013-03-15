
function create_album() {
	this.default_album_name = "请输入名称，你还可以输入20字";
	this.limitInterval = null;
	this.max_length = 180;
	
	this.grant_cache = {};
	this.attachEvent();
	this.attachEventUserDefine();
};

create_album.prototype.attachEvent=function(){
	var me = this;
	var context = $('#create_album_div');
	
	$("#album_name", context).focus(function() {
		var album_name = $.trim($(this).val());
		if(album_name == me.default_album_name) {
			$(this).val('');
		}
	}).blur(function() {
		var album_name = $.trim($(this).val());
		if(!album_name) {
			$(this).val(me.default_album_name);
		}
	});
	
	//绑定form的提交事件
	$('form:first', context).submit(function() {
		var action = $(this).attr('action');
		$(this).ajaxSubmit({
			type:'post',
			url:action,
			dataType:'json',
			beforeSubmit:function() {
				//表单的验证函数
				return me.validatorForm();
			},
			success:function(json) {
				$('#create_album_div').trigger('closeEvent');
				var options =  context.data('options') || {};
				var album_list = {};
				$.ajax({
					type:"get",
					url:"/Api/Album/getAlbumByClass/class_code/" + options.class_code + '/album_id/' + json.data,
					dataType:"json",
					async:false,
					success:function(json) {
						if(typeof options.callback == 'function') {
							options.callback(json.data || {});
						}
					}
				});
				
			}
		});
		
		return false;
	});
	
	//确定按钮
	$('#sub_btn',  context).click(function() {
		$('form:first', context).submit();
	});
	
	//取消按钮
	$('#cancel_btn',  context).click(function() {
		$('#create_album_div').trigger('closeEvent');
	});
	
	//计算器
	$('#album_explain', context).keypress(function(evt) {
		var content = $.trim($('#album_explain').val()).toString();
		if(content.length >= me.max_length) {
			var keyCode = evt.keyCode || evt.which;
			//字符超过限制后只有Backspace键能够按
			if(keyCode != 8) {
				$.showError('相册描述不能超过180字!');
				return false;
			}
		}
	}).focus(function() {
		me.limitInterval = setInterval(function() {
			me.reflushCounter();
		}, 1000);
	}).blur(function() {
		clearInterval(me.limitInterval);
	});
	
};

create_album.prototype.reflushCounter=function() {
	var me = this;
	var context = $('#create_album_div');
	
	var len = $.trim($('#album_explain', context).val()).toString().length;
	var show_nums = me.max_length - len;
	show_nums = show_nums > 0 ? show_nums : 0;
	$("#span_count", context).html(show_nums);
};
//清除弹出层信息
create_album.prototype.clearInfo = function() {
	var me = this;
	var create_album_obj = $('#create_album_div');
	$("#album_name",create_album_obj).val(me.default_album_name);
	$("#album_explain",create_album_obj).val('');
};
create_album.prototype.attachEventUserDefine=function() {
	var me = this;
	$('#create_album_div').bind({
		openEvent: function(evt, options) {
			options = options || {};
			//获取权限设置列表
			$(this).data('options', options);
			//表单提交的地址
			art.dialog({
				id:'create_album_dialog',
			    opacity: 0.5,	// 透明度
				title:'添加相册',
				content:$('#create_album_div').get(0),
				drag: false,
				fixed: true,	//固定定位 ie 支持不好回默认转成绝对定位
				init:function() {
					me.clearInfo();
					me.fillClassGrantList(options.class_code);
					me.initFormOptions(options);
					me.reflushCounter();
				}
			}).lock();
		},
		closeEvent:function() {
			var dialogObj = art.dialog.list['create_album_dialog'];
			if(!$.isEmptyObject(dialogObj)) {
				dialogObj.close();
			}
		}
	});
};

create_album.prototype.fillClassGrantList=function(class_code) {
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
	var grant_obj = $("#grant_sel", $('#create_album_div'));
	//清空对象
	grant_obj.html('');
	for(var i in grant_list) {
		$('<option value="' + i + '">' + grant_list[i] + '</option>').appendTo(grant_obj);
	}
};

//增加form表单的选项信息
create_album.prototype.initFormOptions=function(options) {
	var me = this;
	options = options || {};
	var formObj = $('form:first', $('#create_album_div'));
	//清楚已有的自定义隐藏域
	$('.options_selector', formObj).remove();
	for(var name in options) {
		if(typeof options[name] == 'function') {
			continue;
		}
		$('<input type="hidden" class="options_selector" name="' + name + '" value="' + options[name] + '">').appendTo(formObj);
	}
};

create_album.prototype.validatorForm=function() {
	
	return true;
};


$(function(){
	new create_album();
});